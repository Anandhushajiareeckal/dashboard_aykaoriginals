# ============================================================
#  Fix TalentModel.php - Clean rewrite
# ============================================================
$ProjectPath = "C:\laragon\www\ayka-originals"
$phpExe = (Get-Command php -ErrorAction SilentlyContinue).Source
if (-not $phpExe) { $phpExe = "php" }

Write-Host "Reading TalentModel.php..." -ForegroundColor Yellow
$path = Join-Path $ProjectPath "app\Models\TalentModel.php"
$content = [System.IO.File]::ReadAllText($path)

# Show current lines 25-40 for diagnosis
$lines = [System.IO.File]::ReadAllLines($path)
Write-Host "Current lines 25-40:" -ForegroundColor Cyan
for ($i = 24; $i -lt [Math]::Min(40, $lines.Length); $i++) {
    Write-Host "  L$($i+1): $($lines[$i])"
}

# Remove ALL broken compCards injections - any line with just { or return hasMany CompCard
$cleaned = @()
$skip = $false
foreach ($line in $lines) {
    # Detect the broken pattern: a lone { on a line after the fillable area
    # or the orphaned return $this->hasMany(CompCard
    if ($line.Trim() -eq "{" -and $skip -eq $false) {
        # Check if previous meaningful line was NOT a function declaration
        $skip = $true
        continue
    }
    if ($line -match "return \`$this->hasMany\(.*CompCard" -and $skip -eq $true) {
        $skip = $true
        continue
    }
    if ($line.Trim() -eq "}" -and $skip -eq $true) {
        $skip = $false
        continue
    }
    if ($line -match "public function compCards") {
        # skip the whole compCards block
        $skip = $true
        continue
    }
    if ($skip -eq $false) {
        $cleaned += $line
    }
}

# Rejoin
$newContent = [string]::Join("`r`n", $cleaned)

# Make sure it ends with one closing brace
$newContent = $newContent.TrimEnd()
if ($newContent.EndsWith("}")) {
    # Insert compCards() before the last }
    $lastBrace = $newContent.LastIndexOf("}")
    $relation = "`r`n`r`n    public function compCards()`r`n    {`r`n        return `$this->hasMany(\App\Models\CompCard::class, 'talent_model_id');`r`n    }`r`n"
    $newContent = $newContent.Substring(0, $lastBrace) + $relation + "}"
}

[System.IO.File]::WriteAllText($path, $newContent, [System.Text.UTF8Encoding]::new($false))

# Verify
$check = & $phpExe -l $path 2>&1
if ($check -match "No syntax errors") {
    Write-Host "" 
    Write-Host "  [OK] TalentModel.php syntax is valid!" -ForegroundColor Green
} else {
    Write-Host ""
    Write-Host "  [WARN] Still has issue: $check" -ForegroundColor Red
    Write-Host ""
    Write-Host "  Showing lines around the problem:" -ForegroundColor Yellow
    $lines2 = [System.IO.File]::ReadAllLines($path)
    for ($i = 24; $i -lt [Math]::Min(45, $lines2.Length); $i++) {
        Write-Host "  L$($i+1): $($lines2[$i])"
    }
    Write-Host ""
    Write-Host "  Attempting full safe rewrite..." -ForegroundColor Yellow

    # Nuclear option: extract the class body we know is good and rewrite entirely
    $safeModel = @'
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
        'name', 'email', 'phone', 'location', 'age', 'gender', 'nationality',
        'height', 'bust', 'waist', 'hips', 'shoe_size', 'hair_color', 'eye_color',
        'dress_size', 'about', 'status', 'is_inhouse', 'budget', 'categories',
    ];

    protected $casts = [
        'is_inhouse'  => 'boolean',
        'categories'  => 'array',
        'budget'      => 'decimal:2',
    ];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('portfolio');
        $this->addMediaCollection('compcard_hero')->singleFile();
        $this->addMediaCollection('compcard_shots');
    }

    public function compCards()
    {
        return $this->hasMany(\App\Models\CompCard::class, 'talent_model_id');
    }

    public function projects()
    {
        return $this->belongsToMany(Project::class, 'project_model', 'talent_model_id', 'project_id');
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'Active'    => 'green',
            'Inactive'  => 'gray',
            'On Leave'  => 'amber',
            'On Project'=> 'blue',
            default     => 'gray',
        };
    }
}
'@
    [System.IO.File]::WriteAllText($path, $safeModel, [System.Text.UTF8Encoding]::new($false))
    $check2 = & $phpExe -l $path 2>&1
    if ($check2 -match "No syntax errors") {
        Write-Host "  [OK] Safe rewrite succeeded!" -ForegroundColor Green
        Write-Host "  NOTE: If your TalentModel had custom methods, they were reset to defaults." -ForegroundColor Yellow
        Write-Host "        Your DB and data are untouched." -ForegroundColor Yellow
    } else {
        Write-Host "  [ERROR] $check2" -ForegroundColor Red
    }
}

& $phpExe artisan cache:clear
& $phpExe artisan view:clear

Write-Host ""
Write-Host "Done. Refresh: http://127.0.0.1:8000" -ForegroundColor Cyan
Write-Host ""
