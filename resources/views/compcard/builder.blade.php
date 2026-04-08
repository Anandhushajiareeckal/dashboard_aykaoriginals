@extends('layouts.app')
@section('title', 'Comp Card - ' . $model->name)
@section('content')

@php
    $cats    = is_array($model->categories) ? implode(', ', $model->categories) : ($model->categories ?? '');
    $heroUrl = $model->getFirstMediaUrl('compcard_hero');
    $shots   = $model->getMedia('compcard_shots');
    $md = [
        'name'      => $model->name,
        'age'       => $model->age ?? '',
        'height'    => $model->height ?? '',
        'bust'      => $model->bust ?? '',
        'waist'     => $model->waist ?? '',
        'hips'      => $model->hips ?? '',
        'shoes'     => $model->shoe_size ?? '',
        'hair'      => $model->hair_color ?? '',
        'eyes'      => $model->eye_color ?? '',
        'dress'     => $model->dress_size ?? '',
        'cats'      => $cats,
        'location'  => $model->location ?? '',
        'heroUrl'   => $heroUrl,
    ];
@endphp

<div class="flex items-center justify-between mb-5">
    <div>
        <div class="flex items-center gap-2 text-sm text-gray-400">
            <a href="{{ route('models.show', $model) }}" class="hover:text-gray-600">&larr; {{ $model->name }}</a>
            <span>/</span>
            <span class="text-gray-700 font-medium">Comp Card Builder</span>
        </div>
        <p class="text-xs text-gray-400 mt-0.5">Select template &rarr; Upload photos &rarr; Save &rarr; Send to client</p>
    </div>
    <div class="flex gap-2">
        @if($card)
        <a href="{{ route('compcard.public', $card->public_slug) }}" target="_blank"
           class="text-xs border border-gray-200 px-3 py-2 rounded-lg hover:bg-gray-50">
           &#127760; Public Link
        </a>
        <a href="{{ route('models.compcard.pdf', $model) }}"
           class="text-xs bg-gray-800 text-white px-3 py-2 rounded-lg hover:bg-gray-900">
           &#8681; Export PDF
        </a>
        @endif
    </div>
</div>

