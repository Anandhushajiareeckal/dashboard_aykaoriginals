@extends('layouts.app')
@section('title','New Project')
@section('content')

<div class="max-w-3xl">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('projects.index') }}" class="text-gray-400 hover:text-gray-600">← Back</a>
        <h2 class="font-display text-xl font-bold">New Project</h2>
    </div>

    <form method="POST" action="{{ route('projects.store') }}" class="space-y-5">
        @csrf
        <div class="bg-white border border-gray-100 rounded-xl p-6 space-y-4">
            <h3 class="font-display font-semibold text-sm uppercase tracking-wide text-gray-400">Project Details</h3>
            <div>
                <label class="block text-xs text-gray-500 uppercase tracking-wide mb-1.5">Title *</label>
                <input name="title" value="{{ old('title') }}" required class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm outline-none focus:border-[#0B132B]" placeholder="e.g. Summer Campaign 2025">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs text-gray-500 uppercase tracking-wide mb-1.5">Brand / Client</label>
                    <select name="brand_id" class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm outline-none focus:border-[#0B132B]">
                        <option value="">Select brand…</option>
                        @foreach($brands as $brand)
                            <option value="{{ $brand->id }}" {{ old('brand_id')==$brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 uppercase tracking-wide mb-1.5">Category</label>
                    <select name="category" class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm outline-none focus:border-[#0B132B]">
                        <option value="">Select…</option>
                        @foreach(['Fashion','Commercial','Editorial','Runway','Video','Social','Event'] as $c)
                            <option value="{{ $c }}" {{ old('category')===$c ? 'selected' : '' }}>{{ $c }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs text-gray-500 uppercase tracking-wide mb-1.5">Budget (AED)</label>
                    <input name="budget" type="number" value="{{ old('budget') }}" class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm outline-none focus:border-[#0B132B]">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 uppercase tracking-wide mb-1.5">Start Date</label>
                    <input name="start_date" type="date" value="{{ old('start_date') }}" class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm outline-none focus:border-[#0B132B]">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 uppercase tracking-wide mb-1.5">End Date</label>
                    <input name="end_date" type="date" value="{{ old('end_date') }}" class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm outline-none focus:border-[#0B132B]">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs text-gray-500 uppercase tracking-wide mb-1.5">Status</label>
                    <select name="status" class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm outline-none focus:border-[#0B132B]">
                        @foreach(['Planning','Active','Review','Completed','Cancelled'] as $s)
                            <option value="{{ $s }}" {{ old('status','Planning')===$s ? 'selected' : '' }}>{{ $s }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div>
                <label class="block text-xs text-gray-500 uppercase tracking-wide mb-1.5">Notes</label>
                <textarea name="notes" rows="3" class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm outline-none focus:border-[#0B132B]">{{ old('notes') }}</textarea>
            </div>
        </div>

        <div class="bg-white border border-gray-100 rounded-xl p-6 space-y-3">
            <h3 class="font-display font-semibold text-sm uppercase tracking-wide text-gray-400">Assign Models</h3>
            <div class="grid grid-cols-2 gap-2 max-h-48 overflow-y-auto">
                @foreach($models as $m)
                <label class="flex items-center gap-2 cursor-pointer p-2 rounded-lg hover:bg-gray-50">
                    <input type="checkbox" name="model_ids[]" value="{{ $m->id }}" {{ in_array($m->id, old('model_ids',[])) ? 'checked' : '' }} class="rounded">
                    <span class="text-sm">{{ $m->name }}</span>
                    <span class="text-xs text-gray-400">{{ $m->location }}</span>
                </label>
                @endforeach
            </div>
        </div>

        <div class="bg-white border border-gray-100 rounded-xl p-6 space-y-3">
            <h3 class="font-display font-semibold text-sm uppercase tracking-wide text-gray-400">Assign Crew</h3>
            <div class="grid grid-cols-2 gap-2 max-h-48 overflow-y-auto">
                @foreach($crew as $c)
                <label class="flex items-center gap-2 cursor-pointer p-2 rounded-lg hover:bg-gray-50">
                    <input type="checkbox" name="crew_ids[]" value="{{ $c->id }}" {{ in_array($c->id, old('crew_ids',[])) ? 'checked' : '' }} class="rounded">
                    <span class="text-sm">{{ $c->name }}</span>
                    <span class="text-xs text-gray-400">{{ $c->role }}</span>
                </label>
                @endforeach
            </div>
        </div>

        <div class="flex gap-3 justify-end">
            <a href="{{ route('projects.index') }}" class="px-5 py-2.5 border border-gray-200 rounded-lg text-sm hover:bg-gray-50">Cancel</a>
            <button type="submit" class="px-5 py-2.5 bg-[#C9A96E] text-[#0B132B] font-semibold text-sm rounded-lg hover:bg-[#E8C882]">Create Project</button>
        </div>
    </form>
</div>
@endsection
