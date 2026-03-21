@extends('layouts.app')
@section('title','Projects')
@section('content')

<div class="flex items-center justify-between mb-6">
    <div>
        <h2 class="font-display text-xl font-bold">Projects</h2>
        <p class="text-sm text-gray-400">{{ $projects->total() }} projects total</p>
    </div>
    <a href="{{ route('projects.create') }}" class="bg-[#C9A96E] text-[#0B132B] font-semibold text-sm px-4 py-2 rounded-lg hover:bg-[#E8C882] transition-colors">+ New Project</a>
</div>

<form method="GET" class="bg-white border border-gray-100 rounded-xl p-4 mb-6 flex flex-wrap gap-3 items-end">
    <div>
        <label class="block text-xs text-gray-400 uppercase tracking-wide mb-1">Status</label>
        <select name="status" class="border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-[#0B132B]">
            <option value="">All</option>
            @foreach(['Planning','Active','Review','Completed','Cancelled'] as $s)
                <option value="{{ $s }}" {{ request('status')===$s ? 'selected' : '' }}>{{ $s }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-xs text-gray-400 uppercase tracking-wide mb-1">Brand</label>
        <select name="brand_id" class="border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-[#0B132B]">
            <option value="">All Brands</option>
            @foreach($brands as $brand)
                <option value="{{ $brand->id }}" {{ request('brand_id')==$brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
            @endforeach
        </select>
    </div>
    <button type="submit" class="bg-[#0B132B] text-white text-sm px-4 py-2 rounded-lg">Filter</button>
    <a href="{{ route('projects.index') }}" class="text-sm text-gray-400 hover:text-gray-600 py-2">Clear</a>
</form>

<div class="space-y-3">
    @forelse($projects as $project)
    @php
        $statusColor = ['Active'=>'bg-green-50 text-green-700','Planning'=>'bg-amber-50 text-amber-700','Review'=>'bg-blue-50 text-blue-700','Completed'=>'bg-gray-100 text-gray-600','Cancelled'=>'bg-red-50 text-red-600'][$project->status] ?? 'bg-gray-100 text-gray-600';
    @endphp
    <div class="bg-white border border-gray-100 rounded-xl p-5 hover:translate-x-1 transition-transform">
        <div class="flex items-start justify-between mb-3">
            <div>
                <a href="{{ route('projects.show', $project) }}" class="font-display font-semibold hover:text-[#C9A96E] transition-colors">{{ $project->title }}</a>
                <p class="text-sm text-gray-400 mt-0.5">
                    {{ $project->brand?->name }}
                    @if($project->category) · {{ $project->category }}@endif
                    @if($project->start_date) · {{ $project->start_date->format('d M') }} – {{ $project->end_date?->format('d M Y') }}@endif
                </p>
            </div>
            <div class="flex items-center gap-3">
                <span class="font-display font-semibold text-sm">AED {{ number_format($project->budget) }}</span>
                <span class="text-xs px-2.5 py-1 rounded-full font-medium {{ $statusColor }}">{{ $project->status }}</span>
                <a href="{{ route('projects.edit', $project) }}" class="text-xs border border-gray-200 px-2.5 py-1 rounded-lg hover:bg-gray-50">Edit</a>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <div class="flex-1 bg-gray-100 rounded-full h-1.5">
                <div class="h-1.5 rounded-full bg-[#C9A96E] transition-all" style="width:{{ $project->progress }}%"></div>
            </div>
            <span class="text-xs text-gray-400 min-w-8">{{ $project->progress }}%</span>
        </div>
    </div>
    @empty
        <div class="text-center py-16 text-gray-400 bg-white rounded-xl border border-gray-100">
            <p class="text-3xl mb-2">◈</p>
            <p>No projects yet. <a href="{{ route('projects.create') }}" class="text-[#C9A96E]">Create one</a></p>
        </div>
    @endforelse
</div>
<div class="mt-6">{{ $projects->links() }}</div>
@endsection
