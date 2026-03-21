<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>@yield('title','Dashboard') — Ayka Originals</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>
  <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    body { font-family: 'DM Sans', sans-serif; }
    .font-display { font-family: 'Syne', sans-serif; }
    .sidebar-link.active { background: rgba(201,169,110,0.12); color: #C9A96E; border-left: 2px solid #C9A96E; }
  </style>
</head>
<body class="bg-gray-50 text-gray-900 flex">

{{-- Sidebar --}}
<aside class="w-56 min-h-screen bg-[#0B132B] flex flex-col sticky top-0 h-screen">
  <div class="p-5 border-b border-white/10">
    <p class="font-display text-white text-lg font-bold tracking-wide">Ayka Originals</p>
    <p class="text-[#C9A96E] text-xs tracking-widest uppercase mt-0.5">Production Studio</p>
  </div>
  <nav class="flex-1 py-4 px-2 space-y-0.5 overflow-y-auto">
    <a href="{{ route('dashboard') }}" class="sidebar-link flex items-center gap-2.5 px-3 py-2 rounded text-white/55 hover:bg-white/10 hover:text-white/90 text-sm transition-all {{ request()->routeIs('dashboard') ? 'active' : '' }}">
      <span>▦</span> Dashboard
    </a>
    <p class="px-3 pt-4 pb-1 text-[9px] tracking-widest uppercase text-white/20">Management</p>
    <a href="{{ route('models.index') }}" class="sidebar-link flex items-center gap-2.5 px-3 py-2 rounded text-white/55 hover:bg-white/10 hover:text-white/90 text-sm transition-all {{ request()->routeIs('models.*') ? 'active' : '' }}">
      <span>✦</span> Models
    </a>
    <a href="{{ route('projects.index') }}" class="sidebar-link flex items-center gap-2.5 px-3 py-2 rounded text-white/55 hover:bg-white/10 hover:text-white/90 text-sm transition-all {{ request()->routeIs('projects.*') ? 'active' : '' }}">
      <span>◈</span> Projects
    </a>
    <a href="{{ route('brands.index') }}" class="sidebar-link flex items-center gap-2.5 px-3 py-2 rounded text-white/55 hover:bg-white/10 hover:text-white/90 text-sm transition-all {{ request()->routeIs('brands.*') ? 'active' : '' }}">
      <span>◇</span> Brands
    </a>
    <a href="{{ route('crew.index') }}" class="sidebar-link flex items-center gap-2.5 px-3 py-2 rounded text-white/55 hover:bg-white/10 hover:text-white/90 text-sm transition-all {{ request()->routeIs('crew.*') ? 'active' : '' }}">
      <span>◎</span> Crew
    </a>
    <a href="{{ route('employees.index') }}" class="sidebar-link flex items-center gap-2.5 px-3 py-2 rounded text-white/55 hover:bg-white/10 hover:text-white/90 text-sm transition-all {{ request()->routeIs('employees.*') ? 'active' : '' }}">
      <span>◉</span> Employees
    </a>
    <p class="px-3 pt-4 pb-1 text-[9px] tracking-widest uppercase text-white/20">Finance & Schedule</p>
    <a href="{{ route('invoices.index') }}" class="sidebar-link flex items-center gap-2.5 px-3 py-2 rounded text-white/55 hover:bg-white/10 hover:text-white/90 text-sm transition-all {{ request()->routeIs('invoices.*') ? 'active' : '' }}">
      <span>▷</span> Accounts
    </a>
    <a href="{{ route('meetings.index') }}" class="sidebar-link flex items-center gap-2.5 px-3 py-2 rounded text-white/55 hover:bg-white/10 hover:text-white/90 text-sm transition-all {{ request()->routeIs('meetings.*') ? 'active' : '' }}">
      <span>◻</span> Meetings
    </a>
  
    <p class="px-3 pt-4 pb-1 text-[9px] tracking-widest uppercase text-white/20">System</p>
    <a href="{{ route('activity-log.index') }}" class="sidebar-link flex items-center gap-2.5 px-3 py-2 rounded text-white/55 hover:bg-white/10 hover:text-white/90 text-sm transition-all {{ request()->routeIs('activity-log.*') ? 'active' : '' }}">
      <span>&#9675;</span> Activity Log
    </a>
  </nav>
  <div class="p-4 border-t border-white/10">
    <div class="flex items-center gap-2.5">
      <div class="w-8 h-8 rounded-full bg-[#C9A96E] flex items-center justify-center text-[#0B132B] font-bold text-xs">
        {{ strtoupper(substr(auth()->user()->name,0,2)) }}
      </div>
      <div>
        <p class="text-white/70 text-xs font-medium">{{ auth()->user()->name }}</p>
        <p class="text-[#C9A96E] text-[10px]">{{ auth()->user()->getRoleNames()->first() }}</p>
      </div>
    </div>
    <form method="POST" action="{{ route('logout') }}" class="mt-3">
      @csrf
      <button class="text-white/30 hover:text-white/70 text-xs transition-colors">Sign out</button>
    </form>
  </div>
</aside>

{{-- Main --}}
<div class="flex-1 flex flex-col min-w-0">
  <header class="bg-white border-b border-gray-200 px-7 h-15 flex items-center gap-4 sticky top-0 z-10" style="height:60px">
    <div>
      <h1 class="font-display text-base font-semibold">@yield('title','Dashboard')</h1>
      <p class="text-xs text-gray-400">Ayka Originals / @yield('title','Overview')</p>
    </div>
    <div class="ml-auto flex items-center gap-3">
      <form action="{{ route('search') }}" class="flex items-center gap-2 bg-gray-50 border border-gray-200 rounded-lg px-3 h-9">
        <span class="text-gray-400 text-sm">⌕</span>
        <input name="q" value="{{ request('q') }}" placeholder="Search…" class="bg-transparent border-none outline-none text-sm w-40">
      </form>
      <div class="w-9 h-9 rounded-lg border border-gray-200 flex items-center justify-center cursor-pointer hover:bg-gray-50 text-gray-500 text-sm">🔔</div>
      <div class="w-8 h-8 rounded-lg bg-[#0B132B] flex items-center justify-center text-[#C9A96E] text-xs font-bold cursor-pointer">
        {{ strtoupper(substr(auth()->user()->name,0,2)) }}
      </div>
    </div>
  </header>
  <main class="flex-1 p-7">
    @if(session('success'))
      <div x-data="{show:true}" x-show="show" x-init="setTimeout(()=>show=false,4000)" class="mb-4 bg-green-50 border border-green-200 text-green-800 text-sm px-4 py-3 rounded-lg flex items-center justify-between">
        <span>✓ {{ session('success') }}</span>
        <button @click="show=false" class="text-green-500">✕</button>
      </div>
    @endif
    @yield('content')
  </main>
</div>
</body>
</html>
