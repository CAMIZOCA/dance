<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreActivityRecordRequest;
use App\Http\Resources\Api\V1\ActivityRecordResource;
use App\Models\ActivityRecord;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ActivityRecordController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        return ActivityRecordResource::collection(
            ActivityRecord::query()
                ->where('user_id', $request->user()->id)
                ->latest()
                ->limit(20)
                ->get(),
        );
    }

    public function store(StoreActivityRecordRequest $request): ActivityRecordResource
    {
        $record = ActivityRecord::query()->create([
            ...$request->validated(),
            'user_id' => $request->user()->id,
        ]);

        return new ActivityRecordResource($record);
    }
}
