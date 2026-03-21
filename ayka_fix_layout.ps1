# ============================================================
#  AYKA ORIGINALS - Fix Broken Web Layout
#  Run: powershell -ExecutionPolicy Bypass -File ayka_fix_layout.ps1
# ============================================================

$ProjectPath = "C:\laragon\www\ayka-originals"
Set-Location $ProjectPath

$phpExe = (Get-Command php -ErrorAction SilentlyContinue).Source
if (-not $phpExe) { $phpExe = "php" }

Write-Host ""
Write-Host "======================================================" -ForegroundColor Cyan
Write-Host "  AYKA ORIGINALS - Fixing Web Layout" -ForegroundColor Cyan
Write-Host "======================================================" -ForegroundColor Cyan
Write-Host ""

function Write-File($Path, $Content) {
    $Dir = Split-Path $Path -Parent
    if (!(Test-Path $Dir)) { New-Item -ItemType Directory -Path $Dir -Force | Out-Null }
    [System.IO.File]::WriteAllText($Path, $Content, [System.Text.UTF8Encoding]::new($false))
    Write-Host "  [OK] $Path" -ForegroundColor Green
}

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
    .sidebar-link.active {
      background: rgba(201,169,110,0.12);
      color: #C9A96E;
      border-left: 2px solid #C9A96E;
    }
  </style>
</head>
<body class="bg-gray-50 text-gray-900 min-h-screen">

<!-- Mobile sidebar overlay -->
<div id="sidebar-overlay"
     class="fixed inset-0 bg-black/50 z-40 lg:hidden hidden"
     onclick="closeSidebar()"></div>

<!-- Sidebar -->
<aside id="mobile-sidebar"
       class="fixed top-0 left-0 h-full w-64 bg-[#0B132B] flex flex-col z-50
              -translate-x-full lg:translate-x-0
              transition-transform duration-200 ease-in-out">

  <div class="p-5 border-b border-white/10 flex-shrink-0">
    <p class="font-display text-white text-lg font-bold tracking-wide">Ayka Originals</p>
    <p class="text-[#C9A96E] text-xs tracking-widest uppercase mt-0.5">Production Studio</p>
  </div>

  <nav class="flex-1 py-4 px-2 space-y-0.5 overflow-y-auto">
    <a href="{{ route('dashboard') }}"
       class="sidebar-link flex items-center gap-2.5 px-3 py-2 rounded text-white/55 hover:bg-white/10 hover:text-white/90 text-sm transition-all
              {{ request()->routeIs('dashboard') ? 'active' : '' }}">
      <span>&#9638;</span> Dashboard
    </a>
    <p class="px-3 pt-4 pb-1 text-[9px] tracking-widest uppercase text-white/20">Management</p>
    <a href="{{ route('models.index') }}"
       class="sidebar-link flex items-center gap-2.5 px-3 py-2 rounded text-white/55 hover:bg-white/10 hover:text-white/90 text-sm transition-all
              {{ request()->routeIs('models.*') ? 'active' : '' }}">
      <span>&#10022;</span> Models
    </a>
    <a href="{{ route('projects.index') }}"
       class="sidebar-link flex items-center gap-2.5 px-3 py-2 rounded text-white/55 hover:bg-white/10 hover:text-white/90 text-sm transition-all
              {{ request()->routeIs('projects.*') ? 'active' : '' }}">
      <span>&#11208;</span> Projects
    </a>
    <a href="{{ route('brands.index') }}"
       class="sidebar-link flex items-center gap-2.5 px-3 py-2 rounded text-white/55 hover:bg-white/10 hover:text-white/90 text-sm transition-all
              {{ request()->routeIs('brands.*') ? 'active' : '' }}">
      <span>&#9671;</span> Brands
    </a>
    <a href="{{ route('crew.index') }}"
       class="sidebar-link flex items-center gap-2.5 px-3 py-2 rounded text-white/55 hover:bg-white/10 hover:text-white/90 text-sm transition-all
              {{ request()->routeIs('crew.*') ? 'active' : '' }}">
      <span>&#9678;</span> Crew
    </a>
    <a href="{{ route('employees.index') }}"
       class="sidebar-link flex items-center gap-2.5 px-3 py-2 rounded text-white/55 hover:bg-white/10 hover:text-white/90 text-sm transition-all
              {{ request()->routeIs('employees.*') ? 'active' : '' }}">
      <span>&#9673;</span> Employees
    </a>
    <p class="px-3 pt-4 pb-1 text-[9px] tracking-widest uppercase text-white/20">Finance &amp; Schedule</p>
    <a href="{{ route('invoices.index') }}"
       class="sidebar-link flex items-center gap-2.5 px-3 py-2 rounded text-white/55 hover:bg-white/10 hover:text-white/90 text-sm transition-all
              {{ request()->routeIs('invoices.*') ? 'active' : '' }}">
      <span>&#9655;</span> Accounts
    </a>
    <a href="{{ route('meetings.index') }}"
       class="sidebar-link flex items-center gap-2.5 px-3 py-2 rounded text-white/55 hover:bg-white/10 hover:text-white/90 text-sm transition-all
              {{ request()->routeIs('meetings.*') ? 'active' : '' }}">
      <span>&#9633;</span> Meetings
    </a>
    <p class="px-3 pt-4 pb-1 text-[9px] tracking-widest uppercase text-white/20">System</p>
    <a href="{{ route('activity-log.index') }}"
       class="sidebar-link flex items-center gap-2.5 px-3 py-2 rounded text-white/55 hover:bg-white/10 hover:text-white/90 text-sm transition-all
              {{ request()->routeIs('activity-log.*') ? 'active' : '' }}">
      <span>&#9675;</span> Activity Log
    </a>
  </nav>

  <div class="p-4 border-t border-white/10 flex-shrink-0">
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

