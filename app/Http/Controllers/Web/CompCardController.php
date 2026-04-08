<?php
namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\{TalentModel, CompCard, CompCardShare};
use App\Services\ActivityLogger;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class CompCardController extends Controller
{
    /**
     * Builder page for a model's comp card
     */
    public function builder(TalentModel $model)
    {
        $model->load('compCards');
        $card = $model->compCards()->latest()->first();
        return view('compcard.builder', compact('model', 'card'));
    }

    /**
     * Save or update the comp card
     */
    public function save(Request $req, TalentModel $model, ActivityLogger $logger)
    {
        $data = $req->validate([
            'template'          => 'required|in:noir,clean,bold,luxury,typo',
            'agency_name'       => 'nullable|string',
            'agency_phone'      => 'nullable|string',
            'agency_email'      => 'nullable|email',
            'agency_website'    => 'nullable|string',
            'notable_clients'   => 'nullable|string',
            'recent_campaigns'  => 'nullable|string',
            'special_skills'    => 'nullable|string',
            'day_rate'          => 'nullable|numeric',
            'half_day_rate'     => 'nullable|numeric',
            'available_for'     => 'nullable|array',
        ]);

        $card = $model->compCards()->updateOrCreate(
            ['talent_model_id' => $model->id],
            $data
        );

        // Handle comp card photo uploads via Spatie MediaLibrary
        if ($req->hasFile('hero_photo')) {
            $model->clearMediaCollection('compcard_hero');
            $model->addMedia($req->file('hero_photo'))
                  ->toMediaCollection('compcard_hero');
        }

        if ($req->hasFile('card_photos')) {
            foreach ($req->file('card_photos') as $photo) {
                $model->addMedia($photo)->toMediaCollection('compcard_shots');
            }
        }

        $logger->log('created', 'CompCard', $card->id, $model->name,
            "Saved comp card for model \"{$model->name}\" using template \"{$data['template']}\"");

        return redirect()->route('models.compcard.builder', $model)
            ->with('success', 'Comp card saved successfully.');
    }

    /**
     * Export comp card as PDF
     */
    
    public function uploadShot(Request $req, TalentModel $model)
    {
        $req->validate(['shot' => 'required|image|max:8192']);
        $model->addMedia($req->file('shot'))->toMediaCollection('compcard_shots');
        return response()->json(['url' => $model->getMedia('compcard_shots')->last()->getUrl(), 'success' => true]);
    }

    public function deletePhoto(Request $req, TalentModel $model, int $mediaId)
    {
        $media = $model->media()->findOrFail($mediaId);
        $media->delete();
        return back()->with('success', 'Photo removed.');
    }
    public function exportPdf(TalentModel $model)
    {
        $model->load('compCards');
        $card = $model->compCards()->latest()->firstOrFail();
        $pdf = Pdf::loadView('compcard.pdf', compact('model', 'card'))
                  ->setPaper([0, 0, 297.64, 419.53]) // A6 landscape
                  ->setOption('dpi', 150);
        return $pdf->download("compcard-{$model->name}.pdf");
    }

    /**
     * Public portfolio view (for client links)
     */
    public function publicView(string $slug)
    {
        $card = CompCard::where('public_slug', $slug)
                        ->where('is_active', true)
                        ->with('talentModel')
                        ->firstOrFail();
        $card->increment('view_count');
        return view('compcard.public', compact('card'));
    }

    /**
     * Send profile to client via email
     */
    public function sendToClient(Request $req, TalentModel $model, ActivityLogger $logger)
    {
        $req->validate([
            'emails'            => 'required|array|min:1',
            'emails.*'          => 'required|email',
            'subject'           => 'required|string|max:255',
            'message'           => 'required|string',
            'attach_pdf'        => 'boolean',
            'attach_portfolio'  => 'boolean',
            'attach_photos_zip' => 'boolean',
        ]);

        $model->load('compCards');
        $card = $model->compCards()->latest()->first();

        $pdfAttachment = null;
        if ($req->boolean('attach_pdf') && $card) {
            $pdfAttachment = Pdf::loadView('compcard.pdf', compact('model', 'card'))
                               ->setPaper([0, 0, 297.64, 419.53])
                               ->output();
        }

        $sent = [];
        foreach ($req->emails as $email) {
            Mail::send('compcard.email', [
                'model'    => $model,
                'card'     => $card,
                'bodyText' => $req->message,
                'publicUrl'=> $card ? route('compcard.public', $card->public_slug) : null,
            ], function ($mail) use ($email, $req, $pdfAttachment, $model) {
                $mail->to($email)
                     ->subject($req->subject)
                     ->replyTo(config('mail.from.address'), config('app.name'));
                if ($pdfAttachment) {
                    $mail->attachData($pdfAttachment, "compcard-{$model->name}.pdf", ['mime' => 'application/pdf']);
                }
            });

            CompCardShare::create([
                'comp_card_id'      => $card?->id,
                'sent_by'           => auth()->id(),
                'recipient_email'   => $email,
                'subject'           => $req->subject,
                'message'           => $req->message,
                'attach_pdf'        => $req->boolean('attach_pdf'),
                'attach_portfolio'  => $req->boolean('attach_portfolio'),
                'attach_photos_zip' => $req->boolean('attach_photos_zip'),
            ]);

            $sent[] = $email;
        }

        $logger->log('created', 'CompCardShare', $model->id, $model->name,
            "Sent comp card for \"{$model->name}\" to: " . implode(', ', $sent));

        return back()->with('success', 'Profile sent to ' . count($sent) . ' recipient(s).');
    }
}