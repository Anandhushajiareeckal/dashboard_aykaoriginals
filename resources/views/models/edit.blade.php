@extends('layouts.app')
@section('title','Edit Model')
@section('content')

<div class="max-w-3xl">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('models.show', $model) }}" class="text-gray-400 hover:text-gray-600">← Back</a>
        <h2 class="font-display text-xl font-bold">Edit: {{ $model->name }}</h2>
    </div>

    <form method="POST" action="{{ route('models.update', $model) }}" enctype="multipart/form-data" class="space-y-5">
        @csrf @method('PUT')

        <div class="bg-white border border-gray-100 rounded-xl p-6 space-y-4">
            <h3 class="font-display font-semibold text-sm uppercase tracking-wide text-gray-400">Basic Info</h3>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs text-gray-500 uppercase tracking-wide mb-1.5">Full Name *</label>
                    <input name="name" value="{{ old('name', $model->name) }}" required class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm outline-none focus:border-[#0B132B]">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 uppercase tracking-wide mb-1.5">Age</label>
                    <input name="age" type="number" value="{{ old('age', $model->age) }}" class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm outline-none focus:border-[#0B132B]">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs text-gray-500 uppercase tracking-wide mb-1.5">Email</label>
                    <input name="email" type="email" value="{{ old('email', $model->email) }}" class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm outline-none focus:border-[#0B132B]">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 uppercase tracking-wide mb-1.5">Phone</label>
                    <input name="phone" value="{{ old('phone', $model->phone) }}" class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm outline-none focus:border-[#0B132B]">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs text-gray-500 uppercase tracking-wide mb-1.5">Location</label>
                    <input name="location" value="{{ old('location', $model->location) }}" class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm outline-none focus:border-[#0B132B]">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 uppercase tracking-wide mb-1.5">Status</label>
                    <select name="status" class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm outline-none focus:border-[#0B132B]">
                        @foreach(['Active','Inactive','On Leave','Unavailable'] as $s)
                            <option value="{{ $s }}" {{ old('status',$model->status)===$s ? 'selected' : '' }}>{{ $s }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div>
                <label class="block text-xs text-gray-500 uppercase tracking-wide mb-1.5">About</label>
                <textarea name="about" rows="3" class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm outline-none focus:border-[#0B132B]">{{ old('about', $model->about) }}</textarea>
            </div>
        </div>

        <div class="bg-white border border-gray-100 rounded-xl p-6 space-y-4">
            <h3 class="font-display font-semibold text-sm uppercase tracking-wide text-gray-400">Measurements</h3>
            <div class="grid grid-cols-4 gap-4">
                @foreach(['height'=>'Height','bust'=>'Bust','waist'=>'Waist','hips'=>'Hips'] as $field => $label)
                <div>
                    <label class="block text-xs text-gray-500 uppercase tracking-wide mb-1.5">{{ $label }}</label>
                    <input name="{{ $field }}" value="{{ old($field, $model->$field) }}" class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm outline-none focus:border-[#0B132B]">
                </div>
                @endforeach
            </div>
        </div>

        <div class="bg-white border border-gray-100 rounded-xl p-6 space-y-4">
            <h3 class="font-display font-semibold text-sm uppercase tracking-wide text-gray-400">Categories & Budget</h3>
            <div class="flex flex-wrap gap-2">
                @foreach(['Fashion','Commercial','Runway','Editorial','Fitness','Beauty'] as $cat)
                <label class="flex items-center gap-1.5 cursor-pointer">
                    <input type="checkbox" name="categories[]" value="{{ $cat }}"
                        {{ in_array($cat, old('categories', (array)$model->categories)) ? 'checked' : '' }}
                        class="rounded">
                    <span class="text-sm">{{ $cat }}</span>
                </label>
                @endforeach
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs text-gray-500 uppercase tracking-wide mb-1.5">Day Rate (AED)</label>
                    <input name="budget" type="number" value="{{ old('budget', $model->budget) }}" class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm outline-none focus:border-[#0B132B]">
                </div>
                <div class="flex items-center gap-2 pt-6">
                    <input type="checkbox" name="is_inhouse" id="is_inhouse" value="1" {{ old('is_inhouse', $model->is_inhouse) ? 'checked' : '' }} class="rounded">
                    <label for="is_inhouse" class="text-sm">In-house model</label>
                </div>
            </div>
        </div>

        <div class="flex gap-3 justify-end">
            <a href="{{ route('models.show', $model) }}" class="px-5 py-2.5 border border-gray-200 rounded-lg text-sm hover:bg-gray-50">Cancel</a>
            <button type="submit" class="px-5 py-2.5 bg-[#C9A96E] text-[#0B132B] font-semibold text-sm rounded-lg hover:bg-[#E8C882]">Update Model</button>
        </div>
    </form>
</div>
@endsection
