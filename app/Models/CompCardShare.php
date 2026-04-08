<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompCardShare extends Model
{
    protected $fillable = [
        'comp_card_id', 'sent_by', 'recipient_email', 'recipient_name',
        'subject', 'message', 'attach_pdf', 'attach_portfolio',
        'attach_photos_zip', 'opened_at',
    ];

    protected $casts = [
        'attach_pdf'       => 'boolean',
        'attach_portfolio' => 'boolean',
        'attach_photos_zip'=> 'boolean',
        'opened_at'        => 'datetime',
    ];

    public function compCard() { return $this->belongsTo(CompCard::class); }
    public function sender()   { return $this->belongsTo(User::class, 'sent_by'); }
}