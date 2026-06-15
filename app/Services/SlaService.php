<?php

namespace App\Services;

use App\Models\SlaRule;

class SlaService
{
    /**
     * Résoudre la règle SLA applicable à un ticket.
     * Priorité : règle spécifique au type > règle par défaut de l'unité
     */
    public static function resolve(int $unitId, int $typeId, bool $isUrgent): ?SlaRule
    {
        // 1️⃣ Règle spécifique au type
        $rule = SlaRule::where('unit_id', $unitId)
            ->where('type_id', $typeId)
            ->where('is_urgent', $isUrgent)
            ->where('is_active', true)
            ->first();

        // 2️⃣ Fallback : règle par défaut de l'unité (type_id NULL)
        return $rule ?? SlaRule::where('unit_id', $unitId)
            ->whereNull('type_id')
            ->where('is_urgent', $isUrgent)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Appliquer le SLA sur un ticket (calcul des deadlines).
     */
    public static function applySla($ticket): void
    {
        $rule = self::resolve(
            $ticket->unit_id,
            $ticket->type_id,
            (bool) $ticket->is_urgent
        );

        if (!$rule) {
            return; // Aucune règle trouvée, pas de deadline
        }

        $ticket->response_due_at   = now()->addHours($rule->tto);
        $ticket->resolution_due_at = now()->addHours($rule->ttr);
    }
}