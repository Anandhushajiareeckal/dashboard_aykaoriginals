<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

use App\Models\{Meeting, Invoice};
use App\Jobs\{SendMeetingReminder, SendPaymentReminder};

Schedule::call(function () {
    Meeting::where('reminder_sent', false)
        ->whereBetween('meeting_at', [now()->addMinutes(28), now()->addMinutes(32)])
        ->each(fn($m) => SendMeetingReminder::dispatch($m));
})->everyMinute();

Schedule::call(function () {
    Invoice::whereIn('status', ['Sent'])
        ->where('due_date', '<=', now()->addDays(3))
        ->each(fn($i) => SendPaymentReminder::dispatch($i));
})->dailyAt('09:00');