<div class="grid gap-5" style="grid-template-columns:220px 1fr 360px">

    {{-- -- Col 1: Template selector -- --}}
    <div>
        <div class="bg-white border border-gray-100 rounded-xl p-4 sticky top-4">
            <p class="text-[10px] uppercase tracking-widest text-gray-400 font-semibold mb-3">Template</p>
            @foreach([
                ['noir',   'Noir Editorial',  'Dark luxury',       '#0a0a0a', '#C9A96E'],
                ['clean',  'Clean White',     'Agency standard',   '#fff',    '#0B132B'],
                ['bold',   'Bold Magazine',   'High fashion',      '#0a0a0a', '#C9A96E'],
                ['luxury', 'Luxury Minimal',  'Premium, couture',  '#F7F5F2', '#C9A96E'],
                ['typo',   'Typographic',     'Avant-garde',       '#FFFFF0', '#0a0a0a'],
            ] as [$key, $name, $style, $bg, $accent])
            <div onclick="selectTemplate('{{ $key }}', this)"
                 data-key="{{ $key }}"
                 class="template-opt border rounded-xl p-3 mb-2 cursor-pointer hover:border-[#0B132B] transition-all
                        {{ ($card?->template ?? 'noir') === $key ? 'border-[#0B132B] ring-1 ring-[#0B132B]' : 'border-gray-100' }}">
                {{-- Mini preview --}}
                <div class="w-full rounded-lg mb-2 overflow-hidden" style="height:52px;background:{{ $bg }};border:1px solid rgba(0,0,0,0.06)">
                    <div style="height:6px;background:{{ $accent }};opacity:0.5"></div>
                    <div style="padding:4px 6px">
                        <div style="height:8px;width:70%;background:{{ $accent }};opacity:0.2;border-radius:2px;margin-bottom:3px"></div>
                        <div style="display:flex;gap:2px">
                            <div style="flex:1;height:22px;background:{{ $accent }};opacity:0.12;border-radius:2px"></div>
                            <div style="flex:1;height:22px;background:{{ $accent }};opacity:0.12;border-radius:2px"></div>
                            <div style="flex:1;height:22px;background:{{ $accent }};opacity:0.12;border-radius:2px"></div>
                        </div>
                    </div>
                </div>
                <div class="font-semibold text-xs text-gray-800">{{ $name }}</div>
                <div class="text-[10px] text-gray-400">{{ $style }}</div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- -- Col 2: Live preview -- --}}
    <div class="flex flex-col items-center gap-4">

        {{-- Front card --}}
        <p class="text-[10px] uppercase tracking-widest text-gray-400 self-start">Front</p>
        <div id="preview-front" class="w-full flex justify-center"></div>

        {{-- Photo upload grid --}}
        <div class="w-full bg-white border border-gray-100 rounded-xl p-4">
            <p class="text-[10px] uppercase tracking-widest text-gray-400 font-semibold mb-3">
                Comp Card Shots (3-4 photos)
            </p>
            <div class="grid grid-cols-4 gap-2 mb-3" id="shot-grid">
                @foreach($shots as $photo)
                <div class="aspect-[3/4] rounded-lg overflow-hidden border border-gray-100 relative group">
                    <img src="{{ $photo->getUrl() }}" class="w-full h-full object-cover">
                    <a href="{{ route('models.compcard.deletephoto', [$model, $photo->id]) }}"
                       onclick="return confirm('Remove this photo?')"
                       class="absolute top-1 right-1 bg-red-500 text-white rounded-full w-5 h-5 text-xs items-center justify-content-center hidden group-hover:flex">
                       &times;
                    </a>
                </div>
                @endforeach
                @for($i = $shots->count(); $i < 4; $i++)
                <label class="aspect-[3/4] rounded-lg border-2 border-dashed border-gray-200 flex flex-col items-center justify-center cursor-pointer hover:border-[#0B132B] transition-colors">
                    <input type="file" name="new_shot" accept="image/*" class="hidden" onchange="uploadShot(this)">
                    <div class="text-2xl text-gray-300 mb-1">+</div>
                    <div class="text-[9px] text-gray-300 uppercase tracking-wide">Add</div>
                </label>
                @endfor
            </div>
            <p class="text-[10px] text-gray-300">International standard: headshot, full-length, profile, editorial</p>
        </div>

        {{-- Back / portfolio --}}
        <p class="text-[10px] uppercase tracking-widest text-gray-400 mt-2 self-start">Back / Portfolio Sheet</p>
        <div id="preview-back" class="w-full flex justify-center"></div>
    </div>

    {{-- -- Col 3: Form -- --}}
    <div class="space-y-4">

        {{-- Hero photo --}}
        <div class="bg-white border border-gray-100 rounded-xl p-4">
            <p class="text-[10px] uppercase tracking-widest text-gray-400 font-semibold mb-3">Hero / Main Photo</p>
            <form method="POST" action="{{ route('models.compcard.save', $model) }}"
                  enctype="multipart/form-data" id="hero-form">
                @csrf
                <input type="hidden" name="template" id="template-input" value="{{ $card?->template ?? 'noir' }}">
                <label class="block border-2 border-dashed border-gray-200 rounded-xl cursor-pointer hover:border-[#0B132B] transition-colors overflow-hidden">
                    <input type="file" name="hero_photo" accept="image/*" class="hidden" onchange="previewHero(this)">
                    <div id="hero-preview">
                        @if($heroUrl)
                        <img src="{{ $heroUrl }}" class="w-full" style="max-height:180px;object-fit:cover">
                        @else
                        <div class="p-6 text-center">
                            <div class="text-3xl mb-2 opacity-20">&#128247;</div>
                            <div class="text-sm text-gray-400">Upload hero photo</div>
                            <div class="text-xs text-gray-300 mt-1">Full-length recommended &middot; min 800px</div>
                        </div>
                        @endif
                    </div>
                </label>
            </form>
        </div>

        {{-- Agency details --}}
        <div class="bg-white border border-gray-100 rounded-xl p-4">
            <p class="text-[10px] uppercase tracking-widest text-gray-400 font-semibold mb-3">Agency Details</p>
            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="text-[10px] text-gray-400 uppercase tracking-wide block mb-1">Agency Name</label>
                    <input id="f-agency" value="{{ old('agency_name', $card?->agency_name ?? 'Ayka Originals') }}"
                           class="w-full border border-gray-200 rounded-lg px-2.5 py-1.5 text-xs outline-none focus:border-[#0B132B]"
                           oninput="updatePreview()">
                </div>
                <div>
                    <label class="text-[10px] text-gray-400 uppercase tracking-wide block mb-1">Phone</label>
                    <input id="f-phone" value="{{ old('agency_phone', $card?->agency_phone ?? '') }}"
                           class="w-full border border-gray-200 rounded-lg px-2.5 py-1.5 text-xs outline-none focus:border-[#0B132B]"
                           oninput="updatePreview()">
                </div>
                <div>
                    <label class="text-[10px] text-gray-400 uppercase tracking-wide block mb-1">Email</label>
                    <input id="f-email" value="{{ old('agency_email', $card?->agency_email ?? 'booking@aykaoriginals.com') }}"
                           class="w-full border border-gray-200 rounded-lg px-2.5 py-1.5 text-xs outline-none focus:border-[#0B132B]"
                           oninput="updatePreview()">
                </div>
                <div>
                    <label class="text-[10px] text-gray-400 uppercase tracking-wide block mb-1">Website</label>
                    <input id="f-web" value="{{ old('agency_website', $card?->agency_website ?? 'aykaoriginals.com') }}"
                           class="w-full border border-gray-200 rounded-lg px-2.5 py-1.5 text-xs outline-none focus:border-[#0B132B]"
                           oninput="updatePreview()">
                </div>
            </div>
        </div>

        {{-- Portfolio --}}
        <div class="bg-white border border-gray-100 rounded-xl p-4">
            <p class="text-[10px] uppercase tracking-widest text-gray-400 font-semibold mb-3">Portfolio Details</p>
            <div class="space-y-2">
                <div>
                    <label class="text-[10px] text-gray-400 uppercase tracking-wide block mb-1">Notable Clients / Brands</label>
                    <textarea id="f-clients" rows="2"
                        class="w-full border border-gray-200 rounded-lg px-2.5 py-1.5 text-xs outline-none focus:border-[#0B132B] resize-none"
                        placeholder="Dior, Vogue Arabia, H&M MENA...">{{ old('notable_clients', $card?->notable_clients ?? '') }}</textarea>
                </div>
                <div>
                    <label class="text-[10px] text-gray-400 uppercase tracking-wide block mb-1">Recent Campaigns</label>
                    <textarea id="f-campaigns" rows="2"
                        class="w-full border border-gray-200 rounded-lg px-2.5 py-1.5 text-xs outline-none focus:border-[#0B132B] resize-none"
                        placeholder="Ramadan Campaign 2024 - Chalhoub Group">{{ old('recent_campaigns', $card?->recent_campaigns ?? '') }}</textarea>
                </div>
                <div>
                    <label class="text-[10px] text-gray-400 uppercase tracking-wide block mb-1">Special Skills</label>
                    <input id="f-skills"
                           value="{{ old('special_skills', $card?->special_skills ?? '') }}"
                           class="w-full border border-gray-200 rounded-lg px-2.5 py-1.5 text-xs outline-none focus:border-[#0B132B]"
                           placeholder="Runway, Swimwear, Bilingual...">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-2 mt-2">
                <div>
                    <label class="text-[10px] text-gray-400 uppercase tracking-wide block mb-1">Day Rate (AED)</label>
                    <input id="f-dayrate" type="number"
                           value="{{ old('day_rate', $card?->day_rate ?? '') }}"
                           class="w-full border border-gray-200 rounded-lg px-2.5 py-1.5 text-xs outline-none focus:border-[#0B132B]">
                </div>
                <div>
                    <label class="text-[10px] text-gray-400 uppercase tracking-wide block mb-1">Half Day (AED)</label>
                    <input id="f-halfday" type="number"
                           value="{{ old('half_day_rate', $card?->half_day_rate ?? '') }}"
                           class="w-full border border-gray-200 rounded-lg px-2.5 py-1.5 text-xs outline-none focus:border-[#0B132B]">
                </div>
            </div>
        </div>

        {{-- Send to client --}}
        <div class="bg-white border border-gray-100 rounded-xl p-4">
            <p class="text-[10px] uppercase tracking-widest text-gray-400 font-semibold mb-3">
                &#9993; Send Profile to Client
            </p>
            <form method="POST" action="{{ route('models.compcard.send', $model) }}" id="send-form">
                @csrf
                <div class="space-y-2">
                    <div>
                        <label class="text-[10px] text-gray-400 uppercase tracking-wide block mb-1">To (email addresses)</label>
                        <input name="emails[]"
                               class="w-full border border-gray-200 rounded-lg px-2.5 py-1.5 text-xs outline-none focus:border-[#0B132B]"
                               placeholder="casting@client.com, director@brand.com"
                               id="f-to">
                    </div>
                    <div>
                        <label class="text-[10px] text-gray-400 uppercase tracking-wide block mb-1">Subject</label>
                        <input name="subject"
                               value="Model Profile - {{ $model->name }} | Ayka Originals"
                               class="w-full border border-gray-200 rounded-lg px-2.5 py-1.5 text-xs outline-none focus:border-[#0B132B]">
                    </div>
                    <div>
                        <label class="text-[10px] text-gray-400 uppercase tracking-wide block mb-1">Message</label>
                        <textarea name="message" rows="4"
                            class="w-full border border-gray-200 rounded-lg px-2.5 py-1.5 text-xs outline-none focus:border-[#0B132B] resize-none">Dear Casting Team,

