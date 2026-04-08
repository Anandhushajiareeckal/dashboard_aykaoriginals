<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>{{ $card->talentModel->name }} - Ayka Originals</title>
<meta property="og:title" content="{{ $card->talentModel->name }} | Ayka Originals">
<meta property="og:description" content="Model represented by Ayka Originals">
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;1,400&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<script src="https://cdn.tailwindcss.com"></script>
<style>
body { font-family: 'DM Sans', sans-serif; }
.font-display { font-family: 'Cormorant Garamond', serif; }
</style>
</head>
<body style="background:linear-gradient(135deg,#0B132B 0%,#162040 100%);min-height:100vh">

{{-- Header --}}
<div class="border-b border-white/10 px-6 py-4 flex items-center justify-between">
    <div>
        <div class="font-display text-white text-xl font-semibold italic">Ayka Originals</div>
        <div class="text-[10px] tracking-widest uppercase" style="color:#C9A96E">Talent &amp; Production</div>
    </div>
    @if($card->agency_email)
    <a href="mailto:{{ $card->agency_email }}"
       class="text-xs px-4 py-2 rounded-lg font-semibold transition-colors"
       style="border:1px solid #C9A96E;color:#C9A96E"
       onmouseover="this.style.background='#C9A96E';this.style.color='#0B132B'"
       onmouseout="this.style.background='transparent';this.style.color='#C9A96E'">
        Book Now
    </a>
    @endif
</div>

