<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Workshop;
use Carbon\Carbon;

class updateWorkshopStatus implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        foreach(Workshop::where('status', 'launched')->get() as $workshop){
            $firstSession = $workshop->sessions()->orderBy('date', 'asc')->first();
            if(isset($firstSession) && (Carbon::parse($firstSession->date))->isPast()){
                $workshop->update(['status' => 'progress']);
            }
        }

        foreach(Workshop::where('status', 'progress')->get() as $workshop){
            $lastSession = $workshop->sessions()->orderBy('date', 'desc')->first();
            if(isset($lastSession) && (Carbon::parse($lastSession->date))->isPast()){
                $workshop->update(['status' => 'finished']);
            }
        }
    }
}
