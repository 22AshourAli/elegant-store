<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class BackupController extends Controller
{
    public function index()
    {
        $backupName = config('backup.backup.name', 'elegant-store-backup');
        $backupDir = storage_path('app/' . $backupName);
        $files = [];

        if (is_dir($backupDir)) {
            $files = collect(File::files($backupDir))
                ->filter(fn($f) => $f->getExtension() === 'zip')
                ->map(fn($f) => [
                    'name' => $f->getFilename(),
                    'size' => $f->getSize(),
                    'size_formatted' => $this->formatBytes($f->getSize()),
                    'date' => $f->getMTime(),
                ])
                ->sortByDesc('date')
                ->values();
        }

        return view('admin.backups.index', compact('files'));
    }

    public function create()
    {
        try {
            $backupName = config('backup.backup.name', 'elegant-store-backup');
            $backupDir = storage_path('app/' . $backupName);

            if (!is_dir($backupDir)) {
                mkdir($backupDir, 0755, true);
            }

            $timestamp = now()->format('Y-m-d-H-i-s');
            $filename = $backupName . '-' . $timestamp . '.sql';
            $filepath = $backupDir . '/' . $filename;

            $pdo = DB::connection()->getPdo();
            $driver = DB::connection()->getDriverName();

            if ($driver === 'mysql') {
                $sql = $this->dumpMySQL($pdo);
            } elseif ($driver === 'pgsql') {
                $sql = $this->dumpPostgreSQL($pdo);
            } elseif ($driver === 'sqlite') {
                $sql = $this->dumpSQLite($pdo);
            } else {
                throw new \RuntimeException("Unsupported database driver: $driver");
            }

            file_put_contents($filepath, $sql);

            $zipPath = $backupDir . '/' . $backupName . '-' . $timestamp . '.zip';
            $zip = new \ZipArchive();

            if ($zip->open($zipPath, \ZipArchive::CREATE) === true) {
                $zip->addFile($filepath, $filename);
                $zip->close();
                unlink($filepath);
            } else {
                throw new \RuntimeException('Failed to create zip archive');
            }

            return redirect()->route('admin.backups.index')->with('success', __('global.backup_created'));
        } catch (\Throwable $e) {
            return redirect()->route('admin.backups.index')->with('error', __('global.backup_failed') . ': ' . $e->getMessage());
        }
    }

    private function dumpMySQL(\PDO $pdo): string
    {
        $sql = "-- Elegant Store Database Backup\n";
        $sql .= "-- Generated: " . now()->toDateTimeString() . "\n";
        $sql .= "-- Driver: MySQL\n\n";
        $sql .= "SET FOREIGN_KEY_CHECKS = 0;\n\n";

        $tables = $pdo->query("SHOW TABLES")->fetchAll(\PDO::FETCH_COLUMN);

        foreach ($tables as $table) {
            $stmt = $pdo->query("SHOW CREATE TABLE `$table`");
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            $createStmt = $row['Create Table'];

            $sql .= "--\n-- Table structure for `$table`\n--\n";
            $sql .= "DROP TABLE IF EXISTS `$table`;\n";
            $sql .= $createStmt . ";\n\n";

            $rows = $pdo->query("SELECT * FROM `$table`")->fetchAll(\PDO::FETCH_ASSOC);
            if (empty($rows)) continue;

            $columns = array_keys($rows[0]);
            $columnList = '`' . implode('`, `', $columns) . '`';

            $sql .= "--\n-- Dumping data for `$table`\n--\n";

            $chunks = array_chunk($rows, 200);
            foreach ($chunks as $chunk) {
                $values = [];
                foreach ($chunk as $row) {
                    $escaped = [];
                    foreach ($row as $value) {
                        if ($value === null) {
                            $escaped[] = 'NULL';
                        } else {
                            $escaped[] = $pdo->quote($value);
                        }
                    }
                    $values[] = '(' . implode(', ', $escaped) . ')';
                }
                $sql .= "INSERT INTO `$table` ($columnList) VALUES\n" . implode(",\n", $values) . ";\n";
            }
            $sql .= "\n";
        }

        $sql .= "SET FOREIGN_KEY_CHECKS = 1;\n";

        return $sql;
    }

    private function dumpPostgreSQL(\PDO $pdo): string
    {
        $sql = "-- Elegant Store Database Backup\n";
        $sql .= "-- Generated: " . now()->toDateTimeString() . "\n";
        $sql .= "-- Driver: PostgreSQL\n\n";

        $tables = $pdo->query("SELECT tablename FROM pg_tables WHERE schemaname = 'public' ORDER BY tablename")->fetchAll(\PDO::FETCH_COLUMN);

        foreach ($tables as $table) {
            $stmt = $pdo->query("SELECT column_name, data_type, is_nullable, column_default FROM information_schema.columns WHERE table_schema = 'public' AND table_name = '$table' ORDER BY ordinal_position");
            $columns = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $colDefs = [];
            foreach ($columns as $col) {
                $def = '"' . $col['column_name'] . '" ' . $col['data_type'];
                if ($col['is_nullable'] === 'NO') $def .= ' NOT NULL';
                if ($col['column_default'] !== null) $def .= ' DEFAULT ' . $col['column_default'];
                $colDefs[] = $def;
            }

            $colNames = array_map(fn($c) => '"' . $c['column_name'] . '"', $columns);
            $colList = implode(', ', $colNames);

            $sql .= "--\n-- Table structure for \"$table\"\n--\n";
            $sql .= "DROP TABLE IF EXISTS \"$table\" CASCADE;\n";
            $sql .= "CREATE TABLE \"$table\" (\n  " . implode(",\n  ", $colDefs) . "\n);\n\n";

            $rows = $pdo->query("SELECT * FROM \"$table\"")->fetchAll(\PDO::FETCH_ASSOC);
            if (empty($rows)) continue;

            $sql .= "--\n-- Dumping data for \"$table\"\n--\n";

            $chunks = array_chunk($rows, 200);
            foreach ($chunks as $chunk) {
                $values = [];
                foreach ($chunk as $row) {
                    $escaped = [];
                    foreach ($row as $value) {
                        if ($value === null) {
                            $escaped[] = 'NULL';
                        } else {
                            $escaped[] = $pdo->quote($value);
                        }
                    }
                    $values[] = '(' . implode(', ', $escaped) . ')';
                }
                $sql .= "INSERT INTO \"$table\" ($colList) VALUES\n" . implode(",\n", $values) . ";\n";
            }
            $sql .= "\n";
        }

        return $sql;
    }

    private function dumpSQLite(\PDO $pdo): string
    {
        $sql = "-- Elegant Store Database Backup\n";
        $sql .= "-- Generated: " . now()->toDateTimeString() . "\n";
        $sql .= "-- Driver: SQLite\n\n";
        $sql .= "PRAGMA foreign_keys = OFF;\n\n";

        $tables = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%' ORDER BY name")->fetchAll(\PDO::FETCH_COLUMN);

        foreach ($tables as $table) {
            $stmt = $pdo->query("SELECT sql FROM sqlite_master WHERE type='table' AND name='$table'");
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            $createStmt = $row['sql'];

            $sql .= "--\n-- Table structure for \"$table\"\n--\n";
            $sql .= "DROP TABLE IF EXISTS \"$table\";\n";
            $sql .= $createStmt . ";\n\n";

            $rows = $pdo->query("SELECT * FROM \"$table\"")->fetchAll(\PDO::FETCH_ASSOC);
            if (empty($rows)) continue;

            $columns = array_keys($rows[0]);
            $columnList = '"' . implode('", "', $columns) . '"';

            $sql .= "--\n-- Dumping data for \"$table\"\n--\n";

            $chunks = array_chunk($rows, 200);
            foreach ($chunks as $chunk) {
                $values = [];
                foreach ($chunk as $row) {
                    $escaped = [];
                    foreach ($row as $value) {
                        if ($value === null) {
                            $escaped[] = 'NULL';
                        } else {
                            $escaped[] = $pdo->quote($value);
                        }
                    }
                    $values[] = '(' . implode(', ', $escaped) . ')';
                }
                $sql .= "INSERT INTO \"$table\" ($columnList) VALUES\n" . implode(",\n", $values) . ";\n";
            }
            $sql .= "\n";
        }

        $sql .= "PRAGMA foreign_keys = ON;\n";

        return $sql;
    }

    public function download($file)
    {
        $backupName = config('backup.backup.name', 'elegant-store-backup');
        $file = basename($file);
        $path = storage_path('app/' . $backupName . '/' . $file);

        if (!file_exists($path)) {
            return redirect()->route('admin.backups.index')->with('error', __('global.backup_not_found'));
        }

        return response()->download($path);
    }

    public function destroy($file)
    {
        $backupName = config('backup.backup.name', 'elegant-store-backup');
        $file = basename($file);
        $path = storage_path('app/' . $backupName . '/' . $file);

        if (file_exists($path)) {
            unlink($path);
        }

        return redirect()->route('admin.backups.index')->with('success', __('global.backup_deleted'));
    }

    private function formatBytes($bytes, $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
