@extends('layouts.app')
@section('title','Edit Project')
@section('content')

<div class="max-w-3xl">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('projects.show', $project) }}" class="text-gray-400 hover:text-gray-600">← Back</a>
        <h2 class="font-display text-xl font-bold">Edit: {{ $project->title }}</h2>
    </div>

    <form method="POST" action="{{ route('projects.update', $project) }}" class="space-y-5">
        @csrf @method('PUT')

        <div class="bg-white border border-gray-100 rounded-xl p-6 space-y-4">
            <div>
                <label class="block text-xs text-gray-500 uppercase tracking-wide mb-1.5">Title *</label>
                <input name="title" value="{{ old('title', $project->title) }}" required class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm outline-none focus:border-[#0B132B]">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs text-gray-500 uppercase tracking-wide mb-1.5">Brand</label>
                    <select name="brand_id" class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm outline-none focus:border-[#0B132B]">
                        <option value="">None</option>
                        @foreach($brands as $brand)
                            <option value="{{ $brand->id }}" {{ old('brand_id',$project->brand_id)==$brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 uppercase tracking-wide mb-1.5">Category</label>
                    <select name="category" class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm outline-none focus:border-[#0B132B]">
                        @foreach(['Fashion','Commercial','Editorial','Runway','Video','Social','Event'] as $c)
                            <option value="{{ $c }}" {{ old('category',$project->category)===$c ? 'selected' : '' }}>{{ $c }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs text-gray-500 uppercase tracking-wide mb-1.5">Budget (AED)</label>
                    <input name="budget" type="number" value="{{ old('budget', $project->budget) }}" class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm outline-none focus:border-[#0B132B]">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 uppercase tracking-wide mb-1.5">Start Date</label>
                    <input name="start_date" type="date" value="{{ old('start_date', $project->start_date?->format('Y-m-d')) }}" class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm outline-none focus:border-[#0B132B]">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 uppercase tracking-wide mb-1.5">End Date</label>
                    <input name="end_date" type="date" value="{{ old('end_date', $project->end_date?->format('Y-m-d')) }}" class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm outline-none focus:border-[#0B132B]">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs text-gray-500 uppercase tracking-wide mb-1.5">Status</label>
                    <select name="status" class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm outline-none focus:border-[#0B132B]">
                        @foreach(['Planning','Active','Review','Completed','Cancelled'] as $s)
                            <option value="{{ $s }}" {{ old('status',$project->status)===$s ? 'selected' : '' }}>{{ $s }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 uppercase tracking-wide mb-1.5">Progress (%)</label>
                    <input name="progress" type="number" min="0" max="100" value="{{ old('progress', $project->progress) }}" class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm outline-none focus:border-[#0B132B]">
                </div>
            </div>
            <div>
                <label class="block text-xs text-gray-500 uppercase tracking-wide mb-1.5">Notes</label>
                <textarea name="notes" rows="3" class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm outline-none focus:border-[#0B132B]">{{ old('notes', $project->notes) }}</textarea>
            </div>
        </div>

        <div class="bg-white border border-gray-100 rounded-xl p-6 space-y-3">
            <h3 class="font-display font-semibold text-sm uppercase tracking-wide text-gray-400">Models</h3>
            <div class="grid grid-cols-2 gap-2 max-h-40 overflow-y-auto">
                @foreach($models as $m)
                <label class="flex items-center gap-2 cursor-pointer p-2 rounded-lg hover:bg-gray-50">
                    <input type="checkbox" name="model_ids[]" value="{{ $m->id }}"
                        {{ $project->models->contains($m->id) ? 'checked' : '' }} class="rounded">
                    <span class="text-sm">{{ $m->name }}</span>
                </label>
                @endforeach
            </div>
        </div>

        <div class="bg-white border border-gray-100 rounded-xl p-6 space-y-3">
            <h3 class="font-display font-semibold text-sm uppercase tracking-wide text-gray-400">Crew</h3>
            <div class="grid grid-cols-2 gap-2 max-h-40 overflow-y-auto">
                @foreach($crew as $c)
                <label class="flex items-center gap-2 cursor-pointer p-2 rounded-lg hover:bg-gray-50">
                    <input type="checkbox" name="crew_ids[]" value="{{ $c->id }}"
                        {{ $project->crew->contains($c->id) ? 'checked' : '' }} class="rounded">
                    <span class="text-sm">{{ $c->name }}</span>
                    <span class="text-xs text-gray-400">{{ $c->role }}</span>
                </label>
                @endforeach
            </div>
        </div>

        <div class="flex gap-3 justify-end">
            <a href="{{ route('projects.show', $project) }}" class="px-5 py-2.5 border border-gray-200 rounded-lg text-sm hover:bg-gray-50">Cancel</a>
            <button type="submit" class="px-5 py-2.5 bg-[#C9A96E] text-[#0B132B] font-semibold text-sm rounded-lg hover:bg-[#E8C882]">Update Project</button>
        </div>
    </form>
</div>
@endsection
