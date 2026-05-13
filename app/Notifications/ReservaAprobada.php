<?php

namespace App\Notifications;

use App\Models\Reserva;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReservaAprobada extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Reserva $reserva) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $socio  = $this->reserva->socio;
        $material = $this->reserva->material;

        return (new MailMessage)
            ->subject('Reserva aprobada — E-liber')
            ->line("Hola {$socio->nombre}, tu reserva del material *{$material->titulo}* fue aprobada.")
            ->line("Ya podés retirar el material por la biblioteca.")
            ->action('Ver reservas', url('/alumno/mis-reservas'))
            ->line('Gracias por usar E-liber.');
    }
}
