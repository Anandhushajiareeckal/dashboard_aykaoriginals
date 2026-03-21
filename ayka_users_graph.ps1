# ============================================================
#  AYKA ORIGINALS - Create Users + Funny Activity Graph
#  Run: powershell -ExecutionPolicy Bypass -File ayka_users_graph.ps1
# ============================================================

$ProjectPath = "C:\laragon\www\ayka-originals"
Set-Location $ProjectPath

$phpExe = (Get-Command php -ErrorAction SilentlyContinue).Source
if (-not $phpExe) { $phpExe = "php" }

Write-Host ""
Write-Host "======================================================" -ForegroundColor Cyan
Write-Host "  Creating Users + Funny Activity Graph" -ForegroundColor Cyan
Write-Host "======================================================" -ForegroundColor Cyan
Write-Host ""

function Write-File($RelativePath, $Content) {
    $FullPath = Join-Path $ProjectPath $RelativePath
    $Dir = Split-Path $FullPath -Parent
    if (!(Test-Path $Dir)) { New-Item -ItemType Directory -Path $Dir -Force | Out-Null }
    [System.IO.File]::WriteAllText($FullPath, $Content, [System.Text.UTF8Encoding]::new($false))
    Write-Host "  [OK] $RelativePath" -ForegroundColor Green
}

# -- 1. Users Seeder -------------------------------------------
Write-Host "[1/5] Creating users seeder..." -ForegroundColor Yellow

$seederContent = @'
<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Employee;
use Spatie\Permission\Models\Role;

class StaffUsersSeeder extends Seeder
{
    public function run(): void
    {
        Role::firstOrCreate(['name' => 'manager', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'staff',   'guard_name' => 'web']);

        $users = [
            ['name'=>'Sanchu',  'email'=>'sanchu@aykaoriginals.com',  'pass'=>'sanchu123',  'role'=>'manager', 'dept'=>'Creative'],
            ['name'=>'Ashima',  'email'=>'ashima@aykaoriginals.com',  'pass'=>'ashima123',  'role'=>'staff',   'dept'=>'Bookings'],
            ['name'=>'Ananthu', 'email'=>'ananthu@aykaoriginals.com', 'pass'=>'ananthu123', 'role'=>'staff',   'dept'=>'Operations'],
        ];

        foreach ($users as $u) {
            $user = User::firstOrCreate(
                ['email' => $u['email']],
                ['name'  => $u['name'], 'password' => Hash::make($u['pass'])]
            );
            $user->update(['password' => Hash::make($u['pass'])]);
            $user->syncRoles([$u['role']]);
            Employee::firstOrCreate(
                ['email' => $u['email']],
                ['user_id'=>$user->id,'name'=>$u['name'],'department'=>$u['dept'],'joining_date'=>now()->subMonths(rand(2,8)),'status'=>'Active']
            );
            $this->command->info("  Created: {$u['name']} / {$u['pass']}");
        }
    }
}
'@
Write-File "database\seeders\StaffUsersSeeder.php" $seederContent

# -- 2. User model ---------------------------------------------
Write-Host "[2/5] Updating User model..." -ForegroundColor Yellow

$userModel = @'
<?php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use Notifiable, HasRoles;
    protected $fillable = ['name','email','password'];
    protected $hidden   = ['password','remember_token'];
    protected $casts    = ['email_verified_at'=>'datetime','password'=>'hashed'];
    public function getJWTIdentifier()    { return $this->getKey(); }
    public function getJWTCustomClaims() { return []; }
    public function employee()            { return $this->hasOne(Employee::class); }
    public function activityLogs()        { return $this->hasMany(ActivityLog::class); }
}
'@
Write-File "app\Models\User.php" $userModel

# -- 3. ActivityGraphController --------------------------------
Write-Host "[3/5] Creating graph controller..." -ForegroundColor Yellow

$graphCtrl = @'
<?php
namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\{ActivityLog, User};
use Illuminate\Http\JsonResponse;

