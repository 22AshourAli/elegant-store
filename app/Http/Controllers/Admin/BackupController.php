<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
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
            Artisan::call('backup:run', ['--only-db' => true, '--no-interaction' => true]);
            $output = Artisan::output();

            if (str_contains($output, 'Backup completed!')) {
                return redirect()->route('admin.backups.index')->with('success', __('global.backup_created'));
            }

            return redirect()->route('admin.backups.index')->with('error', __('global.backup_failed_check_logs'));
        } catch (\Exception $e) {
            return redirect()->route('admin.backups.index')->with('error', __('global.backup_failed') . ': ' . $e->getMessage());
        }
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
