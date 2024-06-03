<?php

namespace App\Console\Commands;

use App\Models\Tier;
use Illuminate\Console\Command;

class CreateInAppTiers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:tiers';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create available in-app (iOS) price tiers';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $tiers = [
            ['label' => 'Free', 'value' => 0],
            ['label' => 'Tier 1', 'value' => 1],
            ['label' => 'Tier 2', 'value' => 2],
            ['label' => 'Tier 3', 'value' => 3],
            ['label' => 'Tier 4', 'value' => 4],
            ['label' => 'Tier 5', 'value' => 5],
            ['label' => 'Tier 6', 'value' => 6],
            ['label' => 'Tier 7', 'value' => 7],
            ['label' => 'Tier 8', 'value' => 8],
            ['label' => 'Tier 9', 'value' => 9],
            ['label' => 'Tier 10', 'value' => 10],
            ['label' => 'Tier 11', 'value' => 11],
            ['label' => 'Tier 12', 'value' => 12],
            ['label' => 'Tier 13', 'value' => 13],
            ['label' => 'Tier 14', 'value' => 14],
            ['label' => 'Tier 15', 'value' => 15],
            ['label' => 'Tier 16', 'value' => 16],
            ['label' => 'Tier 17', 'value' => 17],
            ['label' => 'Tier 18', 'value' => 18],
            ['label' => 'Tier 19', 'value' => 19],
            ['label' => 'Tier 20', 'value' => 20],
            ['label' => 'Tier 21', 'value' => 21],
            ['label' => 'Tier 22', 'value' => 22],
            ['label' => 'Tier 23', 'value' => 23],
            ['label' => 'Tier 24', 'value' => 24],
            ['label' => 'Tier 25', 'value' => 25],
            ['label' => 'Tier 26', 'value' => 26],
            ['label' => 'Tier 27', 'value' => 27],
            ['label' => 'Tier 28', 'value' => 28],
            ['label' => 'Tier 29', 'value' => 29],
            ['label' => 'Tier 30', 'value' => 30],
            ['label' => 'Tier 31', 'value' => 31],
            ['label' => 'Tier 32', 'value' => 32],
            ['label' => 'Tier 33', 'value' => 33],
            ['label' => 'Tier 34', 'value' => 34],
            ['label' => 'Tier 35', 'value' => 35],
            ['label' => 'Tier 36', 'value' => 36],
            ['label' => 'Tier 37', 'value' => 37],
            ['label' => 'Tier 38', 'value' => 38],
            ['label' => 'Tier 39', 'value' => 39],
            ['label' => 'Tier 40', 'value' => 40],
            ['label' => 'Tier 41', 'value' => 41],
            ['label' => 'Tier 42', 'value' => 42],
            ['label' => 'Tier 43', 'value' => 43],
            ['label' => 'Tier 44', 'value' => 44],
            ['label' => 'Tier 45', 'value' => 45],
            ['label' => 'Tier 46', 'value' => 46],
            ['label' => 'Tier 47', 'value' => 47],
            ['label' => 'Tier 48', 'value' => 48],
            ['label' => 'Tier 49', 'value' => 49],
            ['label' => 'Tier 50', 'value' => 50],
            ['label' => 'Tier 51', 'value' => 51],
            ['label' => 'Tier 52', 'value' => 52],
            ['label' => 'Tier 53', 'value' => 53],
            ['label' => 'Tier 54', 'value' => 54],
            ['label' => 'Tier 55', 'value' => 55],
            ['label' => 'Tier 56', 'value' => 56],
            ['label' => 'Tier 57', 'value' => 57],
            ['label' => 'Tier 58', 'value' => 58],
            ['label' => 'Tier 59', 'value' => 59],
            ['label' => 'Tier 60', 'value' => 60],
            ['label' => 'Tier 61', 'value' => 61],
            ['label' => 'Tier 62', 'value' => 62],
            ['label' => 'Tier 63', 'value' => 63],
            ['label' => 'Tier 64', 'value' => 64],
            ['label' => 'Tier 65', 'value' => 65],
            ['label' => 'Tier 66', 'value' => 66],
            ['label' => 'Tier 67', 'value' => 67],
            ['label' => 'Tier 68', 'value' => 68],
            ['label' => 'Tier 69', 'value' => 69],
            ['label' => 'Tier 70', 'value' => 70],
            ['label' => 'Tier 71', 'value' => 71],
            ['label' => 'Tier 72', 'value' => 72],
            ['label' => 'Tier 73', 'value' => 73],
            ['label' => 'Tier 74', 'value' => 74],
            ['label' => 'Tier 75', 'value' => 75],
            ['label' => 'Tier 76', 'value' => 76],
            ['label' => 'Tier 77', 'value' => 77],
            ['label' => 'Tier 78', 'value' => 78],
            ['label' => 'Tier 79', 'value' => 79],
            ['label' => 'Tier 80', 'value' => 80],
            ['label' => 'Tier 81', 'value' => 81],
            ['label' => 'Tier 82', 'value' => 82],
            ['label' => 'Tier 83', 'value' => 83],
            ['label' => 'Tier 84', 'value' => 84],
            ['label' => 'Tier 85', 'value' => 85],
            ['label' => 'Tier 86', 'value' => 86],
            ['label' => 'Tier 87', 'value' => 87],
            ['label' => 'Tier 88', 'value' => 88],
            ['label' => 'Tier 89', 'value' => 89],
            ['label' => 'Tier 90', 'value' => 90],
            ['label' => 'Tier 91', 'value' => 91],
            ['label' => 'Tier 92', 'value' => 92],
            ['label' => 'Tier 93', 'value' => 93],
            ['label' => 'Tier 94', 'value' => 94],
            ['label' => 'Tier 95', 'value' => 95],
            ['label' => 'Tier 96', 'value' => 96],
            ['label' => 'Tier 97', 'value' => 97],
            ['label' => 'Tier 98', 'value' => 98],
            ['label' => 'Tier 99', 'value' => 99],
            ['label' => 'Tier 100', 'value' => 100],
        ];

        Tier::insert($tiers);
        $this->info('Successfully Created in-app (iOS) price tiers');
    }
}
