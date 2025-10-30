<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Section; // Make sure you have a Section model

class SectionsSeeder extends Seeder
{
    public function run(): void
    {
        Section::create(['name' => 'Wet Section']);
        Section::create(['name' => 'Dry Goods']);
        Section::create(['name' => 'Semi-Dry']);
    }
}