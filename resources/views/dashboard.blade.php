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