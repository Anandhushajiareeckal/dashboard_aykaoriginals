# ============================================================
#  AYKA ORIGINALS - Fix Image Display
#  Run: powershell -ExecutionPolicy Bypass -File ayka_fix_images.ps1
# ============================================================

$ProjectPath = "C:\laragon\www\ayka-originals"
Set-Location $ProjectPath

$phpExe = (Get-Command php -ErrorAction SilentlyContinue).Source
if (-not $phpExe) { $phpExe = "php" }

Write-Host ""
Write-Host "======================================================" -ForegroundColor Cyan
Write-Host "  AYKA ORIGINALS - Fixing Image Display" -ForegroundColor Cyan
Write-Host "======================================================" -ForegroundColor Cyan
Write-Host ""

function Write-File($RelativePath, $Content) {
    $FullPath = Join-Path $ProjectPath $RelativePath
    $Dir = Split-Path $FullPath -Parent
    if (!(Test-Path $Dir)) { New-Item -ItemType Directory -Path $Dir -Force | Out-Null }
    [System.IO.File]::WriteAllText($FullPath, $Content, [System.Text.UTF8Encoding]::new($false))
    Write-Host "  [OK] $RelativePath" -ForegroundColor Green
}

# -- 1. Publish MediaLibrary config ----------------------------
Write-Host "[1/6] Publishing MediaLibrary config..." -ForegroundColor Yellow
& $phpExe artisan vendor:publish --provider="Spatie\MediaLibrary\MediaLibraryServiceProvider" --tag="medialibrary-config" --force

