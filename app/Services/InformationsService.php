<?php

namespace App\Services;

use App\Enums\HttpStatusEnum;
use App\Http\Resources\InformationResource;
use App\Models\Information;
use Illuminate\Support\Facades\Log;

class InformationsService
{
    public function getInformations()
    {
        try {
            $informations = Information::all();
            return InformationResource::collection($informations);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return HttpStatusEnum::ERROR;
        }
    }
}
