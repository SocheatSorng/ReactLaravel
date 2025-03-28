<?php

namespace App\Providers;

use Illuminate\Database\Schema\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Fix for MySQL < 5.7.7 and MariaDB < 10.2.2
        Schema::defaultStringLength(191);
        
        // Custom morph map to use table names instead of model names
        Relation::morphMap([
            'user' => 'App\Models\NewUser',
            'book' => 'App\Models\Book',
            'category' => 'App\Models\Category',
            'order' => 'App\Models\Order',
            'review' => 'App\Models\Review',
            'wishlist' => 'App\Models\Wishlist',
            'purchase' => 'App\Models\Purchase'
        ]);
    }
}
