<?php

namespace App\Enums;

class StatusEnum
{
    const PENDING = 'pending';
    const APPROVED = 'approved';
    const REJECTED = 'rejected';
    const COMPLETED = 'completed';
    const CONFIRM = 'confirm';
    const PROCESS = 'process';
    const PAID = 'paid';
    const CANCEL = 'cancelled';
    const PARTIAL_PAYMENT = 'partial_payment';
    const REFUND = 'refund';
    const NOT_REQUIRED = 'not_required';
    const READY_PICKUP = 'ready_pickup';
    const SHIPPED = 'shipped';
    const IN_TRANSIT = 'in_transit';
    const DELIVERED = 'delivered';
    const FAILED = 'failed';
    const RETURNED = 'returned';
    
    public static function labels(): array
    {
        return [
            self::PENDING => 'Pendiente',
            self::APPROVED => 'Aprobado',
            self::REJECTED => 'Rechazado',
            self::COMPLETED => 'Completado',
            self::CONFIRM => 'Confirmado',
            self::PROCESS => 'En proceso',
            self::PAID => 'Pagado',
            self::CANCEL => 'Cancelado',
            self::PARTIAL_PAYMENT => 'Pago parcial',
            self::REFUND => 'Reembolso',
            self::NOT_REQUIRED => 'No requiere envio',
            self::READY_PICKUP => 'Listo para retirar',
            self::SHIPPED => 'Enviado',
            self::IN_TRANSIT => 'En tránsito',
            self::DELIVERED => 'Entregado',
            self::FAILED => 'Entrega fallida',
            self::RETURNED => 'Devuelto',
        ];
    }
    
    public static function label(string $value): string
    {
        return self::labels()[$value] ?? $value;
    }

    public static function options(): array
    {
        $options = [];
        foreach (self::labels() as $value => $label) {
            $options[$value] = $label;
        }
        return $options;
    }
    
    public static function all(): array
    {
        return [
            self::PENDING,
            self::APPROVED,
            self::REJECTED,
            self::COMPLETED,
            self::CONFIRM,
            self::PROCESS,
            self::PAID,
            self::CANCEL,
            self::PARTIAL_PAYMENT,
            self::REFUND,
            self::NOT_REQUIRED,
            self::READY_PICKUP,
            self::SHIPPED,
            self::IN_TRANSIT,
            self::DELIVERED,
            self::FAILED,
            self::RETURNED,
        ];
    }
}