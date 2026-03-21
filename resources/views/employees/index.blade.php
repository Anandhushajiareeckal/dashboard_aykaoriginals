@extends('layouts.app')
@section('title','Employees')
@section('content')
<div class="flex items-center justify-between mb-6">
    <div><h2 class="font-display text-xl font-bold">Team & Employees</h2><p class="text-sm text-gray-400">{{ $employees->total() }} staff members</p></div>
    <a href="{{ route('employees.create') }}" class="bg-[#C9A96E] text-[#0B132B] font-semibold text-sm px-4 py-2 rounded-lg hover:bg-[#E8C882]">+ Add Employee</a>
</div>
<div class="bg-white border border-gray-100 rounded-xl overflow-hidden">
    <table class="w-full text-sm">
        <thead class="border-b border-gray-100"><tr>
            <th class="text-left px-5 py-3 text-xs text-gray-400 font-medium uppercase tracking-wide">Employee</th>
            <th class="text-left px-5 py-3 text-xs text-gray-400 font-medium uppercase tracking-wide">Department</th>
            <th class="text-left px-5 py-3 text-xs text-gray-400 font-medium uppercase tracking-wide">Contact</th>
            <th class="text-left px-5 py-3 text-xs text-gray-400 font-medium uppercase tracking-wide">Salary</th>
            <th class="text-left px-5 py-3 text-xs text-gray-400 font-medium uppercase tracking-wide">Joined</th>
            <th class="text-left px-5 py-3 text-xs text-gray-400 font-medium uppercase tracking-wide">Status</th>
            <th class="px-5 py-3"></th>
        </tr></thead>
        <tbody class="divide-y divide-gray-50">
            @forelse($employees as $emp)
            <tr class="hover:bg-gray-50">
                <td class="px-5 py-3.5">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-full bg-[#0B132B] flex items-center justify-center text-[#C9A96E] text-xs font-bold flex-shrink-0">{{ strtoupper(substr($emp->name,0,2)) }}</div>
                        <span class="font-medium">{{ $emp->name }}</span>
                    </div>
                </td>
                <td class="px-5 py-3.5 text-gray-500">{{ $emp->department }}</td>
                <td class="px-5 py-3.5 text-gray-500 text-xs">{{ $emp->email }}<br>{{ $emp->phone }}</td>
                <td class="px-5 py-3.5 font-display font-semibold text-sm">AED {{ number_format($emp->salary) }}</td>
                <td class="px-5 py-3.5 text-gray-500 text-xs">{{ $emp->joining_date?->format('d M Y') }}</td>
                <td class="px-5 py-3.5"><span class="text-xs px-2 py-0.5 rounded-full font-medium {{ $emp->status==='Active' ? 'bg-green-50 text-green-700' : ($emp->status==='On Leave' ? 'bg-amber-50 text-amber-700' : 'bg-red-50 text-red-600') }}">{{ $emp->status }}</span></td>
                <td class="px-5 py-3.5 text-right"><a href="{{ route('employees.edit', $emp) }}" class="text-xs border border-gray-200 px-2.5 py-1 rounded-lg hover:bg-gray-50">Edit</a></td>
            </tr>
            @empty
            <tr><td colspan="7" class="px-5 py-12 text-center text-gray-400">No employees yet.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-5">{{ $employees->links() }}</div>
@endsection
