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
            ['key' => 'maintenance_mode', 'value' => '0', 'type' => 'boolean', 'group' => 'general', 'label' => 'Sitio en mantenimiento'],
            ['key' => 'italian_enabled', 'value' => '0', 'type' => 'boolean', 'group' => 'idioma', 'label' => 'Mostrar el sitio en italiano'],
            ['key' => 'contact_email', 'value' => 'contacto@dante.edu.py', 'group' => 'contacto', 'label' => 'Correo de contacto'],
            ['key' => 'contact_phone', 'value' => '', 'group' => 'contacto', 'label' => 'Teléfono de contacto'],
            ['key' => 'address_asuncion', 'value' => '', 'group' => 'contacto', 'label' => 'Dirección — sede Asunción'],
            // GA4/GTM/Meta Pixel + Conversions API/Turnstile ya no viven acá:
            // se administran desde "Integraciones" (App\Models\IntegrationSetting),
            // con interruptor de activo/inactivo por integración.
            ['key' => 'whatsapp_number', 'value' => '', 'group' => 'contacto', 'label' => 'WhatsApp (formato internacional, ej. 595984464500)'],
            ['key' => 'social_facebook_url', 'value' => '', 'group' => 'contacto', 'label' => 'Facebook (URL completa)'],
            ['key' => 'social_instagram_url', 'value' => '', 'group' => 'contacto', 'label' => 'Instagram (URL completa)'],
            ['key' => 'google_maps_embed_url', 'value' => '', 'group' => 'contacto', 'label' => 'Google Maps — URL de embebido (Compartir → Insertar un mapa → copiar solo la URL del src)'],
            ['key' => 'form_notification_email', 'value' => 'contacto@dante.edu.py', 'group' => 'formularios', 'label' => 'Correo que recibe los formularios'],
        ];

        foreach ($defaults as $setting) {
            SiteSetting::query()->updateOrCreate(['key' => $setting['key']], $setting);
        }
    }
}
