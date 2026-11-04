<?php

namespace App\Support;

use Carbon\Carbon;

class DateInput
{
    /**
     * Parsea "dd/mm/aaaa" (con hora opcional "HH:mm[:ss]", que se ignora, como manda la UI).
     * Devuelve null si viene vacío y lanza \DomainException si la fecha es inválida.
     */
    public static function parse($value, string $label): ?Carbon
    {
        if ($value === null || trim((string) $value) === '') {
            return null;
        }

        $value = trim((string) $value);
        if (!preg_match('#^(\d{2}/\d{2}/\d{4})(\s+\d{2}:\d{2}(:\d{2})?)?$#', $value, $m)) {
            throw new \DomainException("La {$label} no es válida (formato dd/mm/aaaa)");
        }

        $date = Carbon::createFromFormat('d/m/Y', $m[1]);
        if (!$date || $date->format('d/m/Y') !== $m[1]) {
            throw new \DomainException("La {$label} no es válida (formato dd/mm/aaaa)");
        }

        return $date;
    }
}
