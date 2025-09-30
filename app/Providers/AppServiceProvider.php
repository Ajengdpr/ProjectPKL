<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use App\Providers\AppServiceProvider as A;

class AppServiceProvider extends ServiceProvider
{
    public const KEPALA_BIDANG_USERNAME = [
    'SEKRETARIAT' => 'noorekahasni',
    'PPKLH'       => 'emmyariani',
    'KPPI'        => 'hajiehariyanie',
    'TALING'      => 'adhimaulana',
    'PHL'         => 'hardiniwijayanti',
];

    public const PLT_KEPALA_DINAS_USERNAME = 'fathimatuzzahra';
    
    public function boot(): void
    {
        Paginator::useBootstrapFive();
    }
}