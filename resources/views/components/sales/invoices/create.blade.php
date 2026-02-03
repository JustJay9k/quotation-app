@extends('layout.app')

@section('title','New Invoice')
@section('breadcrumb','Sales / Invoices')
@section('page_title','New Invoice')

@section('primary_action')
    <a href="{{ route('sales.invoices.index') }}"
       class="inline-flex items-center px-4 py-2 text-sm rounded-xl border border-slate-200 bg-white hover:bg-slate-50">
        Back to List
    </a>
@endsection

@section('content')
@php
    $oldItems = old('items', [
        ['name' => '', 'qty' => 1, 'rate' => 0, 'discount' => 0],
    ]);
@endphp

<div class="max-w-6xl space-y-4"
     x-data="window.invoiceForm()"
     x-init="init()"
>
    @if (session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl p-4">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="bg-rose-50 border border-rose-200 text-rose-800 rounded-2xl p-4">
            <div class="font-semibold mb-2">Please fix the following:</div>
            <ul class="list-disc pl-5 text-sm space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('sales.invoices.store') }}" class="space-y-4">
        @csrf

        {{-- Header --}}
        <div class="bg-white border border-slate-200 rounded-2xl p-5">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
                <div>
                    <div class="text-sm font-semibold">Invoice Details</div>
                    <div class="text-xs text-slate-500">Fill in customer, dates, and line items</div>
                </div>

                <div class="flex items-center gap-2">
                    <button type="submit"
                            class="px-4 py-2 text-sm rounded-xl bg-[#465FFF] text-white hover:bg-slate-800">
                        Save (UI)
                    </button>
                    <button type="button"
                            class="px-4 py-2 text-sm rounded-xl border border-slate-200 hover:bg-slate-50">
                        Save as Draft (UI)
                    </button>
                </div>
            </div>

            <div class="mt-5 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div>
                    <label class="text-xs text-slate-500">Customer Name *</label>
                    <select name="customer_name"
                            class="mt-1 w-full rounded-xl border-slate-200 focus:border-slate-400 focus:ring-slate-200">
                        <option value="">Select customer</option>
                        @foreach($customers as $c)
                            <option value="{{ $c }}" @selected(old('customer_name') === $c)>{{ $c }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="text-xs text-slate-500">Invoice Date *</label>
                    <input type="date" name="invoice_date" value="{{ old('invoice_date', now()->toDateString()) }}"
                           class="mt-1 w-full rounded-xl border-slate-200 focus:border-slate-400 focus:ring-slate-200">
                </div>

                    <div>
                        <label class="text-xs text-slate-500">Currency</label>
                        <select name="currency" x-model="currency"
                                class="mt-1 w-full rounded-xl border-slate-200 focus:border-slate-400 focus:ring-slate-200">
                            <option value="MWK">MWK (Malawi Kwacha)</option>
                            <option value="USD">USD (US Dollar)</option>
                            <option value="ZAR">ZAR (South African Rand)</option>
                            <option value="EUR">EUR (Euro)</option>
                            <option value="GBP">GBP (British Pound)</option>
                        </select>
                        <div class="mt-1 text-xs text-slate-500">Applies to totals and line amounts.</div>
                    </div>


                <div>
                    <label class="text-xs text-slate-500">Due Date *</label>
                    <input type="date" name="due_date" value="{{ old('due_date', now()->addDays(14)->toDateString()) }}"
                           class="mt-1 w-full rounded-xl border-slate-200 focus:border-slate-400 focus:ring-slate-200">
                </div>

                <div>
                    <label class="text-xs text-slate-500">Invoice # *</label>
                    <input type="text" name="invoice_number" value="{{ old('invoice_number') }}"
                           placeholder="e.g. INV-0001"
                           class="mt-1 w-full rounded-xl border-slate-200 focus:border-slate-400 focus:ring-slate-200">
                </div>

                <div class="md:col-span-2">
                    <label class="text-xs text-slate-500">Description</label>
                    <input type="text" name="subject" value="{{ old('subject') }}"
                           placeholder="e.g. Website development invoice"
                           class="mt-1 w-full rounded-xl border-slate-200 focus:border-slate-400 focus:ring-slate-200">
                </div>
            </div>
        </div>

        {{-- Line Items --}}
        <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-200 flex items-center justify-between">
                <div>
                    <div class="text-sm font-semibold">Line Items</div>
                    <div class="text-xs text-slate-500">Add items/services, quantities, rates</div>

                </div>

                <button type="button" @click="addItem()"
                        class="px-3 py-2 text-sm rounded-xl border border-slate-200 hover:bg-slate-50">
                    + Add Row
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold text-slate-600">Item</th>
                        <th class="px-4 py-3 text-right font-semibold text-slate-600 w-28">Qty</th>
                        <th class="px-4 py-3 text-right font-semibold text-slate-600 w-40">Rate</th>

                        <th class="px-4 py-3 text-right font-semibold text-slate-600 w-44">Discount</th>
                        <th class="px-4 py-3 text-right font-semibold text-slate-600 w-44">Amount</th>
                        <th class="px-4 py-3 text-right font-semibold text-slate-600 w-20"></th>
                    </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100">
                        <template x-for="(row, idx) in items" :key="idx">
                            <tr class="hover:bg-slate-50 align-top">
                                <!-- Item -->
                                <td class="px-4 py-3 min-w-[280px]">
                                    <input type="text"
                                        :name="`items[${idx}][name]`"
                                        x-model="row.name"
                                        placeholder="Item / Service name"
                                        class="w-full rounded-xl border-slate-200 focus:border-slate-400 focus:ring-slate-200">

                                    <div class="mt-3">
                                        <label class="text-xs text-slate-500">Line item note</label>
                                        <textarea rows="2"
                                                :name="`items[${idx}][note]`"
                                                x-model="row.note"
                                                placeholder="Optional note (e.g. deliverables, scope, timeline)"
                                                class="mt-1 w-full rounded-xl border-slate-200 focus:border-slate-400 focus:ring-slate-200"></textarea>
                                    </div>
                                </td>

                                <!-- Qty -->
                                <td class="px-4 py-3">
                                    <input type="number" step="0.01" min="0"
                                        :name="`items[${idx}][qty]`"
                                        x-model.number="row.qty"
                                        class="w-full text-right rounded-xl border-slate-200 focus:border-slate-400 focus:ring-slate-200">
                                </td>

                                <!-- Rate -->
                                <td class="px-4 py-3">
                                    <input type="number" step="1" min="0"
                                        :name="`items[${idx}][rate]`"
                                        x-model.number="row.rate"
                                        class="w-full text-right rounded-xl border-slate-200 focus:border-slate-400 focus:ring-slate-200">
                                </td>

                                <!-- Discount -->
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2 justify-end">
                                        <select :name="`items[${idx}][discount_type]`"
                                                x-model="row.discount_type"
                                                class="w-24 rounded-xl border-slate-200 focus:border-slate-400 focus:ring-slate-200">
                                            <option value="fixed">Fixed</option>
                                            <option value="percent">%</option>
                                        </select>

                                        <input type="number" min="0" step="0.01"
                                            :name="`items[${idx}][discount]`"
                                            x-model.number="row.discount"
                                            class="w-28 text-right rounded-xl border-slate-200 focus:border-slate-400 focus:ring-slate-200"
                                            placeholder="0">
                                    </div>
                                </td>

                                <!-- Amount -->
                                <td class="px-4 py-3 text-right font-semibold whitespace-nowrap">
                                    <span x-text="currencySymbol"></span>
                                    <span x-text="format(lineTotal(row))"></span>
                                </td>

                                <!-- Remove -->
                                <td class="px-4 py-3 text-right">
                                    <button type="button" @click="removeItem(idx)"
                                            class="px-3 py-2 text-xs rounded-xl border border-slate-200 hover:bg-slate-50">
                                        Remove
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>

                </table>
            </div>

            {{-- Totals --}}
            <div class="border-t border-slate-200 bg-white px-5 py-4">
                <div class="max-w-md ml-auto space-y-2 text-sm">
                    <div class="flex items-center justify-between">
                        <div class="text-slate-600">Sub Total</div>
                        <div class="font-semibold"><span x-text="currencySymbol"></span> <span x-text="format(subTotal())"></span></div>
                    </div>

                    <div class="flex items-center justify-between">
                        <div class="text-slate-600">Tax (VAT %)</div>
                        <div class="flex items-center gap-2">
                            <input type="number" min="0" step="0.1" name="vat_rate" x-model.number="vatRate"
                                   class="w-24 text-right rounded-xl border-slate-200 focus:border-slate-400 focus:ring-slate-200">
                            <div class="font-semibold"><span x-text="currencySymbol"></span> <span x-text="format(vatAmount())"></span></div>
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-2 border-t border-slate-200">
                        <div class="text-slate-900 font-semibold">Total</div>
                        <div class="text-slate-900 font-semibold"><span x-text="currencySymbol"></span> <span x-text="format(grandTotal())"></span></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Notes & Terms --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            <div class="bg-white border border-slate-200 rounded-2xl p-5">
                <div class="text-sm font-semibold">Customer Notes</div>
                <div class="text-xs text-slate-500">Shown on the invoice</div>
                <textarea name="notes" rows="6"
                          class="mt-3 w-full rounded-xl border-slate-200 focus:border-slate-400 focus:ring-slate-200"
                          placeholder="e.g. Thank you for your interest...">{{ old('notes') }}</textarea>
            </div>

            <div class="bg-white border border-slate-200 rounded-2xl p-5">
                <div class="text-sm font-semibold">Terms & Conditions</div>
                <div class="text-xs text-slate-500">Payment terms, validity, delivery</div>
                <textarea name="terms" rows="6"
                          class="mt-3 w-full rounded-xl border-slate-200 focus:border-slate-400 focus:ring-slate-200"
                          placeholder="e.g. Valid for 14 days...">{{ old('terms') }}</textarea>
            </div>
        </div>

        {{-- Footer actions --}}
        <div class="flex items-center justify-end gap-2">
            <a href="{{ route('sales.invoices.index') }}"
               class="px-4 py-2 text-sm rounded-xl border border-slate-200 bg-white hover:bg-slate-50">
                Cancel
            </a>
            <button type="submit"
                    class="px-4 py-2 text-sm rounded-xl bg-[#465FFF] text-white hover:bg-slate-800">
                Save Invoice (UI)
            </button>
        </div>
    </form>
</div>

{{-- Alpine logic (uses Alpine from your Vite app.ts) --}}
<script>
window.invoiceForm = function() {
    const initialItems = @json($oldItems);

    return {
        currency: @json(old('currency', 'MWK')),
        currencySymbol: 'MWK',

        items: (initialItems?.length ? initialItems : [
            { name: '', qty: 1, rate: 0, discount_type: 'fixed', discount: 0, note: '' }
        ]).map(r => ({
            name: r.name ?? '',
            qty: Number(r.qty ?? 1),
            rate: Number(r.rate ?? 0),
            discount_type: r.discount_type ?? 'fixed',
            discount: Number(r.discount ?? 0),
            note: r.note ?? '',
        })),

        vatRate: 16.5,

        init() {
            this.setCurrencySymbol();
            this.$watch('currency', () => this.setCurrencySymbol());
        },

        setCurrencySymbol() {
            const map = { MWK: 'MWK', USD: '$', ZAR: 'R', EUR: '€', GBP: '£' };
            this.currencySymbol = map[this.currency] ?? this.currency;
        },

        addItem() {
            this.items.push({ name: '', qty: 1, rate: 0, discount_type: 'fixed', discount: 0, note: '' });
        },

        removeItem(i) {
            this.items.splice(i, 1);
            if (this.items.length === 0) this.addItem();
        },

        lineBase(row) {
            return Number(row.qty || 0) * Number(row.rate || 0);
        },

        lineDiscount(row) {
            const base = this.lineBase(row);
            const d = Number(row.discount || 0);

            if (row.discount_type === 'percent') {
                const pct = Math.min(Math.max(d, 0), 100);
                return base * (pct / 100);
            }

            return Math.min(Math.max(d, 0), base);
        },

        lineTotal(row) {
            const total = this.lineBase(row) - this.lineDiscount(row);
            return total < 0 ? 0 : Math.round(total);
        },

        subTotal() {
            return this.items.reduce((sum, r) => sum + this.lineTotal(r), 0);
        },

        vatAmount() {
            return Math.round(this.subTotal() * (Number(this.vatRate || 0) / 100));
        },

        grandTotal() {
            return this.subTotal() + this.vatAmount();
        },

        format(n) {
            return Number(n || 0).toLocaleString();
        }
    }
}
</script>

@endsection
