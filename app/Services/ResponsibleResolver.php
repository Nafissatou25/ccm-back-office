<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class ResponsibleResolver
{
    public static function resolve($agencyId, $unitId)
    {
        return DB::table('agency_unit_user')
            ->where('agency_id', $agencyId)
            ->where('unit_id', $unitId)
            ->first();
    }
}