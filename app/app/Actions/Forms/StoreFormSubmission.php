<?php

namespace App\Actions\Forms;

use App\Models\FormSubmission;
use App\Models\SiteSetting;
use App\Notifications\NewFormSubmissionNotification;
use App\Services\Integrations\MetaConversionsApi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

/**
 * Guarda el envío y notifica por mail — patrón adaptado de `Contact` de IPG
 * (docs/01 §A.3), sin los campos de cotización.
 */
class StoreFormSubmission
{
    public function __construct(private readonly MetaConversionsApi $metaConversionsApi) {}

    /**
     * @return array{submission: FormSubmission, meta_event_id: ?string}
     */
    public function handle(string $type, array $data, ?string $ip, ?Request $request = null): array
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

        // Solo se manda a Meta si el visitante aceptó cookies de marketing —
        // la cookie la escribe `resources/js/consent.js` tras el banner
        // (docs/08-seo.md §6, "Un banner que carga el pixel igual antes de
        // aceptar no sirve de nada"): el mismo criterio aplica al lado servidor.
        $marketingConsent = $request?->cookie('dante_consent_marketing') === '1';

        $eventId = $marketingConsent
            ? $this->metaConversionsApi->send('Lead', [
                'email' => $data['email'] ?? null,
                'phone' => $data['phone'] ?? null,
            ], $request)
            : null;

        return ['submission' => $submission, 'meta_event_id' => $eventId];
    }
}
