<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * This migration is superseded by 2026_08_20_135000_fix_inventory_count_items_table.php
     * which handles both new table creation and fixing existing partial tables.
     * This migration is kept for migration history but does nothing.
     */
    public function up(): void
    {
        // Migration moved to 2026_08_20_135000_fix_inventory_count_items_table.php
        // This prevents duplicate table creation errors
    }

    public function down(): void
    {
        // No action on rollback
    }
};

