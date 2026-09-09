<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use App\Models\ActivityRecord;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use LogicException;

class ActivityRecordResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        if (! $this->resource instanceof ActivityRecord) {
            throw new LogicException('ActivityRecordResource expects an activity record model.');
        }

        $record = $this->resource;

        return [
            'id' => $record->id,
            'type' => $record->type,
            'label' => $record->label,
            'metadata' => $record->metadata ?? [],
            'created_at' => $record->created_at?->toISOString(),
        ];
    }
}
