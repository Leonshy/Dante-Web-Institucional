<?php

namespace App\Actions\Forms;

use App\Models\FormSubmission;
use App\Models\SiteSetting;
use App\Notifications\NewFormSubmissionNotification;
use Illuminate\Support\Facades\Notification;

/**
 * Guarda el envío y notifica por mail — patrón adaptado de `Contact` de IPG
 * (docs/01 §A.3), sin los campos de cotización.
 */
class StoreFormSubmission
{
    public function handle(string $type, array $data, ?string $ip): FormSubmission
    {
        $submission = FormSubmission::query()->create([
            'type' => $type,
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'site' => $data['site'] ?? null,
            'message' => $data['message'] ?? null,
            'ip_address' => $ip,
            'status' => 'nuevo',
        ]);

        $notifyEmail = SiteSetting::get('form_notification_email');

        if ($notifyEmail) {
            Notification::route('mail', $notifyEmail)
                ->notify(new NewFormSubmissionNotification($submission));
        }

        return $submission;
    }
}