Please find the profile of {{ $model->name }}, represented by Ayka Originals.

{{ $model->height ? 'Height: '.$model->height.' | ' : '' }}{{ $model->bust ? 'B: '.$model->bust.'  W: '.$model->waist.'  H: '.$model->hips : '' }}

We look forward to collaborating.

Best regards,
Ayka Originals</textarea>
                    </div>
                    <div class="flex gap-3 text-xs pt-1">
                        <label class="flex items-center gap-1.5 cursor-pointer">
                            <input type="checkbox" name="attach_pdf" value="1" checked class="rounded"> PDF Card
                        </label>
                        <label class="flex items-center gap-1.5 cursor-pointer">
                            <input type="checkbox" name="attach_portfolio" value="1" checked class="rounded"> Portfolio
                        </label>
                    </div>
                </div>
            </form>
        </div>

        {{-- Shares history --}}
        @if($card && $card->shares->count())
        <div class="bg-white border border-gray-100 rounded-xl p-4">
            <p class="text-[10px] uppercase tracking-widest text-gray-400 font-semibold mb-3">Send History</p>
            @foreach($card->shares()->latest()->take(5)->get() as $share)
            <div class="flex items-center justify-between py-1.5 border-b border-gray-50 last:border-0">
                <div>
                    <div class="text-xs font-medium text-gray-700">{{ $share->recipient_email }}</div>
                    <div class="text-[10px] text-gray-400">{{ $share->created_at->diffForHumans() }}</div>
                </div>
                @if($share->opened_at)
                <span class="text-[10px] bg-green-50 text-green-700 px-2 py-0.5 rounded-full">Opened</span>
                @else
                <span class="text-[10px] bg-gray-50 text-gray-400 px-2 py-0.5 rounded-full">Sent</span>
                @endif
            </div>
            @endforeach
        </div>
        @endif

        {{-- Action buttons --}}
        <div class="flex gap-3 sticky bottom-0 bg-gray-50 py-3">
            <button onclick="submitSend()"
                class="flex-1 bg-[#0B132B] text-white py-3 rounded-xl text-sm font-semibold hover:bg-[#1a2a4a] transition-colors">
                &#9993; Send to Client
            </button>
            <button onclick="submitSave()"
                class="flex-1 py-3 rounded-xl text-sm font-bold transition-colors"
                style="background:#C9A96E;color:#0B132B">
                &#10003; Save Card
            </button>
        </div>
    </div>
