# ============================================================
#  AYKA ORIGINALS - Responsive Dashboard + Mobile App
#  Run: powershell -ExecutionPolicy Bypass -File ayka_mobile.ps1
# ============================================================

$ProjectPath = "C:\laragon\www\ayka-originals"
$MobilePath  = "C:\laragon\www\ayka-mobile"

$phpExe = (Get-Command php -ErrorAction SilentlyContinue).Source
if (-not $phpExe) { $phpExe = "php" }

Set-Location $ProjectPath

Write-Host ""
Write-Host "======================================================" -ForegroundColor Cyan
Write-Host "  AYKA ORIGINALS - Responsive + Mobile App" -ForegroundColor Cyan
Write-Host "======================================================" -ForegroundColor Cyan
Write-Host ""

function Write-File($Path, $Content) {
    $Dir = Split-Path $Path -Parent
    if (!(Test-Path $Dir)) { New-Item -ItemType Directory -Path $Dir -Force | Out-Null }
    [System.IO.File]::WriteAllText($Path, $Content, [System.Text.UTF8Encoding]::new($false))
    Write-Host "  [OK] $Path" -ForegroundColor Green
}

# ????????????????????????????????????????????????????????????
#  PART 1 - RESPONSIVE DASHBOARD
# ????????????????????????????????????????????????????????????
Write-Host "[PART 1] Making dashboard responsive..." -ForegroundColor Yellow

