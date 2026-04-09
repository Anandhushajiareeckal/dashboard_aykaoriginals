@extends('layouts.app')
@section('title', $project->title)
@section('content')

<div class="flex flex-wrap items-center gap-3 mb-6">
    <a href="{{ route('projects.index') }}" class="text-gray-400 hover:text-gray-600 flex-shrink-0">← Back</a>
    <h2 class="font-display text-xl font-bold min-w-0">{{ $project->title }}</h2>
    @php $statusColor = ['Active'=>'bg-green-50 text-green-700','Planning'=>'bg-amber-50 text-amber-700','Review'=>'bg-blue-50 text-blue-700','Completed'=>'bg-gray-100 text-gray-600','Cancelled'=>'bg-red-50 text-red-600'][$project->status] ?? 'bg-gray-100 text-gray-600'; @endphp
    <span class="text-xs px-2.5 py-1 rounded-full font-medium {{ $statusColor }} flex-shrink-0">{{ $project->status }}</span>
    <div class="ml-auto flex gap-2 flex-shrink-0">
        <a href="{{ route('projects.edit', $project) }}" class="px-4 py-2 border border-gray-200 rounded-lg text-sm hover:bg-gray-50">Edit</a>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
    <div class="lg:col-span-2 space-y-4">
        <div class="bg-white border border-gray-100 rounded-xl p-5">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-4">
                <div class="bg-gray-50 rounded-lg p-3 text-center">
                    <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">Budget</p>
                    <p class="font-display font-bold">AED {{ number_format($project->budget) }}</p>
                </div>
                <div class="bg-gray-50 rounded-lg p-3 text-center">
                    <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">Timeline</p>
                    <p class="text-sm font-medium">{{ $project->start_date?->format('d M') }} – {{ $project->end_date?->format('d M Y') }}</p>
                </div>
                <div class="bg-gray-50 rounded-lg p-3 text-center">
                    <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">Progress</p>
                    <p class="font-display font-bold">{{ $project->progress }}%</p>
                </div>
            </div>
            <div class="w-full bg-gray-100 rounded-full h-2 mb-4">
                <div class="h-2 rounded-full bg-[#C9A96E]" style="width:{{ $project->progress }}%"></div>
            </div>
            @if($project->notes)
            <p class="text-sm text-gray-600 leading-relaxed">{{ $project->notes }}</p>
            @endif
        </div>

        {{-- Models --}}
        <div class="bg-white border border-gray-100 rounded-xl p-5">
            <h4 class="font-display font-semibold text-xs uppercase tracking-wide text-gray-400 mb-3">Models ({{ $project->models->count() }})</h4>
            @if($project->models->count())
            <div class="flex flex-wrap gap-2">
                @foreach($project->models as $m)
                <a href="{{ route('models.show', $m) }}" class="flex items-center gap-2 bg-gray-50 border border-gray-100 rounded-lg px-3 py-1.5 hover:border-[#C9A96E] transition-colors">
                    <div class="w-6 h-6 rounded-full bg-[#0B132B] flex items-center justify-center text-[#C9A96E] text-[9px] font-bold">{{ strtoupper(substr($m->name,0,2)) }}</div>
                    <span class="text-sm">{{ $m->name }}</span>
                </a>
                @endforeach
            </div>
            @else <p class="text-sm text-gray-400">No models assigned.</p> @endif
        </div>

        {{-- Crew --}}
        <div class="bg-white border border-gray-100 rounded-xl p-5">
            <h4 class="font-display font-semibold text-xs uppercase tracking-wide text-gray-400 mb-3">Crew ({{ $project->crew->count() }})</h4>
            @if($project->crew->count())
            <div class="flex flex-wrap gap-2">
                @foreach($project->crew as $c)
                <div class="flex items-center gap-2 bg-gray-50 border border-gray-100 rounded-lg px-3 py-1.5">
                    <div class="w-6 h-6 rounded-full bg-[#5E6472] flex items-center justify-center text-white text-[9px] font-bold">{{ strtoupper(substr($c->name,0,2)) }}</div>
                    <span class="text-sm">{{ $c->name }}</span>
                    <span class="text-xs text-gray-400">{{ $c->role }}</span>
                </div>
                @endforeach
            </div>
            @else <p class="text-sm text-gray-400">No crew assigned.</p> @endif
        </div>
    </div>

    <div class="space-y-4">
        <div class="bg-white border border-gray-100 rounded-xl p-5">
            <h4 class="font-display font-semibold text-xs uppercase tracking-wide text-gray-400 mb-3">Details</h4>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-gray-400">Client</dt><dd class="font-medium">{{ $project->brand?->name ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-400">Category</dt><dd>{{ $project->category ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-400">Status</dt><dd><span class="text-xs px-2 py-0.5 rounded-full {{ $statusColor }}">{{ $project->status }}</span></dd></div>
            </dl>
        </div>

        {{-- Invoices --}}
        <div class="bg-white border border-gray-100 rounded-xl p-5">
            <div class="flex items-center justify-between mb-3">
                <h4 class="font-display font-semibold text-xs uppercase tracking-wide text-gray-400">Invoices</h4>
                <a href="{{ route('invoices.create', ['project_id'=>$project->id]) }}" class="text-xs text-[#C9A96E]">+ New</a>
            </div>
            @forelse($project->invoices as $inv)
            <div class="flex items-center justify-between py-2 border-b border-gray-50 last:border-0">
                <div>
                    <p class="text-xs font-semibold">{{ $inv->invoice_number }}</p>
                    <p class="text-xs text-gray-400">Due {{ $inv->due_date?->format('d M Y') }}</p>
                </div>
                <div class="text-right">
                    <p class="text-xs font-semibold">AED {{ number_format($inv->total) }}</p>
                    <span class="text-[10px] px-1.5 py-0.5 rounded-full {{ $inv->status==='Paid' ? 'bg-green-50 text-green-700' : ($inv->status==='Overdue' ? 'bg-red-50 text-red-600' : 'bg-amber-50 text-amber-700') }}">{{ $inv->status }}</span>
                </div>
            </div>
            @empty <p class="text-sm text-gray-400">No invoices yet.</p>
            @endforelse
        </div>

        {{-- Meetings --}}
        <div class="bg-white border border-gray-100 rounded-xl p-5">
            <h4 class="font-display font-semibold text-xs uppercase tracking-wide text-gray-400 mb-3">Meetings</h4>
            @forelse($project->meetings as $mtg)
            <div class="py-2 border-b border-gray-50 last:border-0">
                <p class="text-sm font-medium">{{ $mtg->title }}</p>
                <p class="text-xs text-gray-400">{{ $mtg->meeting_at->format('d M Y, H:i') }} · {{ $mtg->mode }}</p>
            </div>
            @empty <p class="text-sm text-gray-400">No meetings yet.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
