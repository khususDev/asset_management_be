<?php

namespace App\Providers;

use App\Models\Administration\AppSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
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
        // 1. Cek apakah tabel app_settings sudah ada (mencegah error saat pertama kali instalasi/migrate)
        if (Schema::hasTable('app_settings')) {

            // 2. Ambil data dari Cache agar tidak membebani database setiap detik
            $settings = Cache::rememberForever('app_settings_cache', function () {
                return AppSetting::pluck('value', 'key')->all();
            });

            // 3. Jika mail_host tidak kosong, timpa settingan file .env secara dinamis
            if (!empty($settings['mail_host'])) {
                Config::set('mail.mailers.smtp.host', $settings['mail_host']);
                Config::set('mail.mailers.smtp.port', $settings['mail_port']);
                Config::set('mail.mailers.smtp.encryption', $settings['mail_encryption']);
                Config::set('mail.mailers.smtp.username', $settings['mail_username']);
                Config::set('mail.mailers.smtp.password', $settings['mail_password']);
                Config::set('mail.from.address', $settings['mail_from_address'] ?? 'noreply@erp.com');
                Config::set('mail.from.name', $settings['mail_from_name'] ?? 'ERP System');
            }
        }
    }
}
