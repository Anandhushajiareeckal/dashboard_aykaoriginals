@extends('layouts.app')
@section('title', 'Activity Log')
@section('content')
<div class="flex items-center justify-between mb-6">
    <div>
        <h2 class="font-display text-xl font-bold">Activity Log</h2>
        <p class="text-sm text-gray-400">Full audit trail with IP address and device tracking</p>
    </div>
</div>
<div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
    <div class="bg-white border border-gray-100 rounded-xl p-4 text-center">
        <p class="font-display text-2xl font-bold">{{ number_format($totalCount) }}</p>
        <p class="text-xs text-gray-400 uppercase tracking-wide mt-1">Total Events</p>
    </div>
    <div class="bg-white border border-gray-100 rounded-xl p-4 text-center">
        <p class="font-display text-2xl font-bold text-[#0B132B]">{{ $todayCount }}</p>
        <p class="text-xs text-gray-400 uppercase tracking-wide mt-1">Today</p>
    </div>
    <div class="bg-white border border-gray-100 rounded-xl p-4 text-center">
        <p class="font-display text-2xl font-bold text-blue-600">{{ $loginCount }}</p>
        <p class="text-xs text-gray-400 uppercase tracking-wide mt-1">Logins Today</p>
    </div>
    <div class="bg-white border border-gray-100 rounded-xl p-4 text-center">
        <p class="font-display text-2xl font-bold text-[#C9A96E]">{{ $uniqueIps }}</p>
        <p class="text-xs text-gray-400 uppercase tracking-wide mt-1">Unique IPs Today</p>
    </div>
