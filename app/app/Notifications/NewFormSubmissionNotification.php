<?php

namespace App\Notifications;

use App\Models\FormSubmission;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewFormSubmissionNotification extends Notification
{
    use Queueable;

    public function __construct(public FormSubmission $submission) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $label = $this->submission->type === 'contacto' ? 'Contacto' : 'Pre-inscripción';

        return (new MailMessage)
            ->subject("Dante — nuevo formulario de {$label}")
            ->line("Nombre: {$this->submission->name}")
            ->line("Correo: {$this->submission->email}")
            ->line("Teléfono: {$this->submission->phone}")
            ->when($this->submission->site, fn ($mail) => $mail->line("Sede: {$this->submission->site}"))
            ->line("Mensaje: {$this->submission->message}");
    }
}
