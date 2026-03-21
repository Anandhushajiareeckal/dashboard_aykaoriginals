@extends('layouts.app')
@section('title','Add Model')
@section('content')

<div class="max-w-3xl">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('models.index') }}" class="text-gray-400 hover:text-gray-600">← Back</a>
        <h2 class="font-display text-xl font-bold">Add New Model</h2>
    </div>

    <form method="POST" action="{{ route('models.store') }}" enctype="multipart/form-data" class="space-y-5">
        @csrf
        <div class="bg-white border border-gray-100 rounded-xl p-6 space-y-4">
            <h3 class="font-display font-semibold text-sm uppercase tracking-wide text-gray-400">Basic Info</h3>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs text-gray-500 uppercase tracking-wide mb-1.5">Full Name *</label>
                    <input name="name" value="{{ old('name') }}" required class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm outline-none focus:border-[#0B132B] @error('name') border-red-400 @enderror">
                    @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-xs text-gray-500 uppercase tracking-wide mb-1.5">Age</label>
                    <input name="age" type="number" value="{{ old('age') }}" class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm outline-none focus:border-[#0B132B]">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs text-gray-500 uppercase tracking-wide mb-1.5">Email</label>
                    <input name="email" type="email" value="{{ old('email') }}" class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm outline-none focus:border-[#0B132B]">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 uppercase tracking-wide mb-1.5">Phone</label>
                    <input name="phone" value="{{ old('phone') }}" class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm outline-none focus:border-[#0B132B]">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs text-gray-500 uppercase tracking-wide mb-1.5">Location</label>
                    <input name="location" value="{{ old('location') }}" class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm outline-none focus:border-[#0B132B]" placeholder="e.g. Dubai, UAE">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 uppercase tracking-wide mb-1.5">Status</label>
                    <select name="status" class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm outline-none focus:border-[#0B132B]">
                        @foreach(['Active','Inactive','On Leave','Unavailable'] as $s)
                            <option value="{{ $s }}" {{ old('status')===$s ? 'selected' : '' }}>{{ $s }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div>
                <label class="block text-xs text-gray-500 uppercase tracking-wide mb-1.5">About</label>
                <textarea name="about" rows="3" class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm outline-none focus:border-[#0B132B]">{{ old('about') }}</textarea>
            </div>
        </div>

        <div class="bg-white border border-gray-100 rounded-xl p-6 space-y-4">
            <h3 class="font-display font-semibold text-sm uppercase tracking-wide text-gray-400">Measurements</h3>
            <div class="grid grid-cols-4 gap-4">
                @foreach(['height'=>'Height','bust'=>'Bust','waist'=>'Waist','hips'=>'Hips'] as $field => $label)
                <div>
                    <label class="block text-xs text-gray-500 uppercase tracking-wide mb-1.5">{{ $label }}</label>
                    <input name="{{ $field }}" value="{{ old($field) }}" class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm outline-none focus:border-[#0B132B]" placeholder="e.g. 174cm">
                </div>
                @endforeach
            </div>
        </div>

        <div class="bg-white border border-gray-100 rounded-xl p-6 space-y-4">
            <h3 class="font-display font-semibold text-sm uppercase tracking-wide text-gray-400">Categories & Budget</h3>
            <div>
                <label class="block text-xs text-gray-500 uppercase tracking-wide mb-2">Categories</label>
                <div class="flex flex-wrap gap-2">
                    @foreach(['Fashion','Commercial','Runway','Editorial','Fitness','Beauty'] as $cat)
                    <label class="flex items-center gap-1.5 cursor-pointer">
                        <input type="checkbox" name="categories[]" value="{{ $cat }}"
                            {{ in_array($cat, old('categories',[])) ? 'checked' : '' }}
                            class="rounded">
                        <span class="text-sm text-gray-700">{{ $cat }}</span>
                    </label>
                    @endforeach
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs text-gray-500 uppercase tracking-wide mb-1.5">Day Rate (AED)</label>
                    <input name="budget" type="number" value="{{ old('budget') }}" class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm outline-none focus:border-[#0B132B]">
                </div>
                <div class="flex items-center gap-2 pt-6">
                    <input type="checkbox" name="is_inhouse" id="is_inhouse" value="1" {{ old('is_inhouse') ? 'checked' : '' }} class="rounded">
                    <label for="is_inhouse" class="text-sm text-gray-700">In-house model</label>
                </div>
            </div>
        </div>

        <div class="bg-white border border-gray-100 rounded-xl p-6" x-data="{ files: [] }">
            <h3 class="font-display font-semibold text-sm uppercase tracking-wide text-gray-400 mb-4">Portfolio Images</h3>
            <label class="block border-2 border-dashed border-gray-200 rounded-xl p-8 text-center cursor-pointer hover:border-[#0B132B] transition-colors"
                   x-on:dragover.prevent x-on:drop.prevent="files = Array.from($event.dataTransfer.files)">
                <input type="file" name="images[]" multiple accept="image/*" class="hidden"
                       x-on:change="files = Array.from($event.target.files)">
                <p class="text-2xl mb-2">⬆</p>
                <p class="text-sm text-gray-500">Drag & drop images here, or <span class="text-[#C9A96E] font-medium">browse</span></p>
                <p class="text-xs text-gray-400 mt-1">JPG, PNG up to 10MB each</p>
            </label>
            <div class="flex flex-wrap gap-2 mt-3" x-show="files.length">
                <template x-for="file in files" :key="file.name">
                    <span class="text-xs bg-gray-50 border border-gray-200 px-2 py-1 rounded" x-text="file.name"></span>
                </template>
            </div>
        </div>

        <div class="flex gap-3 justify-end">
            <a href="{{ route('models.index') }}" class="px-5 py-2.5 border border-gray-200 rounded-lg text-sm hover:bg-gray-50 transition-colors">Cancel</a>
            <button type="submit" class="px-5 py-2.5 bg-[#C9A96E] text-[#0B132B] font-semibold text-sm rounded-lg hover:bg-[#E8C882] transition-colors">Save Model</button>
        </div>
    </form>
</div>
@endsection
