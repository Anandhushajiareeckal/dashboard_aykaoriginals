<?php
namespace App\Jobs;
use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendPaymentReminder implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public function __construct(public Invoice $invoice) {}
    public function handle(): void {
        if ($this->invoice->brand?->email) {
            Mail::raw(
                "Payment reminder: Invoice {$this->invoice->invoice_number} for AED {$this->invoice->total} is due on {$this->invoice->due_date->format('d M Y')}.",
                fn($m) => $m->to($this->invoice->brand->email)->subject('Payment Reminder: '.$this->invoice->invoice_number)
            );
        }
    }
}
