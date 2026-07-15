<?php

namespace App\Http\Controllers\Api\Administration;

use App\Http\Controllers\Controller;
use App\Models\Administration\AppSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache; // Wajib import Cache

class AppSettingController extends Controller
{
    public function index()
    {
        // 1. CACHING: Ambil dari RAM (Cache). Jika kosong, ambil dari DB lalu simpan di RAM.
        $settings = Cache::rememberForever('app_settings_cache', function () {
            return AppSetting::pluck('value', 'key')->all();
        });

        // 2. Default bawaan (termasuk SMTP agar form tidak error jika data masih kosong)
        $defaults = [
            'app_name' => 'Asset Management ERP',
            'company_name' => 'PT. Solusi Teknologi Nusantara',
            'logo_lg' => '/images/logo/logo-large.png',
            'logo_sm' => '/images/logo/logo-small.png',

            'mail_host' => 'smtp.gmail.com',
            'mail_port' => '587',
            'mail_username' => '',
            'mail_password' => '',
            'mail_encryption' => 'tls',
            'mail_from_address' => 'noreply@erp.com',
            'mail_from_name' => 'ERP System',
        ];

        return response()->json([
            'success' => true,
            'data' => array_merge($defaults, $settings)
        ]);
    }

    public function update(Request $request)
    {
        try {
            // 1. Ambil data asli dari database SEBELUM diubah
            $oldSettings = AppSetting::pluck('value', 'key')->all();

            $changedAttributes = [];
            $oldAttributes = [];

            // 2. Lakukan perulangan untuk Update sekaligus Deteksi Perubahan
            foreach ($request->all() as $key => $value) {
                $newValue = $value ?? '';
                $oldValue = $oldSettings[$key] ?? '';

                // Jika nilai baru BERBEDA dengan nilai lama, dan BUKAN logo, masukkan ke keranjang Log
                if ($oldValue !== $newValue && !in_array($key, ['logo_lg', 'logo_sm'])) {
                    $changedAttributes[$key] = $newValue;
                    $oldAttributes[$key] = $oldValue;
                }

                // 3. Tetap simpan semua data ke Database
                AppSetting::updateOrCreate(
                    ['key' => $key],
                    ['value' => $newValue]
                );
            }

            // Hapus Cache lama
            Cache::forget('app_settings_cache');

            // 4. Catat Log HANYA JIKA ADA perubahan di keranjang Log
            if (!empty($changedAttributes)) {
                activity('APP SETTING')
                    ->causedBy(Auth::user())
                    ->performedOn(new AppSetting())
                    ->event('updated')
                    ->withProperties([
                        'attributes' => $changedAttributes,
                        'old' => $oldAttributes // Kita kirim data lama agar Vue bisa mencoretnya!
                    ])
                    ->log('updated');
            }

            return response()->json([
                'success' => true,
                'message' => 'Configuration updated successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating configuration.',
                'error_detail' => $e->getMessage()
            ], 500);
        }
    }
}
