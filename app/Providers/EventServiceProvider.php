<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;

use App\Listeners\LogSuccessfulLogin;
use App\Listeners\LogSuccessfulLogout;

use App\Observers\GlobalObserver;

use Illuminate\Support\Facades\File;
use Illuminate\Database\Eloquent\Model;

const EXCLUDED_MODELS = [
    'App\Models\Log_activity',
    'App\Models\Log_keuangan',
    'App\Models\Log_permohonan',
    'App\Models\Log_penyelia',
    'App\Models\Log_tld',
    'App\Models\Log_pengiriman',
    'App\Models\Log_proses',
    'App\Models\User',
    'App\Models\Permohonan_tandaterima',
];

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],

        Login::class => [
            LogSuccessfulLogin::class,
        ],

        Logout::class => [
            LogSuccessfulLogout::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        // 1. Dapatkan path folder Models
        $modelsPath = app_path('Models');

        // 2. Cek apakah foldernya ada (jaga-jaga)
        if (File::exists($modelsPath)) {
            // 3. Ambil semua file .php di folder Models
            $modelFiles = File::allFiles($modelsPath);

            foreach ($modelFiles as $file) {
                $namespace = 'App\\Models\\';
                $className = $file->getFilenameWithoutExtension();
                $fullClassName = $namespace . $className;

                if (class_exists($fullClassName) &&
                    is_subclass_of($fullClassName, Model::class) &&
                    !in_array($fullClassName, EXCLUDED_MODELS)
                ) {
                    // 6. Tempelkan Observer
                    $fullClassName::observe(GlobalObserver::class);
                }
            }
        }
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