# -- Responsive layout.app.blade.php --------------------------
$layout = @'
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>@yield('title','Dashboard') - Ayka Originals</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>
  <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    body { font-family: 'DM Sans', sans-serif; }
    .font-display { font-family: 'Syne', sans-serif; }
    .sidebar-link.active { background: rgba(201,169,110,0.12); color: #C9A96E; border-left: 2px solid #C9A96E; }
    #mobile-sidebar { transition: transform 0.25s ease; }
    .overlay { transition: opacity 0.25s ease; }
  </style>
</head>
<body class="bg-gray-50 text-gray-900">

<!-- Mobile overlay -->
<div id="sidebar-overlay" class="overlay fixed inset-0 bg-black/50 z-40 hidden lg:hidden" onclick="closeSidebar()"></div>

<!-- SIDEBAR -->
<aside id="mobile-sidebar"
    class="fixed top-0 left-0 h-full w-64 bg-[#0B132B] flex flex-col z-50
           -translate-x-full lg:translate-x-0 lg:static lg:h-screen lg:flex-shrink-0
           transition-transform duration-250">
  <div class="p-5 border-b border-white/10">
    <p class="font-display text-white text-lg font-bold tracking-wide">Ayka Originals</p>
    <p class="text-[#C9A96E] text-xs tracking-widest uppercase mt-0.5">Production Studio</p>
  </div>
  <nav class="flex-1 py-4 px-2 space-y-0.5 overflow-y-auto">
    <a href="{{ route('dashboard') }}" class="sidebar-link flex items-center gap-2.5 px-3 py-2 rounded text-white/55 hover:bg-white/10 hover:text-white/90 text-sm transition-all {{ request()->routeIs('dashboard') ? 'active' : '' }}">
      <span>&#9638;</span> Dashboard
    </a>
    <p class="px-3 pt-4 pb-1 text-[9px] tracking-widest uppercase text-white/20">Management</p>
    <a href="{{ route('models.index') }}" class="sidebar-link flex items-center gap-2.5 px-3 py-2 rounded text-white/55 hover:bg-white/10 hover:text-white/90 text-sm transition-all {{ request()->routeIs('models.*') ? 'active' : '' }}">
      <span>&#10022;</span> Models
    </a>
    <a href="{{ route('projects.index') }}" class="sidebar-link flex items-center gap-2.5 px-3 py-2 rounded text-white/55 hover:bg-white/10 hover:text-white/90 text-sm transition-all {{ request()->routeIs('projects.*') ? 'active' : '' }}">
      <span>&#11208;</span> Projects
    </a>
    <a href="{{ route('brands.index') }}" class="sidebar-link flex items-center gap-2.5 px-3 py-2 rounded text-white/55 hover:bg-white/10 hover:text-white/90 text-sm transition-all {{ request()->routeIs('brands.*') ? 'active' : '' }}">
      <span>&#9671;</span> Brands
    </a>
    <a href="{{ route('crew.index') }}" class="sidebar-link flex items-center gap-2.5 px-3 py-2 rounded text-white/55 hover:bg-white/10 hover:text-white/90 text-sm transition-all {{ request()->routeIs('crew.*') ? 'active' : '' }}">
      <span>&#9678;</span> Crew
    </a>
    <a href="{{ route('employees.index') }}" class="sidebar-link flex items-center gap-2.5 px-3 py-2 rounded text-white/55 hover:bg-white/10 hover:text-white/90 text-sm transition-all {{ request()->routeIs('employees.*') ? 'active' : '' }}">
      <span>&#9673;</span> Employees
    </a>
    <p class="px-3 pt-4 pb-1 text-[9px] tracking-widest uppercase text-white/20">Finance & Schedule</p>
    <a href="{{ route('invoices.index') }}" class="sidebar-link flex items-center gap-2.5 px-3 py-2 rounded text-white/55 hover:bg-white/10 hover:text-white/90 text-sm transition-all {{ request()->routeIs('invoices.*') ? 'active' : '' }}">
      <span>&#9655;</span> Accounts
    </a>
    <a href="{{ route('meetings.index') }}" class="sidebar-link flex items-center gap-2.5 px-3 py-2 rounded text-white/55 hover:bg-white/10 hover:text-white/90 text-sm transition-all {{ request()->routeIs('meetings.*') ? 'active' : '' }}">
      <span>&#9633;</span> Meetings
    </a>
    <p class="px-3 pt-4 pb-1 text-[9px] tracking-widest uppercase text-white/20">System</p>
    <a href="{{ route('activity-log.index') }}" class="sidebar-link flex items-center gap-2.5 px-3 py-2 rounded text-white/55 hover:bg-white/10 hover:text-white/90 text-sm transition-all {{ request()->routeIs('activity-log.*') ? 'active' : '' }}">
      <span>&#9675;</span> Activity Log
    </a>
  </nav>
  <div class="p-4 border-t border-white/10">
    <div class="flex items-center gap-2.5">
      <div class="w-8 h-8 rounded-full bg-[#C9A96E] flex items-center justify-center text-[#0B132B] font-bold text-xs flex-shrink-0">
        {{ strtoupper(substr(auth()->user()->name,0,2)) }}
      </div>
      <div class="min-w-0">
        <p class="text-white/70 text-xs font-medium truncate">{{ auth()->user()->name }}</p>
        <p class="text-[#C9A96E] text-[10px]">{{ auth()->user()->getRoleNames()->first() }}</p>
      </div>
    </div>
    <form method="POST" action="{{ route('logout') }}" class="mt-3">
      @csrf
      <button class="text-white/30 hover:text-white/70 text-xs transition-colors">Sign out</button>
    </form>
  </div>
</aside>

<!-- MAIN -->
<div class="flex flex-col min-h-screen lg:flex-row">
  <!-- Spacer for desktop sidebar -->
  <div class="hidden lg:block w-64 flex-shrink-0"></div>

  <div class="flex-1 flex flex-col min-w-0">
    <!-- Topbar -->
    <header class="bg-white border-b border-gray-200 px-4 lg:px-7 sticky top-0 z-30" style="height:60px;display:flex;align-items:center;gap:12px">
      <!-- Hamburger (mobile only) -->
      <button onclick="openSidebar()" class="lg:hidden w-9 h-9 flex items-center justify-center rounded-lg border border-gray-200 text-gray-500 flex-shrink-0">
        <svg width="18" height="14" viewBox="0 0 18 14" fill="none"><path d="M1 1h16M1 7h16M1 13h16" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
      </button>
      <div class="min-w-0">
        <h1 class="font-display text-sm lg:text-base font-semibold truncate">@yield('title','Dashboard')</h1>
        <p class="text-xs text-gray-400 hidden sm:block">Ayka Originals / @yield('title','Overview')</p>
      </div>
      <div class="ml-auto flex items-center gap-2 lg:gap-3">
        <form action="{{ route('search') }}" class="hidden sm:flex items-center gap-2 bg-gray-50 border border-gray-200 rounded-lg px-3 h-9">
          <span class="text-gray-400 text-sm">&#9906;</span>
          <input name="q" value="{{ request('q') }}" placeholder="Search..." class="bg-transparent border-none outline-none text-sm w-28 lg:w-40">
        </form>
        <div class="w-8 h-8 lg:w-9 lg:h-9 rounded-lg border border-gray-200 flex items-center justify-center cursor-pointer hover:bg-gray-50 text-gray-500 text-sm">&#128276;</div>
        <div class="w-8 h-8 rounded-lg bg-[#0B132B] flex items-center justify-center text-[#C9A96E] text-xs font-bold cursor-pointer flex-shrink-0">
          {{ strtoupper(substr(auth()->user()->name,0,2)) }}
        </div>
      </div>
    </header>

    <!-- Content -->
    <main class="flex-1 p-4 lg:p-7">
      @if(session('success'))
        <div x-data="{show:true}" x-show="show" x-init="setTimeout(()=>show=false,4000)"
             class="mb-4 bg-green-50 border border-green-200 text-green-800 text-sm px-4 py-3 rounded-lg flex items-center justify-between">
          <span>{{ session('success') }}</span>
          <button @click="show=false" class="text-green-500 text-lg leading-none">&times;</button>
        </div>
      @endif
      @yield('content')
    </main>
  </div>
</div>

<script>
function openSidebar() {
    document.getElementById('mobile-sidebar').classList.remove('-translate-x-full');
    document.getElementById('sidebar-overlay').classList.remove('hidden');
}
function closeSidebar() {
    document.getElementById('mobile-sidebar').classList.add('-translate-x-full');
    document.getElementById('sidebar-overlay').classList.add('hidden');
}
</script>
</body>
</html>
'@
Write-File "$ProjectPath\resources\views\layouts\app.blade.php" $layout

# -- Responsive dashboard.blade.php ---------------------------
$dashboard = @'
@extends('layouts.app')
@section('title','Dashboard')
@section('content')

<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 lg:gap-4 mb-5 lg:mb-6">
    @foreach([
        ['Total Models',     $stats['total_models'],                          'bg-[#0B132B]','text-white'],
        ['Active Projects',  $stats['active_projects'],                       'bg-white',    'text-[#0B132B]'],
        ['Monthly Revenue',  'AED '.number_format($stats['monthly_revenue']), 'bg-white',    'text-[#0B132B]'],
        ['Pending Payments', 'AED '.number_format($stats['pending_payments']),'bg-[#C9A96E]','text-[#0B132B]'],
    ] as $s)
    <div class="rounded-xl border border-gray-100 p-4 lg:p-5 {{ $s[2] }} hover:-translate-y-0.5 transition-transform cursor-pointer">
        <p class="text-[10px] lg:text-xs uppercase tracking-widest {{ $s[3]==='text-white'?'text-white/50':'text-gray-400' }} mb-2 lg:mb-3">{{ $s[0] }}</p>
        <p class="font-display text-xl lg:text-2xl font-bold {{ $s[3] }}">{{ $s[1] }}</p>
    </div>
    @endforeach
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-5 lg:mb-6">
    <div class="lg:col-span-2 bg-white border border-gray-100 rounded-xl p-4 lg:p-5">
        <div class="flex items-center justify-between mb-3 lg:mb-4">
            <h3 class="font-display font-semibold text-sm lg:text-base">Revenue vs Expenses</h3>
            <div class="flex gap-3 text-xs text-gray-400">
                <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-[#0B132B] inline-block"></span>Revenue</span>
                <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-[#C9A96E] inline-block"></span>Expenses</span>
            </div>
        </div>
        <canvas id="revenueChart" height="100"></canvas>
    </div>
    <div class="bg-white border border-gray-100 rounded-xl p-4 lg:p-5">
        <h3 class="font-display font-semibold text-sm lg:text-base mb-3 lg:mb-4">Project Status</h3>
        <canvas id="statusChart" height="140"></canvas>
    </div>
</div>

{{-- FUNNY ACTIVITY GRAPH --}}
<div class="bg-white border border-gray-100 rounded-xl p-4 lg:p-6 mb-5 lg:mb-6">
    <div class="flex items-start justify-between mb-3">
        <div>
            <h3 class="font-display text-base lg:text-lg font-bold text-[#0B132B]">Who Is Actually Working? &#x1F440;</h3>
            <p class="text-xs lg:text-sm text-gray-400 mt-0.5">The Weekly Productivity Battlefield &mdash; Last 7 Days</p>
        </div>
        <button onclick="reloadGraph()" class="text-xs border border-gray-200 px-2 lg:px-3 py-1.5 rounded-lg hover:bg-gray-50 text-gray-500 flex-shrink-0 ml-2">&#8635; Refresh</button>
    </div>

    <div class="flex gap-2 lg:gap-3 mb-4 flex-wrap min-h-10" id="leaderboard-badges">
        <div class="text-xs text-gray-300 italic self-center">Loading scoreboard...</div>
    </div>

    <div class="relative" style="height:220px;lg:height:300px">
        <canvas id="activityChart"></canvas>
        <div id="chart-loading" style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;color:#ccc;font-size:13px">
            Loading battle data...
        </div>
    </div>

    <div id="funny-bar" style="display:none" class="mt-3 lg:mt-4 border-t border-gray-100 pt-3 lg:pt-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
        <div id="roast-message" class="text-xs lg:text-sm font-medium text-gray-600 flex-1 leading-relaxed"></div>
        <a href="{{ route('activity-log.index') }}"
           class="text-xs border border-[#C9A96E] text-[#C9A96E] px-3 py-1.5 rounded-lg hover:bg-[#C9A96E] hover:text-[#0B132B] transition-colors font-medium whitespace-nowrap flex-shrink-0">
            See Full Log &rarr;
        </a>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
    <div class="bg-white border border-gray-100 rounded-xl p-4 lg:p-5">
        <div class="flex items-center justify-between mb-3 lg:mb-4">
            <h3 class="font-display font-semibold text-sm lg:text-base">Recent Projects</h3>
            <a href="{{ route('projects.index') }}" class="text-xs text-gray-400 hover:text-gray-700">View all</a>
        </div>
        <div class="overflow-x-auto">
        <table class="w-full text-sm" style="min-width:320px">
            <thead><tr class="border-b border-gray-100">
                <th class="text-left py-2 text-xs text-gray-400 font-medium uppercase tracking-wide">Project</th>
                <th class="text-left py-2 text-xs text-gray-400 font-medium uppercase tracking-wide hidden sm:table-cell">Status</th>
                <th class="text-right py-2 text-xs text-gray-400 font-medium uppercase tracking-wide">Budget</th>
            </tr></thead>
            <tbody>
                @foreach($recentProjects as $p)
                <tr class="border-b border-gray-50 hover:bg-gray-50">
                    <td class="py-2.5">
                        <a href="{{ route('projects.show',$p) }}" class="font-medium text-sm hover:text-[#C9A96E] block truncate max-w-[180px]">{{ $p->title }}</a>
                        <p class="text-xs text-gray-400 truncate max-w-[180px]">{{ $p->brand?->name }}</p>
                    </td>
                    <td class="py-2.5 hidden sm:table-cell">
                        <span class="text-xs px-2 py-0.5 rounded-full font-medium
                            {{ ['Active'=>'bg-green-50 text-green-700','Planning'=>'bg-amber-50 text-amber-700','Review'=>'bg-blue-50 text-blue-700','Completed'=>'bg-gray-100 text-gray-600'][$p->status]??'bg-gray-100 text-gray-600' }}">
                            {{ $p->status }}
                        </span>
                    </td>
                    <td class="py-2.5 text-right font-mono text-xs lg:text-sm whitespace-nowrap">AED {{ number_format($p->budget) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        </div>
    </div>
    <div class="bg-white border border-gray-100 rounded-xl p-4 lg:p-5">
        <h3 class="font-display font-semibold text-sm lg:text-base mb-3 lg:mb-4">Recent Activity</h3>
        <div class="space-y-3">
            @forelse($recentActivity as $log)
            <div class="flex items-start gap-3">
                <div class="w-2 h-2 rounded-full bg-[#C9A96E] flex-shrink-0 mt-1.5"></div>
                <div class="min-w-0">
                    <p class="text-xs lg:text-sm text-gray-700 truncate">{{ $log->description }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">{{ $log->created_at->diffForHumans() }}@if($log->user) &middot; {{ $log->user->name }}@endif</p>
                </div>
            </div>
            @empty
            <p class="text-sm text-gray-400 text-center py-6">No activity yet.</p>
            @endforelse
        </div>
    </div>
</div>

<script>
(function(){
new Chart(document.getElementById('revenueChart'),{type:'line',data:{labels:@json(array_column($revenueChart,'month')),datasets:[{label:'Revenue',data:@json(array_column($revenueChart,'revenue')),borderColor:'#0B132B',backgroundColor:'rgba(11,19,43,0.04)',tension:0.4,pointBackgroundColor:'#0B132B',pointRadius:3,borderWidth:2},{label:'Expenses',data:@json(array_column($revenueChart,'expense')),borderColor:'#C9A96E',backgroundColor:'rgba(201,169,110,0.04)',tension:0.4,pointBackgroundColor:'#C9A96E',pointRadius:3,borderWidth:2}]},options:{plugins:{legend:{display:false}},scales:{x:{grid:{display:false},ticks:{font:{size:10}}},y:{grid:{color:'rgba(0,0,0,0.04)'},ticks:{font:{size:10},callback:function(v){return 'AED '+v.toLocaleString();}}}},responsive:true,maintainAspectRatio:true}});
new Chart(document.getElementById('statusChart'),{type:'doughnut',data:{labels:@json($projectStatus->pluck('status')),datasets:[{data:@json($projectStatus->pluck('count')),backgroundColor:['#0B132B','#C9A96E','#5E6472','#E8E6E0'],borderWidth:0}]},options:{plugins:{legend:{position:'bottom',labels:{font:{size:10},color:'#5E6472',padding:8,boxWidth:8}}},responsive:true,maintainAspectRatio:true,cutout:'65%'}});
})();
</script>

<script>
var activityChart=null;
var WIN=['"{n}" is built different. Literally a machine.','"{n}" said sleep is for the weak.','"{n}" is carrying this entire team on their back.','"{n}" woke up and chose VIOLENCE today.','"{n}" has entered the chat and NEVER left.'];
var LOSE=['"{n}" is on a permanent coffee break.','"{n}"? Never heard of them. Are they even here?','"{n}" has logged in and immediately taken a nap.','"{n}" activity colder than the office AC.','"{n}" is in a meeting with their pillow.'];
var ZERO=['"{n}" achieved ABSOLUTE ZERO. Impressive wrongly.','"{n}" fell into a black hole. Nobody noticed.','Camera sees "{n}". Activity log does not.','"{n}" is technically employed. Allegedly.'];
var EQ=['Everyone tied. Equally productive or equally lazy.','Perfect tie! This team runs on chaos and vibes.','Nobody winning, nobody losing. This is fine.'];
var EM={crown:'\uD83D\uDC51',star:'\u2B50',rocket:'\uD83D\DE80',ghost:'\uD83D\uDC7B',alien:'\uD83D\uDC7D'};
var MD=['\uD83E\uDD47','\uD83E\uDD48','\uD83E\uDD49','\uD83D\uDC80'];
function pick(a){return a[Math.floor(Math.random()*a.length)];}
function roast(t,n){return t.replace('"{n}"','<b>'+n+'</b>');}
function buildBadges(ds){
    var s=ds.slice().sort(function(a,b){return b.total-a.total;});
    return s.map(function(d,i){
        var m=MD[Math.min(i,3)],ic=EM[d.emoji]||'\uD83D\uDC64';
        var f=i===0&&d.total>0;
        return '<div style="display:inline-flex;align-items:center;gap:8px;padding:8px 12px;border-radius:12px;background:'+(f?d.borderColor+'18':'#f9f9f8')+';border:1px solid '+(f?d.borderColor+'44':'#eee')+';'+(f?'animation:pC 2s infinite':'')+'">'
            +'<span style="font-size:18px">'+m+'</span><span style="font-size:16px">'+ic+'</span>'
            +'<div><div style="font-family:Syne,sans-serif;font-weight:700;font-size:12px;color:#0B132B">'+d.label+'</div>'
            +'<div style="font-size:10px;color:#8A8880;text-transform:uppercase;letter-spacing:.5px">'+d.title+' &middot; '+d.total+' actions</div></div></div>';
    }).join('');
}
function getRoast(ds){
    var t=ds.map(function(d){return{n:d.label,v:d.total};});
    var s=t.slice().sort(function(a,b){return b.v-a.v;});
    if(t.every(function(x){return x.v===0;})) return '\uD83D\uDCA4 '+roast(pick(ZERO),pick(t).n);
    if(t.every(function(x){return x.v===t[0].v;})) return '\u2696\uFE0F '+pick(EQ);
    return '\uD83C\uDFC6 '+roast(pick(WIN),s[0].n)+'&nbsp;&nbsp;|&nbsp;&nbsp;\uD83D\uDE34 '+roast(s[s.length-1].v===0?pick(ZERO):pick(LOSE),s[s.length-1].n);
}
function reloadGraph(){
    document.getElementById('chart-loading').style.display='flex';
    document.getElementById('funny-bar').style.display='none';
    fetch('{{ route("activity-graph.data") }}').then(function(r){return r.json();}).then(function(data){
        document.getElementById('chart-loading').style.display='none';
        document.getElementById('leaderboard-badges').innerHTML=buildBadges(data.datasets);
        if(activityChart)activityChart.destroy();
        var ep={id:'ep',afterDatasetsDraw:function(chart){var ctx=chart.ctx;chart.data.datasets.forEach(function(d,i){chart.getDatasetMeta(i).data.forEach(function(pt,j){if(d.data[j]>0){ctx.font='12px serif';ctx.textAlign='center';ctx.fillText(EM[d.emoji]||'\uD83D\uDC64',pt.x,pt.y-12);}});});}};
        activityChart=new Chart(document.getElementById('activityChart'),{type:'line',plugins:[ep],data:{labels:data.labels,datasets:data.datasets},options:{responsive:true,maintainAspectRatio:false,interaction:{mode:'index',intersect:false},animation:{duration:900,easing:'easeInOutQuart'},plugins:{legend:{display:true,position:'top',labels:{usePointStyle:true,font:{size:11},color:'#5E6472',padding:16,generateLabels:function(c){return c.data.datasets.map(function(d,i){return{text:d.label+' ('+d.total+')',fillStyle:d.borderColor,strokeStyle:d.borderColor,pointStyle:'circle',datasetIndex:i};});}}},tooltip:{backgroundColor:'#0B132B',padding:10,titleColor:'#C9A96E',bodyColor:'#fff',callbacks:{title:function(c){return '\uD83D\uDCC5 '+c[0].label;},label:function(c){var v=c.parsed.y,ic=EM[c.dataset.emoji]||'\uD83D\uDC64';return ic+' '+c.dataset.label+(v===0?' sleeping \uD83D\uDE34':v>=10?' is a beast! \uD83D\uDD25':' is trying \uD83D\uDE42')+': '+v;},afterBody:function(c){var vals=c.map(function(x){return x.parsed.y;});var mx=Math.max.apply(null,vals),mn=Math.min.apply(null,vals);if(mx===0)return['','\uD83D\uDCA4 Everyone offline. Suspicious.'];if(mx===mn)return['','\uD83E\uDD1D Perfectly equal. Suspicious.'];var w=c.find(function(x){return x.parsed.y===mx;}),l=c.find(function(x){return x.parsed.y===mn;});return['','\uD83C\uDFC6 MVP: '+(w?w.dataset.label:'?'),'\uD83D\uDCA4 Needs coffee: '+(l?l.dataset.label:'?')];}}}},scales:{x:{grid:{display:false},ticks:{font:{size:10},color:'#8A8880'}},y:{beginAtZero:true,grid:{color:'rgba(0,0,0,0.04)'},ticks:{stepSize:1,font:{size:10},color:'#8A8880',callback:function(v){return v===0?'\uD83D\uDE34':v;}}}}}});
        document.getElementById('roast-message').innerHTML=getRoast(data.datasets);
        document.getElementById('funny-bar').style.display='flex';
    }).catch(function(){document.getElementById('chart-loading').textContent='Could not load data.';});
}
document.addEventListener('DOMContentLoaded',reloadGraph);
setInterval(reloadGraph,60000);
</script>
<style>@keyframes pC{0%,100%{opacity:1;transform:scale(1)}50%{opacity:.9;transform:scale(1.01)}}</style>
@endsection
'@
Write-File "$ProjectPath\resources\views\dashboard.blade.php" $dashboard

Write-Host "  [OK] Responsive layout + dashboard written" -ForegroundColor Green

# ????????????????????????????????????????????????????????????
#  PART 2 - NATIVE PHP MOBILE APP
# ????????????????????????????????????????????????????????????
Write-Host ""
Write-Host "[PART 2] Deploying native PHP mobile app..." -ForegroundColor Yellow

if (!(Test-Path $MobilePath)) {
    New-Item -ItemType Directory -Path $MobilePath -Force | Out-Null
    Write-Host "  Created: $MobilePath" -ForegroundColor Gray
}

# Read the mobile app index.php from the project dir if it exists, else write inline
$mobileApp = @'
<?php
session_start();

define('API_BASE', 'http://127.0.0.1:8000/api/v1');
define('APP_NAME', 'Ayka Originals');

function api(string $endpoint, string $method = 'GET', array $data = [], bool $auth = true): array {
    $url = API_BASE . $endpoint;
    $ch  = curl_init($url);
    $headers = ['Content-Type: application/json', 'Accept: application/json'];
    if ($auth && isset($_SESSION['token'])) $headers[] = 'Authorization: Bearer ' . $_SESSION['token'];
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER=>true, CURLOPT_HTTPHEADER=>$headers, CURLOPT_TIMEOUT=>10, CURLOPT_SSL_VERIFYPEER=>false]);
    if ($method === 'POST') { curl_setopt($ch, CURLOPT_POST, true); curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data)); }
    $resp = curl_exec($ch); $code = curl_getinfo($ch, CURLINFO_HTTP_CODE); curl_close($ch);
    $r = json_decode($resp, true) ?? []; $r['_status'] = $code; return $r;
}
function isLoggedIn(): bool { return !empty($_SESSION['token']); }
function redirect(string $p): never { header('Location: '.$p); exit; }
function e($v): string { return htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8'); }
function badge(string $s): string {
    $m=['Active'=>'bg-green-50 text-green-700','Paid'=>'bg-green-50 text-green-700','Available'=>'bg-green-50 text-green-700',
        'Planning'=>'bg-amber-50 text-amber-700','Sent'=>'bg-blue-50 text-blue-700','Pending'=>'bg-amber-50 text-amber-700',
        'On Leave'=>'bg-amber-50 text-amber-700','Review'=>'bg-blue-50 text-blue-700','On Project'=>'bg-blue-50 text-blue-700',
        'Overdue'=>'bg-red-50 text-red-600','Inactive'=>'bg-gray-100 text-gray-600','Cancelled'=>'bg-gray-100 text-gray-500','Completed'=>'bg-gray-100 text-gray-600'];
    $c=$m[$s]??'bg-gray-100 text-gray-500';
    return '<span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold '.$c.'">'.e($s).'</span>';
}

$page = $_GET['page'] ?? 'login';
if ($page === 'logout') { session_destroy(); redirect('?page=login'); }
if ($page === 'login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $res = api('/login','POST',['email'=>$_POST['email']??'','password'=>$_POST['password']??''],false);
    if (!empty($res['token'])) { $_SESSION['token']=$res['token']; $_SESSION['user']=$res['user']; redirect('?page=dashboard'); }
    else $loginError = 'Invalid credentials.';
}
if ($page !== 'login' && !isLoggedIn()) redirect('?page=login');

$pd = []; $user = $_SESSION['user'] ?? [];
switch ($page) {
    case 'dashboard':
        $pd['summary']  = api('/accounts/summary');
        $pd['projects'] = api('/projects?per_page=4');
        $pd['meetings'] = api('/meetings?per_page=3');
        $pd['invoices'] = api('/accounts/invoices?per_page=3');
        break;
    case 'models':
        $q = http_build_query(array_filter(['name'=>$_GET['name']??'','status'=>$_GET['status']??'','page'=>$_GET['p']??1]));
        $pd['models'] = api('/models?'.$q);
        break;
    case 'model_detail': $pd['model'] = api('/models/'.(int)($_GET['id']??0)); break;
    case 'projects':
        $q = http_build_query(array_filter(['status'=>$_GET['status']??'','page'=>$_GET['p']??1]));
        $pd['projects'] = api('/projects?'.$q);
        break;
    case 'project_detail': $pd['project'] = api('/projects/'.(int)($_GET['id']??0)); break;
    case 'meetings': $pd['meetings'] = api('/meetings?page='.($_GET['p']??1)); break;
    case 'accounts':
        $pd['summary']  = api('/accounts/summary');
        $pd['invoices'] = api('/accounts/invoices?page='.($_GET['p']??1));
        break;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1,user-scalable=no">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="theme-color" content="#0B132B">
<title><?= APP_NAME ?></title>
<script src="https://cdn.tailwindcss.com"></script>
<style>
*{box-sizing:border-box;-webkit-tap-highlight-color:transparent}
body{background:#F4F3EF;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;-webkit-font-smoothing:antialiased}
.app{max-width:480px;margin:0 auto;min-height:100vh;background:#F4F3EF;display:flex;flex-direction:column}
.bottom-nav{position:fixed;bottom:0;left:50%;transform:translateX(-50%);width:100%;max-width:480px;background:#0B132B;display:flex;border-top:1px solid rgba(255,255,255,.08);padding-bottom:env(safe-area-inset-bottom,0);z-index:50}
.nav-item{flex:1;display:flex;flex-direction:column;align-items:center;gap:2px;padding:10px 4px 8px;color:rgba(255,255,255,.4);font-size:10px;text-decoration:none}
.nav-item.active,.nav-item:active{color:#C9A96E}
.card{background:#fff;border-radius:14px;border:1px solid #E8E6E0;margin-bottom:12px;overflow:hidden}
.list-row{display:flex;align-items:center;gap:12px;padding:12px 16px;border-bottom:1px solid #E8E6E0;text-decoration:none;color:#0B132B}
.list-row:last-child{border-bottom:none}
.list-row:active{background:#f4f3ef}
.prog{background:#eee;border-radius:20px;height:4px;overflow:hidden;margin-top:5px}
.prog-fill{height:100%;background:#C9A96E;border-radius:20px}
.av{width:38px;height:38px;border-radius:50%;background:#0B132B;color:#C9A96E;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:12px;flex-shrink:0}
.section-lbl{font-size:10px;text-transform:uppercase;letter-spacing:1.2px;color:#8A8880;font-weight:700;margin:14px 0 7px;padding:0 2px}
.kv{display:flex;justify-content:space-between;padding:9px 0;border-bottom:1px solid #E8E6E0}
.kv:last-child{border-bottom:none}
.detail-hdr{background:#0B132B;margin:-16px -16px 16px;padding:20px 18px;padding-top:20px}
.det-av{width:54px;height:54px;border-radius:50%;background:#C9A96E;color:#0B132B;display:flex;align-items:center;justify-content:center;font-size:18px;font-weight:800;margin-bottom:10px}
</style>
</head>
<body>
<div class="app">
<?php if ($page === 'login'): ?>
<div class="min-h-screen flex flex-col" style="background:#0B132B">
    <div class="flex-1 flex flex-col items-center justify-center p-8">
        <div class="text-white text-3xl font-bold tracking-tight mb-1"><?= APP_NAME ?></div>
        <div class="text-[#C9A96E] text-xs tracking-widest uppercase">Production Studio</div>
    </div>
    <div class="bg-white rounded-t-3xl p-6" style="padding-bottom:calc(24px + env(safe-area-inset-bottom,0))">
        <h2 class="text-xl font-bold text-gray-900 mb-5">Sign in</h2>
        <?php if (!empty($loginError)): ?><div class="bg-red-50 text-red-700 rounded-xl px-4 py-3 text-sm font-medium mb-4"><?= e($loginError) ?></div><?php endif; ?>
        <form method="POST">
            <div class="mb-4"><label class="block text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1.5">Email</label>
                <input class="w-full border border-gray-200 rounded-xl px-4 py-3 text-base outline-none focus:border-gray-900" type="email" name="email" placeholder="you@aykaoriginals.com" required></div>
            <div class="mb-5"><label class="block text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1.5">Password</label>
                <input class="w-full border border-gray-200 rounded-xl px-4 py-3 text-base outline-none focus:border-gray-900" type="password" name="password" required></div>
            <button class="w-full bg-[#0B132B] text-white py-3.5 rounded-xl text-base font-bold" type="submit">Sign In</button>
        </form>
    </div>
</div>
<?php else: ?>
<!-- Topbar -->
<div class="sticky top-0 z-40 bg-[#0B132B] px-4 py-3 flex items-center gap-3" style="padding-top:calc(12px + env(safe-area-inset-top,0))">
    <?php if (in_array($page,['model_detail','project_detail'])): ?>
    <a href="?page=<?= str_replace('_detail','',$page) ?>" class="text-white/50 text-sm">&larr;</a>
    <?php endif; ?>
    <div class="flex-1">
        <div class="text-white font-bold text-base leading-tight">
            <?php $titles=['dashboard'=>APP_NAME,'models'=>'Models','model_detail'=>'Profile','projects'=>'Projects','project_detail'=>'Project','meetings'=>'Meetings','accounts'=>'Accounts'];
            echo e($titles[$page]??APP_NAME); ?>
        </div>
        <?php if($page==='dashboard'): ?><div class="text-[#C9A96E] text-[10px] tracking-widest uppercase">Production Studio</div><?php endif; ?>
    </div>
    <div class="w-8 h-8 rounded-full bg-[#C9A96E] flex items-center justify-center text-[#0B132B] font-bold text-xs flex-shrink-0">
        <?= strtoupper(substr($user['name']??'U',0,2)) ?>
    </div>
</div>
<div class="flex-1 p-4" style="padding-bottom:calc(80px + env(safe-area-inset-bottom,0))">

<?php if ($page==='dashboard'):
    $sm=$pd['summary']??[]; $pjs=$pd['projects']['data']??[]; $mtgs=$pd['meetings']['data']??[]; $invs=$pd['invoices']['data']??[];
?>
<div class="text-sm text-gray-500 mb-4">Hello <strong class="text-gray-900"><?= e($user['name']??'') ?></strong> &#128075;</div>
<div class="grid grid-cols-2 gap-3 mb-4">
    <div class="bg-[#0B132B] rounded-2xl p-4 col-span-2"><div class="text-white/50 text-xs uppercase tracking-wide mb-1">Net Profit</div>
        <div class="text-white text-2xl font-bold">AED <?= number_format($sm['net_profit']??0) ?></div></div>
    <div class="bg-white rounded-2xl p-4 border border-gray-100"><div class="text-gray-400 text-xs uppercase tracking-wide mb-1">Revenue</div>
        <div class="text-gray-900 text-lg font-bold">AED <?= number_format(($sm['total_revenue']??0)/1000,1) ?>k</div></div>
    <div class="bg-[#C9A96E] rounded-2xl p-4"><div class="text-[#0B132B]/60 text-xs uppercase tracking-wide mb-1">Pending</div>
        <div class="text-[#0B132B] text-lg font-bold">AED <?= number_format(($sm['pending_payments']??0)/1000,1) ?>k</div></div>
</div>

<?php if(!empty($mtgs)): ?>
<div class="card">
    <div class="px-4 pt-3 pb-1 flex items-center justify-between"><span class="text-xs font-bold uppercase tracking-wide text-gray-400">Meetings</span></div>
    <?php foreach(array_slice($mtgs,0,3) as $m): $dt=new DateTime($m['meeting_at']??'now'); ?>
    <div class="list-row cursor-default">
        <div class="w-2 h-2 rounded-full flex-shrink-0 mt-0.5" style="background:<?= ($m['mode']??'')!=='Online'?'#B7770A':'#1A5276' ?>"></div>
        <div class="flex-1 min-w-0">
            <div class="font-semibold text-sm truncate"><?= e($m['title']) ?></div>
            <div class="text-xs text-gray-400 mt-0.5"><?= $dt->format('D d M') ?> &middot; <?= $dt->format('h:i A') ?></div>
        </div>
        <span class="text-xs px-2 py-0.5 rounded-full font-semibold <?= ($m['mode']??'')==='Online'?'bg-blue-50 text-blue-700':'bg-amber-50 text-amber-700' ?>"><?= e($m['mode']??'') ?></span>
    </div>
    <?php endforeach; ?>
    <a class="block text-center py-3 text-sm font-semibold text-[#C9A96E] border-t border-gray-100" href="?page=meetings">All meetings &rarr;</a>
</div>
<?php endif; ?>

<?php if(!empty($pjs)): ?>
<div class="card">
    <div class="px-4 pt-3 pb-1"><span class="text-xs font-bold uppercase tracking-wide text-gray-400">Projects</span></div>
    <?php foreach(array_slice($pjs,0,4) as $p): ?>
    <a class="list-row" href="?page=project_detail&id=<?= (int)$p['id'] ?>">
        <div class="flex-1 min-w-0"><div class="font-semibold text-sm truncate"><?= e($p['title']) ?></div>
            <div class="text-xs text-gray-400 truncate"><?= e($p['brand_name']??'') ?></div>
            <div class="prog"><div class="prog-fill" style="width:<?= (int)($p['progress']??0) ?>%"></div></div>
        </div>
        <div class="text-right flex-shrink-0"><?= badge($p['status']??'') ?><div class="text-xs text-gray-400 mt-1"><?= (int)($p['progress']??0) ?>%</div></div>
        <span class="text-gray-200 text-lg">&#8250;</span>
    </a>
    <?php endforeach; ?>
    <a class="block text-center py-3 text-sm font-semibold text-[#C9A96E] border-t border-gray-100" href="?page=projects">All projects &rarr;</a>
</div>
<?php endif; ?>

<?php elseif($page==='models'):
    $models=$pd['models']['data']??[]; $lp=(int)($pd['models']['last_page']??1); $cp=(int)($_GET['p']??1);
?>
<form method="GET">
    <input type="hidden" name="page" value="models">
    <input class="w-full bg-white border border-gray-200 rounded-2xl px-4 py-2.5 text-sm outline-none mb-3" type="text" name="name" value="<?= e($_GET['name']??'') ?>" placeholder="Search models...">
    <div class="flex gap-2 overflow-x-auto pb-1 mb-3 scrollbar-none">
        <?php foreach([''=>'All','Active'=>'Active','Inactive'=>'Inactive','On Leave'=>'On Leave'] as $v=>$l): ?>
        <button type="submit" name="status" value="<?= e($v) ?>" class="flex-shrink-0 px-3 py-1.5 rounded-full text-xs font-semibold border <?= ($_GET['status']??'')===$v?'bg-[#0B132B] text-white border-[#0B132B]':'bg-white text-gray-500 border-gray-200' ?>"><?= e($l) ?></button>
        <?php endforeach; ?>
    </div>
</form>
<?php if(empty($models)): ?><div class="text-center py-12 text-gray-300"><div class="text-4xl mb-2">&#10022;</div><div class="text-sm">No models found</div></div>
<?php else: ?>
<div class="card mb-0">
    <?php foreach($models as $m): ?>
    <a class="list-row" href="?page=model_detail&id=<?= (int)$m['id'] ?>">
        <div class="av"><?= strtoupper(substr($m['name']??'M',0,2)) ?></div>
        <div class="flex-1 min-w-0">
            <div class="font-semibold text-sm truncate"><?= e($m['name']) ?></div>
            <div class="text-xs text-gray-400 truncate"><?= e($m['location']??'') ?><?php if($m['height']): ?> &middot; <?= e($m['height']) ?><?php endif; ?></div>
        </div>
        <?= badge($m['status']??'') ?>
        <span class="text-gray-200 text-lg">&#8250;</span>
    </a>
    <?php endforeach; ?>
</div>
<?php if($lp>1): ?><div class="flex justify-center gap-2 py-3">
    <?php if($cp>1): ?><a class="px-4 py-2 bg-white border border-gray-200 rounded-full text-sm font-semibold" href="?page=models&p=<?= $cp-1 ?>&name=<?= urlencode($_GET['name']??'') ?>&status=<?= urlencode($_GET['status']??'') ?>">&larr;</a><?php endif; ?>
    <span class="px-4 py-2 bg-[#0B132B] text-white rounded-full text-sm font-semibold"><?= $cp ?>/<?= $lp ?></span>
    <?php if($cp<$lp): ?><a class="px-4 py-2 bg-white border border-gray-200 rounded-full text-sm font-semibold" href="?page=models&p=<?= $cp+1 ?>&name=<?= urlencode($_GET['name']??'') ?>&status=<?= urlencode($_GET['status']??'') ?>">&rarr;</a><?php endif; ?>
</div><?php endif; ?>
<?php endif; ?>

<?php elseif($page==='model_detail'): $m=$pd['model']??[]; ?>
<?php if(empty($m)||!empty($m['message'])): ?><div class="text-center py-12 text-gray-300"><div class="text-4xl mb-2">&#10022;</div><div>Not found</div></div>
<?php else: ?>
<div class="detail-hdr">
    <div class="det-av"><?= strtoupper(substr($m['name']??'M',0,2)) ?></div>
    <div class="text-white text-xl font-bold"><?= e($m['name']) ?></div>
    <div class="text-white/50 text-sm"><?= e($m['location']??'') ?><?php if($m['age']): ?> &middot; <?= e($m['age']) ?> yrs<?php endif; ?></div>
    <div class="flex flex-wrap gap-1.5 mt-2"><?= badge($m['status']??'') ?><?php if($m['is_inhouse']): ?><span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-800">In-house</span><?php endif; ?></div>
</div>
<?php if($m['height']||$m['bust']||$m['waist']||$m['hips']): ?>
<div class="section-lbl">Measurements</div>
<div class="grid grid-cols-2 gap-2 mb-4">
    <?php foreach(['height'=>'Height','bust'=>'Bust','waist'=>'Waist','hips'=>'Hips','shoe_size'=>'Shoe'] as $k=>$l): if(!empty($m[$k])): ?>
    <div class="bg-white rounded-xl border border-gray-100 p-3 text-center"><div class="text-xs text-gray-400 uppercase tracking-wide mb-1"><?= $l ?></div><div class="font-bold text-lg text-gray-900"><?= e($m[$k]) ?></div></div>
    <?php endif; endforeach; ?>
</div>
<?php endif; ?>
<div class="section-lbl">Contact</div>
<div class="card"><div class="px-4">
    <?php foreach(['email'=>'Email','phone'=>'Phone','budget'=>'Day Rate'] as $k=>$l): if(!empty($m[$k])): ?>
    <div class="kv"><span class="text-gray-400 text-sm"><?= $l ?></span><span class="font-semibold text-sm"><?= $k==='budget'?'AED '.number_format((float)$m[$k]):e($m[$k]) ?></span></div>
    <?php endif; endforeach; ?>
</div></div>
<?php if($m['about']): ?><div class="section-lbl">About</div><div class="bg-white rounded-xl border border-gray-100 p-4 text-sm text-gray-500 leading-relaxed"><?= nl2br(e($m['about'])) ?></div><?php endif; ?>
<?php if(!empty($m['projects'])): ?>
<div class="section-lbl">Projects (<?= count($m['projects']) ?>)</div>
<div class="card">
    <?php foreach($m['projects'] as $p): ?><a class="list-row" href="?page=project_detail&id=<?= (int)$p['id'] ?>"><div class="flex-1"><div class="font-semibold text-sm"><?= e($p['title']) ?></div></div><?= badge($p['status']??'') ?></a><?php endforeach; ?>
</div>
<?php endif; ?>
<?php endif; ?>

<?php elseif($page==='projects'):
    $pjs=$pd['projects']['data']??[]; $lp=(int)($pd['projects']['last_page']??1); $cp=(int)($_GET['p']??1);
?>
<div class="flex gap-2 overflow-x-auto pb-1 mb-3">
    <?php foreach([''=>'All','Active'=>'Active','Planning'=>'Planning','Review'=>'Review','Completed'=>'Done'] as $v=>$l): ?>
    <a class="flex-shrink-0 px-3 py-1.5 rounded-full text-xs font-semibold border <?= ($_GET['status']??'')===$v?'bg-[#0B132B] text-white border-[#0B132B]':'bg-white text-gray-500 border-gray-200' ?>" href="?page=projects&status=<?= urlencode($v) ?>"><?= e($l) ?></a>
    <?php endforeach; ?>
</div>
<?php if(empty($pjs)): ?><div class="text-center py-12 text-gray-300"><div class="text-4xl mb-2">&#11208;</div><div class="text-sm">No projects</div></div>
<?php else: ?><div class="card mb-0">
    <?php foreach($pjs as $p): ?><a class="list-row" href="?page=project_detail&id=<?= (int)$p['id'] ?>">
        <div class="flex-1 min-w-0"><div class="font-semibold text-sm truncate"><?= e($p['title']) ?></div>
            <div class="text-xs text-gray-400 truncate"><?= e($p['brand_name']??'') ?></div>
            <div class="prog"><div class="prog-fill" style="width:<?= (int)($p['progress']??0) ?>%"></div></div>
        </div>
        <div class="text-right flex-shrink-0 ml-3"><?= badge($p['status']??'') ?><div class="text-xs font-bold mt-1">AED <?= number_format((float)($p['budget']??0)/1000,0) ?>k</div></div>
        <span class="text-gray-200">&#8250;</span>
    </a><?php endforeach; ?>
</div><?php endif; ?>

<?php elseif($page==='project_detail'): $p=$pd['project']??[]; ?>
<?php if(empty($p)||!empty($p['message'])): ?><div class="text-center py-12 text-gray-300 text-sm">Not found</div>
<?php else: ?>
<div class="detail-hdr" style="border-radius:0 0 20px 20px;margin-bottom:16px">
    <div class="det-av" style="border-radius:14px;width:48px;height:48px;font-size:15px"><?= strtoupper(substr($p['title']??'P',0,2)) ?></div>
    <div class="text-white text-xl font-bold leading-tight"><?= e($p['title']) ?></div>
    <div class="text-white/50 text-sm mt-0.5"><?= e($p['brand_name']??$p['brand']['name']??'') ?> &middot; <?= e($p['category']??'') ?></div>
    <div class="mt-2"><?= badge($p['status']??'') ?></div>
</div>
<div class="grid grid-cols-2 gap-3 mb-4">
    <div class="bg-white rounded-xl border border-gray-100 p-3 text-center"><div class="text-xs text-gray-400 uppercase tracking-wide mb-1">Budget</div><div class="font-bold text-lg">AED <?= number_format((float)($p['budget']??0)/1000,1) ?>k</div></div>
    <div class="bg-white rounded-xl border border-gray-100 p-3 text-center"><div class="text-xs text-gray-400 uppercase tracking-wide mb-1">Progress</div><div class="font-bold text-lg"><?= (int)($p['progress']??0) ?>%</div></div>
</div>
<div class="bg-gray-200 rounded-full h-1.5 mb-4"><div class="bg-[#C9A96E] h-1.5 rounded-full" style="width:<?= (int)($p['progress']??0) ?>%"></div></div>
<?php if(!empty($p['models'])): ?><div class="section-lbl">Models (<?= count($p['models']) ?>)</div>
<div class="flex flex-wrap gap-2 mb-4"><?php foreach($p['models'] as $m): ?><div class="flex items-center gap-1.5 bg-white border border-gray-100 rounded-full px-3 py-1.5"><div class="av" style="width:26px;height:26px;font-size:9px"><?= strtoupper(substr($m['name']??'',0,2)) ?></div><span class="text-xs font-semibold"><?= e($m['name']) ?></span></div><?php endforeach; ?></div>
<?php endif; ?>
<?php if(!empty($p['invoices'])): ?><div class="section-lbl">Invoices</div><div class="card">
    <?php foreach($p['invoices'] as $inv): ?><div class="list-row cursor-default"><div class="flex-1"><div class="font-semibold text-sm"><?= e($inv['invoice_number']) ?></div><div class="text-xs text-gray-400">Due <?= isset($inv['due_date'])?date('d M Y',strtotime($inv['due_date'])):'-' ?></div></div><div class="text-right"><?= badge($inv['status']??'') ?><div class="font-bold text-sm mt-1">AED <?= number_format((float)($inv['total']??0)) ?></div></div></div><?php endforeach; ?>
</div><?php endif; ?>
<?php if($p['notes']): ?><div class="section-lbl">Notes</div><div class="bg-white rounded-xl border border-gray-100 p-4 text-sm text-gray-500"><?= nl2br(e($p['notes'])) ?></div><?php endif; ?>
<?php endif; ?>

<?php elseif($page==='meetings'):
    $mtgs=$pd['meetings']['data']??[]; $lp=(int)($pd['meetings']['last_page']??1); $cp=(int)($_GET['p']??1);
?>
<?php if(empty($mtgs)): ?><div class="text-center py-12 text-gray-300"><div class="text-4xl mb-2">&#9633;</div><div class="text-sm">No meetings</div></div>
<?php else: ?><div class="card mb-0">
    <?php foreach($mtgs as $m): $dt=new DateTime($m['meeting_at']??'now'); $today=$dt->format('Y-m-d')===date('Y-m-d'); ?>
    <div class="list-row cursor-default <?= $dt<new DateTime()?'opacity-50':'' ?>">
        <div class="w-2 h-2 rounded-full flex-shrink-0" style="background:<?= ($m['mode']??'')!=='Online'?'#B7770A':'#1A5276' ?>"></div>
        <div class="flex-1 min-w-0"><div class="font-semibold text-sm truncate"><?= e($m['title']) ?></div>
            <div class="text-xs text-gray-400 mt-0.5"><?= $today?'<strong class="text-amber-600">Today</strong>':$dt->format('D d M') ?> &middot; <?= $dt->format('h:i A') ?></div>
        </div>
        <span class="text-xs px-2 py-0.5 rounded-full font-semibold <?= ($m['mode']??'')==='Online'?'bg-blue-50 text-blue-700':'bg-amber-50 text-amber-700' ?>"><?= e($m['mode']??'') ?></span>
    </div>
    <?php endforeach; ?>
</div>
<?php if($lp>1): ?><div class="flex justify-center gap-2 py-3">
    <?php if($cp>1): ?><a class="px-4 py-2 bg-white border rounded-full text-sm font-semibold" href="?page=meetings&p=<?= $cp-1 ?>">&larr;</a><?php endif; ?>
    <span class="px-4 py-2 bg-[#0B132B] text-white rounded-full text-sm font-semibold"><?= $cp ?>/<?= $lp ?></span>
    <?php if($cp<$lp): ?><a class="px-4 py-2 bg-white border rounded-full text-sm font-semibold" href="?page=meetings&p=<?= $cp+1 ?>">&rarr;</a><?php endif; ?>
</div><?php endif; ?>
<?php endif; ?>

<?php elseif($page==='accounts'):
    $sm=$pd['summary']??[]; $invs=$pd['invoices']['data']??[]; $lp=(int)($pd['invoices']['last_page']??1); $cp=(int)($_GET['p']??1);
?>
<div class="grid grid-cols-2 gap-3 mb-4">
    <div class="col-span-2 bg-[#0B132B] rounded-2xl p-4"><div class="text-white/50 text-xs uppercase tracking-wide mb-1">Net Profit</div><div class="text-white text-2xl font-bold">AED <?= number_format($sm['net_profit']??0) ?></div></div>
    <div class="bg-white rounded-2xl p-4 border border-gray-100"><div class="text-gray-400 text-xs uppercase tracking-wide mb-1">Revenue</div><div class="text-green-700 text-lg font-bold">AED <?= number_format(($sm['total_revenue']??0)/1000,1) ?>k</div></div>
    <div class="bg-white rounded-2xl p-4 border border-gray-100"><div class="text-gray-400 text-xs uppercase tracking-wide mb-1">Expenses</div><div class="text-red-600 text-lg font-bold">AED <?= number_format(($sm['total_expenses']??0)/1000,1) ?>k</div></div>
</div>
<div class="section-lbl">Invoices</div>
<?php if(empty($invs)): ?><div class="text-center py-8 text-gray-300 text-sm">No invoices yet</div>
<?php else: ?><div class="card mb-0">
    <?php foreach($invs as $inv): ?><div class="list-row cursor-default">
        <div class="flex-1 min-w-0"><div class="font-semibold text-sm font-mono"><?= e($inv['invoice_number']) ?></div>
            <div class="text-xs text-gray-400 truncate"><?= e($inv['brand_name']??'') ?> &middot; Due <?= isset($inv['due_date'])?date('d M',strtotime($inv['due_date'])):'-' ?></div></div>
        <div class="text-right"><div class="font-bold text-sm">AED <?= number_format((float)($inv['total']??0)) ?></div><div class="mt-1"><?= badge($inv['status']??'') ?></div></div>
    </div><?php endforeach; ?>
</div>
<?php if($lp>1): ?><div class="flex justify-center gap-2 py-3">
    <?php if($cp>1): ?><a class="px-4 py-2 bg-white border rounded-full text-sm font-semibold" href="?page=accounts&p=<?= $cp-1 ?>">&larr;</a><?php endif; ?>
    <span class="px-4 py-2 bg-[#0B132B] text-white rounded-full text-sm font-semibold"><?= $cp ?>/<?= $lp ?></span>
    <?php if($cp<$lp): ?><a class="px-4 py-2 bg-white border rounded-full text-sm font-semibold" href="?page=accounts&p=<?= $cp+1 ?>">&rarr;</a><?php endif; ?>
</div><?php endif; ?>
<?php endif; ?>
<?php endif; ?>

</div>

<!-- Bottom Nav -->
<?php if(!in_array($page,['model_detail','project_detail'])): ?>
<nav class="bottom-nav">
    <?php
    $nav=[['dashboard','&#8964;','Home'],['models','&#10022;','Models'],['projects','&#11208;','Projects'],['meetings','&#9633;','Meetings'],['accounts','&#9655;','Finance']];
    foreach($nav as [$np,$ic,$lb]): $a=$page===$np?'active':''; ?>
    <a class="nav-item <?= $a ?>" href="?page=<?= $np ?>"><span style="font-size:20px"><?= $ic ?></span><span><?= $lb ?></span></a>
    <?php endforeach; ?>
</nav>
<?php endif; ?>

<?php endif; ?>
</div>
</body>
</html>
'@
Write-File "$MobilePath\index.php" $mobileApp

# -- Clear caches ----------------------------------------------
Write-Host ""
Write-Host "Clearing caches..." -ForegroundColor Yellow
Set-Location $ProjectPath
& $phpExe artisan view:clear
& $phpExe artisan cache:clear
& $phpExe artisan config:clear

Write-Host ""
Write-Host "======================================================" -ForegroundColor Green
Write-Host "  ALL DONE!" -ForegroundColor Green
Write-Host "======================================================" -ForegroundColor Green
Write-Host ""
Write-Host "  Web Dashboard (responsive):" -ForegroundColor White
Write-Host "    http://localhost:8000" -ForegroundColor Cyan
Write-Host "    Works on all screen sizes" -ForegroundColor Gray
Write-Host "    Hamburger menu on mobile/tablet" -ForegroundColor Gray
Write-Host ""
Write-Host "  Mobile App (native PHP):" -ForegroundColor White
Write-Host "    http://localhost/ayka-mobile/" -ForegroundColor Cyan
Write-Host "    Full mobile-optimised UI" -ForegroundColor Gray
Write-Host "    Dashboard, Models, Projects, Meetings, Accounts" -ForegroundColor Gray
Write-Host "    Talks to Laravel API (make sure artisan serve is running)" -ForegroundColor Gray
Write-Host ""
Write-Host "  Mobile logins (same as web):" -ForegroundColor White
Write-Host "    admin@aykaoriginals.com  / password" -ForegroundColor Gray
Write-Host "    sanchu@aykaoriginals.com / sanchu123" -ForegroundColor Gray
Write-Host "    ashima@aykaoriginals.com / ashima123" -ForegroundColor Gray
Write-Host "    ananthu@aykaoriginals.com/ ananthu123" -ForegroundColor Gray
Write-Host ""
