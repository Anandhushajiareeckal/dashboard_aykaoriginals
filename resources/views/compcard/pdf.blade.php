<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }
body {
    font-family: 'DejaVu Sans', sans-serif;
    width: 420px;
    background: #0a0a0a;
    color: #fff;
}

/* -- FRONT PAGE -- */
.front {
    width: 420px;
    min-height: 595px;
    background: #0a0a0a;
    position: relative;
    page-break-after: always;
}
.front-header {
    padding: 18px 22px 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.agency-name {
    font-size: 7px;
    letter-spacing: 3px;
    text-transform: uppercase;
    color: #C9A96E;
}
.agency-tagline {
    font-size: 7px;
    letter-spacing: 2px;
    color: rgba(255,255,255,0.25);
    text-transform: uppercase;
}
.hero-section {
    display: block;
    width: 100%;
    margin-top: 14px;
    position: relative;
}
.hero-img {
    width: 100%;
    height: 240px;
    object-fit: cover;
    display: block;
}
.hero-placeholder {
    width: 100%;
    height: 240px;
    background: linear-gradient(135deg, #1a1a2e 0%, #162040 50%, #0f3460 100%);
    display: flex;
    align-items: center;
    justify-content: center;
}
.hero-initial {
    font-size: 80px;
    color: rgba(201,169,110,0.15);
    font-style: italic;
}
.name-block {
    padding: 14px 22px 0;
}
.model-name {
    font-size: 30px;
    font-weight: 300;
    font-style: italic;
    color: #fff;
    line-height: 1;
    letter-spacing: -0.5px;
}
.gold-line {
    width: 36px;
    height: 1px;
    background: #C9A96E;
    margin: 10px 0;
}
.model-category {
    font-size: 7px;
    letter-spacing: 2.5px;
    text-transform: uppercase;
    color: rgba(255,255,255,0.35);
}
.stats-row {
    display: flex;
    border-top: 1px solid rgba(255,255,255,0.08);
    margin-top: 14px;
    padding: 12px 22px;
}
.stat-cell {
    flex: 1;
    text-align: center;
}
.stat-val {
    font-size: 13px;
    font-weight: 600;
    color: #C9A96E;
    display: block;
}
.stat-lbl {
    font-size: 7px;
    letter-spacing: 1.5px;
    text-transform: uppercase;
    color: rgba(255,255,255,0.3);
    display: block;
    margin-top: 2px;
}
.extra-stats {
    padding: 0 22px 12px;
    display: flex;
    gap: 20px;
}
.extra-item {
    font-size: 9px;
    color: rgba(255,255,255,0.4);
}
.extra-item strong {
    color: rgba(255,255,255,0.7);
    font-size: 10px;
}
.contact-bar {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    padding: 12px 22px;
    border-top: 1px solid rgba(255,255,255,0.08);
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.contact-item {
    font-size: 8px;
    color: rgba(255,255,255,0.3);
    letter-spacing: 0.5px;
}
.contact-item strong {
    display: block;
    font-size: 7px;
    letter-spacing: 1.5px;
    text-transform: uppercase;
    color: rgba(255,255,255,0.2);
    margin-bottom: 2px;
}

/* -- BACK PAGE -- */
.back {
    width: 420px;
    min-height: 595px;
    background: #fff;
    color: #0B132B;
}
.back-header {
    background: #0B132B;
    padding: 14px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.back-name {
    font-size: 16px;
    font-style: italic;
    color: #fff;
}
.back-agency {
    font-size: 7px;
    letter-spacing: 2px;
    text-transform: uppercase;
    color: #C9A96E;
}
.back-photos {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap: 3px;
    padding: 10px;
}
.back-photo {
    height: 140px;
    overflow: hidden;
}
.back-photo img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.back-photo-placeholder {
    width: 100%;
    height: 100%;
    background: #f0ede8;
}
.back-data {
    padding: 12px 16px;
    border-top: 1px solid #E8E6E0;
    display: flex;
    gap: 20px;
}
.back-col {
    flex: 1;
}
.back-col-title {
    font-size: 7px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1.5px;
    color: #0B132B;
    margin-bottom: 6px;
    padding-bottom: 4px;
    border-bottom: 1px solid #E8E6E0;
}
.back-col p {
    font-size: 8px;
    color: #5E6472;
    line-height: 1.8;
}
.back-col p strong {
    color: #0B132B;
}
.back-footer {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    padding: 10px 16px;
    border-top: 1px solid #E8E6E0;
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #fff;
}
.back-footer-text {
    font-size: 7px;
    letter-spacing: 1.5px;
    text-transform: uppercase;
    color: #bbb;
}
</style>
</head>
<body>

@php
    $m    = $model;
    $c    = $card;
    $name = $m->name ?? 'Model';
    $cats = is_array($m->categories) ? implode(', ', $m->categories) : ($m->categories ?? '');
    $heroUrl = $m->getFirstMediaUrl('compcard_hero');
    $shots   = $m->getMedia('compcard_shots');
    $initial = strtoupper(substr($name, 0, 1));
@endphp

{{-- ???? FRONT ???? --}}
<div class="front">

    <div class="front-header">
        <div class="agency-name">{{ $c->agency_name ?? 'Ayka Originals' }}</div>
        <div class="agency-tagline">{{ strtoupper($m->nationality ?? $m->location ?? '') }}</div>
    </div>

    <div class="hero-section">
        @if($heroUrl)
            <img class="hero-img" src="{{ $heroUrl }}">
        @else
            <div class="hero-placeholder">
                <span class="hero-initial">{{ $initial }}</span>
            </div>
        @endif
    </div>

    <div class="name-block">
        <div class="model-name">{{ $name }}</div>
        <div class="gold-line"></div>
        <div class="model-category">{{ $cats }}</div>
    </div>

    <div class="stats-row">
        @if($m->height)
        <div class="stat-cell">
            <span class="stat-val">{{ $m->height }}</span>
            <span class="stat-lbl">Height</span>
        </div>
        @endif
        @if($m->bust)
        <div class="stat-cell">
            <span class="stat-val">{{ $m->bust }}</span>
            <span class="stat-lbl">Bust</span>
        </div>
        @endif
        @if($m->waist)
        <div class="stat-cell">
            <span class="stat-val">{{ $m->waist }}</span>
            <span class="stat-lbl">Waist</span>
        </div>
        @endif
        @if($m->hips)
        <div class="stat-cell">
            <span class="stat-val">{{ $m->hips }}</span>
            <span class="stat-lbl">Hips</span>
        </div>
        @endif
        @if($m->shoe_size)
        <div class="stat-cell">
            <span class="stat-val">{{ $m->shoe_size }}</span>
            <span class="stat-lbl">Shoes</span>
        </div>
        @endif
        @if($m->age)
        <div class="stat-cell">
            <span class="stat-val">{{ $m->age }}</span>
            <span class="stat-lbl">Age</span>
        </div>
        @endif
    </div>

    <div class="extra-stats">
        @if($m->hair_color)
        <div class="extra-item"><strong>{{ $m->hair_color }}</strong> Hair</div>
        @endif
        @if($m->eye_color)
        <div class="extra-item"><strong>{{ $m->eye_color }}</strong> Eyes</div>
        @endif
        @if($m->dress_size)
        <div class="extra-item"><strong>{{ $m->dress_size }}</strong> Dress</div>
        @endif
    </div>

    @if($c->notable_clients)
    <div style="padding: 0 22px 12px; font-size:8px; color:rgba(255,255,255,0.35); line-height:1.6">
        <div style="font-size:7px;letter-spacing:1.5px;text-transform:uppercase;color:#C9A96E;margin-bottom:3px">Clients</div>
        {{ $c->notable_clients }}
    </div>
    @endif

    <div class="contact-bar">
        <div class="contact-item">
            <strong>Phone</strong>
            {{ $c->agency_phone ?? '' }}
        </div>
        <div class="contact-item">
            <strong>Email</strong>
            {{ $c->agency_email ?? '' }}
        </div>
        <div class="contact-item">
            <strong>Web</strong>
            {{ $c->agency_website ?? '' }}
        </div>
    </div>
</div>

{{-- ???? BACK ???? --}}
<div class="back" style="position:relative;">

    <div class="back-header">
        <div class="back-name">{{ $name }}</div>
        <div class="back-agency">{{ $c->agency_name ?? 'Ayka Originals' }}</div>
    </div>

    <div class="back-photos">
        @php $photoCount = 0; @endphp
        @foreach($shots->take(6) as $shot)
        @php $photoCount++; @endphp
        <div class="back-photo">
            <img src="{{ $shot->getUrl() }}">
        </div>
        @endforeach
        @for($i = $photoCount; $i < 6; $i++)
        <div class="back-photo">
            <div class="back-photo-placeholder"></div>
        </div>
        @endfor
    </div>

    <div class="back-data">
        <div class="back-col">
            <div class="back-col-title">Measurements</div>
            <p>
                @if($m->height)<strong>H:</strong> {{ $m->height }}<br>@endif
                @if($m->bust)<strong>B:</strong> {{ $m->bust }}<br>@endif
                @if($m->waist)<strong>W:</strong> {{ $m->waist }}<br>@endif
                @if($m->hips)<strong>Hips:</strong> {{ $m->hips }}<br>@endif
                @if($m->shoe_size)<strong>Shoes:</strong> {{ $m->shoe_size }}<br>@endif
                @if($m->dress_size)<strong>Dress:</strong> {{ $m->dress_size }}@endif
            </p>
        </div>
        <div class="back-col">
            <div class="back-col-title">Details</div>
            <p>
                @if($m->age)<strong>Age:</strong> {{ $m->age }} years<br>@endif
                @if($m->hair_color)<strong>Hair:</strong> {{ $m->hair_color }}<br>@endif
                @if($m->eye_color)<strong>Eyes:</strong> {{ $m->eye_color }}<br>@endif
                @if($m->nationality)<strong>Nat:</strong> {{ $m->nationality }}<br>@endif
                @if($m->location)<strong>Based:</strong> {{ $m->location }}@endif
            </p>
        </div>
        <div class="back-col">
            <div class="back-col-title">Contact</div>
            <p>
                <strong>{{ $c->agency_name ?? 'Ayka Originals' }}</strong><br>
                {{ $c->agency_phone ?? '' }}<br>
                {{ $c->agency_email ?? '' }}<br>
                {{ $c->agency_website ?? '' }}
            </p>
        </div>
    </div>

    @if($c->recent_campaigns)
    <div style="padding:8px 16px;font-size:8px;color:#5E6472;line-height:1.7">
        <strong style="color:#0B132B;font-size:7px;letter-spacing:1px;text-transform:uppercase">Recent Campaigns</strong><br>
        {{ $c->recent_campaigns }}
    </div>
    @endif

    <div class="back-footer">
        <div class="back-footer-text">{{ $c->agency_name ?? 'Ayka Originals' }} &mdash; Talent Management</div>
        <div class="back-footer-text">{{ now()->format('Y') }}</div>
    </div>
</div>

</body>
</html>