</div>

{{-- Hidden save form --}}
<form method="POST" action="{{ route('models.compcard.save', $model) }}"
      enctype="multipart/form-data" id="save-form" style="display:none">
    @csrf
    <input type="hidden" name="template" id="save-template" value="{{ $card?->template ?? 'noir' }}">
    <input type="hidden" name="agency_name" id="save-agency">
    <input type="hidden" name="agency_phone" id="save-phone">
    <input type="hidden" name="agency_email" id="save-email">
    <input type="hidden" name="agency_website" id="save-web">
    <input type="hidden" name="notable_clients" id="save-clients">
    <input type="hidden" name="recent_campaigns" id="save-campaigns">
    <input type="hidden" name="special_skills" id="save-skills">
    <input type="hidden" name="day_rate" id="save-dayrate">
    <input type="hidden" name="half_day_rate" id="save-halfday">
</form>

<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;1,300;1,400&family=Bebas+Neue&family=Space+Mono:wght@700&display=swap" rel="stylesheet">

<script>
// -- Data from server -------------------------------------------
const MODEL = @json($md);
const SHOTS = @json($shots->map(fn($s) => $s->getUrl())->values());
const HERO  = '{{ $heroUrl }}';

let activeTemplate = '{{ $card?->template ?? "noir" }}';

