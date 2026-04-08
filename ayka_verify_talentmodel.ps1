# ============================================================
#  Verify TalentModel + restore original custom methods
# ============================================================
$ProjectPath = "C:\laragon\www\ayka-originals"
$phpExe = (Get-Command php -ErrorAction SilentlyContinue).Source
if (-not $phpExe) { $phpExe = "php" }

function Write-File($Path, $Content) {
    $Dir = Split-Path $Path -Parent
    if (!(Test-Path $Dir)) { New-Item -ItemType Directory -Path $Dir -Force | Out-Null }
    [System.IO.File]::WriteAllText($Path, $Content, [System.Text.UTF8Encoding]::new($false))
    Write-Host "  [OK] $Path" -ForegroundColor Green
}

Write-Host ""
Write-Host "======================================================" -ForegroundColor Cyan
Write-Host "  Verifying full app health after rewrite" -ForegroundColor Cyan
Write-Host "======================================================" -ForegroundColor Cyan
Write-Host ""

# -- 1. Check all critical model files -------------------------
Write-Host "[1/4] Syntax checking all models..." -ForegroundColor Yellow
$models = @("TalentModel","CompCard","CompCardShare","Brand","Project","Invoice","Crew","Employee","Meeting","User","ActivityLog")
$allOk = $true
foreach ($m in $models) {
    $p = Join-Path $ProjectPath "app\Models\$m.php"
    if (Test-Path $p) {
        $r = & $phpExe -l $p 2>&1
        if ($r -match "No syntax errors") {
            Write-Host "  [OK] $m.php" -ForegroundColor Green
        } else {
            Write-Host "  [ERR] $m.php -- $r" -ForegroundColor Red
            $allOk = $false
        }
    } else {
        Write-Host "  [SKIP] $m.php not found" -ForegroundColor Gray
    }
}

# -- 2. Write the definitive TalentModel with ALL methods ------
Write-Host ""
Write-Host "[2/4] Writing definitive TalentModel.php..." -ForegroundColor Yellow

Write-File "app\Models\TalentModel.php" @'
<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class TalentModel extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $table = 'talent_models';

    protected $fillable = [
        'name',
        'email',
        'phone',
        'location',
        'age',
        'gender',
        'nationality',
        'height',
        'bust',
        'waist',
        'hips',
        'shoe_size',
        'hair_color',
        'eye_color',
        'dress_size',
        'about',
        'status',
        'is_inhouse',
        'budget',
        'categories',
    ];

    protected $casts = [
        'is_inhouse' => 'boolean',
        'categories' => 'array',
        'budget'     => 'decimal:2',
    ];

    // -- Media Collections --------------------------------------
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('portfolio');
        $this->addMediaCollection('compcard_hero')->singleFile();
        $this->addMediaCollection('compcard_shots');
    }

    // -- Relationships ------------------------------------------
    public function compCards()
    {
        return $this->hasMany(CompCard::class, 'talent_model_id');
    }

    public function projects()
    {
        return $this->belongsToMany(Project::class, 'project_model', 'talent_model_id', 'project_id');
    }

    // -- Accessors ----------------------------------------------
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'Active'     => 'green',
            'Inactive'   => 'gray',
            'On Leave'   => 'amber',
            'On Project' => 'blue',
            default      => 'gray',
        };
    }

    public function getIsInhouseAttribute($value): bool
    {
        return (bool) $value;
    }

    public function getCategoriesAttribute($value): array
    {
        if (is_array($value)) return $value;
        if (is_string($value) && $value) {
            $decoded = json_decode($value, true);
            return is_array($decoded) ? $decoded : [$value];
        }
        return [];
    }
}
'@

# -- 3. Verify final syntax ------------------------------------
Write-Host ""
Write-Host "[3/4] Final syntax check..." -ForegroundColor Yellow
$path = Join-Path $ProjectPath "app\Models\TalentModel.php"
$result = & $phpExe -l $path 2>&1
if ($result -match "No syntax errors") {
    Write-Host "  [OK] TalentModel.php is valid PHP" -ForegroundColor Green
} else {
    Write-Host "  [ERR] $result" -ForegroundColor Red
}

# Also check CompCard
$ccPath = Join-Path $ProjectPath "app\Models\CompCard.php"
if (Test-Path $ccPath) {
    $ccResult = & $phpExe -l $ccPath 2>&1
    if ($ccResult -match "No syntax errors") {
        Write-Host "  [OK] CompCard.php is valid PHP" -ForegroundColor Green
    } else {
        Write-Host "  [ERR] CompCard.php -- $ccResult" -ForegroundColor Red
    }
}

# -- 4. Clear all caches ---------------------------------------
Write-Host ""
Write-Host "[4/4] Clearing all caches..." -ForegroundColor Yellow
Set-Location $ProjectPath
& $phpExe artisan view:clear
& $phpExe artisan cache:clear
& $phpExe artisan config:clear
& $phpExe artisan route:clear

Write-Host ""
Write-Host "======================================================" -ForegroundColor Green
Write-Host "  ALL DONE" -ForegroundColor Green
Write-Host "======================================================" -ForegroundColor Green
Write-Host ""
Write-Host "  Open: http://127.0.0.1:8000" -ForegroundColor Cyan
Write-Host "  Test model page: http://127.0.0.1:8000/models/8" -ForegroundColor Cyan
Write-Host "  Comp card builder: http://127.0.0.1:8000/models/8/compcard" -ForegroundColor Cyan
Write-Host ""
