@extends('layouts.app')
@section('title','Search')
@section('content')

<div class="mb-6">
    <h2 class="font-display text-xl font-bold mb-4">Search Results</h2>
    <form method="GET" action="{{ route('search') }}" class="flex gap-3">
        <input name="q" value="{{ $q }}" autofocus
            class="flex-1 border border-gray-200 rounded-xl px-4 py-3 text-sm outline-none focus:border-[#0B132B] max-w-lg"
            placeholder="Search models, brands, projects…">
        <button type="submit" class="bg-[#0B132B] text-white px-5 py-3 rounded-xl text-sm font-medium hover:bg-[#1a2a4a] transition-colors">Search</button>
    </form>
</div>

@if(strlen($q) < 2)
    <div class="text-center py-20 text-gray-400">
        <p class="text-4xl mb-3">⌕</p>
        <p>Enter at least 2 characters to search.</p>
    </div>
@else
    @php $total = $models->count() + $brands->count() + $projects->count(); @endphp
    <p class="text-sm text-gray-400 mb-6">{{ $total }} result{{ $total !== 1 ? 's' : '' }} for "<strong class="text-gray-700">{{ $q }}</strong>"</p>

    @if($models->count())
    <div class="mb-8">
        <h3 class="font-display font-semibold text-xs uppercase tracking-widest text-gray-400 mb-3">Models ({{ $models->count() }})</h3>
        <div class="grid grid-cols-3 gap-3">
            @foreach($models as $model)
            <a href="{{ route('models.show', $model) }}" class="bg-white border border-gray-100 rounded-xl p-4 flex items-center gap-3 hover:border-[#C9A96E]/40 transition-colors">
                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-[#0B132B] to-[#2C3E6B] flex items-center justify-center text-[#C9A96E] text-sm font-bold flex-shrink-0">
                    {{ strtoupper(substr($model->name,0,2)) }}
                </div>
                <div>
                    <p class="font-medium text-sm">{{ $model->name }}</p>
                    <p class="text-xs text-gray-400">{{ $model->location }} · {{ $model->status }}</p>
                </div>
            </a>
            @endforeach
        </div>
    </div>
    @endif

    @if($brands->count())
    <div class="mb-8">
        <h3 class="font-display font-semibold text-xs uppercase tracking-widest text-gray-400 mb-3">Brands ({{ $brands->count() }})</h3>
        <div class="grid grid-cols-3 gap-3">
            @foreach($brands as $brand)
            <a href="{{ route('brands.show', $brand) }}" class="bg-white border border-gray-100 rounded-xl p-4 flex items-center gap-3 hover:border-[#C9A96E]/40 transition-colors">
                <div class="w-10 h-10 rounded-full bg-[#5E6472] flex items-center justify-center text-white text-sm font-bold flex-shrink-0">
                    {{ strtoupper(substr($brand->name,0,2)) }}
                </div>
                <div>
                    <p class="font-medium text-sm">{{ $brand->name }}</p>
                    <p class="text-xs text-gray-400">{{ $brand->location }} · {{ $brand->contact_name }}</p>
                </div>
            </a>
            @endforeach
        </div>
    </div>
    @endif

    @if($projects->count())
    <div class="mb-8">
        <h3 class="font-display font-semibold text-xs uppercase tracking-widest text-gray-400 mb-3">Projects ({{ $projects->count() }})</h3>
        <div class="space-y-2">
            @foreach($projects as $project)
            @php $sc = ['Active'=>'bg-green-50 text-green-700','Planning'=>'bg-amber-50 text-amber-700','Review'=>'bg-blue-50 text-blue-700','Completed'=>'bg-gray-100 text-gray-600'][$project->status] ?? 'bg-gray-100 text-gray-600'; @endphp
            <a href="{{ route('projects.show', $project) }}" class="bg-white border border-gray-100 rounded-xl p-4 flex items-center justify-between hover:border-[#C9A96E]/40 transition-colors block">
                <div>
                    <p class="font-medium text-sm">{{ $project->title }}</p>
                    <p class="text-xs text-gray-400">{{ $project->brand?->name }} · {{ $project->category }}</p>
                </div>
                <div class="flex items-center gap-3">
                    <span class="font-display font-semibold text-sm">AED {{ number_format($project->budget) }}</span>
                    <span class="text-xs px-2.5 py-1 rounded-full {{ $sc }}">{{ $project->status }}</span>
                </div>
            </a>
            @endforeach
        </div>
    </div>
    @endif

    @if($total === 0)
    <div class="text-center py-20 text-gray-400">
        <p class="text-4xl mb-3">◈</p>
        <p>No results found for "<strong>{{ $q }}</strong>"</p>
        <p class="text-sm mt-1">Try a different name, location, or brand.</p>
    </div>
    @endif
@endif
@endsection
