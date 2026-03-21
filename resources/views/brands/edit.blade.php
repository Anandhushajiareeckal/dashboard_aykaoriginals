@extends('layouts.app')
@section('title','Edit Brand')
@section('content')
<div class="max-w-2xl">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('brands.show', $brand) }}" class="text-gray-400 hover:text-gray-600">← Back</a>
        <h2 class="font-display text-xl font-bold">Edit: {{ $brand->name }}</h2>
    </div>
    <form method="POST" action="{{ route('brands.update', $brand) }}" class="space-y-5">
        @csrf @method('PUT')
        <div class="bg-white border border-gray-100 rounded-xl p-6 space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div><label class="block text-xs text-gray-500 uppercase tracking-wide mb-1.5">Brand Name *</label><input name="name" value="{{ old('name',$brand->name) }}" required class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm outline-none focus:border-[#0B132B]"></div>
                <div><label class="block text-xs text-gray-500 uppercase tracking-wide mb-1.5">Location</label><input name="location" value="{{ old('location',$brand->location) }}" class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm outline-none focus:border-[#0B132B]"></div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div><label class="block text-xs text-gray-500 uppercase tracking-wide mb-1.5">Contact Person</label><input name="contact_name" value="{{ old('contact_name',$brand->contact_name) }}" class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm outline-none focus:border-[#0B132B]"></div>
                <div><label class="block text-xs text-gray-500 uppercase tracking-wide mb-1.5">Designation</label><input name="contact_designation" value="{{ old('contact_designation',$brand->contact_designation) }}" class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm outline-none focus:border-[#0B132B]"></div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div><label class="block text-xs text-gray-500 uppercase tracking-wide mb-1.5">Email</label><input name="email" type="email" value="{{ old('email',$brand->email) }}" class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm outline-none focus:border-[#0B132B]"></div>
                <div><label class="block text-xs text-gray-500 uppercase tracking-wide mb-1.5">Phone</label><input name="phone" value="{{ old('phone',$brand->phone) }}" class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm outline-none focus:border-[#0B132B]"></div>
            </div>
            <div><label class="block text-xs text-gray-500 uppercase tracking-wide mb-1.5">Website</label><input name="website" type="url" value="{{ old('website',$brand->website) }}" class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm outline-none focus:border-[#0B132B]"></div>
            <div><label class="block text-xs text-gray-500 uppercase tracking-wide mb-1.5">Description</label><textarea name="description" rows="3" class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm outline-none focus:border-[#0B132B]">{{ old('description',$brand->description) }}</textarea></div>
            <div><label class="block text-xs text-gray-500 uppercase tracking-wide mb-1.5">Next Follow-up</label><input name="next_followup_date" type="date" value="{{ old('next_followup_date',$brand->next_followup_date?->format('Y-m-d')) }}" class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm outline-none focus:border-[#0B132B]"></div>
        </div>
        <div class="flex gap-3 justify-end">
            <a href="{{ route('brands.show', $brand) }}" class="px-5 py-2.5 border border-gray-200 rounded-lg text-sm hover:bg-gray-50">Cancel</a>
            <button type="submit" class="px-5 py-2.5 bg-[#C9A96E] text-[#0B132B] font-semibold text-sm rounded-lg hover:bg-[#E8C882]">Update Brand</button>
        </div>
    </form>
</div>
@endsection
