<?php

namespace App\Console\Commands;

use App\Models\City;
use App\Models\Governorate;
use App\Models\ShippingRate;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SeedGovernoratesCommand extends Command
{
    protected $signature = 'shipping:seed-governorates {--fresh : Delete existing data and re-seed}';
    protected $description = 'Seed governorates and cities data. Use --fresh to replace all existing data.';

    public function handle(): int
    {
        if ($this->option('fresh')) {
            if (!$this->confirm('This will DELETE all governorates, cities, and shipping rates. Orders will keep their references (set to null). Continue?')) {
                return Command::FAILURE;
            }

            $this->warn('Deleting existing data...');
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
            ShippingRate::truncate();
            City::truncate();
            Governorate::truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
            $this->info('Existing data cleared.');
        }

        $govCount = Governorate::count();
        $cityCount = City::count();

        if ($govCount > 0 && $cityCount > 0) {
            $this->info('Governorates and cities already seeded. Use --fresh to re-seed.');
            return Command::SUCCESS;
        }

        $this->call('db:seed', ['--class' => 'Database\Seeders\GovernorateCitySeeder', '--force' => true]);

        $this->info('Seeding complete!');
        $this->table(
            ['Table', 'Count'],
            [
                ['Governorates', Governorate::count()],
                ['Cities', City::count()],
            ]
        );

        return Command::SUCCESS;
    }
}
