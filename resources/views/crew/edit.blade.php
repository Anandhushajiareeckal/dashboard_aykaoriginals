@extends('layouts.app')
@section('title','Edit Crew')
@section('content')
<div class="max-w-xl">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('crew.index') }}" class="text-gray-400 hover:text-gray-600">← Back</a>
        <h2 class="font-display text-xl font-bold">Edit: {{ $crew->name }}</h2>
    </div>
    <form method="POST" action="{{ route('crew.update', $crew) }}" class="bg-white border border-gray-100 rounded-xl p-6 space-y-4">
        @csrf @method('PUT')
        <div class="grid grid-cols-2 gap-4">
            <div><label class="block text-xs text-gray-500 uppercase tracking-wide mb-1.5">Name *</label><input name="name" value="{{ old('name',$crew->name) }}" required class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm outline-none focus:border-[#0B132B]"></div>
            <div><label class="block text-xs text-gray-500 uppercase tracking-wide mb-1.5">Role *</label>
                <select name="role" class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm outline-none focus:border-[#0B132B]">
                    @foreach(['Photographer','Videographer','Makeup Artist','Stylist','Hair Stylist','Lighting Tech','Art Director','Producer','Assistant'] as $r)
                        <option value="{{ $r }}" {{ old('role',$crew->role)===$r?'selected':'' }}>{{ $r }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div><label class="block text-xs text-gray-500 uppercase tracking-wide mb-1.5">Email</label><input name="email" type="email" value="{{ old('email',$crew->email) }}" class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm outline-none focus:border-[#0B132B]"></div>
            <div><label class="block text-xs text-gray-500 uppercase tracking-wide mb-1.5">Phone</label><input name="phone" value="{{ old('phone',$crew->phone) }}" class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm outline-none focus:border-[#0B132B]"></div>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div><label class="block text-xs text-gray-500 uppercase tracking-wide mb-1.5">Location</label><input name="location" value="{{ old('location',$crew->location) }}" class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm outline-none focus:border-[#0B132B]"></div>
            <div><label class="block text-xs text-gray-500 uppercase tracking-wide mb-1.5">Status</label>
                <select name="status" class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm outline-none focus:border-[#0B132B]">
                    @foreach(['Available','On Project','Inactive'] as $s)<option value="{{ $s }}" {{ old('status',$crew->status)===$s?'selected':'' }}>{{ $s }}</option>@endforeach
                </select>
            </div>
        </div>
        <div class="flex gap-3 justify-end pt-2">
            <a href="{{ route('crew.index') }}" class="px-5 py-2.5 border border-gray-200 rounded-lg text-sm hover:bg-gray-50">Cancel</a>
            <button type="submit" class="px-5 py-2.5 bg-[#C9A96E] text-[#0B132B] font-semibold text-sm rounded-lg hover:bg-[#E8C882]">Update</button>
        </div>
    </form>
</div>
@endsection
