@extends('layouts.app')
@section('title','Accounts')
@section('content')

<div class="flex items-center justify-between mb-6">
    <div>
        <h2 class="font-display text-xl font-bold">Accounts & Finance</h2>
        <p class="text-sm text-gray-400">All amounts in AED</p>
    </div>
    <a href="{{ route('invoices.create') }}" class="bg-[#C9A96E] text-[#0B132B] font-semibold text-sm px-4 py-2 rounded-lg hover:bg-[#E8C882] transition-colors">+ New Invoice</a>
</div>

{{-- KPI Cards --}}
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
    <div class="bg-white border border-gray-100 rounded-xl p-5">
        <p class="text-xs text-gray-400 uppercase tracking-widest mb-2">Total Invoiced</p>
        <p class="font-display text-2xl font-bold">AED {{ number_format($summary['total']) }}</p>
    </div>
    <div class="bg-white border border-gray-100 rounded-xl p-5">
        <p class="text-xs text-gray-400 uppercase tracking-widest mb-2">Collected</p>
        <p class="font-display text-2xl font-bold text-green-700">AED {{ number_format($summary['paid']) }}</p>
    </div>
    <div class="bg-white border border-gray-100 rounded-xl p-5" style="background:rgba(201,169,110,0.06)">
        <p class="text-xs text-gray-400 uppercase tracking-widest mb-2">Outstanding</p>
        <p class="font-display text-2xl font-bold text-[#C9A96E]">AED {{ number_format($summary['pending']) }}</p>
    </div>
</div>

{{-- Filter --}}
<form method="GET" class="bg-white border border-gray-100 rounded-xl p-4 mb-5 flex flex-wrap gap-3 items-end">
    <div>
        <label class="block text-xs text-gray-400 uppercase tracking-wide mb-1">Invoice #</label>
        <input type="text" name="inv" value="{{ request('inv') }}" placeholder="INV-..." class="border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-[#0B132B] w-32">
    </div>
    <div>
        <label class="block text-xs text-gray-400 uppercase tracking-wide mb-1">Customer</label>
        <select name="brand_id" class="border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-[#0B132B] w-40">
            <option value="">All Customers</option>
            @foreach($brands as $b)
                <option value="{{ $b->id }}" {{ request('brand_id') == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-xs text-gray-400 uppercase tracking-wide mb-1">Date From</label>
        <input type="date" name="from_date" value="{{ request('from_date') }}" class="border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-[#0B132B]">
    </div>
    <div>
        <label class="block text-xs text-gray-400 uppercase tracking-wide mb-1">Date To</label>
        <input type="date" name="to_date" value="{{ request('to_date') }}" class="border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-[#0B132B]">
    </div>
    <div>
        <label class="block text-xs text-gray-400 uppercase tracking-wide mb-1">Status</label>
        <select name="status" class="border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-[#0B132B]">
            <option value="">All</option>
            @foreach(['Draft','Sent','Paid','Overdue','Cancelled'] as $s)
                <option value="{{ $s }}" {{ request('status')===$s ? 'selected' : '' }}>{{ $s }}</option>
            @endforeach
        </select>
    </div>
    <button type="submit" class="bg-[#0B132B] text-white text-sm px-4 py-2 rounded-lg">Filter</button>
    <a href="{{ route('invoices.index') }}" class="text-sm text-gray-400 hover:text-gray-600 py-2">Clear</a>
</form>

{{-- Table --}}
<div class="bg-white border border-gray-100 rounded-xl overflow-x-auto">
    <table class="w-full text-sm min-w-[680px]">
        <thead class="border-b border-gray-100">
            <tr>
                <th class="text-left px-5 py-3 text-xs text-gray-400 font-medium uppercase tracking-wide">Invoice</th>
                <th class="text-left px-5 py-3 text-xs text-gray-400 font-medium uppercase tracking-wide">Client</th>
                <th class="text-left px-5 py-3 text-xs text-gray-400 font-medium uppercase tracking-wide">Project</th>
                <th class="text-right px-5 py-3 text-xs text-gray-400 font-medium uppercase tracking-wide">Amount</th>
                <th class="text-left px-5 py-3 text-xs text-gray-400 font-medium uppercase tracking-wide">Due Date</th>
                <th class="text-left px-5 py-3 text-xs text-gray-400 font-medium uppercase tracking-wide">Status</th>
                <th class="px-5 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            @forelse($invoices as $invoice)
            @php
                $sc = ['Draft'=>'bg-gray-100 text-gray-600','Sent'=>'bg-blue-50 text-blue-700','Paid'=>'bg-green-50 text-green-700','Overdue'=>'bg-red-50 text-red-600','Cancelled'=>'bg-gray-100 text-gray-400'][$invoice->status] ?? 'bg-gray-100 text-gray-600';
            @endphp
            <tr class="hover:bg-gray-50 transition-colors">
                <td class="px-5 py-3.5">
                    <span class="font-display font-semibold text-sm">{{ $invoice->invoice_number }}</span>
                </td>
                <td class="px-5 py-3.5 font-medium">{{ $invoice->brand?->name ?? '—' }}</td>
                <td class="px-5 py-3.5 text-gray-500 text-xs">{{ $invoice->project?->title ?? '—' }}</td>
                <td class="px-5 py-3.5 text-right font-display font-semibold">AED {{ number_format($invoice->total) }}</td>
                <td class="px-5 py-3.5 text-xs {{ $invoice->due_date?->isPast() && $invoice->status !== 'Paid' ? 'text-red-600 font-medium' : 'text-gray-500' }}">
                    {{ $invoice->due_date?->format('d M Y') ?? '—' }}
                </td>
                <td class="px-5 py-3.5">
                    <span class="text-xs px-2.5 py-1 rounded-full font-medium {{ $sc }}">{{ $invoice->status }}</span>
                </td>
                <td class="px-5 py-3.5">
                    <div class="flex items-center gap-2 justify-end">
                        <a href="{{ route('invoices.pdf', $invoice) }}" class="text-xs border border-gray-200 px-2.5 py-1 rounded-lg hover:bg-gray-50 transition-colors">PDF</a>
                        @if($invoice->status !== 'Paid' && $invoice->status !== 'Cancelled')
                        <form method="POST" action="{{ route('invoices.status', $invoice) }}">
                            @csrf @method('PATCH')
                            <select name="status" onchange="this.form.submit()" class="text-xs border border-gray-200 rounded-lg px-2 py-1 outline-none focus:border-[#0B132B] cursor-pointer">
                                @foreach(['Draft','Sent','Paid','Overdue','Cancelled'] as $s)
                                    <option value="{{ $s }}" {{ $invoice->status===$s ? 'selected' : '' }}>{{ $s }}</option>
                                @endforeach
                            </select>
                        </form>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="7" class="px-5 py-12 text-center text-gray-400">No invoices yet. <a href="{{ route('invoices.create') }}" class="text-[#C9A96E]">Create one</a></td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-5">{{ $invoices->links() }}</div>
@endsection
