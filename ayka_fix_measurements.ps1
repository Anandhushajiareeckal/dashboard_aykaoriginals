# ============================================================
#  AYKA ORIGINALS - Fix Model Measurements
#  Run: powershell -ExecutionPolicy Bypass -File ayka_fix_measurements.ps1
# ============================================================

$ProjectPath = "C:\laragon\www\ayka-originals"
Set-Location $ProjectPath

$phpExe = (Get-Command php -ErrorAction SilentlyContinue).Source
if (-not $phpExe) { $phpExe = "php" }

Write-Host ""
Write-Host "======================================================" -ForegroundColor Cyan
Write-Host "  AYKA ORIGINALS - Fix Model Measurements" -ForegroundColor Cyan
Write-Host "======================================================" -ForegroundColor Cyan
Write-Host ""

function Write-File($RelativePath, $Content) {
    $FullPath = Join-Path $ProjectPath $RelativePath
    $Dir = Split-Path $FullPath -Parent
    if (!(Test-Path $Dir)) { New-Item -ItemType Directory -Path $Dir -Force | Out-Null }
    [System.IO.File]::WriteAllText($FullPath, $Content, [System.Text.UTF8Encoding]::new($false))
    Write-Host "  [OK] $RelativePath" -ForegroundColor Green
}

# -- 1. Migration to add measurement columns if missing --------
Write-Host "[1/5] Creating measurements migration..." -ForegroundColor Yellow

$ts = Get-Date -Format "yyyy_MM_dd_HHmmss"
Write-File "database\migrations\${ts}_add_measurements_to_talent_models.php" ('<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table(''talent_models'', function (Blueprint $table) {
            if (!Schema::hasColumn(''talent_models'', ''height''))    $table->string(''height'')->nullable()->after(''age'');
            if (!Schema::hasColumn(''talent_models'', ''bust''))      $table->string(''bust'')->nullable()->after(''height'');
            if (!Schema::hasColumn(''talent_models'', ''waist''))     $table->string(''waist'')->nullable()->after(''bust'');
            if (!Schema::hasColumn(''talent_models'', ''hips''))      $table->string(''hips'')->nullable()->after(''waist'');
            if (!Schema::hasColumn(''talent_models'', ''shoe_size'')) $table->string(''shoe_size'')->nullable()->after(''hips'');
        });
    }

    public function down(): void
    {
        Schema::table(''talent_models'', function (Blueprint $table) {
            $table->dropColumn([''height'', ''bust'', ''waist'', ''hips'', ''shoe_size'']);
        });
    }
};
')

# -- 2. TalentModel - ensure all fields in fillable ------------
Write-Host "[2/5] Updating TalentModel..." -ForegroundColor Yellow

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
        ''name'', ''age'',
        ''height'', ''bust'', ''waist'', ''hips'', ''shoe_size'',
        ''categories'', ''email'', ''phone'', ''location'',
        ''about'', ''budget'', ''is_inhouse'', ''status'',
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
        $this->addMediaCollection(''portfolio'')->useDisk(''public'');
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion(''thumb'')
             ->width(400)->height(400)->sharpen(10)->nonQueued();
        $this->addMediaConversion(''preview'')
             ->width(800)->height(800)->nonQueued();
    }
}
')

# -- 3. ModelController - include measurements in validation ---
Write-Host "[3/5] Updating ModelController..." -ForegroundColor Yellow

