# ============================================================
#  AYKA ORIGINALS - Comp Card & Portfolio System
#  Run: powershell -ExecutionPolicy Bypass -File ayka_compcard.ps1
# ============================================================

$ProjectPath = "C:\laragon\www\ayka-originals"
Set-Location $ProjectPath
$phpExe = (Get-Command php -ErrorAction SilentlyContinue).Source
if (-not $phpExe) { $phpExe = "php" }

Write-Host ""
Write-Host "======================================================" -ForegroundColor Cyan
Write-Host "  AYKA ORIGINALS - Comp Card & Portfolio System" -ForegroundColor Cyan
Write-Host "======================================================" -ForegroundColor Cyan
Write-Host ""

function Write-File($Path, $Content) {
    $Dir = Split-Path $Path -Parent
    if (!(Test-Path $Dir)) { New-Item -ItemType Directory -Path $Dir -Force | Out-Null }
    [System.IO.File]::WriteAllText($Path, $Content, [System.Text.UTF8Encoding]::new($false))
    Write-Host "  [OK] $Path" -ForegroundColor Green
}

# -- 1. Migration ----------------------------------------------
Write-Host "[1/8] Creating comp_cards migration..." -ForegroundColor Yellow
$ts = Get-Date -Format "yyyy_MM_dd_HHmmss"