<div class="max-w-4xl mx-auto px-6 py-10">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">

        {{-- Left: Hero photo + name --}}
        <div>
            @php
                $heroUrl = $card->talentModel->getFirstMediaUrl('compcard_hero');
                $initial = strtoupper(substr($card->talentModel->name, 0, 1));
                $firstName = explode(' ', $card->talentModel->name)[0];
            @endphp

            @if($heroUrl)
            <img src="{{ $heroUrl }}"
                 class="w-full rounded-2xl mb-6"
                 style="aspect-ratio:3/4;object-fit:cover">
            @else
            <div class="w-full rounded-2xl mb-6 flex items-center justify-center"
                 style="aspect-ratio:3/4;background:linear-gradient(135deg,#162040,#1a2a4a)">
                <span class="font-display text-9xl italic" style="color:rgba(201,169,110,0.15)">{{ $initial }}</span>
            </div>
            @endif

            <h1 class="font-display text-4xl text-white font-light italic mb-3">
                {{ $card->talentModel->name }}
            </h1>

            @php
                $categories = is_array($card->talentModel->categories)
                    ? $card->talentModel->categories
                    : (is_string($card->talentModel->categories) && $card->talentModel->categories
                        ? json_decode($card->talentModel->categories, true) ?? [$card->talentModel->categories]
                        : []);
            @endphp

            <div class="flex flex-wrap gap-2 mb-4">
                @foreach($categories as $cat)
                <span class="text-xs px-3 py-1 rounded-full"
                      style="background:rgba(201,169,110,0.15);color:#C9A96E">{{ $cat }}</span>
                @endforeach
                @if($card->talentModel->is_inhouse)
                <span class="text-xs px-3 py-1 rounded-full"
                      style="background:rgba(74,222,128,0.15);color:#4ade80">In-house</span>
                @endif
                @if($card->talentModel->location)
                <span class="text-xs px-3 py-1 rounded-full text-white/50"
                      style="background:rgba(255,255,255,0.06)">{{ $card->talentModel->location }}</span>
                @endif
            </div>

            @if($card->special_skills)
            <p class="text-sm leading-relaxed" style="color:rgba(255,255,255,0.45)">
                {{ $card->special_skills }}
            </p>
            @endif
        </div>

        {{-- Right: Stats + Contact --}}
        <div class="space-y-4">

            {{-- Measurements --}}
            <div class="rounded-2xl p-6"
                 style="background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.08)">
                <h3 class="text-xs uppercase tracking-widest mb-4" style="color:#C9A96E">Measurements</h3>
                <div class="grid grid-cols-3 gap-4">
                    @foreach([
                        'height'    => 'Height',
                        'bust'      => 'Bust',
                        'waist'     => 'Waist',
                        'hips'      => 'Hips',
                        'shoe_size' => 'Shoes',
                        'age'       => 'Age',
                    ] as $field => $label)
                    @if(!empty($card->talentModel->$field))
                    <div class="text-center">
                        <div class="font-display text-2xl text-white font-light">
                            {{ $card->talentModel->$field }}{{ $field === 'age' ? ' yrs' : '' }}
                        </div>
                        <div class="text-xs uppercase tracking-widest mt-1"
                             style="color:rgba(255,255,255,0.3)">{{ $label }}</div>
                    </div>
                    @endif
                    @endforeach
                </div>

                {{-- Hair & Eyes --}}
                @if($card->talentModel->hair_color || $card->talentModel->eye_color)
                <div class="mt-4 pt-4 flex gap-6" style="border-top:1px solid rgba(255,255,255,0.08)">
                    @if($card->talentModel->hair_color)
                    <div class="text-center">
                        <div class="text-sm text-white font-medium">{{ $card->talentModel->hair_color }}</div>
                        <div class="text-xs uppercase tracking-widest mt-0.5" style="color:rgba(255,255,255,0.3)">Hair</div>
                    </div>
                    @endif
                    @if($card->talentModel->eye_color)
                    <div class="text-center">
                        <div class="text-sm text-white font-medium">{{ $card->talentModel->eye_color }}</div>
                        <div class="text-xs uppercase tracking-widest mt-0.5" style="color:rgba(255,255,255,0.3)">Eyes</div>
                    </div>
                    @endif
                    @if($card->talentModel->dress_size)
                    <div class="text-center">
                        <div class="text-sm text-white font-medium">{{ $card->talentModel->dress_size }}</div>
                        <div class="text-xs uppercase tracking-widest mt-0.5" style="color:rgba(255,255,255,0.3)">Dress</div>
                    </div>
                    @endif
                </div>
                @endif
            </div>

            {{-- Notable Clients --}}
            @if($card->notable_clients)
            <div class="rounded-2xl p-5"
                 style="background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.08)">
                <h3 class="text-xs uppercase tracking-widest mb-3" style="color:#C9A96E">Notable Clients</h3>
                <p class="text-sm leading-relaxed" style="color:rgba(255,255,255,0.6)">
                    {{ $card->notable_clients }}
                </p>
            </div>
            @endif

            {{-- Recent Campaigns --}}
            @if($card->recent_campaigns)
            <div class="rounded-2xl p-5"
                 style="background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.08)">
                <h3 class="text-xs uppercase tracking-widest mb-3" style="color:#C9A96E">Recent Campaigns</h3>
                <p class="text-sm leading-relaxed whitespace-pre-line" style="color:rgba(255,255,255,0.6)">
                    {{ $card->recent_campaigns }}
                </p>
            </div>
            @endif

            {{-- Booking / Contact --}}
            <div class="rounded-2xl p-5"
                 style="background:rgba(201,169,110,0.08);border:1px solid rgba(201,169,110,0.2)">
                <h3 class="text-xs uppercase tracking-widest mb-3" style="color:#C9A96E">Booking</h3>
                <div class="space-y-2">
                    <div class="flex justify-between text-sm">
                        <span style="color:rgba(255,255,255,0.4)">Agency</span>
                        <span class="text-white font-medium">{{ $card->agency_name ?? 'Ayka Originals' }}</span>
                    </div>
                    @if($card->agency_phone)
                    <div class="flex justify-between text-sm">
                        <span style="color:rgba(255,255,255,0.4)">Phone</span>
                        <a href="tel:{{ $card->agency_phone }}" style="color:#C9A96E">{{ $card->agency_phone }}</a>
                    </div>
                    @endif
                    @if($card->agency_email)
                    <div class="flex justify-between text-sm">
                        <span style="color:rgba(255,255,255,0.4)">Email</span>
                        <a href="mailto:{{ $card->agency_email }}" style="color:#C9A96E">{{ $card->agency_email }}</a>
                    </div>
                    @endif
                    @if($card->agency_website)
                    <div class="flex justify-between text-sm">
                        <span style="color:rgba(255,255,255,0.4)">Web</span>
                        <a href="https://{{ $card->agency_website }}" target="_blank" style="color:#C9A96E">
                            {{ $card->agency_website }}
                        </a>
                    </div>
                    @endif
                    @if($card->day_rate)
                    <div class="flex justify-between text-sm mt-2 pt-2"
                         style="border-top:1px solid rgba(201,169,110,0.2)">
                        <span style="color:rgba(255,255,255,0.4)">Day Rate</span>
                        <span class="text-white font-semibold">AED {{ number_format($card->day_rate) }}</span>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Portfolio Gallery --}}
    @php
        $shots     = $card->talentModel->getMedia('compcard_shots');
        $portfolio = $card->talentModel->getMedia('portfolio');
        $allPhotos = $shots->concat($portfolio)->take(12);
    @endphp

    @if($allPhotos->count() > 0)
    <div class="mt-10">
        <h2 class="font-display text-3xl text-white font-light italic mb-6">Portfolio</h2>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
            @foreach($allPhotos as $media)
            <div class="rounded-xl overflow-hidden cursor-pointer transition-opacity hover:opacity-90"
                 style="aspect-ratio:3/4"
                 onclick="openLightbox('{{ $media->getUrl() }}')">
                <img src="{{ $media->getUrl() }}"
                     class="w-full h-full"
                     style="object-fit:cover"
                     loading="lazy">
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Share / Download CTA --}}
    <div class="mt-10 rounded-2xl p-6 text-center"
         style="background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.08)">
        <p class="text-xs uppercase tracking-widest mb-2" style="color:rgba(255,255,255,0.3)">
            Interested in working with {{ $firstName }}?
        </p>
        @if($card->agency_email)
        <a href="mailto:{{ $card->agency_email }}?subject=Booking Enquiry - {{ $card->talentModel->name }}"
           class="inline-block px-8 py-3 rounded-xl font-semibold text-sm transition-colors"
           style="background:#C9A96E;color:#0B132B">
            Send Booking Enquiry
        </a>
        @endif
    </div>
</div>

{{-- Lightbox --}}
<div id="lightbox"
     onclick="closeLightbox()"
     style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.95);z-index:100;align-items:center;justify-content:center;cursor:zoom-out">
    <img id="lightbox-img" src="" style="max-height:90vh;max-width:90vw;border-radius:8px;object-fit:contain">
</div>

<div class="text-center py-8 text-xs tracking-widest"
     style="color:rgba(255,255,255,0.2)">
    AYKA ORIGINALS &nbsp;&middot;&nbsp; TALENT &amp; PRODUCTION MANAGEMENT
    &nbsp;&middot;&nbsp; {{ number_format($card->view_count) }} VIEWS
</div>

<script>
function openLightbox(src) {
    document.getElementById('lightbox-img').src = src;
    document.getElementById('lightbox').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}
function closeLightbox() {
    document.getElementById('lightbox').style.display = 'none';
    document.body.style.overflow = '';
}
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeLightbox();
});
</script>
</body>
</html>