Write-File "app\Http\Controllers\Web\ModelController.php" ('<?php
namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\TalentModel;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;

class ModelController extends Controller
{
    public function index(Request $req)
    {
        $q = TalentModel::query();
        if ($req->name)       $q->where(''name'', ''like'', ''%''.$req->name.''%'');
        if ($req->location)   $q->where(''location'', ''like'', ''%''.$req->location.''%'');
        if ($req->status)     $q->where(''status'', $req->status);
        if ($req->inhouse)    $q->where(''is_inhouse'', true);
        if ($req->category)   $q->whereJsonContains(''categories'', $req->category);
        if ($req->height_min) $q->whereRaw(''CAST(REGEXP_REPLACE(height, "[^0-9]", "") AS UNSIGNED) >= ?'', [(int)$req->height_min]);
        if ($req->height_max) $q->whereRaw(''CAST(REGEXP_REPLACE(height, "[^0-9]", "") AS UNSIGNED) <= ?'', [(int)$req->height_max]);
        $models = $q->latest()->paginate(12)->withQueryString();
        return view(''models.index'', compact(''models''));
    }

    public function create()
    {
        return view(''models.create'');
    }

    public function store(Request $req, ActivityLogger $logger)
    {
        $data = $req->validate([
            ''name''      => ''required'',
            ''age''       => ''nullable|integer|min:16|max:80'',
            ''height''    => ''nullable|string|max:20'',
            ''bust''      => ''nullable|string|max:20'',
            ''waist''     => ''nullable|string|max:20'',
            ''hips''      => ''nullable|string|max:20'',
            ''shoe_size'' => ''nullable|string|max:20'',
            ''categories'' => ''nullable|array'',
            ''email''     => ''nullable|email'',
            ''phone''     => ''nullable'',
            ''location''  => ''nullable'',
            ''about''     => ''nullable'',
            ''budget''    => ''nullable|numeric'',
            ''is_inhouse'' => ''boolean'',
            ''status''    => ''in:Active,Inactive,On Leave,Unavailable'',
        ]);

        $data[''is_inhouse''] = $req->boolean(''is_inhouse'');
        $model = TalentModel::create($data);

        if ($req->hasFile(''images'')) {
            foreach ($req->file(''images'') as $image) {
                $model->addMedia($image)->toMediaCollection(''portfolio'');
            }
        }

        $logger->created(''Model'', $model->id, $model->name);
        return redirect()->route(''models.show'', $model)->with(''success'', ''Model added successfully.'');
    }

    public function show(TalentModel $model)
    {
        $model->load(''projects'');
        return view(''models.show'', compact(''model''));
    }

    public function edit(TalentModel $model)
    {
        return view(''models.edit'', compact(''model''));
    }

    public function update(Request $req, TalentModel $model, ActivityLogger $logger)
    {
        $data = $req->validate([
            ''name''      => ''required'',
            ''age''       => ''nullable|integer|min:16|max:80'',
            ''height''    => ''nullable|string|max:20'',
            ''bust''      => ''nullable|string|max:20'',
            ''waist''     => ''nullable|string|max:20'',
            ''hips''      => ''nullable|string|max:20'',
            ''shoe_size'' => ''nullable|string|max:20'',
            ''categories'' => ''nullable|array'',
            ''email''     => ''nullable|email'',
            ''phone''     => ''nullable'',
            ''location''  => ''nullable'',
            ''about''     => ''nullable'',
            ''budget''    => ''nullable|numeric'',
            ''is_inhouse'' => ''boolean'',
            ''status''    => ''in:Active,Inactive,On Leave,Unavailable'',
        ]);

        $data[''is_inhouse''] = $req->boolean(''is_inhouse'');
        $model->update($data);

        if ($req->hasFile(''images'')) {
            foreach ($req->file(''images'') as $image) {
                $model->addMedia($image)->toMediaCollection(''portfolio'');
            }
        }

        $logger->updated(''Model'', $model->id, $model->name);
        return redirect()->route(''models.show'', $model)->with(''success'', ''Model updated.'');
    }

    public function destroy(TalentModel $model, ActivityLogger $logger)
    {
        $logger->deleted(''Model'', $model->id, $model->name);
        $model->delete();
        return redirect()->route(''models.index'')->with(''success'', ''Model archived.'');
    }
}
')

# -- 4. Models index view - add height filter ------------------
Write-Host "[4/5] Updating models index view with height filter..." -ForegroundColor Yellow

Write-File "resources\views\models\index.blade.php" ('@extends(''layouts.app'')
@section(''title'',''Models'')
@section(''content'')

<div class="flex items-center justify-between mb-6">
    <div>
        <h2 class="font-display text-xl font-bold">Model Roster</h2>
        <p class="text-sm text-gray-400">{{ $models->total() }} models total</p>
    </div>
    <a href="{{ route(''models.create'') }}" class="bg-[#C9A96E] text-[#0B132B] font-semibold text-sm px-4 py-2 rounded-lg hover:bg-[#E8C882] transition-colors">+ Add Model</a>
</div>

<form method="GET" class="bg-white border border-gray-100 rounded-xl p-4 mb-6">
    <div class="grid grid-cols-4 gap-3 mb-3">
        <div>
            <label class="block text-xs text-gray-400 uppercase tracking-wide mb-1">Name</label>
            <input name="name" value="{{ request(''name'') }}" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-[#0B132B]" placeholder="Search name...">
        </div>
        <div>
            <label class="block text-xs text-gray-400 uppercase tracking-wide mb-1">Location</label>
            <input name="location" value="{{ request(''location'') }}" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-[#0B132B]" placeholder="City...">
        </div>
        <div>
            <label class="block text-xs text-gray-400 uppercase tracking-wide mb-1">Status</label>
            <select name="status" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-[#0B132B]">
                <option value="">All</option>
                @foreach([''Active'',''Inactive'',''On Leave'',''Unavailable''] as $s)
                    <option value="{{ $s }}" {{ request(''status'')===$s ? ''selected'' : '''' }}>{{ $s }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs text-gray-400 uppercase tracking-wide mb-1">Category</label>
            <select name="category" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-[#0B132B]">
                <option value="">All</option>
                @foreach([''Fashion'',''Commercial'',''Runway'',''Editorial'',''Fitness'',''Beauty''] as $c)
                    <option value="{{ $c }}" {{ request(''category'')===$c ? ''selected'' : '''' }}>{{ $c }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="grid grid-cols-4 gap-3 items-end">
        <div>
            <label class="block text-xs text-gray-400 uppercase tracking-wide mb-1">Height Min (cm)</label>
            <input name="height_min" type="number" value="{{ request(''height_min'') }}" min="140" max="200"
                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-[#0B132B]" placeholder="e.g. 165">
        </div>
        <div>
            <label class="block text-xs text-gray-400 uppercase tracking-wide mb-1">Height Max (cm)</label>
            <input name="height_max" type="number" value="{{ request(''height_max'') }}" min="140" max="200"
                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-[#0B132B]" placeholder="e.g. 185">
        </div>
        <div class="flex items-center gap-2 pb-1">
            <input type="checkbox" name="inhouse" id="inhouse" value="1" {{ request(''inhouse'') ? ''checked'' : '''' }} class="rounded">
            <label for="inhouse" class="text-sm text-gray-600">In-house only</label>
        </div>
        <div class="flex gap-2">
            <button type="submit" class="flex-1 bg-[#0B132B] text-white text-sm px-4 py-2 rounded-lg hover:bg-[#1a2a4a] transition-colors">Filter</button>
            <a href="{{ route(''models.index'') }}" class="px-4 py-2 border border-gray-200 rounded-lg text-sm text-gray-400 hover:text-gray-600 hover:bg-gray-50 transition-colors">Clear</a>
        </div>
    </div>

    {{-- Active filter tags --}}
    @if(request()->hasAny([''name'',''location'',''status'',''category'',''height_min'',''height_max'',''inhouse'']))
    <div class="flex flex-wrap gap-2 mt-3 pt-3 border-t border-gray-100">
        <span class="text-xs text-gray-400">Active filters:</span>
        @if(request(''name''))      <span class="text-xs bg-[#0B132B]/8 text-[#0B132B] px-2 py-0.5 rounded-full">Name: {{ request(''name'') }}</span> @endif
        @if(request(''location''))  <span class="text-xs bg-[#0B132B]/8 text-[#0B132B] px-2 py-0.5 rounded-full">Location: {{ request(''location'') }}</span> @endif
        @if(request(''status''))    <span class="text-xs bg-[#0B132B]/8 text-[#0B132B] px-2 py-0.5 rounded-full">Status: {{ request(''status'') }}</span> @endif
        @if(request(''category''))  <span class="text-xs bg-[#0B132B]/8 text-[#0B132B] px-2 py-0.5 rounded-full">Category: {{ request(''category'') }}</span> @endif
        @if(request(''height_min'')) <span class="text-xs bg-[#0B132B]/8 text-[#0B132B] px-2 py-0.5 rounded-full">Height >= {{ request(''height_min'') }}cm</span> @endif
        @if(request(''height_max'')) <span class="text-xs bg-[#0B132B]/8 text-[#0B132B] px-2 py-0.5 rounded-full">Height <= {{ request(''height_max'') }}cm</span> @endif
        @if(request(''inhouse''))   <span class="text-xs bg-[#0B132B]/8 text-[#0B132B] px-2 py-0.5 rounded-full">In-house only</span> @endif
    </div>
    @endif
</form>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
    @forelse($models as $model)
    <div class="bg-white border border-gray-100 rounded-xl overflow-hidden hover:-translate-y-1 transition-transform">
        <div class="h-36 bg-gradient-to-br from-[#0B132B] to-[#2C3E6B] flex items-center justify-center overflow-hidden">
            @if($model->getFirstMediaUrl(''portfolio''))
                <img src="{{ $model->getFirstMediaUrl(''portfolio'') }}" class="w-full h-full object-cover">
            @else
                <span class="font-display text-3xl font-bold text-[#C9A96E]/40">{{ strtoupper(substr($model->name,0,2)) }}</span>
            @endif
        </div>
        <div class="p-4">
            <h3 class="font-display font-semibold text-sm mb-1">{{ $model->name }}</h3>

            {{-- Measurements row --}}
            @if($model->height || $model->bust || $model->waist || $model->hips)
            <div class="flex gap-2 mb-2 flex-wrap">
                @if($model->height)<span class="text-[10px] bg-gray-50 border border-gray-100 px-1.5 py-0.5 rounded text-gray-600 font-mono">H {{ $model->height }}</span>@endif
                @if($model->bust)  <span class="text-[10px] bg-gray-50 border border-gray-100 px-1.5 py-0.5 rounded text-gray-600 font-mono">B {{ $model->bust }}</span>@endif
                @if($model->waist) <span class="text-[10px] bg-gray-50 border border-gray-100 px-1.5 py-0.5 rounded text-gray-600 font-mono">W {{ $model->waist }}</span>@endif
                @if($model->hips)  <span class="text-[10px] bg-gray-50 border border-gray-100 px-1.5 py-0.5 rounded text-gray-600 font-mono">Hip {{ $model->hips }}</span>@endif
            </div>
            @endif

            <div class="flex gap-2 text-xs text-gray-400 mb-2 flex-wrap">
                @if($model->age)      <span>{{ $model->age }} yrs</span> @endif
                @if($model->location) <span>{{ $model->location }}</span> @endif
            </div>
            <div class="flex flex-wrap gap-1 mb-3">
                @foreach((array)$model->categories as $cat)
                    <span class="text-[10px] px-2 py-0.5 rounded-full bg-[#0B132B]/8 text-[#0B132B] font-medium">{{ $cat }}</span>
                @endforeach
                @if($model->is_inhouse)
                    <span class="text-[10px] px-2 py-0.5 rounded-full bg-green-50 text-green-700 font-medium">In-house</span>
                @endif
            </div>
            <div class="flex items-center justify-between">
                <span class="text-xs px-2 py-0.5 rounded-full font-medium {{ $model->status === ''Active'' ? ''bg-green-50 text-green-700'' : ''bg-gray-100 text-gray-500'' }}">{{ $model->status }}</span>
                <a href="{{ route(''models.show'', $model) }}" class="text-xs text-[#0B132B] border border-gray-200 px-2.5 py-1 rounded-lg hover:bg-gray-50 transition-colors">View</a>
            </div>
        </div>
    </div>
    @empty
        <div class="col-span-4 text-center py-16 text-gray-400">
            <p class="text-3xl mb-2">?</p>
            <p>No models found. <a href="{{ route(''models.create'') }}" class="text-[#C9A96E]">Add one</a></p>
        </div>
    @endforelse
</div>
<div class="mt-6">{{ $models->links() }}</div>
@endsection
')

# -- 5. Models show view - display measurements prominently ----
Write-Host "[5/5] Updating models show view..." -ForegroundColor Yellow

Write-File "resources\views\models\show.blade.php" ('@extends(''layouts.app'')
@section(''title'', $model->name)
@section(''content'')

<div class="flex items-center gap-3 mb-6">
    <a href="{{ route(''models.index'') }}" class="text-gray-400 hover:text-gray-600 text-sm">Back</a>
    <span class="text-gray-200">/</span>
    <h2 class="font-display text-xl font-bold">{{ $model->name }}</h2>
    <span class="text-xs px-2.5 py-1 rounded-full font-medium {{ $model->status === ''Active'' ? ''bg-green-50 text-green-700'' : ''bg-gray-100 text-gray-500'' }}">{{ $model->status }}</span>
    <div class="ml-auto flex gap-2">
        <a href="{{ route(''models.edit'', $model) }}" class="px-4 py-2 border border-gray-200 rounded-lg text-sm hover:bg-gray-50 transition-colors">Edit</a>
        <form method="POST" action="{{ route(''models.destroy'', $model) }}" onsubmit="return confirm(''Archive this model?'')">
            @csrf @method(''DELETE'')
            <button class="px-4 py-2 border border-red-200 text-red-600 rounded-lg text-sm hover:bg-red-50 transition-colors">Archive</button>
        </form>
    </div>
</div>

<div class="grid grid-cols-3 gap-6">

    {{-- Left column --}}
    <div class="col-span-1 space-y-4">

        {{-- Photo --}}
        <div class="bg-white border border-gray-100 rounded-xl overflow-hidden">
            <div class="w-full h-56 bg-gradient-to-br from-[#0B132B] to-[#2C3E6B] flex items-center justify-center">
                @if($model->getFirstMediaUrl(''portfolio''))
                    <img src="{{ $model->getFirstMediaUrl(''portfolio'') }}" class="w-full h-full object-cover">
                @else
                    <span class="font-display text-5xl font-bold text-[#C9A96E]/40">{{ strtoupper(substr($model->name,0,2)) }}</span>
                @endif
            </div>
            <div class="p-4">
                <h3 class="font-display font-bold text-lg">{{ $model->name }}</h3>
                @if($model->location)<p class="text-sm text-gray-400 mt-0.5">{{ $model->location }}</p>@endif
                <div class="flex flex-wrap gap-1 mt-2">
                    @foreach((array)$model->categories as $cat)
                        <span class="text-xs px-2 py-0.5 rounded-full bg-[#0B132B]/8 text-[#0B132B]">{{ $cat }}</span>
                    @endforeach
                    @if($model->is_inhouse)
                        <span class="text-xs px-2 py-0.5 rounded-full bg-green-50 text-green-700">In-house</span>
                    @endif
                </div>
                @if($model->about)
                    <p class="text-sm text-gray-600 leading-relaxed mt-3">{{ $model->about }}</p>
                @endif
            </div>
        </div>

        {{-- Measurements card --}}
        <div class="bg-white border border-gray-100 rounded-xl p-5">
            <h4 class="font-display font-semibold text-xs uppercase tracking-widest text-gray-400 mb-4">Measurements</h4>
            @if($model->height || $model->bust || $model->waist || $model->hips || $model->shoe_size)
            <div class="grid grid-cols-2 gap-3">
                @if($model->height)
                <div class="bg-gray-50 rounded-lg p-3 text-center">
                    <p class="text-[10px] text-gray-400 uppercase tracking-wide mb-1">Height</p>
                    <p class="font-display font-bold text-base text-[#0B132B]">{{ $model->height }}</p>
                </div>
                @endif
                @if($model->bust)
                <div class="bg-gray-50 rounded-lg p-3 text-center">
                    <p class="text-[10px] text-gray-400 uppercase tracking-wide mb-1">Bust</p>
                    <p class="font-display font-bold text-base text-[#0B132B]">{{ $model->bust }}</p>
                </div>
                @endif
                @if($model->waist)
                <div class="bg-gray-50 rounded-lg p-3 text-center">
                    <p class="text-[10px] text-gray-400 uppercase tracking-wide mb-1">Waist</p>
                    <p class="font-display font-bold text-base text-[#0B132B]">{{ $model->waist }}</p>
                </div>
                @endif
                @if($model->hips)
                <div class="bg-gray-50 rounded-lg p-3 text-center">
                    <p class="text-[10px] text-gray-400 uppercase tracking-wide mb-1">Hips</p>
                    <p class="font-display font-bold text-base text-[#0B132B]">{{ $model->hips }}</p>
                </div>
                @endif
                @if($model->shoe_size)
                <div class="bg-gray-50 rounded-lg p-3 text-center">
                    <p class="text-[10px] text-gray-400 uppercase tracking-wide mb-1">Shoe Size</p>
                    <p class="font-display font-bold text-base text-[#0B132B]">{{ $model->shoe_size }}</p>
                </div>
                @endif
            </div>
            @else
            <p class="text-sm text-gray-400 text-center py-3">No measurements recorded.</p>
            @endif
        </div>

        {{-- Contact & details --}}
        <div class="bg-white border border-gray-100 rounded-xl p-5">
            <h4 class="font-display font-semibold text-xs uppercase tracking-widest text-gray-400 mb-3">Details</h4>
            <dl class="space-y-2.5 text-sm">
                @if($model->age)
                <div class="flex justify-between items-center">
                    <dt class="text-gray-400">Age</dt>
                    <dd class="font-medium">{{ $model->age }} years</dd>
                </div>
                @endif
                @if($model->email)
                <div class="flex justify-between items-center">
                    <dt class="text-gray-400">Email</dt>
                    <dd><a href="mailto:{{ $model->email }}" class="text-[#C9A96E] hover:underline text-xs">{{ $model->email }}</a></dd>
                </div>
                @endif
                @if($model->phone)
                <div class="flex justify-between items-center">
                    <dt class="text-gray-400">Phone</dt>
                    <dd class="font-medium">{{ $model->phone }}</dd>
                </div>
                @endif
                @if($model->budget)
                <div class="flex justify-between items-center">
                    <dt class="text-gray-400">Day Rate</dt>
                    <dd class="font-display font-bold">AED {{ number_format($model->budget) }}</dd>
                </div>
                @endif
            </dl>
        </div>
    </div>

    {{-- Right columns --}}
    <div class="col-span-2 space-y-4">

        {{-- Portfolio gallery --}}
        <div class="bg-white border border-gray-100 rounded-xl p-5">
            <div class="flex items-center justify-between mb-4">
                <h4 class="font-display font-semibold text-xs uppercase tracking-widest text-gray-400">Portfolio</h4>
                <a href="{{ route(''models.edit'', $model) }}" class="text-xs text-[#C9A96E] hover:underline">+ Add photos</a>
            </div>
            @if($model->getMedia(''portfolio'')->count())
            <div class="grid grid-cols-3 gap-2">
                @foreach($model->getMedia(''portfolio'') as $media)
                <div class="aspect-square rounded-lg overflow-hidden cursor-pointer hover:opacity-90 transition-opacity"
                     onclick="openLightbox(''{{ $media->getUrl() }}'')">
                    <img src="{{ $media->getUrl(''thumb'') ?: $media->getUrl() }}" class="w-full h-full object-cover"
                         onerror="this.src=''{{ $media->getUrl() }}''">
                </div>
                @endforeach
            </div>
            @else
            <div class="text-center py-10 text-gray-300 border-2 border-dashed border-gray-100 rounded-xl">
                <p class="text-3xl mb-2">?</p>
                <p class="text-sm text-gray-400">No portfolio images yet.</p>
                <a href="{{ route(''models.edit'', $model) }}" class="text-xs text-[#C9A96E] mt-1 inline-block">Upload photos</a>
            </div>
            @endif
        </div>

        {{-- Projects --}}
        <div class="bg-white border border-gray-100 rounded-xl p-5">
            <h4 class="font-display font-semibold text-xs uppercase tracking-widest text-gray-400 mb-3">Projects ({{ $model->projects->count() }})</h4>
            @if($model->projects->count())
            <table class="w-full text-sm">
                <thead><tr class="border-b border-gray-100">
                    <th class="text-left py-2 text-xs text-gray-400 font-medium">Project</th>
                    <th class="text-left py-2 text-xs text-gray-400 font-medium">Status</th>
                    <th class="text-right py-2 text-xs text-gray-400 font-medium">Budget</th>
                </tr></thead>
                <tbody>
                    @foreach($model->projects as $p)
                    <tr class="border-b border-gray-50 hover:bg-gray-50">
                        <td class="py-2.5">
                            <a href="{{ route(''projects.show'', $p) }}" class="hover:text-[#C9A96E] font-medium">{{ $p->title }}</a>
                            <p class="text-xs text-gray-400">{{ $p->brand?->name }}</p>
                        </td>
                        <td class="py-2.5">
                            <span class="text-xs px-2 py-0.5 rounded-full {{ $p->status === ''Active'' ? ''bg-green-50 text-green-700'' : ''bg-gray-100 text-gray-500'' }}">{{ $p->status }}</span>
                        </td>
                        <td class="py-2.5 text-right font-mono text-sm">AED {{ number_format($p->budget) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <p class="text-sm text-gray-400">Not assigned to any projects yet.</p>
            @endif
        </div>

    </div>
</div>

{{-- Lightbox --}}
<div id="lightbox-wrap" class="hidden fixed inset-0 bg-black/85 z-50 flex items-center justify-center p-4"
     onclick="closeLightbox()">
    <img id="lightbox-img" class="max-h-[90vh] max-w-[90vw] rounded-xl object-contain">
</div>

<script>
function openLightbox(url) {
    document.getElementById(''lightbox-img'').src = url;
    document.getElementById(''lightbox-wrap'').classList.remove(''hidden'');
    document.getElementById(''lightbox-wrap'').classList.add(''flex'');
}
function closeLightbox() {
    document.getElementById(''lightbox-wrap'').classList.add(''hidden'');
    document.getElementById(''lightbox-wrap'').classList.remove(''flex'');
}
document.addEventListener(''keydown'', e => { if (e.key === ''Escape'') closeLightbox(); });
</script>
@endsection
')

# -- Run migration ---------------------------------------------
Write-Host ""
Write-Host "Running migration..." -ForegroundColor Yellow
& $phpExe artisan migrate --force
& $phpExe artisan view:clear
& $phpExe artisan cache:clear

Write-Host ""
Write-Host "======================================================" -ForegroundColor Green
Write-Host "  DONE! Measurements fixed." -ForegroundColor Green
Write-Host "======================================================" -ForegroundColor Green
Write-Host ""
Write-Host "  What was fixed:" -ForegroundColor White
Write-Host "    - Migration adds height/bust/waist/hips/shoe_size columns if missing" -ForegroundColor Gray
Write-Host "    - All measurement fields now save correctly" -ForegroundColor Gray
Write-Host "    - Model detail page shows measurements as cards" -ForegroundColor Gray
Write-Host "    - Model index cards show H/B/W/Hip badges" -ForegroundColor Gray
Write-Host "    - Filter by height min/max (in cm) added to index" -ForegroundColor Gray
Write-Host ""