# -- 2. Fix filesystems.php - ensure public disk is correct ---
Write-Host "[2/6] Ensuring filesystem config is correct..." -ForegroundColor Yellow
Write-File "config\filesystems.php" ('<?php
return [
    ''default'' => env(''FILESYSTEM_DISK'', ''local''),
    ''disks'' => [
        ''local'' => [
            ''driver'' => ''local'',
            ''root''   => storage_path(''app/private''),
            ''serve''  => true,
            ''throw''  => false,
        ],
        ''public'' => [
            ''driver''     => ''local'',
            ''root''       => storage_path(''app/public''),
            ''url''        => env(''APP_URL'').''/storage'',
            ''visibility'' => ''public'',
            ''throw''      => false,
        ],
        ''s3'' => [
            ''driver'' => ''s3'',
            ''key''    => env(''AWS_ACCESS_KEY_ID''),
            ''secret'' => env(''AWS_SECRET_ACCESS_KEY''),
            ''region'' => env(''AWS_DEFAULT_REGION''),
            ''bucket'' => env(''AWS_BUCKET''),
            ''url''    => env(''AWS_URL''),
            ''endpoint'' => env(''AWS_ENDPOINT''),
            ''use_path_style_endpoint'' => env(''AWS_USE_PATH_STYLE_ENDPOINT'', false),
            ''throw''  => false,
        ],
    ],
    ''links'' => [
        public_path(''storage'') => storage_path(''app/public''),
    ],
];
')

# -- 3. Update .env to use public disk -------------------------
Write-Host "[3/6] Checking .env FILESYSTEM_DISK..." -ForegroundColor Yellow
$envPath = Join-Path $ProjectPath ".env"
$envContent = [System.IO.File]::ReadAllText($envPath)
if ($envContent -match "FILESYSTEM_DISK=local") {
    $envContent = $envContent -replace "FILESYSTEM_DISK=local", "FILESYSTEM_DISK=public"
    [System.IO.File]::WriteAllText($envPath, $envContent, [System.Text.UTF8Encoding]::new($false))
    Write-Host "  [OK] Changed FILESYSTEM_DISK to public" -ForegroundColor Green
} elseif ($envContent -notmatch "FILESYSTEM_DISK=public") {
    $envContent += "`nFILESYSTEM_DISK=public"
    [System.IO.File]::WriteAllText($envPath, $envContent, [System.Text.UTF8Encoding]::new($false))
    Write-Host "  [OK] Added FILESYSTEM_DISK=public" -ForegroundColor Green
} else {
    Write-Host "  [SKIP] Already set to public" -ForegroundColor Gray
}

# -- 4. Update MediaLibrary config to use public disk ---------
Write-Host "[4/6] Updating MediaLibrary config to use public disk..." -ForegroundColor Yellow
$mlConfigPath = Join-Path $ProjectPath "config\media-library.php"
if (Test-Path $mlConfigPath) {
    $mlConfig = [System.IO.File]::ReadAllText($mlConfigPath)
    $mlConfig = $mlConfig -replace "''disk_name'' => env\('MEDIA_DISK', '[^']*'\)", "''disk_name'' => env('MEDIA_DISK', 'public')"
    [System.IO.File]::WriteAllText($mlConfigPath, $mlConfig, [System.Text.UTF8Encoding]::new($false))
    Write-Host "  [OK] media-library.php disk set to public" -ForegroundColor Green
} else {
    Write-Host "  [NOTE] config/media-library.php not found, will be created on publish" -ForegroundColor Yellow
}

# -- 5. Fix TalentModel to register media conversions ---------
Write-Host "[5/6] Updating TalentModel with media conversions..." -ForegroundColor Yellow
Write-File "app\Models\TalentModel.php" ('<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class TalentModel extends Model implements HasMedia
{
    use HasFactory, SoftDeletes, InteractsWithMedia;

    protected $table = ''talent_models'';

    protected $fillable = [
        ''name'', ''age'', ''height'', ''bust'', ''waist'', ''hips'', ''shoe_size'',
        ''categories'', ''email'', ''phone'', ''location'', ''about'',
        ''budget'', ''is_inhouse'', ''status'',
    ];

    protected $casts = [
        ''categories'' => ''array'',
        ''is_inhouse''  => ''boolean'',
        ''budget''      => ''decimal:2'',
    ];

    public function projects()
    {
        return $this->belongsToMany(Project::class, ''project_model'');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(''portfolio'')
             ->useDisk(''public'');
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion(''thumb'')
             ->width(400)
             ->height(400)
             ->sharpen(10)
             ->nonQueued();

        $this->addMediaConversion(''preview'')
             ->width(800)
             ->height(800)
             ->nonQueued();
    }
}
')

# -- 6. Storage link + clear caches ---------------------------
Write-Host "[6/6] Creating storage link and clearing caches..." -ForegroundColor Yellow

# Remove broken symlink if exists
$storageLinkPath = Join-Path $ProjectPath "public\storage"
if (Test-Path $storageLinkPath) {
    Write-Host "  Removing old storage link..." -ForegroundColor Gray
    Remove-Item $storageLinkPath -Recurse -Force -ErrorAction SilentlyContinue
}

& $phpExe artisan storage:link
& $phpExe artisan config:clear
& $phpExe artisan cache:clear
& $phpExe artisan view:clear

# -- Check if symlink was created -----------------------------
Write-Host ""
if (Test-Path (Join-Path $ProjectPath "public\storage")) {
    Write-Host "  [OK] Storage symlink exists" -ForegroundColor Green
} else {
    Write-Host "  [WARN] Symlink failed - trying manual junction..." -ForegroundColor Yellow
    $target = Join-Path $ProjectPath "storage\app\public"
    $link   = Join-Path $ProjectPath "public\storage"
    cmd /c mklink /J "$link" "$target"
    Write-Host "  [OK] Junction created" -ForegroundColor Green
}

# -- Verify storage/app/public exists -------------------------
$storagePubPath = Join-Path $ProjectPath "storage\app\public"
if (!(Test-Path $storagePubPath)) {
    New-Item -ItemType Directory -Path $storagePubPath -Force | Out-Null
    Write-Host "  [OK] Created storage/app/public folder" -ForegroundColor Green
}

Write-Host ""
Write-Host "======================================================" -ForegroundColor Green
Write-Host "  DONE! Images should now display correctly." -ForegroundColor Green
Write-Host "======================================================" -ForegroundColor Green
Write-Host ""
Write-Host "  If images already uploaded still show broken:" -ForegroundColor White
Write-Host "   Run: php artisan media-library:regenerate" -ForegroundColor Gray
Write-Host ""
Write-Host "  For new uploads, images will display immediately." -ForegroundColor White
Write-Host ""
Write-Host "  Storage path: storage\app\public\media" -ForegroundColor Gray
Write-Host "  Public URL:   public\storage  (symlink)" -ForegroundColor Gray
Write-Host ""
