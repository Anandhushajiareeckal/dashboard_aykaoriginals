@extends('layouts.app')
@section('title','New Invoice')
@section('content')

<div class="max-w-2xl">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('invoices.index') }}" class="text-gray-400 hover:text-gray-600">← Back</a>
        <h2 class="font-display text-xl font-bold">New Invoice</h2>
    </div>

    <form method="POST" action="{{ route('invoices.store') }}" class="space-y-5" x-data="invoiceCalc()">
        @csrf

        <div class="bg-white border border-gray-100 rounded-xl p-6 space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs text-gray-500 uppercase tracking-wide mb-1.5">Invoice Number *</label>
                    <input name="invoice_number" value="{{ old('invoice_number', $nextNumber) }}" required
                        class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm outline-none focus:border-[#0B132B] font-display font-semibold">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 uppercase tracking-wide mb-1.5">Due Date</label>
                    <input name="due_date" type="date" value="{{ old('due_date') }}"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm outline-none focus:border-[#0B132B]">
                </div>
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
                            <option value="{{ $project->id }}" {{ old('project_id', request('project_id'))==$project->id ? 'selected' : '' }}>{{ $project->title }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="bg-white border border-gray-100 rounded-xl p-6 space-y-4">
            <h3 class="font-display font-semibold text-sm uppercase tracking-wide text-gray-400">Amount</h3>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs text-gray-500 uppercase tracking-wide mb-1.5">Amount (AED) *</label>
                    <input name="amount" type="number" step="0.01" value="{{ old('amount') }}" required
                        x-model="amount" x-on:input="calc()"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm outline-none focus:border-[#0B132B]">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 uppercase tracking-wide mb-1.5">VAT / Tax (%)</label>
                    <input name="tax" type="number" step="0.01" value="{{ old('tax', 5) }}"
                        x-model="tax" x-on:input="calc()"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm outline-none focus:border-[#0B132B]">
                </div>
            </div>

            {{-- Live total --}}
            <div class="bg-gray-50 rounded-xl p-4 flex items-center justify-between">
                <div class="text-sm text-gray-500">
                    <span x-text="'AED ' + parseFloat(amount||0).toLocaleString()"></span>
                    <span class="mx-2 text-gray-300">+</span>
                    <span x-text="tax + '% tax'"></span>
                </div>
                <div>
                    <span class="text-xs text-gray-400 mr-2">Total</span>
                    <span class="font-display font-bold text-xl" x-text="'AED ' + total.toLocaleString(undefined,{minimumFractionDigits:2,maximumFractionDigits:2})"></span>
                </div>
            </div>
        </div>

        <div class="bg-white border border-gray-100 rounded-xl p-6">
            <label class="block text-xs text-gray-500 uppercase tracking-wide mb-1.5">Notes</label>
            <textarea name="notes" rows="3"
                class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm outline-none focus:border-[#0B132B]"
                placeholder="Payment terms, bank details, notes…">{{ old('notes') }}</textarea>
        </div>

        <div class="flex gap-3 justify-end">
            <a href="{{ route('invoices.index') }}" class="px-5 py-2.5 border border-gray-200 rounded-lg text-sm hover:bg-gray-50 transition-colors">Cancel</a>
            <button type="submit" class="px-5 py-2.5 bg-[#C9A96E] text-[#0B132B] font-semibold text-sm rounded-lg hover:bg-[#E8C882] transition-colors">Create Invoice</button>
        </div>
    </form>
</div>

<script>
function invoiceCalc() {
    return {
        amount: {{ old('amount', 0) }},
        tax: {{ old('tax', 5) }},
        total: 0,
        calc() {
            const a = parseFloat(this.amount) || 0;
            const t = parseFloat(this.tax) || 0;
            this.total = a + (a * t / 100);
        },
        init() { this.calc(); }
    }
}
</script>
@endsection