</div>
<form method="GET" class="bg-white border border-gray-100 rounded-xl p-4 mb-5">
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3 mb-3">
        <div>
            <label class="block text-xs text-gray-400 uppercase tracking-wide mb-1">User</label>
            <select name="user_id" class="w-full border border-gray-200 rounded-lg px-2.5 py-2 text-sm outline-none focus:border-[#0B132B]">
                <option value="">All users</option>
                @foreach($users as $u)
                    <option value="{{ $u->id }}" {{ request('user_id')==$u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs text-gray-400 uppercase tracking-wide mb-1">Action</label>
            <select name="action" class="w-full border border-gray-200 rounded-lg px-2.5 py-2 text-sm outline-none focus:border-[#0B132B]">
                <option value="">All actions</option>
                @foreach($actions as $a)
                    <option value="{{ $a }}" {{ request('action')===$a ? 'selected' : '' }}>{{ ucfirst($a) }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs text-gray-400 uppercase tracking-wide mb-1">Module</label>
            <select name="module" class="w-full border border-gray-200 rounded-lg px-2.5 py-2 text-sm outline-none focus:border-[#0B132B]">
                <option value="">All modules</option>
                @foreach($modules as $m)
                    <option value="{{ $m }}" {{ request('module')===$m ? 'selected' : '' }}>{{ $m }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs text-gray-400 uppercase tracking-wide mb-1">IP Address</label>
            <input name="ip" value="{{ request('ip') }}" placeholder="e.g. 192.168."
                class="w-full border border-gray-200 rounded-lg px-2.5 py-2 text-sm outline-none focus:border-[#0B132B]">
        </div>
        <div>
            <label class="block text-xs text-gray-400 uppercase tracking-wide mb-1">From</label>
            <input name="date_from" type="date" value="{{ request('date_from') }}"
                class="w-full border border-gray-200 rounded-lg px-2.5 py-2 text-sm outline-none focus:border-[#0B132B]">
        </div>
        <div>
            <label class="block text-xs text-gray-400 uppercase tracking-wide mb-1">To</label>
            <input name="date_to" type="date" value="{{ request('date_to') }}"
                class="w-full border border-gray-200 rounded-lg px-2.5 py-2 text-sm outline-none focus:border-[#0B132B]">
        </div>
    </div>
    <div class="flex gap-3">
        <input name="search" value="{{ request('search') }}" placeholder="Search descriptions..."
            class="flex-1 border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-[#0B132B]">
        <button type="submit" class="bg-[#0B132B] text-white text-sm px-5 py-2 rounded-lg hover:bg-[#1a2a4a]">Filter</button>
        <a href="{{ route('activity-log.index') }}" class="text-sm text-gray-400 hover:text-gray-700 py-2 px-2">Clear</a>
    </div>
</form>
<div class="bg-white border border-gray-100 rounded-xl overflow-hidden overflow-x-auto">
    <table class="w-full text-sm min-w-[700px]">
        <thead class="border-b border-gray-100 bg-gray-50">
            <tr>
                <th class="text-left px-4 py-3 text-xs text-gray-400 font-medium uppercase tracking-wide">Time</th>
                <th class="text-left px-4 py-3 text-xs text-gray-400 font-medium uppercase tracking-wide">User</th>
                <th class="text-left px-4 py-3 text-xs text-gray-400 font-medium uppercase tracking-wide">Action</th>
                <th class="text-left px-4 py-3 text-xs text-gray-400 font-medium uppercase tracking-wide">Module</th>
                <th class="text-left px-4 py-3 text-xs text-gray-400 font-medium uppercase tracking-wide">Description</th>
                <th class="text-left px-4 py-3 text-xs text-gray-400 font-medium uppercase tracking-wide">IP Address</th>
                <th class="text-left px-4 py-3 text-xs text-gray-400 font-medium uppercase tracking-wide">Device</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            @forelse($logs as $log)
            @php
                $color = match($log->action) {
                    'login'    => 'bg-blue-50 text-blue-700',
                    'logout'   => 'bg-gray-100 text-gray-500',
                    'created'  => 'bg-green-50 text-green-700',
                    'updated'  => 'bg-amber-50 text-amber-700',
                    'deleted'  => 'bg-red-50 text-red-600',
                    'status'   => 'bg-purple-50 text-purple-700',
                    'exported' => 'bg-indigo-50 text-indigo-700',
                    default    => 'bg-gray-100 text-gray-600',
                };
                $ua = $log->user_agent ?? '';
                if (str_contains($ua, 'Chrome') && !str_contains($ua, 'Edge')) $browser = 'Chrome';
                elseif (str_contains($ua, 'Firefox')) $browser = 'Firefox';
                elseif (str_contains($ua, 'Safari') && !str_contains($ua, 'Chrome')) $browser = 'Safari';
                elseif (str_contains($ua, 'Edge')) $browser = 'Edge';
                else $browser = 'Unknown';
                $device = str_contains($ua, 'Mobile') ? 'Mobile' : 'Desktop';
            @endphp
            <tr class="hover:bg-gray-50 transition-colors">
                <td class="px-4 py-3 whitespace-nowrap">
                    <div class="text-xs text-gray-500">{{ $log->created_at->format('d M Y') }}</div>
                    <div class="text-xs font-semibold text-gray-800">{{ $log->created_at->format('H:i:s') }}</div>
                    <div class="text-[10px] text-gray-400 mt-0.5">{{ $log->created_at->diffForHumans() }}</div>
                </td>
                <td class="px-4 py-3">
                    @if($log->user)
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-full bg-[#0B132B] flex items-center justify-center text-[#C9A96E] text-[9px] font-bold flex-shrink-0">
                            {{ strtoupper(substr($log->user->name, 0, 2)) }}
                        </div>
                        <div>
                            <div class="text-xs font-semibold text-gray-800">{{ $log->user->name }}</div>
                            <div class="text-[10px] text-gray-400">{{ $log->user->email }}</div>
                        </div>
                    </div>
                    @else
                        <span class="text-xs text-gray-400 italic">System</span>
                    @endif
                </td>
                <td class="px-4 py-3">
                    <span class="inline-flex items-center text-xs px-2.5 py-0.5 rounded-full font-medium {{ $color }}">
                        {{ ucfirst($log->action) }}
                    </span>
                </td>
                <td class="px-4 py-3">
                    @if($log->module)
                        <span class="text-xs px-2 py-0.5 rounded-full bg-[#0B132B]/5 text-[#0B132B] font-medium">{{ $log->module }}</span>
                    @else
                        <span class="text-gray-300 text-xs">-</span>
                    @endif
                </td>
                <td class="px-4 py-3">
                    <p class="text-sm text-gray-700">{{ $log->description }}</p>
                    @if($log->entity_label && $log->module !== 'Auth')
                        <p class="text-[10px] text-gray-400 mt-0.5">ID #{{ $log->entity_id }} &middot; {{ $log->entity_label }}</p>
                    @endif
                </td>
                <td class="px-4 py-3">
                    <div class="font-mono text-xs text-gray-800 bg-gray-50 border border-gray-100 px-2 py-1 rounded-lg inline-block">
                        {{ $log->ip_address ?? '-' }}
                    </div>
                    @if($log->url)
                        <div class="text-[10px] text-gray-400 mt-1">{{ $log->method }} {{ parse_url($log->url, PHP_URL_PATH) }}</div>
                    @endif
                </td>
                <td class="px-4 py-3">
                    <div class="text-xs font-medium text-gray-700">{{ $device }}</div>
                    <div class="text-[10px] text-gray-400 mt-0.5">{{ $browser }}</div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="px-4 py-16 text-center text-gray-400">
                    <p class="text-3xl mb-2 font-display">0</p>
                    <p class="font-medium">No activity recorded yet</p>
                    <p class="text-xs mt-1">Events appear here after users log in and take actions.</p>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-5 flex items-center justify-between">
    <p class="text-xs text-gray-400">
        Showing {{ $logs->firstItem() ?? 0 }} to {{ $logs->lastItem() ?? 0 }} of {{ number_format($logs->total()) }} events
    </p>
    <div>{{ $logs->links() }}</div>
</div>
@endsection