Write-File "database\migrations\${ts}_create_comp_cards_table.php" @'
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('comp_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('talent_model_id')->constrained('talent_models')->cascadeOnDelete();
            $table->string('template')->default('noir');
            $table->string('title')->nullable();
            $table->string('agency_name')->default('Ayka Originals');
            $table->string('agency_phone')->nullable();
            $table->string('agency_email')->nullable();
            $table->string('agency_website')->nullable();
            $table->text('notable_clients')->nullable();
            $table->text('recent_campaigns')->nullable();
            $table->text('special_skills')->nullable();
            $table->decimal('day_rate', 10, 2)->nullable();
            $table->decimal('half_day_rate', 10, 2)->nullable();
            $table->json('available_for')->nullable();
            $table->string('public_slug')->unique()->nullable();
            $table->unsignedInteger('view_count')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('comp_card_shares', function (Blueprint $table) {
            $table->id();
            $table->foreignId('comp_card_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sent_by')->constrained('users')->cascadeOnDelete();
            $table->string('recipient_email');
            $table->string('recipient_name')->nullable();
            $table->string('subject');
            $table->text('message');
            $table->boolean('attach_pdf')->default(true);
            $table->boolean('attach_portfolio')->default(true);
            $table->boolean('attach_photos_zip')->default(false);
            $table->timestamp('opened_at')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('comp_card_shares');
        Schema::dropIfExists('comp_cards');
    }
};
'@

# -- 2. CompCard Model -----------------------------------------
Write-Host "[2/8] Creating CompCard model..." -ForegroundColor Yellow

Write-File "app\Models\CompCard.php" @'
<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CompCard extends Model
{
    protected $fillable = [
        'talent_model_id', 'template', 'title', 'agency_name',
        'agency_phone', 'agency_email', 'agency_website',
        'notable_clients', 'recent_campaigns', 'special_skills',
        'day_rate', 'half_day_rate', 'available_for',
        'public_slug', 'view_count', 'is_active',
    ];

    protected $casts = [
        'available_for' => 'array',
        'is_active'     => 'boolean',
        'day_rate'      => 'decimal:2',
        'half_day_rate' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::creating(function (CompCard $card) {
            if (empty($card->public_slug)) {
                $card->public_slug = Str::slug($card->talentModel->name ?? 'model') . '-' . Str::random(6);
            }
        });
    }

    public function talentModel()
    {
        return $this->belongsTo(TalentModel::class, 'talent_model_id');
    }

    public function shares()
    {
        return $this->hasMany(CompCardShare::class);
    }

    public function getTemplateNameAttribute(): string
    {
        return match($this->template) {
            'noir'       => 'Noir Editorial',
            'clean'      => 'Clean White',
            'bold'       => 'Bold Magazine',
            'luxury'     => 'Luxury Minimal',
            'typo'       => 'Typographic',
            default      => 'Noir Editorial',
        };
    }
}
'@

Write-File "app\Models\CompCardShare.php" @'
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
'@

# -- 3. CompCardController -------------------------------------
Write-Host "[3/8] Creating CompCardController..." -ForegroundColor Yellow

Write-File "app\Http\Controllers\Web\CompCardController.php" @'
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
'@

# -- 4. Add relationship to TalentModel -----------------------
Write-Host "[4/8] Adding compCards relationship to TalentModel..." -ForegroundColor Yellow

$modelPath = Join-Path $ProjectPath "app\Models\TalentModel.php"
$modelContent = [System.IO.File]::ReadAllText($modelPath)
if ($modelContent -notmatch "compCards") {
    $modelContent = $modelContent -replace "(public function projects\(\))", "public function compCards() { return \$this->hasMany(CompCard::class, 'talent_model_id'); }`r`n`r`n    `$1"
    [System.IO.File]::WriteAllText($modelPath, $modelContent, [System.Text.UTF8Encoding]::new($false))
    Write-Host "  [OK] compCards() relation added" -ForegroundColor Green
} else {
    Write-Host "  [SKIP] Already has compCards relation" -ForegroundColor Gray
}

# -- 5. Routes -------------------------------------------------
Write-Host "[5/8] Adding routes..." -ForegroundColor Yellow

$routesPath = Join-Path $ProjectPath "routes\web.php"
$routes = [System.IO.File]::ReadAllText($routesPath)

if ($routes -notmatch "compcard") {
    $newRoutes = @'

// Comp Card Builder & Portfolio
Route::get('models/{model}/compcard', [\App\Http\Controllers\Web\CompCardController::class, 'builder'])->name('models.compcard.builder');
Route::post('models/{model}/compcard/save', [\App\Http\Controllers\Web\CompCardController::class, 'save'])->name('models.compcard.save');
Route::get('models/{model}/compcard/pdf', [\App\Http\Controllers\Web\CompCardController::class, 'exportPdf'])->name('models.compcard.pdf');
Route::post('models/{model}/compcard/send', [\App\Http\Controllers\Web\CompCardController::class, 'sendToClient'])->name('models.compcard.send');
'@
    # Insert inside auth middleware group before the closing });
    $routes = $routes -replace "(Route::get\('/search'[^\r\n]*\);)", "`$1$newRoutes"
    [System.IO.File]::WriteAllText($routesPath, $routes, [System.Text.UTF8Encoding]::new($false))
    Write-Host "  [OK] Comp card routes added" -ForegroundColor Green
}

# Public route (no auth)
if ($routes -notmatch "compcard.public") {
    $publicRoute = "`r`n// Public comp card view (no auth - client link)`r`nRoute::get('portfolio/{slug}', [\App\Http\Controllers\Web\CompCardController::class, 'publicView'])->name('compcard.public');"
    $routes = [System.IO.File]::ReadAllText($routesPath)
    $routes = $routes -replace "(Route::middleware\('guest'\))", "$publicRoute`r`n`r`n`$1"
    [System.IO.File]::WriteAllText($routesPath, $routes, [System.Text.UTF8Encoding]::new($false))
    Write-Host "  [OK] Public portfolio route added" -ForegroundColor Green
}

# -- 6. Views --------------------------------------------------
Write-Host "[6/8] Creating views..." -ForegroundColor Yellow

Write-File "resources\views\compcard\builder.blade.php" @'
@extends('layouts.app')
@section('title', 'Comp Card Builder - ' . $model->name)
@section('content')
<div class="flex items-center justify-between mb-6">
    <div>
        <div class="flex items-center gap-3">
            <a href="{{ route('models.show', $model) }}" class="text-gray-400 hover:text-gray-600 text-sm">Back</a>
            <span class="text-gray-200">/</span>
            <h2 class="font-display text-xl font-bold">Comp Card Builder</h2>
        </div>
        <p class="text-sm text-gray-400 mt-0.5">{{ $model->name }} &mdash; Professional comp card &amp; portfolio</p>
    </div>
    <div class="flex gap-2">
        @if($card)
        <a href="{{ route('compcard.public', $card->public_slug) }}" target="_blank"
           class="text-sm border border-gray-200 px-4 py-2 rounded-lg hover:bg-gray-50">View Public &rarr;</a>
        <a href="{{ route('models.compcard.pdf', $model) }}"
           class="text-sm bg-gray-800 text-white px-4 py-2 rounded-lg hover:bg-gray-900">Export PDF</a>
        @endif
    </div>
</div>

<div class="grid grid-cols-5 gap-5">

    {{-- Template Selector --}}
    <div class="col-span-1">
        <div class="bg-white border border-gray-100 rounded-xl p-4 sticky top-4">
            <h3 class="font-display font-semibold text-xs uppercase tracking-widest text-gray-400 mb-3">Template</h3>
            <div id="template-list" class="space-y-2">
                @foreach([
                    ['noir',   'Noir Editorial',   'Dark luxury',           '#0a0a0a'],
                    ['clean',  'Clean White',       'Agency standard',       '#fff'],
                    ['bold',   'Bold Magazine',     'High fashion',          '#0a0a0a'],
                    ['luxury', 'Luxury Minimal',    'Premium, couture',      '#F7F5F2'],
                    ['typo',   'Typographic',       'Avant-garde',           '#FFFFF0'],
                ] as [$key, $name, $style, $bg])
                <div class="template-option border rounded-lg p-2.5 cursor-pointer hover:border-[#0B132B] transition-colors
                    {{ ($card?->template === $key || (!$card && $key === 'noir')) ? 'border-[#0B132B] bg-[#0B132B]/3' : 'border-gray-100' }}"
                    onclick="selectTemplate('{{ $key }}', this)"
                    data-template="{{ $key }}">
                    <div class="w-full h-8 rounded mb-2" style="background:{{ $bg }};border:1px solid #eee"></div>
                    <div class="font-semibold text-xs text-gray-800">{{ $name }}</div>
                    <div class="text-[10px] text-gray-400">{{ $style }}</div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Live Preview --}}
    <div class="col-span-2 flex flex-col items-center gap-4" id="live-preview">
        <div class="text-xs uppercase tracking-widest text-gray-400">Front</div>
        <div id="preview-front" class="w-full flex justify-center"></div>
        <div class="text-xs uppercase tracking-widest text-gray-400 mt-2">Back / Portfolio</div>
        <div id="preview-back" class="w-full flex justify-center"></div>
    </div>

    {{-- Form --}}
    <div class="col-span-2">
        <form method="POST" action="{{ route('models.compcard.save', $model) }}" enctype="multipart/form-data" id="card-form">
            @csrf
            <input type="hidden" name="template" id="template-input" value="{{ $card?->template ?? 'noir' }}">

            {{-- Photos --}}
            <div class="bg-white border border-gray-100 rounded-xl p-5 mb-4">
                <h3 class="font-display font-semibold text-xs uppercase tracking-widest text-gray-400 mb-3">Hero Photo</h3>
                <label class="block border-2 border-dashed border-gray-200 rounded-xl p-6 text-center cursor-pointer hover:border-[#0B132B] transition-colors">
                    <input type="file" name="hero_photo" accept="image/*" class="hidden" onchange="previewHero(this)">
                    <div id="hero-preview">
                        @if($model->getFirstMediaUrl('compcard_hero'))
                        <img src="{{ $model->getFirstMediaUrl('compcard_hero') }}" class="w-full max-h-48 object-contain rounded-lg">
                        @else
                        <div class="text-3xl mb-2 opacity-30">&#128247;</div>
                        <div class="text-sm text-gray-400">Click to upload hero photo</div>
                        <div class="text-xs text-gray-300 mt-1">Full-length, 300dpi for print quality</div>
                        @endif
                    </div>
                </label>

                <h3 class="font-display font-semibold text-xs uppercase tracking-widest text-gray-400 mb-3 mt-4">Comp Card Shots (3-4)</h3>
                <div class="grid grid-cols-3 gap-2 mb-3" id="shot-grid">
                    @foreach($model->getMedia('compcard_shots') as $photo)
                    <div class="aspect-[3/4] rounded-lg overflow-hidden border border-gray-100">
                        <img src="{{ $photo->getUrl() }}" class="w-full h-full object-cover">
                    </div>
                    @endforeach
                    <label class="aspect-[3/4] rounded-lg border-2 border-dashed border-gray-200 flex items-center justify-center cursor-pointer hover:border-[#0B132B] transition-colors">
                        <input type="file" name="card_photos[]" accept="image/*" multiple class="hidden" onchange="previewShots(this)">
                        <div class="text-center"><div class="text-2xl opacity-30">+</div><div class="text-[10px] text-gray-400 mt-1">Add shots</div></div>
                    </label>
                </div>
            </div>

            {{-- Agency details --}}
            <div class="bg-white border border-gray-100 rounded-xl p-5 mb-4">
                <h3 class="font-display font-semibold text-xs uppercase tracking-widest text-gray-400 mb-3">Agency Details</h3>
                <div class="grid grid-cols-2 gap-3">
                    <div><label class="block text-xs text-gray-400 uppercase tracking-wide mb-1">Agency Name</label>
                        <input name="agency_name" value="{{ old('agency_name', $card?->agency_name ?? 'Ayka Originals') }}" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-[#0B132B]"></div>
                    <div><label class="block text-xs text-gray-400 uppercase tracking-wide mb-1">Phone</label>
                        <input name="agency_phone" value="{{ old('agency_phone', $card?->agency_phone) }}" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-[#0B132B]"></div>
                    <div><label class="block text-xs text-gray-400 uppercase tracking-wide mb-1">Email</label>
                        <input name="agency_email" value="{{ old('agency_email', $card?->agency_email ?? 'booking@aykaoriginals.com') }}" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-[#0B132B]"></div>
                    <div><label class="block text-xs text-gray-400 uppercase tracking-wide mb-1">Website</label>
                        <input name="agency_website" value="{{ old('agency_website', $card?->agency_website ?? 'aykaoriginals.com') }}" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-[#0B132B]"></div>
                </div>
            </div>

            {{-- Portfolio details --}}
            <div class="bg-white border border-gray-100 rounded-xl p-5 mb-4">
                <h3 class="font-display font-semibold text-xs uppercase tracking-widest text-gray-400 mb-3">Portfolio Details</h3>
                <div class="space-y-3">
                    <div><label class="block text-xs text-gray-400 uppercase tracking-wide mb-1">Notable Clients / Brands</label>
                        <textarea name="notable_clients" rows="2" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-[#0B132B]" placeholder="Dior, Vogue Arabia, H&M MENA...">{{ old('notable_clients', $card?->notable_clients) }}</textarea></div>
                    <div><label class="block text-xs text-gray-400 uppercase tracking-wide mb-1">Recent Campaigns</label>
                        <textarea name="recent_campaigns" rows="2" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-[#0B132B]">{{ old('recent_campaigns', $card?->recent_campaigns) }}</textarea></div>
                    <div><label class="block text-xs text-gray-400 uppercase tracking-wide mb-1">Special Skills</label>
                        <input name="special_skills" value="{{ old('special_skills', $card?->special_skills) }}" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-[#0B132B]" placeholder="Runway, Swimwear, Fitness, Bilingual"></div>
                </div>
                <div class="grid grid-cols-2 gap-3 mt-3">
                    <div><label class="block text-xs text-gray-400 uppercase tracking-wide mb-1">Day Rate (AED)</label>
                        <input name="day_rate" type="number" value="{{ old('day_rate', $card?->day_rate) }}" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-[#0B132B]"></div>
                    <div><label class="block text-xs text-gray-400 uppercase tracking-wide mb-1">Half Day (AED)</label>
                        <input name="half_day_rate" type="number" value="{{ old('half_day_rate', $card?->half_day_rate) }}" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-[#0B132B]"></div>
                </div>
            </div>

            {{-- Send to client --}}
            <div class="bg-white border border-gray-100 rounded-xl p-5 mb-4">
                <h3 class="font-display font-semibold text-xs uppercase tracking-widest text-gray-400 mb-3">Send Profile to Client</h3>
                <div class="space-y-3">
                    <div>
                        <label class="block text-xs text-gray-400 uppercase tracking-wide mb-1">Recipient Emails</label>
                        <input name="recipient_emails" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-[#0B132B]"
                            placeholder="casting@client.com, director@brand.com">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-400 uppercase tracking-wide mb-1">Subject</label>
                        <input name="email_subject" value="Model Profile - {{ $model->name }} | Ayka Originals"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-[#0B132B]">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-400 uppercase tracking-wide mb-1">Message</label>
                        <textarea name="email_message" rows="4" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-[#0B132B]">Dear Casting Team,

Please find the profile of {{ $model->name }}, represented by Ayka Originals.

Measurements: {{ $model->height }} | {{ $model->bust }}/{{ $model->waist }}/{{ $model->hips }} | Shoes {{ $model->shoe_size }}

We look forward to collaborating with you.

Best regards,
Ayka Originals Bookings
{{ $card?->agency_phone ?? '' }}</textarea>
                    </div>
                    <div class="flex gap-4 text-sm">
                        <label class="flex items-center gap-1.5 cursor-pointer"><input type="checkbox" name="attach_pdf" checked class="rounded"> Comp Card PDF</label>
                        <label class="flex items-center gap-1.5 cursor-pointer"><input type="checkbox" name="attach_portfolio" checked class="rounded"> Portfolio Sheet</label>
                    </div>
                </div>
            </div>

            <div class="flex gap-3 sticky bottom-0 bg-gray-50 py-3">
                <button type="button" onclick="sendEmail()"
                    class="flex-1 bg-[#0B132B] text-white py-2.5 rounded-lg text-sm font-semibold hover:bg-[#1a2a4a] transition-colors">
                    &#9993; Send to Client
                </button>
                <button type="submit"
                    class="flex-1 bg-[#C9A96E] text-[#0B132B] py-2.5 rounded-lg text-sm font-bold hover:bg-[#E8C882] transition-colors">
                    &#10003; Save Card
                </button>
            </div>
        </form>
    </div>
</div>

<script>
let currentTemplate = '{{ $card?->template ?? 'noir' }}';

const modelData = {
    name:   '{{ $model->name }}',
    age:    '{{ $model->age }}',
    height: '{{ $model->height }}',
    bust:   '{{ $model->bust }}',
    waist:  '{{ $model->waist }}',
    hips:   '{{ $model->hips }}',
    shoes:  '{{ $model->shoe_size }}',
    hair:   '{{ $model->categories[0] ?? '' }}',
    agency: 'Ayka Originals',
    phone:  '{{ $card?->agency_phone ?? '+971 4 000 0000' }}',
    email:  '{{ $card?->agency_email ?? 'booking@aykaoriginals.com' }}',
};

function selectTemplate(key, el) {
    document.querySelectorAll('.template-option').forEach(o => o.classList.remove('border-[#0B132B]','bg-[#0B132B]/3'));
    el.classList.add('border-[#0B132B]','bg-[#0B132B]/3');
    currentTemplate = key;
    document.getElementById('template-input').value = key;
    renderPreview();
}

function renderPreview() {
    const d = modelData;
    const heroUrl = '{{ $model->getFirstMediaUrl("compcard_hero") }}';
    const photoStyle = heroUrl ? `background:url(${heroUrl}) center/cover` : 'background:linear-gradient(135deg,#1a1a2e,#0f3460)';
    const initial = d.name.charAt(0);

    let front = '';
    if (currentTemplate === 'noir') {
        front = `<div style="background:#0a0a0a;color:#fff;font-family:'Cormorant Garamond',serif;width:280px;border-radius:6px;overflow:hidden;box-shadow:0 16px 48px rgba(0,0,0,.3)">
            <div style="padding:20px 18px 0;display:flex;justify-content:space-between;align-items:center">
                <div style="font-size:7px;letter-spacing:3px;text-transform:uppercase;color:#C9A96E;font-family:'DM Sans',sans-serif">${d.agency}</div>
            </div>
            <div style="width:100%;height:200px;${photoStyle};display:flex;align-items:center;justify-content:center;font-size:60px;color:rgba(201,169,110,.2);font-style:italic;margin-top:10px">${heroUrl?'':initial}</div>
            <div style="padding:8px 18px"><div style="font-size:26px;font-weight:300;font-style:italic;line-height:1">${d.name}</div>
            <div style="width:32px;height:1px;background:#C9A96E;margin:8px 0"></div>
            <div style="font-family:'DM Sans',sans-serif;font-size:8px;letter-spacing:2px;color:rgba(255,255,255,.4);text-transform:uppercase">${d.hair||'Fashion, Editorial'}</div></div>
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;padding:12px 18px;border-top:1px solid rgba(255,255,255,.08);margin-top:4px">
                <div style="text-align:center"><div style="font-size:13px;font-weight:600;color:#C9A96E">${d.height}</div><div style="font-size:7px;letter-spacing:1.5px;color:rgba(255,255,255,.3);text-transform:uppercase;margin-top:2px;font-family:'DM Sans',sans-serif">Height</div></div>
                <div style="text-align:center"><div style="font-size:13px;font-weight:600;color:#C9A96E">${d.bust}</div><div style="font-size:7px;letter-spacing:1.5px;color:rgba(255,255,255,.3);text-transform:uppercase;margin-top:2px;font-family:'DM Sans',sans-serif">Bust</div></div>
                <div style="text-align:center"><div style="font-size:13px;font-weight:600;color:#C9A96E">${d.waist}</div><div style="font-size:7px;letter-spacing:1.5px;color:rgba(255,255,255,.3);text-transform:uppercase;margin-top:2px;font-family:'DM Sans',sans-serif">Waist</div></div>
            </div>
            <div style="padding:8px 18px 14px;border-top:1px solid rgba(255,255,255,.08);display:flex;justify-content:space-between">
                <div style="font-family:'DM Sans',sans-serif;font-size:8px;color:rgba(255,255,255,.35)">${d.phone}</div>
                <div style="font-family:'DM Sans',sans-serif;font-size:8px;color:rgba(255,255,255,.35)">${d.email}</div>
            </div>
        </div>`;
    } else if (currentTemplate === 'clean') {
        front = `<div style="background:#fff;color:#0B132B;font-family:'Playfair Display',serif;width:280px;border-radius:6px;overflow:hidden;box-shadow:0 16px 48px rgba(0,0,0,.15)">
            <div style="height:4px;background:linear-gradient(90deg,#0B132B,#C9A96E)"></div>
            <div style="padding:14px 18px 0;display:flex;justify-content:space-between">
                <div style="font-family:'DM Sans',sans-serif;font-size:7px;letter-spacing:2px;text-transform:uppercase;color:#8A8880">${d.agency}</div>
            </div>
            <div style="width:100%;height:190px;${photoStyle};display:flex;align-items:center;justify-content:center;font-size:60px;color:rgba(11,19,43,.1);font-style:italic;margin-top:8px">${heroUrl?'':initial}</div>
            <div style="padding:12px 18px 0;display:flex;align-items:baseline;gap:8px"><div style="font-size:22px;font-weight:700">${d.name}</div><div style="font-family:'DM Sans',sans-serif;font-size:11px;color:#8A8880">${d.age} yrs</div></div>
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr 1fr;padding:10px 18px;border-top:1px solid #E8E6E0;border-bottom:1px solid #E8E6E0;margin:10px 0">
                <div style="text-align:center"><div style="font-size:12px;font-weight:700">${d.height}</div><div style="font-family:'DM Sans',sans-serif;font-size:7px;text-transform:uppercase;letter-spacing:1px;color:#8A8880;margin-top:2px">H</div></div>
                <div style="text-align:center"><div style="font-size:12px;font-weight:700">${d.bust}</div><div style="font-family:'DM Sans',sans-serif;font-size:7px;text-transform:uppercase;letter-spacing:1px;color:#8A8880;margin-top:2px">B</div></div>
                <div style="text-align:center"><div style="font-size:12px;font-weight:700">${d.waist}</div><div style="font-family:'DM Sans',sans-serif;font-size:7px;text-transform:uppercase;letter-spacing:1px;color:#8A8880;margin-top:2px">W</div></div>
                <div style="text-align:center"><div style="font-size:12px;font-weight:700">${d.hips}</div><div style="font-family:'DM Sans',sans-serif;font-size:7px;text-transform:uppercase;letter-spacing:1px;color:#8A8880;margin-top:2px">Hip</div></div>
            </div>
            <div style="padding:6px 18px 14px;display:flex;justify-content:space-between;align-items:center">
                <div style="font-family:'DM Sans',sans-serif;font-size:9px;color:#8A8880"><div>${d.email}</div><div>${d.phone}</div></div>
                <div style="font-family:'Cormorant Garamond',serif;font-size:13px;font-style:italic;color:rgba(11,19,43,.3)">${d.agency}</div>
            </div>
        </div>`;
    } else if (currentTemplate === 'luxury') {
        front = `<div style="background:#F7F5F2;color:#1a1a1a;font-family:'DM Sans',sans-serif;width:280px;border-radius:6px;overflow:hidden;box-shadow:0 16px 48px rgba(0,0,0,.15)">
            <div style="width:100%;height:230px;${photoStyle};display:flex;align-items:center;justify-content:center;font-family:'Cormorant Garamond',serif;font-size:80px;color:rgba(201,169,110,.2);font-style:italic">${heroUrl?'':initial}</div>
            <div style="padding:16px">
                <div style="font-size:8px;letter-spacing:3px;text-transform:uppercase;color:#C9A96E;margin-bottom:6px">${d.agency}</div>
                <div style="font-family:'Cormorant Garamond',serif;font-size:24px;font-weight:300;letter-spacing:-0.5px;color:#1a1a1a;line-height:1.1">${d.name}</div>
                <div style="font-size:10px;color:#999;margin-bottom:12px">${d.hair||'Fashion'} &mdash; ${d.age} yrs</div>
                <div style="display:flex;border-top:1px solid #e0dcd6;border-bottom:1px solid #e0dcd6;padding:8px 0;margin-bottom:12px">
                    <div style="flex:1;text-align:center;border-right:1px solid #e0dcd6"><div style="font-size:12px;font-weight:600">${d.height}</div><div style="font-size:7px;letter-spacing:1.5px;text-transform:uppercase;color:#bbb;margin-top:2px">Height</div></div>
                    <div style="flex:1;text-align:center;border-right:1px solid #e0dcd6"><div style="font-size:12px;font-weight:600">${d.bust}/${d.waist}</div><div style="font-size:7px;letter-spacing:1.5px;text-transform:uppercase;color:#bbb;margin-top:2px">B/W</div></div>
                    <div style="flex:1;text-align:center"><div style="font-size:12px;font-weight:600">${d.hips}</div><div style="font-size:7px;letter-spacing:1.5px;text-transform:uppercase;color:#bbb;margin-top:2px">Hips</div></div>
                </div>
                <div style="display:flex;justify-content:space-between;font-size:8px;color:#bbb;letter-spacing:1px;text-transform:uppercase">
                    <div>${d.phone}</div><div>${d.email}</div>
                </div>
            </div>
        </div>`;
    } else {
        front = `<div style="background:#fff;width:280px;border-radius:6px;overflow:hidden;box-shadow:0 16px 48px rgba(0,0,0,.12);font-family:'DM Sans',sans-serif;color:#0B132B">
            <div style="background:#0B132B;padding:12px 16px;display:flex;justify-content:space-between">
                <div style="color:#C9A96E;font-size:8px;letter-spacing:3px;text-transform:uppercase">${d.agency}</div>
                <div style="color:rgba(255,255,255,.3);font-size:8px;letter-spacing:2px;text-transform:uppercase">${d.hair||'Fashion'}</div>
            </div>
            <div style="width:100%;height:190px;${photoStyle};display:flex;align-items:center;justify-content:center;font-size:60px;color:rgba(11,19,43,.08)">${heroUrl?'':initial}</div>
            <div style="padding:14px 16px">
                <div style="font-size:22px;font-weight:700;letter-spacing:-0.5px;margin-bottom:10px">${d.name}</div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:6px;font-size:11px">
                    <div style="display:flex;justify-content:space-between;padding:4px 0;border-bottom:1px solid #eee"><span style="opacity:.5;font-size:9px;text-transform:uppercase">Height</span><strong>${d.height}</strong></div>
                    <div style="display:flex;justify-content:space-between;padding:4px 0;border-bottom:1px solid #eee"><span style="opacity:.5;font-size:9px;text-transform:uppercase">Bust</span><strong>${d.bust}</strong></div>
                    <div style="display:flex;justify-content:space-between;padding:4px 0;border-bottom:1px solid #eee"><span style="opacity:.5;font-size:9px;text-transform:uppercase">Waist</span><strong>${d.waist}</strong></div>
                    <div style="display:flex;justify-content:space-between;padding:4px 0;border-bottom:1px solid #eee"><span style="opacity:.5;font-size:9px;text-transform:uppercase">Hips</span><strong>${d.hips}</strong></div>
                    <div style="display:flex;justify-content:space-between;padding:4px 0"><span style="opacity:.5;font-size:9px;text-transform:uppercase">Shoes</span><strong>${d.shoes}</strong></div>
                </div>
                <div style="margin-top:10px;font-size:8px;color:#8A8880;display:flex;justify-content:space-between"><div>${d.phone}</div><div>${d.email}</div></div>
            </div>
        </div>`;
    }

    document.getElementById('preview-front').innerHTML = front;
    document.getElementById('preview-back').innerHTML = `
        <div style="background:#fff;width:280px;border-radius:6px;overflow:hidden;box-shadow:0 16px 48px rgba(0,0,0,.12)">
            <div style="background:#0B132B;padding:12px 16px;display:flex;justify-content:space-between;align-items:center">
                <div style="font-family:'Cormorant Garamond',serif;color:#fff;font-style:italic;font-size:14px">${d.name}</div>
                <div style="font-size:7px;letter-spacing:2px;color:#C9A96E;text-transform:uppercase">${d.agency}</div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:2px;padding:8px">
                ${Array(6).fill('<div style="height:80px;background:#f0ede8;border-radius:3px"></div>').join('')}
            </div>
            <div style="padding:8px 12px;border-top:1px solid #E8E6E0;font-size:9px;color:#8A8880;display:grid;grid-template-columns:1fr 1fr;gap:6px">
                <div><strong style="color:#0B132B;font-size:9px;display:block;margin-bottom:3px">MEASUREMENTS</strong>H: ${d.height} &nbsp; B: ${d.bust}<br>W: ${d.waist} &nbsp; Hips: ${d.hips}<br>Shoes: ${d.shoes}</div>
                <div><strong style="color:#0B132B;font-size:9px;display:block;margin-bottom:3px">CONTACT</strong>${d.phone}<br>${d.email}</div>
            </div>
        </div>`;
}

function previewHero(input) {
    const file = input.files[0];
    if (file) {
        const url = URL.createObjectURL(file);
        document.getElementById('hero-preview').innerHTML = `<img src="${url}" class="w-full max-h-48 object-contain rounded-lg">`;
        renderPreview();
    }
}

function previewShots(input) { renderPreview(); }

function sendEmail() {
    const emails = document.querySelector('[name=recipient_emails]').value;
    const subject = document.querySelector('[name=email_subject]').value;
    const message = document.querySelector('[name=email_message]').value;
    if (!emails) { alert('Please enter at least one recipient email.'); return; }
    if (confirm('Send profile to: ' + emails + '?')) {
        document.getElementById('card-form').action = '{{ route("models.compcard.send", $model) }}';
        document.querySelector('[name=template]')?.remove();
        const inp = document.createElement('input');
        inp.type='hidden'; inp.name='emails[]'; inp.value=emails;
        document.getElementById('card-form').appendChild(inp);
        const s = document.createElement('input');
        s.type='hidden'; s.name='subject'; s.value=subject;
        document.getElementById('card-form').appendChild(s);
        const m = document.createElement('input');
        m.type='hidden'; m.name='message'; m.value=message;
        document.getElementById('card-form').appendChild(m);
        document.getElementById('card-form').submit();
    }
}

renderPreview();
</script>
@endsection
'@

# Email template view
Write-File "resources\views\compcard\email.blade.php" @'
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"><style>
body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;background:#f4f3ef;margin:0;padding:0}
.wrap{max-width:560px;margin:0 auto;padding:32px 16px}
.card{background:#fff;border-radius:12px;overflow:hidden;border:1px solid #E8E6E0}
.header{background:#0B132B;padding:24px 28px;display:flex;justify-content:space-between;align-items:center}
.header-name{font-family:Georgia,serif;color:#fff;font-size:20px;font-style:italic}
.header-agency{font-size:9px;letter-spacing:2px;text-transform:uppercase;color:#C9A96E}
.body{padding:24px 28px}
.body p{font-size:14px;color:#333;line-height:1.8;margin-bottom:16px}
.stats-row{background:#f9f8f6;border-radius:8px;padding:14px 18px;margin:18px 0;display:flex;gap:24px}
.stat-item{text-align:center}
.stat-v{font-size:16px;font-weight:700;color:#0B132B}
.stat-l{font-size:9px;text-transform:uppercase;letter-spacing:1px;color:#8A8880;margin-top:2px}
.cta{background:#C9A96E;color:#0B132B;padding:12px 28px;border-radius:8px;text-decoration:none;font-weight:700;font-size:14px;display:inline-block;margin-top:8px}
.footer{background:#0B132B;padding:16px 28px;text-align:center;font-size:11px;color:rgba(255,255,255,.35);letter-spacing:1px}
</style></head>
<body>
<div class="wrap">
<div class="card">
    <div class="header">
        <div><div class="header-name">{{ $model->name }}</div><div style="font-size:10px;color:rgba(255,255,255,.4);margin-top:3px">Model Profile</div></div>
        <div class="header-agency">Ayka Originals</div>
    </div>
    <div class="body">
        <p>{!! nl2br(e($bodyText)) !!}</p>
        <div class="stats-row">
            @if($model->height)<div class="stat-item"><div class="stat-v">{{ $model->height }}</div><div class="stat-l">Height</div></div>@endif
            @if($model->bust)<div class="stat-item"><div class="stat-v">{{ $model->bust }}</div><div class="stat-l">Bust</div></div>@endif
            @if($model->waist)<div class="stat-item"><div class="stat-v">{{ $model->waist }}</div><div class="stat-l">Waist</div></div>@endif
            @if($model->hips)<div class="stat-item"><div class="stat-v">{{ $model->hips }}</div><div class="stat-l">Hips</div></div>@endif
            @if($model->shoe_size)<div class="stat-item"><div class="stat-v">{{ $model->shoe_size }}</div><div class="stat-l">Shoes</div></div>@endif
        </div>
        @if($publicUrl)
        <p style="margin-bottom:8px">View the full interactive portfolio online:</p>
        <a class="cta" href="{{ $publicUrl }}">View Full Portfolio &rarr;</a>
        @endif
        <p style="margin-top:18px;font-size:12px;color:#8A8880">This email was sent by Ayka Originals on behalf of {{ $model->name }}. All rights reserved.</p>
    </div>
    <div class="footer">AYKA ORIGINALS &nbsp;&nbsp; TALENT &amp; PRODUCTION MANAGEMENT</div>
</div>
</div>
</body>
</html>
'@

# Public portfolio view
Write-File "resources\views\compcard\public.blade.php" @'
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>{{ $card->talentModel->name }} - Ayka Originals</title>
<meta property="og:title" content="{{ $card->talentModel->name }} | Ayka Originals">
<meta property="og:description" content="{{ $card->talentModel->height }} | {{ implode(', ', (array)$card->talentModel->categories) }}">
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;1,400&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<script src="https://cdn.tailwindcss.com"></script>
<style>body{font-family:'DM Sans',sans-serif;background:#0B132B}.font-display{font-family:'Cormorant Garamond',serif}</style>
</head>
<body>
<div class="min-h-screen" style="background:linear-gradient(135deg,#0B132B 0%,#162040 100%)">
    {{-- Header --}}
    <div class="border-b border-white/10 px-6 py-4 flex items-center justify-between">
        <div>
            <div class="font-display text-white text-lg font-semibold italic">Ayka Originals</div>
            <div class="text-[#C9A96E] text-[9px] tracking-widest uppercase">Talent & Production</div>
        </div>
        <a href="mailto:{{ $card->agency_email ?? 'booking@aykaoriginals.com' }}"
           class="text-xs border border-[#C9A96E] text-[#C9A96E] px-4 py-2 rounded-lg hover:bg-[#C9A96E] hover:text-[#0B132B] transition-colors">
           Book {{ $card->talentModel->name->split(' ')[0] ?? 'Now' }}
        </a>
    </div>

    <div class="max-w-4xl mx-auto px-6 py-10">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            {{-- Left: Main photo + name --}}
            <div>
                @if($card->talentModel->getFirstMediaUrl('compcard_hero'))
                <img src="{{ $card->talentModel->getFirstMediaUrl('compcard_hero') }}"
                     class="w-full aspect-[3/4] object-cover rounded-2xl mb-6">
                @else
                <div class="w-full aspect-[3/4] rounded-2xl mb-6 flex items-center justify-center"
                     style="background:linear-gradient(135deg,#162040,#1a2a4a)">
                    <span class="font-display text-8xl text-[#C9A96E]/20 italic">{{ substr($card->talentModel->name,0,1) }}</span>
                </div>
                @endif
                <h1 class="font-display text-4xl text-white font-light italic mb-2">{{ $card->talentModel->name }}</h1>
                <div class="flex flex-wrap gap-2">
                    @foreach((array)$card->talentModel->categories as $cat)
                    <span class="text-xs px-3 py-1 rounded-full" style="background:rgba(201,169,110,.15);color:#C9A96E">{{ $cat }}</span>
                    @endforeach
                    @if($card->talentModel->is_inhouse)
                    <span class="text-xs px-3 py-1 rounded-full bg-green-900/40 text-green-400">In-house</span>
                    @endif
                </div>
            </div>

            {{-- Right: Stats + Portfolio --}}
            <div>
                {{-- Measurements --}}
                <div class="rounded-2xl p-6 mb-5" style="background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.08)">
                    <h3 class="text-[10px] text-[#C9A96E] uppercase tracking-widest mb-4">Measurements</h3>
                    <div class="grid grid-cols-3 gap-4">
                        @foreach(['height'=>'Height','bust'=>'Bust','waist'=>'Waist','hips'=>'Hips','shoe_size'=>'Shoe','age'=>'Age'] as $key => $label)
                        @if($card->talentModel->$key)
                        <div class="text-center">
                            <div class="font-display text-2xl text-white font-light">{{ $card->talentModel->$key }}{{ $key==='age'?' yrs':'' }}</div>
                            <div class="text-[9px] text-white/30 uppercase tracking-widest mt-1">{{ $label }}</div>
                        </div>
                        @endif
                        @endforeach
                    </div>
                </div>

                {{-- Notable clients --}}
                @if($card->notable_clients)
                <div class="rounded-2xl p-5 mb-5" style="background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.08)">
                    <h3 class="text-[10px] text-[#C9A96E] uppercase tracking-widest mb-3">Notable Clients</h3>
                    <p class="text-sm text-white/60 leading-relaxed">{{ $card->notable_clients }}</p>
                </div>
                @endif

                {{-- Contact --}}
                <div class="rounded-2xl p-5" style="background:rgba(201,169,110,.08);border:1px solid rgba(201,169,110,.2)">
                    <h3 class="text-[10px] text-[#C9A96E] uppercase tracking-widest mb-3">Booking</h3>
                    <div class="space-y-1.5">
                        <div class="flex justify-between text-sm"><span class="text-white/40">Agency</span><span class="text-white font-medium">{{ $card->agency_name }}</span></div>
                        @if($card->agency_phone)<div class="flex justify-between text-sm"><span class="text-white/40">Phone</span><a href="tel:{{ $card->agency_phone }}" class="text-[#C9A96E]">{{ $card->agency_phone }}</a></div>@endif
                        @if($card->agency_email)<div class="flex justify-between text-sm"><span class="text-white/40">Email</span><a href="mailto:{{ $card->agency_email }}" class="text-[#C9A96E]">{{ $card->agency_email }}</a></div>@endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Portfolio gallery --}}
        @if($card->talentModel->getMedia('compcard_shots')->count() || $card->talentModel->getMedia('portfolio')->count())
        <div class="mt-10">
            <h2 class="font-display text-2xl text-white font-light italic mb-6">Portfolio</h2>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                @foreach($card->talentModel->getMedia('compcard_shots')->concat($card->talentModel->getMedia('portfolio'))->take(12) as $media)
                <div class="aspect-[3/4] rounded-xl overflow-hidden cursor-pointer hover:opacity-90 transition-opacity">
                    <img src="{{ $media->getUrl() }}" class="w-full h-full object-cover">
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    <div class="text-center py-8 text-white/20 text-xs tracking-widest">
        AYKA ORIGINALS &nbsp;&middot;&nbsp; TALENT & PRODUCTION MANAGEMENT &nbsp;&middot;&nbsp; {{ $card->view_count }} VIEWS
    </div>
</div>
</body>
</html>
'@

# PDF template
Write-File "resources\views\compcard\pdf.blade.php" @'
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8">
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'DejaVu Sans',sans-serif;background:#0a0a0a;color:#fff;width:420px;min-height:297px}
.front{width:420px;min-height:297px;background:#0a0a0a;position:relative;padding:20px}
.agency{font-size:7px;letter-spacing:3px;text-transform:uppercase;color:#C9A96E;margin-bottom:12px}
.main-img{width:180px;height:220px;background:linear-gradient(135deg,#1a1a2e,#162040);float:left;margin-right:16px;border-radius:4px}
.name{font-size:28px;font-weight:300;line-height:1;font-style:italic;color:#fff;margin-bottom:6px}
.divider{width:28px;height:1px;background:#C9A96E;margin:8px 0}
.cat{font-size:7px;letter-spacing:2px;text-transform:uppercase;color:rgba(255,255,255,.4)}
.stats{display:flex;gap:14px;margin-top:12px}
.sv{font-size:13px;font-weight:600;color:#C9A96E}
.sl{font-size:7px;letter-spacing:1.5px;text-transform:uppercase;color:rgba(255,255,255,.3);margin-top:2px}
.contact{position:absolute;bottom:16px;left:20px;right:20px;display:flex;justify-content:space-between;font-size:8px;color:rgba(255,255,255,.3);border-top:1px solid rgba(255,255,255,.08);padding-top:10px}
</style>
</head>
<body>
<div class="front">
    <div class="agency">{{ $card->agency_name }}</div>
    <div class="main-img">
        @if($model->getFirstMediaUrl('compcard_hero'))
        <img src="{{ $model->getFirstMediaUrl('compcard_hero') }}" style="width:100%;height:100%;object-fit:cover">
        @endif
    </div>
    <div style="overflow:hidden">
        <div class="name">{{ $model->name }}</div>
        <div class="divider"></div>
        <div class="cat">{{ implode(', ', (array)$model->categories) }}</div>
        <div class="stats">
            <div><div class="sv">{{ $model->height }}</div><div class="sl">Height</div></div>
            <div><div class="sv">{{ $model->bust }}</div><div class="sl">Bust</div></div>
            <div><div class="sv">{{ $model->waist }}</div><div class="sl">Waist</div></div>
            <div><div class="sv">{{ $model->hips }}</div><div class="sl">Hips</div></div>
            <div><div class="sv">{{ $model->shoe_size }}</div><div class="sl">Shoes</div></div>
        </div>
        @if($card->notable_clients)
        <div style="margin-top:12px;font-size:8px;color:rgba(255,255,255,.4);line-height:1.5">
            <div style="color:#C9A96E;letter-spacing:1.5px;text-transform:uppercase;font-size:7px;margin-bottom:3px">Clients</div>
            {{ $card->notable_clients }}
        </div>
        @endif
    </div>
    <div class="contact">
        <div>{{ $card->agency_phone }}</div>
        <div>{{ $card->agency_email }}</div>
        <div>{{ $card->agency_website }}</div>
    </div>
</div>
</body>
</html>
'@

# -- 7. Add "Comp Card" button to models show page ?????????????
Write-Host "[7/8] Updating models show page with comp card button..." -ForegroundColor Yellow

$showPath = Join-Path $ProjectPath "resources\views\models\show.blade.php"
if (Test-Path $showPath) {
    $show = [System.IO.File]::ReadAllText($showPath)
    if ($show -notmatch "compcard.builder") {
        # Build the button string cleanly, no backtick issues
        $compBtn  = '<a href="{{ route(''models.compcard.builder'', $model) }}" '
        $compBtn += 'class="px-4 py-2 bg-[#C9A96E] text-[#0B132B] text-sm font-bold rounded-lg hover:bg-[#E8C882]">'
        $compBtn += 'Comp Card</a>'
        # Insert before the Edit button link
        $editMarker = 'href="{{ route(''models.edit'', $model) }}"'
        if ($show.Contains($editMarker)) {
            $show = $show.Replace($editMarker, $compBtn + "`r`n        " + 'href="{{ route(''models.edit'', $model) }}"')
            [System.IO.File]::WriteAllText($showPath, $show, [System.Text.UTF8Encoding]::new($false))
            Write-Host "  [OK] Comp Card button added to model detail page" -ForegroundColor Green
        } else {
            Write-Host "  [NOTE] Could not auto-insert button - add manually to models/show.blade.php" -ForegroundColor Yellow
        }
    } else {
        Write-Host "  [SKIP] Already has comp card button" -ForegroundColor Gray
    }
}

# -- 8. Migrate ------------------------------------------------
Write-Host "[8/8] Running migration..." -ForegroundColor Yellow
& $phpExe artisan migrate --force
& $phpExe artisan view:clear
& $phpExe artisan cache:clear
& $phpExe artisan route:clear

Write-Host ""
Write-Host "======================================================" -ForegroundColor Green
Write-Host "  DONE! Comp Card & Portfolio system installed." -ForegroundColor Green
Write-Host "======================================================" -ForegroundColor Green
Write-Host ""
Write-Host "  How to use:" -ForegroundColor White
Write-Host "    1. Open any model's detail page" -ForegroundColor Gray
Write-Host "    2. Click the gold 'Comp Card' button" -ForegroundColor Gray
Write-Host "    3. Pick a template (5 available)" -ForegroundColor Gray
Write-Host "    4. Upload hero photo + 3-4 comp card shots" -ForegroundColor Gray
Write-Host "    5. Fill in agency details + portfolio credits" -ForegroundColor Gray
Write-Host "    6. Click 'Save Card' then 'Send to Client'" -ForegroundColor Gray
Write-Host ""
Write-Host "  Client gets:" -ForegroundColor White
Write-Host "    - Professional email with measurements + bio" -ForegroundColor Gray
Write-Host "    - PDF comp card attached (A6 print-ready)" -ForegroundColor Gray
Write-Host "    - Public portfolio link: /portfolio/{slug}" -ForegroundColor Gray
Write-Host ""
Write-Host "  All sends logged in Activity Log with timestamps." -ForegroundColor Gray
Write-Host ""
