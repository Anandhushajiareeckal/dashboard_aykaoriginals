@extends('layouts.app')
@section('title', $brand->name)
@section('content')

<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('brands.index') }}" class="text-gray-400 hover:text-gray-600">← Back</a>
    <h2 class="font-display text-xl font-bold">{{ $brand->name }}</h2>
    <div class="ml-auto">
        <a href="{{ route('brands.edit', $brand) }}" class="px-4 py-2 border border-gray-200 rounded-lg text-sm hover:bg-gray-50">Edit</a>
    </div>
</div>

<div class="grid grid-cols-3 gap-5">
    <div class="col-span-2 space-y-4">
        {{-- Projects --}}
        <div class="bg-white border border-gray-100 rounded-xl p-5">
            <h4 class="font-display font-semibold text-xs uppercase tracking-wide text-gray-400 mb-3">Projects ({{ $brand->projects->count() }})</h4>
            @forelse($brand->projects as $p)
            <div class="flex items-center justify-between py-2.5 border-b border-gray-50 last:border-0">
                <div>
                    <a href="{{ route('projects.show', $p) }}" class="font-medium text-sm hover:text-[#C9A96E]">{{ $p->title }}</a>
                    <p class="text-xs text-gray-400">{{ $p->category }} · {{ $p->start_date?->format('d M Y') }}</p>
                </div>
                <div class="flex items-center gap-3">
                    <span class="font-semibold text-sm">AED {{ number_format($p->budget) }}</span>
                    <span class="text-xs px-2 py-0.5 rounded-full {{ $p->status==='Active' ? 'bg-green-50 text-green-700' : 'bg-gray-100 text-gray-500' }}">{{ $p->status }}</span>
                </div>
            </div>
            @empty <p class="text-sm text-gray-400">No projects yet.</p>
            @endforelse
        </div>

        {{-- Follow-up log --}}
        <div class="bg-white border border-gray-100 rounded-xl p-5">
            <h4 class="font-display font-semibold text-xs uppercase tracking-wide text-gray-400 mb-3">Interaction History</h4>
            @forelse($brand->followups as $f)
            <div class="flex gap-3 py-3 border-b border-gray-50 last:border-0">
                <div class="w-1.5 h-1.5 rounded-full bg-[#C9A96E] flex-shrink-0 mt-2"></div>
                <div>
                    <p class="text-sm text-gray-700">{{ $f->note }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">{{ $f->created_at->format('d M Y') }} · {{ $f->user?->name }}</p>
                </div>
            </div>
            @empty <p class="text-sm text-gray-400 mb-3">No interactions logged yet.</p>
            @endforelse

            <form method="POST" action="{{ route('brands.followup', $brand) }}" class="mt-4 space-y-2">
                @csrf
                <textarea name="note" rows="2" required placeholder="Add a follow-up note…" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-[#0B132B]"></textarea>
                <div class="flex gap-2 items-center">
                    <input type="date" name="followup_date" class="border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-[#0B132B]">
                    <button type="submit" class="px-4 py-2 bg-[#0B132B] text-white text-sm rounded-lg hover:bg-[#1a2a4a]">Log Follow-up</button>
                </div>
            </form>
        </div>
    </div>

    <div class="space-y-4">
        <div class="bg-white border border-gray-100 rounded-xl p-5">
            <h4 class="font-display font-semibold text-xs uppercase tracking-wide text-gray-400 mb-3">Contact</h4>
            <dl class="space-y-2 text-sm">
                @if($brand->contact_name)<div><dt class="text-gray-400 text-xs">Contact</dt><dd class="font-medium">{{ $brand->contact_name }}</dd><dd class="text-xs text-gray-400">{{ $brand->contact_designation }}</dd></div>@endif
                @if($brand->email)<div><dt class="text-gray-400 text-xs mt-2">Email</dt><dd><a href="mailto:{{ $brand->email }}" class="text-[#C9A96E] hover:underline">{{ $brand->email }}</a></dd></div>@endif
                @if($brand->phone)<div><dt class="text-gray-400 text-xs mt-2">Phone</dt><dd>{{ $brand->phone }}</dd></div>@endif
                @if($brand->website)<div><dt class="text-gray-400 text-xs mt-2">Website</dt><dd><a href="{{ $brand->website }}" target="_blank" class="text-[#C9A96E] hover:underline text-xs">{{ $brand->website }}</a></dd></div>@endif
                @if($brand->location)<div><dt class="text-gray-400 text-xs mt-2">Location</dt><dd>{{ $brand->location }}</dd></div>@endif
                @if($brand->next_followup_date)<div><dt class="text-gray-400 text-xs mt-2">Next Follow-up</dt><dd class="{{ $brand->next_followup_date->isPast() ? 'text-red-600 font-medium' : '' }}">{{ $brand->next_followup_date->format('d M Y') }}</dd></div>@endif
            </dl>
        </div>
        @if($brand->description)
        <div class="bg-white border border-gray-100 rounded-xl p-5">
            <h4 class="font-display font-semibold text-xs uppercase tracking-wide text-gray-400 mb-2">About</h4>
            <p class="text-sm text-gray-600 leading-relaxed">{{ $brand->description }}</p>
        </div>
        @endif
    </div>
</div>
@endsection
