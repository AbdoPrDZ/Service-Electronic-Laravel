<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\File;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Category::create([
            'id' => 1,
            'name' => json_encode([
                'en' => 'Mobile phones',
                'ar' => 'الهواتف النقالة'
            ], JSON_UNESCAPED_UNICODE),
            'image_id' => 'category-1',
        ]);
        File::create([
            'name' => 'category-1',
            'disk' => 'public',
            'path' => 'categories/1.png',
        ]);
        Category::create([
            'id' => 2,
            'name' => json_encode([
                'en' => 'Computers',
                'ar' => 'أجهزة الكمبيوتر'
            ], JSON_UNESCAPED_UNICODE),
            'image_id' => 'category-2',
        ]);
        File::create([
            'name' => 'category-2',
            'disk' => 'public',
            'path' => 'categories/2.png',
        ]);
    }
}
