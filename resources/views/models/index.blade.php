@extends('layouts.app')
@section('title','Models')
@section('content')

<div class="flex items-center justify-between mb-6">
    <div>
        <h2 class="font-display text-xl font-bold">Model Roster</h2>
        <p class="text-sm text-gray-400">{{ $models->total() }} models total</p>
    </div>
    <a href="{{ route('models.create') }}" class="bg-[#C9A96E] text-[#0B132B] font-semibold text-sm px-4 py-2 rounded-lg hover:bg-[#E8C882] transition-colors">+ Add Model</a>
</div>

<form method="GET" class="bg-white border border-gray-100 rounded-xl p-4 mb-6">
    <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-4 gap-3 mb-3">
        <div>
            <label class="block text-xs text-gray-400 uppercase tracking-wide mb-1">Name</label>
            <input name="name" value="{{ request('name') }}" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-[#0B132B]" placeholder="Search name...">
        </div>
        <div>
            <label class="block text-xs text-gray-400 uppercase tracking-wide mb-1">Location</label>
            <input name="location" value="{{ request('location') }}" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-[#0B132B]" placeholder="City...">
        </div>
        <div>
            <label class="block text-xs text-gray-400 uppercase tracking-wide mb-1">Status</label>
            <select name="status" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-[#0B132B]">
                <option value="">All</option>
                @foreach(['Active','Inactive','On Leave','Unavailable'] as $s)
                    <option value="{{ $s }}" {{ request('status')===$s ? 'selected' : '' }}>{{ $s }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs text-gray-400 uppercase tracking-wide mb-1">Category</label>
            <select name="category" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-[#0B132B]">
                <option value="">All</option>
                @foreach(['Fashion','Commercial','Runway','Editorial','Fitness','Beauty'] as $c)
                    <option value="{{ $c }}" {{ request('category')===$c ? 'selected' : '' }}>{{ $c }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 items-end">
        <div>
            <label class="block text-xs text-gray-400 uppercase tracking-wide mb-1">Height Min (cm)</label>
            <input name="height_min" type="number" value="{{ request('height_min') }}" min="140" max="200"
                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-[#0B132B]" placeholder="e.g. 165">
        </div>
        <div>
            <label class="block text-xs text-gray-400 uppercase tracking-wide mb-1">Height Max (cm)</label>
            <input name="height_max" type="number" value="{{ request('height_max') }}" min="140" max="200"
                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-[#0B132B]" placeholder="e.g. 185">
        </div>
        <div class="flex items-center gap-2 pb-1">
            <input type="checkbox" name="inhouse" id="inhouse" value="1" {{ request('inhouse') ? 'checked' : '' }} class="rounded">
            <label for="inhouse" class="text-sm text-gray-600">In-house only</label>
        </div>
        <div class="flex gap-2">
            <button type="submit" class="flex-1 bg-[#0B132B] text-white text-sm px-4 py-2 rounded-lg hover:bg-[#1a2a4a] transition-colors">Filter</button>
            <a href="{{ route('models.index') }}" class="px-4 py-2 border border-gray-200 rounded-lg text-sm text-gray-400 hover:text-gray-600 hover:bg-gray-50 transition-colors">Clear</a>
        </div>
    </div>

    {{-- Active filter tags --}}
    @if(request()->hasAny(['name','location','status','category','height_min','height_max','inhouse']))
    <div class="flex flex-wrap gap-2 mt-3 pt-3 border-t border-gray-100">
        <span class="text-xs text-gray-400">Active filters:</span>
        @if(request('name'))      <span class="text-xs bg-[#0B132B]/8 text-[#0B132B] px-2 py-0.5 rounded-full">Name: {{ request('name') }}</span> @endif
        @if(request('location'))  <span class="text-xs bg-[#0B132B]/8 text-[#0B132B] px-2 py-0.5 rounded-full">Location: {{ request('location') }}</span> @endif
        @if(request('status'))    <span class="text-xs bg-[#0B132B]/8 text-[#0B132B] px-2 py-0.5 rounded-full">Status: {{ request('status') }}</span> @endif
        @if(request('category'))  <span class="text-xs bg-[#0B132B]/8 text-[#0B132B] px-2 py-0.5 rounded-full">Category: {{ request('category') }}</span> @endif
        @if(request('height_min')) <span class="text-xs bg-[#0B132B]/8 text-[#0B132B] px-2 py-0.5 rounded-full">Height >= {{ request('height_min') }}cm</span> @endif
        @if(request('height_max')) <span class="text-xs bg-[#0B132B]/8 text-[#0B132B] px-2 py-0.5 rounded-full">Height <= {{ request('height_max') }}cm</span> @endif
        @if(request('inhouse'))   <span class="text-xs bg-[#0B132B]/8 text-[#0B132B] px-2 py-0.5 rounded-full">In-house only</span> @endif
    </div>
    @endif
</form>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
    @forelse($models as $model)
    <div class="bg-white border border-gray-100 rounded-xl overflow-hidden hover:-translate-y-1 transition-transform">
        <div class="h-36 bg-gradient-to-br from-[#0B132B] to-[#2C3E6B] flex items-center justify-center overflow-hidden">
            @if($model->getFirstMediaUrl('portfolio'))
                <img src="{{ $model->getFirstMediaUrl('portfolio') }}" class="w-full h-full object-cover">
            @else
                <span class="font-display text-3xl font-bold text-[#C9A96E]/40">{{ strtoupper(substr($model->name,0,2)) }}</span>
            @endif
        </div>
        <div class="p-4">
            <h3 class="font-display font-semibold text-sm mb-1">{{ $model->name }}</h3>

            {{-- Measurements row --}}
            @if($model->height || $model->bust || $model->waist || $model->hips)
            <div class="flex gap-2 mb-2 flex-wrap">
                @if($model->height)<span class="text-[10px] bg-gray-50 border border-gray-100 px-1.5 py-0.5 rounded text-gray-600 font-mono">H {{ $model->height }}</span>@endif
                @if($model->bust)  <span class="text-[10px] bg-gray-50 border border-gray-100 px-1.5 py-0.5 rounded text-gray-600 font-mono">B {{ $model->bust }}</span>@endif
                @if($model->waist) <span class="text-[10px] bg-gray-50 border border-gray-100 px-1.5 py-0.5 rounded text-gray-600 font-mono">W {{ $model->waist }}</span>@endif
                @if($model->hips)  <span class="text-[10px] bg-gray-50 border border-gray-100 px-1.5 py-0.5 rounded text-gray-600 font-mono">Hip {{ $model->hips }}</span>@endif
            </div>
            @endif

            <div class="flex gap-2 text-xs text-gray-400 mb-2 flex-wrap">
                @if($model->age)      <span>{{ $model->age }} yrs</span> @endif
                @if($model->location) <span>{{ $model->location }}</span> @endif
            </div>
            <div class="flex flex-wrap gap-1 mb-3">
                @foreach((array)$model->categories as $cat)
                    <span class="text-[10px] px-2 py-0.5 rounded-full bg-[#0B132B]/8 text-[#0B132B] font-medium">{{ $cat }}</span>
                @endforeach
                @if($model->is_inhouse)
                    <span class="text-[10px] px-2 py-0.5 rounded-full bg-green-50 text-green-700 font-medium">In-house</span>
                @endif
            </div>
            <div class="flex items-center justify-between">
                <span class="text-xs px-2 py-0.5 rounded-full font-medium {{ $model->status === 'Active' ? 'bg-green-50 text-green-700' : 'bg-gray-100 text-gray-500' }}">{{ $model->status }}</span>
                <a href="{{ route('models.show', $model) }}" class="text-xs text-[#0B132B] border border-gray-200 px-2.5 py-1 rounded-lg hover:bg-gray-50 transition-colors">View</a>
            </div>
        </div>
    </div>
    @empty
        <div class="col-span-4 text-center py-16 text-gray-400">
            <p class="text-3xl mb-2">?</p>
            <p>No models found. <a href="{{ route('models.create') }}" class="text-[#C9A96E]">Add one</a></p>
        </div>
    @endforelse
</div>
<div class="mt-6">{{ $models->links() }}</div>
@endsection
