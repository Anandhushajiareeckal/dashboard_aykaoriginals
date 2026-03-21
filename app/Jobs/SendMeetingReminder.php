<?php
namespace App\Jobs;
use App\Models\Meeting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendMeetingReminder implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public function __construct(public Meeting $meeting) {}
    public function handle(): void {
        if ($this->meeting->employee?->email) {
            Mail::raw(
                "Reminder: '{$this->meeting->title}' is in 30 minutes. Mode: {$this->meeting->mode}",
                fn($m) => $m->to($this->meeting->employee->email)->subject('Meeting Reminder: '.$this->meeting->title)
            );
        }
        $this->meeting->update(['reminder_sent' => true]);
    }
}