// -- Template selection -----------------------------------------
function selectTemplate(key, el) {
    document.querySelectorAll('.template-opt').forEach(o => {
        o.classList.remove('border-[#0B132B]', 'ring-1', 'ring-[#0B132B]');
        o.classList.add('border-gray-100');
    });
    el.classList.add('border-[#0B132B]', 'ring-1', 'ring-[#0B132B]');
    el.classList.remove('border-gray-100');
    activeTemplate = key;
    document.getElementById('template-input').value = key;
    document.getElementById('save-template').value = key;
    renderPreview();
}

// -- Hero photo preview -----------------------------------------
function previewHero(input) {
    if (input.files && input.files[0]) {
        const url = URL.createObjectURL(input.files[0]);
        MODEL.heroUrl = url;
        document.getElementById('hero-preview').innerHTML =
            '<img src="' + url + '" class="w-full" style="max-height:180px;object-fit:cover">';
        document.getElementById('hero-form').submit();
    }
}

// -- Upload shot via fetch --------------------------------------
function uploadShot(input) {
    if (!input.files || !input.files[0]) return;
    const fd = new FormData();
    fd.append('_token', '{{ csrf_token() }}');
    fd.append('shot', input.files[0]);
    fetch('{{ route("models.compcard.uploadshot", $model) }}', { method:'POST', body:fd })
        .then(r => r.json())
        .then(d => { if (d.url) { location.reload(); } });
}

// -- Collect form data ------------------------------------------
function getFormData() {
    return {
        agency:    document.getElementById('f-agency').value,
        phone:     document.getElementById('f-phone').value,
        email:     document.getElementById('f-email').value,
        web:       document.getElementById('f-web').value,
        clients:   document.getElementById('f-clients').value,
        campaigns: document.getElementById('f-campaigns').value,
        skills:    document.getElementById('f-skills').value,
    };
}

// -- Render: shared helpers ------------------------------------
function stat(val, lbl, color) {
    if (!val) return '';
    return '<div style="text-align:center;flex:1">'
         + '<div style="font-size:13px;font-weight:600;color:' + color + '">' + val + '</div>'
         + '<div style="font-size:7px;letter-spacing:1.5px;text-transform:uppercase;opacity:.4;margin-top:2px">' + lbl + '</div>'
         + '</div>';
}

function heroBlock(bg, initial, accent) {
    if (MODEL.heroUrl) {
        return '<img src="' + MODEL.heroUrl + '" style="width:100%;height:180px;object-fit:cover;display:block">';
    }
    return '<div style="width:100%;height:180px;background:' + bg + ';display:flex;align-items:center;justify-content:center;font-size:60px;color:' + accent + ';opacity:.15;font-style:italic">' + MODEL.name.charAt(0) + '</div>';
}

function shotsMini(count, bg) {
    let html = '<div style="display:flex;gap:3px;padding:0 12px 12px">';
    for (let i = 0; i < count; i++) {
        if (SHOTS[i]) {
            html += '<div style="flex:1;height:56px;border-radius:3px;overflow:hidden"><img src="' + SHOTS[i] + '" style="width:100%;height:100%;object-fit:cover"></div>';
        } else {
            html += '<div style="flex:1;height:56px;border-radius:3px;background:' + bg + '"></div>';
        }
    }
    html += '</div>';
    return html;
}

