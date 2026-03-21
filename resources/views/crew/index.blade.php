@extends('layouts.app')
@section('title','Crew')
@section('content')
<div class="flex items-center justify-between mb-6">
    <div><h2 class="font-display text-xl font-bold">Crew Directory</h2><p class="text-sm text-gray-400">{{ $crew->total() }} crew members</p></div>
    <a href="{{ route('crew.create') }}" class="bg-[#C9A96E] text-[#0B132B] font-semibold text-sm px-4 py-2 rounded-lg hover:bg-[#E8C882]">+ Add Crew</a>
</div>
<div class="bg-white border border-gray-100 rounded-xl overflow-hidden">
    <table class="w-full text-sm">
        <thead class="border-b border-gray-100"><tr>
            <th class="text-left px-5 py-3 text-xs text-gray-400 font-medium uppercase tracking-wide">Name</th>
            <th class="text-left px-5 py-3 text-xs text-gray-400 font-medium uppercase tracking-wide">Role</th>
            <th class="text-left px-5 py-3 text-xs text-gray-400 font-medium uppercase tracking-wide">Location</th>
            <th class="text-left px-5 py-3 text-xs text-gray-400 font-medium uppercase tracking-wide">Contact</th>
            <th class="text-left px-5 py-3 text-xs text-gray-400 font-medium uppercase tracking-wide">Status</th>
            <th class="px-5 py-3"></th>
        </tr></thead>
        <tbody class="divide-y divide-gray-50">
            @forelse($crew as $member)
            <tr class="hover:bg-gray-50">
                <td class="px-5 py-3.5">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-full bg-[#5E6472] flex items-center justify-center text-white text-xs font-bold flex-shrink-0">{{ strtoupper(substr($member->name,0,2)) }}</div>
                        <span class="font-medium">{{ $member->name }}</span>
                    </div>
                </td>
                <td class="px-5 py-3.5"><span class="text-xs px-2.5 py-1 rounded-full bg-[#0B132B]/8 text-[#0B132B]">{{ $member->role }}</span></td>
                <td class="px-5 py-3.5 text-gray-500">{{ $member->location }}</td>
                <td class="px-5 py-3.5 text-gray-500">{{ $member->email }}</td>
                <td class="px-5 py-3.5">
                    <span class="text-xs px-2 py-0.5 rounded-full font-medium {{ $member->status==='Available' ? 'bg-green-50 text-green-700' : ($member->status==='On Project' ? 'bg-blue-50 text-blue-700' : 'bg-gray-100 text-gray-500') }}">{{ $member->status }}</span>
                </td>
                <td class="px-5 py-3.5 text-right flex gap-2 justify-end">
                    <a href="{{ route('crew.edit', $member) }}" class="text-xs border border-gray-200 px-2.5 py-1 rounded-lg hover:bg-gray-50">Edit</a>
                    <form method="POST" action="{{ route('crew.destroy', $member) }}" onsubmit="return confirm('Remove crew member?')">
                        @csrf @method('DELETE')
                        <button class="text-xs border border-red-200 text-red-500 px-2.5 py-1 rounded-lg hover:bg-red-50">Remove</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="px-5 py-12 text-center text-gray-400">No crew yet. <a href="{{ route('crew.create') }}" class="text-[#C9A96E]">Add someone</a></td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-5">{{ $crew->links() }}</div>
@endsection
