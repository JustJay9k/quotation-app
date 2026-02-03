@extends('layout.app')

@section('title','Invoices')
@section('breadcrumb','Sales')
@section('page_title','Invoices')

@section('primary_action')
    <a href="{{ route('sales.invoices.create') }}"
       class="inline-flex items-center px-4 py-2 text-sm rounded-xl bg-[#465FFF] text-white hover:bg-slate-800">
        + New Invoice
    </a>
@endsection

@section('content')
    <div class="space-y-4">
        <x-sales.list-toolbar 
            searchPlaceholder="Search invoices (number, customer)..." 
            :statusOptions="['All Statuses','Draft','Sent','Paid','Unpaid','Overdue']"
        />

        <x-sales.table
            :columns="[
                ['key'=>'date','label'=>'Date'],
                ['key'=>'number','label'=>'Invoice #'],
                ['key'=>'customer','label'=>'Customer Name'],
                ['key'=>'status','label'=>'Status'],
                ['key'=>'due','label'=>'Due Date'],
                ['key'=>'amount','label'=>'Amount'],
            ]"
            :rows="$rows"
            emptyTitle="No invoices yet"
            emptyHint="Invoices will appear here once created."
        />
    </div>
@endsection