// -- Render front card -----------------------------------------
function renderFront() {
    const d = getFormData();
    const statsRow = '<div style="display:flex;padding:10px 14px;border-top:1px solid rgba(255,255,255,.08);margin-top:8px">'
        + stat(MODEL.height,'Height','#C9A96E') + stat(MODEL.bust,'Bust','#C9A96E')
        + stat(MODEL.waist,'Waist','#C9A96E') + stat(MODEL.hips,'Hips','#C9A96E')
        + stat(MODEL.shoes,'Shoes','#C9A96E') + '</div>';

    const statsRowLight = '<div style="display:flex;padding:10px 14px;border-top:1px solid #E8E6E0;border-bottom:1px solid #E8E6E0;margin:8px 0">'
        + stat(MODEL.height,'H','#0B132B') + stat(MODEL.bust,'B','#0B132B')
        + stat(MODEL.waist,'W','#0B132B') + stat(MODEL.hips,'Hip','#0B132B')
        + stat(MODEL.shoes,'Shoe','#0B132B') + '</div>';

    const contactDark = '<div style="padding:8px 14px;border-top:1px solid rgba(255,255,255,.08);display:flex;justify-content:space-between">'
        + '<div style="font-size:8px;color:rgba(255,255,255,.3)">' + (d.phone||'') + '</div>'
        + '<div style="font-size:8px;color:rgba(255,255,255,.3)">' + (d.email||'') + '</div>'
        + '</div>';

    const contactLight = '<div style="padding:8px 14px;display:flex;justify-content:space-between">'
        + '<div style="font-size:8px;color:#8A8880">' + (d.email||'') + '</div>'
        + '<div style="font-size:8px;color:#8A8880">' + (d.phone||'') + '</div>'
        + '</div>';

    let card = '';

    if (activeTemplate === 'noir') {
        card = '<div style="background:#0a0a0a;color:#fff;width:260px;border-radius:8px;overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,.4);font-family:\'Cormorant Garamond\',Georgia,serif">'
            + '<div style="padding:16px 16px 0;display:flex;justify-content:space-between;align-items:center">'
            + '<div style="font-size:6px;letter-spacing:3px;text-transform:uppercase;color:#C9A96E;font-family:sans-serif">' + (d.agency||'') + '</div>'
            + '</div>'
            + heroBlock('linear-gradient(135deg,#1a1a2e,#0f3460)', MODEL.name.charAt(0), '#C9A96E')
            + '<div style="padding:12px 16px 0">'
            + '<div style="font-size:24px;font-weight:300;font-style:italic;line-height:1;color:#fff">' + MODEL.name + '</div>'
            + '<div style="width:28px;height:1px;background:#C9A96E;margin:7px 0"></div>'
            + '<div style="font-size:7px;letter-spacing:2px;text-transform:uppercase;color:rgba(255,255,255,.35);font-family:sans-serif">' + MODEL.cats + '</div>'
            + '</div>'
            + statsRow + shotsMini(3,'#1a1a2e') + contactDark
            + '</div>';

    } else if (activeTemplate === 'clean') {
        card = '<div style="background:#fff;width:260px;border-radius:8px;overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,.12);font-family:Georgia,serif">'
            + '<div style="height:4px;background:linear-gradient(90deg,#0B132B,#C9A96E)"></div>'
            + '<div style="padding:12px 14px 0;display:flex;justify-content:space-between">'
            + '<div style="font-size:6px;letter-spacing:2px;text-transform:uppercase;color:#8A8880;font-family:sans-serif">' + (d.agency||'') + '</div>'
            + '</div>'
            + heroBlock('#f0ede8', MODEL.name.charAt(0), '#0B132B')
            + '<div style="padding:12px 14px 0;display:flex;align-items:baseline;gap:8px">'
            + '<div style="font-size:20px;font-weight:700;color:#0B132B">' + MODEL.name + '</div>'
            + (MODEL.age ? '<div style="font-size:10px;color:#8A8880;font-family:sans-serif">' + MODEL.age + ' yrs</div>' : '')
            + '</div>'
            + statsRowLight + shotsMini(4,'#f0ede8') + contactLight
            + '</div>';

    } else if (activeTemplate === 'bold') {
        card = '<div style="background:#0a0a0a;width:260px;border-radius:8px;overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,.4)">'
            + '<div style="background:#000;padding:8px 14px;display:flex;justify-content:space-between">'
            + '<div style="font-size:9px;letter-spacing:3px;color:#C9A96E;font-family:\'Bebas Neue\',sans-serif">' + (d.agency||'') + '</div>'
            + '<div style="font-size:7px;letter-spacing:2px;color:rgba(255,255,255,.3);font-family:sans-serif;text-transform:uppercase">' + MODEL.cats + '</div>'
            + '</div>'
            + '<div style="display:flex">'
            + '<div style="flex:2">' + heroBlock('linear-gradient(160deg,#1a1a2e,#0f3460)', MODEL.name.charAt(0),'#C9A96E') + '</div>'
            + '<div style="flex:1;background:#111;padding:10px 8px;display:flex;flex-direction:column;justify-content:space-between">'
            + '<div style="font-family:\'Bebas Neue\',sans-serif;font-size:28px;color:#fff;letter-spacing:2px;line-height:.95;writing-mode:vertical-rl;transform:rotate(180deg)">' + MODEL.name.split(' ')[0] + '</div>'
            + '<div>' + (MODEL.height?'<div style="font-size:14px;font-family:\'Bebas Neue\',sans-serif;color:#C9A96E">'+MODEL.height+'</div><div style="font-size:6px;letter-spacing:1px;text-transform:uppercase;color:rgba(255,255,255,.3);margin-bottom:4px;font-family:sans-serif">Height</div>':'')
            + (MODEL.waist?'<div style="font-size:14px;font-family:\'Bebas Neue\',sans-serif;color:#C9A96E">'+MODEL.waist+'</div><div style="font-size:6px;letter-spacing:1px;text-transform:uppercase;color:rgba(255,255,255,.3);font-family:sans-serif">Waist</div>':'') + '</div>'
            + '</div></div>'
            + '<div style="height:3px;background:#C9A96E"></div>'
            + shotsMini(3,'#222')
            + '<div style="padding:6px 14px;display:flex;justify-content:space-between">'
            + '<div style="font-size:7px;color:rgba(255,255,255,.3)">' + (d.phone||'') + '</div>'
            + '<div style="font-size:7px;color:rgba(255,255,255,.3)">' + (d.email||'') + '</div>'
            + '</div>'
            + '</div>';

    } else if (activeTemplate === 'luxury') {
        card = '<div style="background:#F7F5F2;width:260px;border-radius:8px;overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,.12)">'
            + heroBlock('linear-gradient(160deg,#e8e2d8,#d5cdc0)', MODEL.name.charAt(0),'#C9A96E')
            + '<div style="padding:14px 16px">'
            + '<div style="font-size:7px;letter-spacing:3px;text-transform:uppercase;color:#C9A96E;margin-bottom:5px;font-family:sans-serif">' + (d.agency||'') + '</div>'
            + '<div style="font-family:\'Cormorant Garamond\',Georgia,serif;font-size:22px;font-weight:300;letter-spacing:-0.5px;color:#1a1a1a;line-height:1.1">' + MODEL.name + '</div>'
            + '<div style="font-size:10px;color:#999;margin-bottom:10px;font-family:sans-serif">' + MODEL.cats + (MODEL.age?' &mdash; '+MODEL.age+' yrs':'') + '</div>'
            + '<div style="display:flex;border-top:1px solid #e0dcd6;border-bottom:1px solid #e0dcd6;padding:8px 0;margin-bottom:10px">'
            + stat(MODEL.height,'H','#1a1a1a') + stat(MODEL.bust+'/'+MODEL.waist,'B/W','#1a1a1a') + stat(MODEL.hips,'Hip','#1a1a1a') + stat(MODEL.shoes,'Shoe','#1a1a1a')
            + '</div>'
            + shotsMini(4,'#e8e2d8')
            + '<div style="display:flex;justify-content:space-between;font-size:7px;color:#bbb;letter-spacing:1px;text-transform:uppercase;font-family:sans-serif">'
            + '<div>' + (d.phone||'') + '</div><div>' + (d.email||'') + '</div>'
            + '</div></div></div>';

    } else {
        // typographic
        card = '<div style="background:#FFFFF0;width:260px;border-radius:8px;overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,.12);font-family:\'Space Mono\',monospace">'
            + '<div style="padding:10px 14px;border-bottom:2px solid #0a0a0a;display:flex;justify-content:space-between;align-items:baseline">'
            + '<div style="font-size:8px;letter-spacing:2px;text-transform:uppercase">' + (d.agency||'') + '</div>'
            + '<div style="font-size:20px;font-weight:700;opacity:.12">01</div>'
            + '</div>'
            + heroBlock('repeating-linear-gradient(45deg,#f0ede0 0,#f0ede0 2px,#FFFFF0 2px,#FFFFF0 12px)', MODEL.name.charAt(0),'#0a0a0a')
            + '<div style="padding:10px 14px">'
            + '<div style="font-size:20px;font-weight:700;letter-spacing:-1px;border-bottom:2px solid #0a0a0a;padding-bottom:8px;margin-bottom:8px">' + MODEL.name.toUpperCase() + '</div>'
            + '<div style="display:grid;grid-template-columns:1fr 1fr;gap:0">'
            + (MODEL.height ? '<div style="display:flex;justify-content:space-between;padding:3px 0;border-bottom:1px solid rgba(0,0,0,.08);font-size:9px"><span style="opacity:.4;text-transform:uppercase">H</span><strong>' + MODEL.height + '</strong></div>' : '')
            + (MODEL.bust   ? '<div style="display:flex;justify-content:space-between;padding:3px 0;border-bottom:1px solid rgba(0,0,0,.08);font-size:9px"><span style="opacity:.4;text-transform:uppercase">B</span><strong>' + MODEL.bust + '</strong></div>' : '')
            + (MODEL.waist  ? '<div style="display:flex;justify-content:space-between;padding:3px 0;border-bottom:1px solid rgba(0,0,0,.08);font-size:9px"><span style="opacity:.4;text-transform:uppercase">W</span><strong>' + MODEL.waist + '</strong></div>' : '')
            + (MODEL.hips   ? '<div style="display:flex;justify-content:space-between;padding:3px 0;border-bottom:1px solid rgba(0,0,0,.08);font-size:9px"><span style="opacity:.4;text-transform:uppercase">Hip</span><strong>' + MODEL.hips + '</strong></div>' : '')
            + (MODEL.shoes  ? '<div style="display:flex;justify-content:space-between;padding:3px 0;font-size:9px"><span style="opacity:.4;text-transform:uppercase">Shoe</span><strong>' + MODEL.shoes + '</strong></div>' : '')
            + '</div>'
            + shotsMini(4,'#e8e4d8')
            + '</div>'
            + '<div style="padding:8px 14px;border-top:2px solid #0a0a0a;display:flex;justify-content:space-between;font-size:7px;text-transform:uppercase;letter-spacing:1px;opacity:.4">'
            + '<div>' + (d.phone||'') + '</div><div>' + (d.web||'') + '</div>'
            + '</div>'
            + '</div>';
    }

    return card;
}

