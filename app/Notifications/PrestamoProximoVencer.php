<?php

namespace App\Notifications;

use App\Models\Prestamo;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PrestamoProximoVencer extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Prestamo $prestamo) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $socio  = $this->prestamo->socio;
        $material = $this->prestamo->material;

        return (new MailMessage)
            ->subject('Préstamo próximo a vencer — E-liber')
            ->line("Hola {$socio->nombre}, el préstamo del material *{$material->titulo}* vence pronto.")
            ->line("Fecha de vencimiento: {$this->prestamo->fecha_devolucion->format('d/m/Y')}")
            ->action('Ver préstamos', url('/alumno/mis-prestamos'))
            ->line('Recordá devolver el material a tiempo para evitar multas.');
    }
}
