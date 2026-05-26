<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $parent = Category::create(['name' => 'ملابس رجالي', 'slug' => 'men-clothing', 'is_active' => true]);
        Category::create(['name' => 'تيشيرتات', 'slug' => 'men-tshirts', 'parent_id' => $parent->id, 'is_active' => true]);
        Category::create(['name' => 'بناطيل', 'slug' => 'men-pants', 'parent_id' => $parent->id, 'is_active' => true]);
    }
}