// -- Render back / portfolio sheet -----------------------------
function renderBack() {
    const d = getFormData();
    let photos = '<div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:3px;padding:8px">';
    for (let i = 0; i < 6; i++) {
        if (SHOTS[i]) {
            photos += '<div style="height:90px;border-radius:3px;overflow:hidden"><img src="' + SHOTS[i] + '" style="width:100%;height:100%;object-fit:cover"></div>';
        } else {
            photos += '<div style="height:90px;border-radius:3px;background:#f0ede8"></div>';
        }
    }
    photos += '</div>';

    return '<div style="background:#fff;width:260px;border-radius:8px;overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,.12)">'
        + '<div style="background:#0B132B;padding:12px 14px;display:flex;justify-content:space-between;align-items:center">'
        + '<div style="font-family:Georgia,serif;color:#fff;font-style:italic;font-size:14px">' + MODEL.name + '</div>'
        + '<div style="font-size:6px;letter-spacing:2px;color:#C9A96E;text-transform:uppercase">' + (d.agency||'Ayka Originals') + '</div>'
        + '</div>'
        + photos
        + '<div style="padding:8px 12px;border-top:1px solid #E8E6E0;display:grid;grid-template-columns:1fr 1fr;gap:8px;font-size:8px;color:#5E6472">'
        + '<div><strong style="color:#0B132B;display:block;margin-bottom:3px;font-size:7px;letter-spacing:1px;text-transform:uppercase">Measurements</strong>'
        + (MODEL.height?'H: '+MODEL.height+'<br>':'') + (MODEL.bust?'B: '+MODEL.bust+'<br>':'')
        + (MODEL.waist?'W: '+MODEL.waist+'<br>':'') + (MODEL.hips?'Hips: '+MODEL.hips+'<br>':'') + (MODEL.shoes?'Shoes: '+MODEL.shoes:'') + '</div>'
        + '<div><strong style="color:#0B132B;display:block;margin-bottom:3px;font-size:7px;letter-spacing:1px;text-transform:uppercase">Contact</strong>'
        + (d.phone||'') + '<br>' + (d.email||'') + '<br>' + (d.web||'') + '</div>'
        + '</div>'
        + '</div>';
}

