@extends('layouts.app')
@section('title','Meetings')
@section('content')

<div class="flex items-center justify-between mb-6">
    <div>
        <h2 class="font-display text-xl font-bold">Meetings & Schedule</h2>
        <p class="text-sm text-gray-400">{{ now()->format('F Y') }}</p>
    </div>
    <a href="{{ route('meetings.create') }}" class="bg-[#C9A96E] text-[#0B132B] font-semibold text-sm px-4 py-2 rounded-lg hover:bg-[#E8C882] transition-colors">+ Schedule Meeting</a>
</div>

<div class="grid grid-cols-5 gap-5">

    {{-- Calendar --}}
    <div class="col-span-2 bg-white border border-gray-100 rounded-xl p-5">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-display font-semibold text-sm">{{ now()->format('F Y') }}</h3>
        </div>
        {{-- Day headers --}}
        <div class="grid grid-cols-7 mb-1">
            @foreach(['Su','Mo','Tu','We','Th','Fr','Sa'] as $d)
            <div class="text-center text-xs text-gray-400 font-medium py-1">{{ $d }}</div>
            @endforeach
        </div>
        {{-- Calendar days --}}
        @php
            $firstDay = now()->startOfMonth()->dayOfWeek;
            $daysInMonth = now()->daysInMonth;
            $today = now()->day;
            $meetingDays = $meetings->pluck('meeting_at')->map(fn($d) => $d->day)->unique()->toArray();
        @endphp
        <div class="grid grid-cols-7 gap-0.5">
            @for($i = 0; $i < $firstDay; $i++)
                <div></div>
            @endfor
            @for($d = 1; $d <= $daysInMonth; $d++)
            @php
                $isToday = $d === $today;
                $hasEvent = in_array($d, $meetingDays);
            @endphp
            <div class="aspect-square flex items-center justify-center rounded-lg text-xs cursor-pointer transition-colors relative
                {{ $isToday ? 'bg-[#0B132B] text-white font-bold' : ($hasEvent ? 'font-semibold text-[#0B132B] hover:bg-gray-50' : 'text-gray-500 hover:bg-gray-50') }}">
                {{ $d }}
                @if($hasEvent && !$isToday)
                    <span class="absolute bottom-1 left-1/2 -translate-x-1/2 w-1 h-1 rounded-full bg-[#C9A96E]"></span>
                @endif
            </div>
            @endfor
        </div>

        {{-- Upcoming count --}}
        <div class="mt-4 pt-4 border-t border-gray-100">
            <p class="text-xs text-gray-400">
                <span class="font-semibold text-[#0B132B]">{{ $upcoming->count() }}</span> upcoming this month
            </p>
        </div>
    </div>

    {{-- Meetings list --}}
    <div class="col-span-3 space-y-3">
        <h3 class="font-display font-semibold text-sm text-gray-500 uppercase tracking-wide">Upcoming</h3>
        @forelse($upcoming as $mtg)
        <div class="bg-white border border-gray-100 rounded-xl p-4 hover:border-[#C9A96E]/30 transition-colors">
            <div class="flex items-start justify-between">
                <div>
                    <p class="font-semibold text-sm">{{ $mtg->title }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">{{ $mtg->brand?->name }}</p>
                </div>
                <span class="text-xs px-2.5 py-1 rounded-full font-medium {{ $mtg->mode === 'Online' ? 'bg-blue-50 text-blue-700' : 'bg-amber-50 text-amber-700' }}">
                    {{ $mtg->mode }}
                </span>
            </div>
            <div class="flex gap-4 mt-3 text-xs text-gray-400">
                <span>📅 {{ $mtg->meeting_at->format('D, d M Y') }}</span>
                <span>🕐 {{ $mtg->meeting_at->format('h:i A') }}</span>
                <span>⏱ {{ $mtg->duration_minutes }} min</span>
                @if($mtg->employee)<span>👤 {{ $mtg->employee->name }}</span>@endif
            </div>
            @if($mtg->notes)
            <p class="text-xs text-gray-500 mt-2 bg-gray-50 rounded-lg px-3 py-2">{{ $mtg->notes }}</p>
            @endif
        </div>
        @empty
        <div class="bg-white border border-gray-100 rounded-xl p-8 text-center text-gray-400">
            <p class="text-2xl mb-2">◻</p>
            <p>No upcoming meetings.<br><a href="{{ route('meetings.create') }}" class="text-[#C9A96E]">Schedule one</a></p>
        </div>
        @endforelse

        @if($meetings->count() > $upcoming->count())
        <div class="pt-2">
            <h3 class="font-display font-semibold text-sm text-gray-500 uppercase tracking-wide mb-3">All Meetings</h3>
            @foreach($meetings as $mtg)
            @if($mtg->meeting_at->lt(now()))
            <div class="bg-white border border-gray-50 rounded-xl p-4 mb-2 opacity-60">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="font-medium text-sm">{{ $mtg->title }}</p>
                        <p class="text-xs text-gray-400">{{ $mtg->meeting_at->format('D, d M Y · h:i A') }} · {{ $mtg->brand?->name }}</p>
                    </div>
                    <span class="text-xs text-gray-400">Completed</span>
                </div>
            </div>
            @endif
            @endforeach
        </div>
        @endif
    </div>
</div>
@endsection