class ActivityGraphController extends Controller
{
    public function data(): JsonResponse
    {
        $days   = collect(range(6,0))->map(fn($d)=>now()->subDays($d)->format('Y-m-d'));
        $labels = $days->map(fn($d)=>now()->parse($d)->format('D d'));

        $personalities = [
            'Sanchu'  => ['color'=>'#C9A96E','emoji'=>'crown', 'title'=>'The Boss'],
            'Ashima'  => ['color'=>'#5B8FE8','emoji'=>'star',  'title'=>'The Hustler'],
            'Ananthu' => ['color'=>'#E87B5B','emoji'=>'rocket','title'=>'The Rookie'],
            'Admin'   => ['color'=>'#6B5BE8','emoji'=>'ghost', 'title'=>'The Ghost Admin'],
        ];

        $datasets = User::all()->map(function($user) use ($days,$personalities) {
            $counts = $days->map(fn($day)=>ActivityLog::where('user_id',$user->id)->whereDate('created_at',$day)->count());
            $p = $personalities[$user->name] ?? ['color'=>'#8A8880','emoji'=>'alien','title'=>'Mystery User'];
            return [
                'label'            => $user->name,
                'title'            => $p['title'],
                'emoji'            => $p['emoji'],
                'data'             => $counts->values()->toArray(),
                'total'            => $counts->sum(),
                'borderColor'      => $p['color'],
                'backgroundColor'  => $p['color'].'22',
                'tension'          => 0.4,
                'pointRadius'      => 6,
                'pointHoverRadius' => 9,
                'borderWidth'      => 2.5,
                'fill'             => true,
            ];
        })->values();

        return response()->json(['labels'=>$labels->values(),'datasets'=>$datasets]);
    }
}
'@
Write-File "app\Http\Controllers\Web\ActivityGraphController.php" $graphCtrl

# -- 4. Route --------------------------------------------------
Write-Host "[4/5] Adding route..." -ForegroundColor Yellow

$routesPath = Join-Path $ProjectPath "routes\web.php"
$routes = [System.IO.File]::ReadAllText($routesPath)
if ($routes -notmatch "activity-graph") {
    $newRoute = "    Route::get('/activity-graph/data', [\App\Http\Controllers\Web\ActivityGraphController::class, 'data'])->name('activity-graph.data');"
    $routes   = $routes -replace "(Route::get\('/activity-log'[^\r\n]*\);)", "`$1`r`n$newRoute"
    [System.IO.File]::WriteAllText($routesPath, $routes, [System.Text.UTF8Encoding]::new($false))
    Write-Host "  [OK] Route added" -ForegroundColor Green
} else {
    Write-Host "  [SKIP] Already exists" -ForegroundColor Gray
}

# -- 5. Dashboard view -----------------------------------------
Write-Host "[5/5] Writing dashboard..." -ForegroundColor Yellow

$dash = @'
@extends('layouts.app')
@section('title','Dashboard')
@section('content')

<div class="grid grid-cols-4 gap-4 mb-6">
    @foreach([
        ['Total Models',     $stats['total_models'],                          'bg-[#0B132B]','text-white'],
        ['Active Projects',  $stats['active_projects'],                       'bg-white',    'text-[#0B132B]'],
        ['Monthly Revenue',  'AED '.number_format($stats['monthly_revenue']), 'bg-white',    'text-[#0B132B]'],
        ['Pending Payments', 'AED '.number_format($stats['pending_payments']),'bg-[#C9A96E]','text-[#0B132B]'],
    ] as $s)
    <div class="rounded-xl border border-gray-100 p-5 {{ $s[2] }} cursor-pointer hover:-translate-y-0.5 transition-transform">
        <p class="text-xs uppercase tracking-widest {{ $s[3]==='text-white'?'text-white/50':'text-gray-400' }} mb-3">{{ $s[0] }}</p>
        <p class="font-display text-2xl font-bold {{ $s[3] }}">{{ $s[1] }}</p>
    </div>
    @endforeach
</div>