<!-- Page wrapper - pushed right on desktop by sidebar width -->
<div class="lg:pl-64 flex flex-col min-h-screen">

  <!-- Topbar -->
  <header class="bg-white border-b border-gray-200 sticky top-0 z-30 h-[60px] flex items-center px-4 lg:px-7 gap-3">

    <!-- Hamburger - mobile only -->
    <button onclick="openSidebar()"
            class="lg:hidden w-9 h-9 flex items-center justify-center rounded-lg border border-gray-200 text-gray-500 flex-shrink-0">
      <svg width="18" height="14" viewBox="0 0 18 14" fill="none">
        <path d="M1 1h16M1 7h16M1 13h16" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
      </svg>
    </button>

    <div class="min-w-0 flex-1">
      <h1 class="font-display text-sm lg:text-base font-semibold truncate">@yield('title','Dashboard')</h1>
      <p class="text-xs text-gray-400 hidden sm:block truncate">Ayka Originals / @yield('title','Overview')</p>
    </div>

    <div class="ml-auto flex items-center gap-2 lg:gap-3 flex-shrink-0">
      <form action="{{ route('search') }}" class="hidden sm:flex items-center gap-2 bg-gray-50 border border-gray-200 rounded-lg px-3 h-9">
        <span class="text-gray-400 text-sm">&#9906;</span>
        <input name="q" value="{{ request('q') }}" placeholder="Search..."
               class="bg-transparent border-none outline-none text-sm w-28 lg:w-40">
      </form>
      <div class="w-8 h-8 lg:w-9 lg:h-9 rounded-lg border border-gray-200 flex items-center justify-center
                  cursor-pointer hover:bg-gray-50 text-gray-500 text-sm">&#128276;</div>
      <div class="w-8 h-8 rounded-lg bg-[#0B132B] flex items-center justify-center
                  text-[#C9A96E] text-xs font-bold cursor-pointer">
        {{ strtoupper(substr(auth()->user()->name,0,2)) }}
      </div>
    </div>
  </header>

  <!-- Main content -->
  <main class="flex-1 p-4 lg:p-7">
    @if(session('success'))
      <div x-data="{show:true}" x-show="show" x-init="setTimeout(()=>show=false,4000)"
           class="mb-4 bg-green-50 border border-green-200 text-green-800 text-sm px-4 py-3 rounded-lg flex items-center justify-between">
        <span>&#10003; {{ session('success') }}</span>
        <button @click="show=false" class="text-green-500 text-lg leading-none">&times;</button>
      </div>
    @endif
    @yield('content')
  </main>

</div><!-- /lg:pl-64 -->

<script>
function openSidebar() {
    document.getElementById('mobile-sidebar').classList.remove('-translate-x-full');
    document.getElementById('sidebar-overlay').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}
function closeSidebar() {
    document.getElementById('mobile-sidebar').classList.add('-translate-x-full');
    document.getElementById('sidebar-overlay').classList.add('hidden');
    document.body.style.overflow = '';
}
// Close sidebar on escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeSidebar();
});
</script>
</body>
</html>
'@

Write-File "$ProjectPath\resources\views\layouts\app.blade.php" $layout

Write-Host ""
Write-Host "Clearing view cache..." -ForegroundColor Yellow
& $phpExe artisan view:clear
& $phpExe artisan cache:clear

Write-Host ""
Write-Host "======================================================" -ForegroundColor Green
Write-Host "  DONE! Web layout is fixed." -ForegroundColor Green
Write-Host "======================================================" -ForegroundColor Green
Write-Host ""
Write-Host "  Hard refresh: Ctrl + Shift + R" -ForegroundColor Yellow
Write-Host ""
