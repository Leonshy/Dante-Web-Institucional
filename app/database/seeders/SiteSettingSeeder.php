<?php

namespace Database\Seeders;

use App\Models\SiteSetting;
use Illuminate\Database\Seeder;

class SiteSettingSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            ['key' => 'site_name', 'value' => 'Dante — Società Dante Alighieri Asunción', 'group' => 'general', 'label' => 'Nombre del sitio'],
            ['key' => 'italian_enabled', 'value' => '0', 'type' => 'boolean', 'group' => 'idioma', 'label' => 'Mostrar el sitio en italiano'],
            ['key' => 'contact_email', 'value' => 'contacto@dante.edu.py', 'group' => 'contacto', 'label' => 'Correo de contacto'],
            ['key' => 'contact_phone', 'value' => '', 'group' => 'contacto', 'label' => 'Teléfono de contacto'],
            ['key' => 'address_asuncion', 'value' => '', 'group' => 'contacto', 'label' => 'Dirección — sede Asunción'],
            ['key' => 'google_analytics_id', 'value' => '', 'group' => 'integraciones', 'label' => 'ID de Google Analytics (GA4)'],
            ['key' => 'google_tag_manager_id', 'value' => '', 'group' => 'integraciones', 'label' => 'ID de Google Tag Manager'],
            ['key' => 'meta_pixel_id', 'value' => '', 'group' => 'integraciones', 'label' => 'ID de Meta Pixel'],
            ['key' => 'form_notification_email', 'value' => 'contacto@dante.edu.py', 'group' => 'formularios', 'label' => 'Correo que recibe los formularios'],
        ];

        foreach ($defaults as $setting) {
            SiteSetting::query()->updateOrCreate(['key' => $setting['key']], $setting);
        }
    }
}
