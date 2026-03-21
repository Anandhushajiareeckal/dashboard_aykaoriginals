@extends('layouts.app')
@section('title','Brands')
@section('content')

<div class="flex items-center justify-between mb-6">
    <div>
        <h2 class="font-display text-xl font-bold">Brand Directory</h2>
        <p class="text-sm text-gray-400">{{ $brands->total() }} brands</p>
    </div>
    <a href="{{ route('brands.create') }}" class="bg-[#C9A96E] text-[#0B132B] font-semibold text-sm px-4 py-2 rounded-lg hover:bg-[#E8C882] transition-colors">+ Add Brand</a>
</div>

<div class="bg-white border border-gray-100 rounded-xl overflow-hidden">
    <table class="w-full text-sm">
        <thead class="border-b border-gray-100">
            <tr>
                <th class="text-left px-5 py-3 text-xs text-gray-400 font-medium uppercase tracking-wide">Brand</th>
                <th class="text-left px-5 py-3 text-xs text-gray-400 font-medium uppercase tracking-wide">Location</th>
                <th class="text-left px-5 py-3 text-xs text-gray-400 font-medium uppercase tracking-wide">Contact</th>
                <th class="text-left px-5 py-3 text-xs text-gray-400 font-medium uppercase tracking-wide">Projects</th>
                <th class="text-left px-5 py-3 text-xs text-gray-400 font-medium uppercase tracking-wide">Next Follow-up</th>
                <th class="px-5 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            @forelse($brands as $brand)
            <tr class="hover:bg-gray-50 transition-colors">
                <td class="px-5 py-3.5">
                    <div class="font-semibold">{{ $brand->name }}</div>
                    @if($brand->website)<a href="{{ $brand->website }}" target="_blank" class="text-xs text-[#C9A96E] hover:underline">{{ $brand->website }}</a>@endif
                </td>
                <td class="px-5 py-3.5 text-gray-500">{{ $brand->location }}</td>
                <td class="px-5 py-3.5">
                    <div>{{ $brand->contact_name }}</div>
                    <div class="text-xs text-gray-400">{{ $brand->contact_designation }}</div>
                </td>
                <td class="px-5 py-3.5 text-center">
                    <span class="text-xs bg-[#0B132B]/8 text-[#0B132B] px-2 py-0.5 rounded-full font-medium">{{ $brand->projects_count }}</span>
                </td>
                <td class="px-5 py-3.5">
                    @if($brand->next_followup_date)
                        <span class="text-xs {{ $brand->next_followup_date->isPast() ? 'text-red-600 font-medium' : 'text-gray-500' }}">
                            {{ $brand->next_followup_date->format('d M Y') }}
                        </span>
                    @else
                        <span class="text-xs text-gray-300">—</span>
                    @endif
                </td>
                <td class="px-5 py-3.5 text-right">
                    <a href="{{ route('brands.show', $brand) }}" class="text-xs border border-gray-200 px-2.5 py-1 rounded-lg hover:bg-gray-50 transition-colors">View</a>
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="px-5 py-12 text-center text-gray-400">No brands yet. <a href="{{ route('brands.create') }}" class="text-[#C9A96E]">Add one</a></td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-5">{{ $brands->links() }}</div>
@endsection