<div class="grid grid-cols-3 gap-4 mb-6">
    <div class="col-span-2 bg-white border border-gray-100 rounded-xl p-5">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-display font-semibold">Revenue vs Expenses</h3>
            <div class="flex gap-4 text-xs text-gray-400">
                <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-[#0B132B] inline-block"></span>Revenue</span>
                <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-[#C9A96E] inline-block"></span>Expenses</span>
            </div>
        </div>
        <canvas id="revenueChart" height="80"></canvas>
    </div>
    <div class="bg-white border border-gray-100 rounded-xl p-5">
        <h3 class="font-display font-semibold mb-4">Project Status</h3>
        <canvas id="statusChart" height="120"></canvas>
    </div>
</div>

{{-- FUNNY ACTIVITY GRAPH --}}
<div class="bg-white border border-gray-100 rounded-xl p-6 mb-6">
    <div class="flex items-start justify-between mb-3">
        <div>
            <h3 class="font-display text-lg font-bold text-[#0B132B]">Who Is Actually Working? &#x1F440;</h3>
            <p class="text-sm text-gray-400 mt-0.5">The Weekly Productivity Battlefield &mdash; Last 7 Days &mdash; live</p>
        </div>
        <button onclick="reloadGraph()" class="text-xs border border-gray-200 px-3 py-1.5 rounded-lg hover:bg-gray-50 text-gray-500 transition-colors">
            &#8635; Refresh
        </button>
    </div>

    {{-- Medal row --}}
    <div class="flex gap-3 mb-5 flex-wrap min-h-12" id="leaderboard-badges">
        <div class="text-xs text-gray-300 italic self-center">Loading scoreboard...</div>
    </div>

    {{-- Chart --}}
    <div class="relative" style="height:300px">
        <canvas id="activityChart"></canvas>
        <div id="chart-loading" style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;color:#ccc;font-size:14px">
            Loading battle data...
        </div>
    </div>

    {{-- Roast bar --}}
    <div id="funny-bar" style="display:none" class="mt-4 border-t border-gray-100 pt-4 flex items-center justify-between flex-wrap gap-3">
        <div id="roast-message" class="text-sm font-medium text-gray-600 flex-1 leading-relaxed"></div>
        <a href="{{ route('activity-log.index') }}"
           class="text-xs border border-[#C9A96E] text-[#C9A96E] px-3 py-1.5 rounded-lg hover:bg-[#C9A96E] hover:text-[#0B132B] transition-colors font-medium whitespace-nowrap">
            See Full Log &rarr;
        </a>
    </div>
</div>

<div class="grid grid-cols-2 gap-4">
    <div class="bg-white border border-gray-100 rounded-xl p-5">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-display font-semibold">Recent Projects</h3>
            <a href="{{ route('projects.index') }}" class="text-xs text-gray-400 hover:text-gray-700">View all</a>
        </div>
        <table class="w-full text-sm">
            <thead><tr class="border-b border-gray-100">
                <th class="text-left py-2 text-xs text-gray-400 font-medium uppercase tracking-wide">Project</th>
                <th class="text-left py-2 text-xs text-gray-400 font-medium uppercase tracking-wide">Status</th>
                <th class="text-right py-2 text-xs text-gray-400 font-medium uppercase tracking-wide">Budget</th>
            </tr></thead>
            <tbody>
                @foreach($recentProjects as $p)
                <tr class="border-b border-gray-50 hover:bg-gray-50">
                    <td class="py-2.5">
                        <a href="{{ route('projects.show',$p) }}" class="font-medium hover:text-[#C9A96E]">{{ $p->title }}</a>
                        <p class="text-xs text-gray-400">{{ $p->brand?->name }}</p>
                    </td>
                    <td class="py-2.5">
                        <span class="text-xs px-2 py-0.5 rounded-full font-medium
                            {{ ['Active'=>'bg-green-50 text-green-700','Planning'=>'bg-amber-50 text-amber-700','Review'=>'bg-blue-50 text-blue-700','Completed'=>'bg-gray-100 text-gray-600'][$p->status]??'bg-gray-100 text-gray-600' }}">
                            {{ $p->status }}
                        </span>
                    </td>
                    <td class="py-2.5 text-right font-mono text-sm">AED {{ number_format($p->budget) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="bg-white border border-gray-100 rounded-xl p-5">
        <h3 class="font-display font-semibold mb-4">Recent Activity</h3>
        <div class="space-y-3">
            @forelse($recentActivity as $log)
            <div class="flex items-start gap-3">
                <div class="w-2 h-2 rounded-full bg-[#C9A96E] flex-shrink-0 mt-1.5"></div>
                <div>
                    <p class="text-sm text-gray-700">{{ $log->description }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">{{ $log->created_at->diffForHumans() }}@if($log->user) &middot; {{ $log->user->name }}@endif</p>
                </div>
            </div>
            @empty
            <p class="text-sm text-gray-400 text-center py-6">No activity yet. Someone go do something!</p>
            @endforelse
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
(function(){
    new Chart(document.getElementById('revenueChart'),{
        type:'line',
        data:{
            labels:@json(array_column($revenueChart,'month')),
            datasets:[
                {label:'Revenue',data:@json(array_column($revenueChart,'revenue')),borderColor:'#0B132B',backgroundColor:'rgba(11,19,43,0.04)',tension:0.4,pointBackgroundColor:'#0B132B',pointRadius:4,borderWidth:2.5},
                {label:'Expenses',data:@json(array_column($revenueChart,'expense')),borderColor:'#C9A96E',backgroundColor:'rgba(201,169,110,0.04)',tension:0.4,pointBackgroundColor:'#C9A96E',pointRadius:4,borderWidth:2}
            ]
        },
        options:{plugins:{legend:{display:false}},scales:{x:{grid:{display:false},ticks:{font:{size:11}}},y:{grid:{color:'rgba(0,0,0,0.04)'},ticks:{font:{size:11},callback:function(v){return 'AED '+v.toLocaleString();}}}},responsive:true,maintainAspectRatio:true}
    });
    new Chart(document.getElementById('statusChart'),{
        type:'doughnut',
        data:{labels:@json($projectStatus->pluck('status')),datasets:[{data:@json($projectStatus->pluck('count')),backgroundColor:['#0B132B','#C9A96E','#5E6472','#E8E6E0'],borderWidth:0,hoverOffset:4}]},
        options:{plugins:{legend:{position:'bottom',labels:{font:{size:11},color:'#5E6472',padding:10,boxWidth:10}}},responsive:true,maintainAspectRatio:true,cutout:'68%'}
    });
})();
</script>

<script>
var activityChart = null;

var WIN_ROASTS = [
    "{n} is built different. Literally a machine.",
    "{n} said sleep is for the weak.",
    "{n} is carrying this entire team on their back.",
    "{n} said hold my chai and started grinding.",
    "{n} woke up and chose VIOLENCE today.",
    "{n} has entered the chat and NEVER left.",
    "{n} is the reason the system is still running."
];
var LOSE_ROASTS = [
    "{n} is on a permanent coffee break.",
    "{n}? Never heard of them. Are they even here?",
    "{n} is moving at geological speed today.",
    "{n} has logged in and immediately taken a nap.",
    "{n} activity is colder than the office AC.",
    "{n} is in a meeting with their pillow.",
    "{n} minimised the browser and opened Netflix."
];
var ZERO_ROASTS = [
    "{n} has achieved ABSOLUTE ZERO. Impressive in the wrong way.",
    "{n} fell into a black hole and nobody noticed.",
    "The camera sees {n}. The activity log does not.",
    "{n} is technically employed. Allegedly.",
    "{n}: ghost mode activated. Full invisibility.",
    "Rumour has it {n} exists. Unconfirmed by the data."
];
var EQUAL_ROASTS = [
    "Everyone tied. Equally productive. Or equally lazy. We may never know.",
    "A perfect tie! This team runs on chaos and vibes.",
    "Nobody winning, nobody losing. This is fine. Everything is fine.",
    "All at the same level! Group nap scheduled for 3pm."
];

var EMOJI_MAP = {crown:'\uD83D\uDC51', star:'\u2B50', rocket:'\uD83D\DE80', ghost:'\uD83D\uDC7B', alien:'\uD83D\uDC7D'};
var MEDALS    = ['\uD83E\uDD47','\uD83E\uDD48','\uD83E\uDD49','\uD83D\uDC80'];

function pick(arr){ return arr[Math.floor(Math.random()*arr.length)]; }
function roast(tpl,name){ return tpl.replace('{n}','<b>'+name+'</b>'); }

function buildBadges(datasets){
    var sorted = datasets.slice().sort(function(a,b){return b.total-a.total;});
    return sorted.map(function(d,i){
        var medal  = MEDALS[Math.min(i,MEDALS.length-1)];
        var icon   = EMOJI_MAP[d.emoji]||'\uD83D\uDC64';
        var first  = i===0 && d.total>0;
        var border = first ? '1.5px solid '+d.borderColor : '1px solid #eee';
        var bg     = first ? d.borderColor+'15' : '#f9f9f8';
        var anim   = first ? 'animation:pulseCard 2s ease-in-out infinite;' : '';
        return '<div style="display:inline-flex;align-items:center;gap:10px;padding:10px 16px;border-radius:14px;background:'+bg+';border:'+border+';'+anim+'">'
            + '<span style="font-size:20px">'+medal+'</span>'
            + '<span style="font-size:18px">'+icon+'</span>'
            + '<div>'
            + '<div style="font-family:Syne,sans-serif;font-weight:700;font-size:13px;color:#0B132B">'+d.label+'</div>'
            + '<div style="font-size:10px;color:#8A8880;letter-spacing:0.5px;text-transform:uppercase">'+d.title+' &middot; '+d.total+' actions this week</div>'
            + '</div></div>';
    }).join('');
}

function getRoast(datasets){
    var totals  = datasets.map(function(d){return{name:d.label,total:d.total};});
    var sorted  = totals.slice().sort(function(a,b){return b.total-a.total;});
    var allZero = totals.every(function(t){return t.total===0;});
    var allSame = totals.every(function(t){return t.total===totals[0].total;});
    if(allZero)  return '\uD83D\uDCA4 '+roast(pick(ZERO_ROASTS), pick(totals).name);
    if(allSame)  return '\u2696\uFE0F '+pick(EQUAL_ROASTS);
    var winner  = sorted[0];
    var loser   = sorted[sorted.length-1];
    var winMsg  = '\uD83C\uDFC6 '+roast(pick(WIN_ROASTS),winner.name);
    var loseMsg = loser.total===0
        ? '\uD83D\uDCA4 '+roast(pick(ZERO_ROASTS),loser.name)
        : '\uD83D\uDE34 '+roast(pick(LOSE_ROASTS),loser.name);
    return winMsg+'&nbsp;&nbsp;|&nbsp;&nbsp;'+loseMsg;
}

function reloadGraph(){
    document.getElementById('chart-loading').style.display='flex';
    document.getElementById('funny-bar').style.display='none';

    fetch('{{ route("activity-graph.data") }}')
        .then(function(r){return r.json();})
        .then(function(data){
            document.getElementById('chart-loading').style.display='none';

            document.getElementById('leaderboard-badges').innerHTML = buildBadges(data.datasets);

            if(activityChart) activityChart.destroy();

            var emojiPlugin={
                id:'ep',
                afterDatasetsDraw:function(chart){
                    var ctx=chart.ctx;
                    chart.data.datasets.forEach(function(ds,i){
                        var meta=chart.getDatasetMeta(i);
                        meta.data.forEach(function(pt,j){
                            if(ds.data[j]>0){
                                ctx.font='14px serif';
                                ctx.textAlign='center';
                                ctx.fillText(EMOJI_MAP[ds.emoji]||'\uD83D\uDC64',pt.x,pt.y-14);
                            }
                        });
                    });
                }
            };

            activityChart=new Chart(document.getElementById('activityChart'),{
                type:'line',
                plugins:[emojiPlugin],
                data:{labels:data.labels,datasets:data.datasets},
                options:{
                    responsive:true,
                    maintainAspectRatio:false,
                    interaction:{mode:'index',intersect:false},
                    animation:{duration:900,easing:'easeInOutQuart'},
                    plugins:{
                        legend:{
                            display:true,
                            position:'top',
                            labels:{
                                usePointStyle:true,
                                font:{size:12},
                                color:'#5E6472',
                                padding:20,
                                generateLabels:function(chart){
                                    return chart.data.datasets.map(function(d,i){
                                        return {text:d.label+' ('+d.total+' actions)',fillStyle:d.borderColor,strokeStyle:d.borderColor,pointStyle:'circle',datasetIndex:i};
                                    });
                                }
                            }
                        },
                        tooltip:{
                            backgroundColor:'#0B132B',
                            padding:12,
                            titleColor:'#C9A96E',
                            bodyColor:'#fff',
                            borderColor:'rgba(201,169,110,0.3)',
                            borderWidth:1,
                            callbacks:{
                                title:function(ctx){return '\uD83D\uDCC5 '+ctx[0].label;},
                                label:function(ctx){
                                    var d=ctx.dataset;
                                    var v=ctx.parsed.y;
                                    var icon=EMOJI_MAP[d.emoji]||'\uD83D\uDC64';
                                    var t=v===0?' is sleeping \uD83D\uDE34':v>=10?' is a beast! \uD83D\uDD25':' is trying \uD83D\uDE42';
                                    return icon+' '+d.label+t+': '+v+' actions';
                                },
                                afterBody:function(ctx){
                                    var vals=ctx.map(function(c){return c.parsed.y;});
                                    var mx=Math.max.apply(null,vals);
                                    var mn=Math.min.apply(null,vals);
                                    if(mx===0) return['','\uD83D\uDCA4 Everyone offline. Very suspicious.'];
                                    if(mx===mn) return['','\uD83E\uDD1D Perfect tie. Equally lazy.'];
                                    var w=ctx.find(function(c){return c.parsed.y===mx;});
                                    var l=ctx.find(function(c){return c.parsed.y===mn;});
                                    return['','\uD83C\uDFC6 MVP: '+(w?w.dataset.label:'?'),'\uD83D\uDCA4 Needs coffee: '+(l?l.dataset.label:'?')];
                                }
                            }
                        }
                    },
                    scales:{
                        x:{grid:{display:false},ticks:{font:{size:11},color:'#8A8880'}},
                        y:{
                            beginAtZero:true,
                            grid:{color:'rgba(0,0,0,0.04)'},
                            ticks:{
                                stepSize:1,
                                font:{size:11},
                                color:'#8A8880',
                                callback:function(v){return v===0?'\uD83D\uDE34 0':v+' actions';}
                            }
                        }
                    }
                }
            });

            document.getElementById('roast-message').innerHTML=getRoast(data.datasets);
            document.getElementById('funny-bar').style.display='flex';
        })
        .catch(function(){
            document.getElementById('chart-loading').textContent='Could not load data. Everyone is hiding.';
        });
}

document.addEventListener('DOMContentLoaded',reloadGraph);
setInterval(reloadGraph,60000);
</script>

<style>
@keyframes pulseCard{0%,100%{opacity:1;transform:scale(1)}50%{opacity:.9;transform:scale(1.01)}}
</style>
@endsection
'@
Write-File "resources\views\dashboard.blade.php" $dash

# -- Run seeder and clear caches -------------------------------
Write-Host ""
Write-Host "Running seeder and clearing caches..." -ForegroundColor Yellow
& $phpExe artisan db:seed --class=StaffUsersSeeder --force
& $phpExe artisan view:clear
& $phpExe artisan cache:clear
& $phpExe artisan config:clear

Write-Host ""
Write-Host "======================================================" -ForegroundColor Green
Write-Host "  DONE!" -ForegroundColor Green
Write-Host "======================================================" -ForegroundColor Green
Write-Host ""
Write-Host "  3 users created:" -ForegroundColor White
Write-Host "    Sanchu   sanchu@aykaoriginals.com  / sanchu123  (Manager)" -ForegroundColor Cyan
Write-Host "    Ashima   ashima@aykaoriginals.com  / ashima123  (Staff)"   -ForegroundColor Cyan
Write-Host "    Ananthu  ananthu@aykaoriginals.com / ananthu123 (Staff)"   -ForegroundColor Cyan
Write-Host ""
Write-Host "  Dashboard activity graph features:" -ForegroundColor White
Write-Host "    Gold/Silver/Bronze/Skull medals with weekly totals"  -ForegroundColor Gray
Write-Host "    Crown/Star/Rocket/Ghost emoji on every data point"   -ForegroundColor Gray
Write-Host "    Roast messages based on who worked and who slept"    -ForegroundColor Gray
Write-Host "    Hover tooltips with taunts per user per day"         -ForegroundColor Gray
Write-Host "    Auto-refreshes every 60 seconds"                     -ForegroundColor Gray
Write-Host ""
