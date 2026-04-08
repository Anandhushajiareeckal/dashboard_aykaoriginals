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