function updatePreview() {
    document.getElementById('preview-front').innerHTML = renderFront();
    document.getElementById('preview-back').innerHTML  = renderBack();
}

// -- Save + Send -----------------------------------------------
function submitSave() {
    const f = document.getElementById('save-form');
    document.getElementById('save-template').value   = activeTemplate;
    document.getElementById('save-agency').value     = document.getElementById('f-agency').value;
    document.getElementById('save-phone').value      = document.getElementById('f-phone').value;
    document.getElementById('save-email').value      = document.getElementById('f-email').value;
    document.getElementById('save-web').value        = document.getElementById('f-web').value;
    document.getElementById('save-clients').value    = document.getElementById('f-clients').value;
    document.getElementById('save-campaigns').value  = document.getElementById('f-campaigns').value;
    document.getElementById('save-skills').value     = document.getElementById('f-skills').value;
    document.getElementById('save-dayrate').value    = document.getElementById('f-dayrate').value;
    document.getElementById('save-halfday').value    = document.getElementById('f-halfday').value;
    f.submit();
}

function submitSend() {
    const to = document.getElementById('f-to').value.trim();
    if (!to) { alert('Please enter at least one recipient email address.'); return; }
    document.getElementById('send-form').submit();
}

// -- Init ------------------------------------------------------
updatePreview();
</script>
@endsection