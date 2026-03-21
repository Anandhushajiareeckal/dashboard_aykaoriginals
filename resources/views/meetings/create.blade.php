@extends('layouts.app')
@section('title','Schedule Meeting')
@section('content')

<div class="max-w-2xl">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('meetings.index') }}" class="text-gray-400 hover:text-gray-600">← Back</a>
        <h2 class="font-display text-xl font-bold">Schedule Meeting</h2>
    </div>

    <form method="POST" action="{{ route('meetings.store') }}" class="space-y-5">
        @csrf

        <div class="bg-white border border-gray-100 rounded-xl p-6 space-y-4">
            <div>
                <label class="block text-xs text-gray-500 uppercase tracking-wide mb-1.5">Title *</label>
                <input name="title" value="{{ old('title') }}" required
                    class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm outline-none focus:border-[#0B132B]"
                    placeholder="e.g. Brand deck review">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs text-gray-500 uppercase tracking-wide mb-1.5">Client / Brand</label>
                    <select name="brand_id" class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm outline-none focus:border-[#0B132B]">
                        <option value="">Select brand…</option>
                        @foreach($brands as $brand)
                            <option value="{{ $brand->id }}" {{ old('brand_id')==$brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 uppercase tracking-wide mb-1.5">Project</label>
                    <select name="project_id" class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm outline-none focus:border-[#0B132B]">
                        <option value="">Select project…</option>
                        @foreach($projects as $project)
                            <option value="{{ $project->id }}" {{ old('project_id')==$project->id ? 'selected' : '' }}>{{ $project->title }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-3 gap-4">
                <div class="col-span-2">
                    <label class="block text-xs text-gray-500 uppercase tracking-wide mb-1.5">Date & Time *</label>
                    <input name="meeting_at" type="datetime-local" value="{{ old('meeting_at') }}" required
                        class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm outline-none focus:border-[#0B132B]">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 uppercase tracking-wide mb-1.5">Duration (min)</label>
                    <input name="duration_minutes" type="number" value="{{ old('duration_minutes', 60) }}"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm outline-none focus:border-[#0B132B]">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs text-gray-500 uppercase tracking-wide mb-1.5">Mode</label>
                    <select name="mode" class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm outline-none focus:border-[#0B132B]">
                        @foreach(['Online','In-person','Hybrid'] as $m)
                            <option value="{{ $m }}" {{ old('mode')===$m ? 'selected' : '' }}>{{ $m }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 uppercase tracking-wide mb-1.5">Assigned To</label>
                    <select name="employee_id" class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm outline-none focus:border-[#0B132B]">
                        <option value="">Select employee…</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}" {{ old('employee_id')==$emp->id ? 'selected' : '' }}>{{ $emp->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs text-gray-500 uppercase tracking-wide mb-1.5">Notes</label>
                <textarea name="notes" rows="3"
                    class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm outline-none focus:border-[#0B132B]"
                    placeholder="Agenda, location, dial-in link…">{{ old('notes') }}</textarea>
            </div>
        </div>

        <div class="flex gap-3 justify-end">
            <a href="{{ route('meetings.index') }}" class="px-5 py-2.5 border border-gray-200 rounded-lg text-sm hover:bg-gray-50 transition-colors">Cancel</a>
            <button type="submit" class="px-5 py-2.5 bg-[#C9A96E] text-[#0B132B] font-semibold text-sm rounded-lg hover:bg-[#E8C882] transition-colors">Schedule</button>
        </div>
    </form>
</div>
@endsection
