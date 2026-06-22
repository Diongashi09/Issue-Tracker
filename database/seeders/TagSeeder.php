<?php

namespace Database\Seeders;

use App\Models\Tag;
use Illuminate\Database\Seeder;

class TagSeeder extends Seeder
{
    public function run(): void
    {
        $tags = [
            ['name' => 'bug',           'color' => '#dc3545'],
            ['name' => 'feature',       'color' => '#0d6efd'],
            ['name' => 'enhancement',   'color' => '#6f42c1'],
            ['name' => 'documentation', 'color' => '#6c757d'],
            ['name' => 'urgent',        'color' => '#fd7e14'],
            ['name' => 'backend',       'color' => '#0dcaf0'],
            ['name' => 'frontend',      'color' => '#198754'],
            ['name' => 'design',        'color' => '#d63384'],
            ['name' => 'wontfix',       'color' => '#adb5bd'],
            ['name' => 'duplicate',     'color' => '#ffc107'],
        ];

        foreach ($tags as $tag) {
            Tag::create($tag);
        }
    }
}
