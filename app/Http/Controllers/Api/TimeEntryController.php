<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TimeEntry;
use App\Http\Resources\TimeEntryResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;

class TimeEntryController extends Controller
{
    public function index()
    {
        $entries = TimeEntry::all();
        return TimeEntryResource::collection($entries);
    }

    public function show(TimeEntry $timeEntry)
    {
        return new TimeEntryResource($timeEntry);
    }

    public function store(Request $request)
    {
        $data           = $request->all();
        $data['status'] = 'Draft';
        $entry          = TimeEntry::create($data);
        return new TimeEntryResource($entry);
    }

    public function approve(TimeEntry $timeEntry)
    {
        DB::transaction(function () use ($timeEntry) {
            $entry = TimeEntry::lockForUpdate()->findOrFail($timeEntry->id);
            if ($entry->status === 'Approved') {
                throw new Exception('Already approved');
            }
            $entry->update(['status' => 'Approved', 'approved_at' => now()]);
            DB::table('projects')
                ->where('id', $entry->project_id)
                ->increment('consumed_hours', $entry->hours);
        });

        return new TimeEntryResource($timeEntry->fresh());
    }

    public function destroy(TimeEntry $timeEntry)
    {
        $timeEntry->delete();
        return response()->noContent();
    }
}
