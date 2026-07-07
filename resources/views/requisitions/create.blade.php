@extends('layouts.app')

@section('title', 'Create Requisition')

@section('content')
<div class="text-xs text-gray-400 mb-1">Create requisiton</div>

<form action="{{ route('requisitions.store') }}" method="POST" id="reqForm">
@csrf
<div class="grid grid-cols-3 gap-6">

    <div class="col-span-2 bg-white rounded-lg border p-6">
        <div class="flex items-center justify-between mb-1">
            <h1 class="text-xl font-bold">CREATE PURCHASE REQUISITION</h1>
        </div>
        <p class="text-sm text-gray-500 mb-6">Fill in the details below to request materials or services</p>

        <div class="grid grid-cols-2 gap-6 mb-6">
            <div>
                <label class="text-sm font-medium">Requisition Title<span class="text-red-500">*</span></label>
                <input name="title" required placeholder="Enter a short title for the requisition"
                       class="mt-1 w-full border rounded-md px-3 py-2 text-sm">
            </div>
            <div>
                <label class="text-sm font-medium">Department<span class="text-red-500">*</span></label>
                <select name="department" required class="mt-1 w-full border rounded-md px-3 py-2 text-sm">
                    <option value="">Select department</option>
                    <option>Marketing</option>
                    <option>Eng</option>
                    <option>Ops</option>
                    <option>Finance</option>
                </select>
            </div>
            <div>
                <label class="text-sm font-medium">Requestor<span class="text-red-500">*</span></label>
                <input name="requestor" required placeholder="Name"
                       class="mt-1 w-full border rounded-md px-3 py-2 text-sm">
            </div>
            <div>
                <label class="text-sm font-medium">Needed by<span class="text-red-500">*</span></label>
                <input type="date" name="needed_by" required
                       class="mt-1 w-full border rounded-md px-3 py-2 text-sm">
            </div>
        </div>

        <div class="mb-6">
            <label class="text-sm font-medium">Purpose/Description<span class="text-red-500">*</span></label>
            <textarea name="purpose" rows="3" placeholder="Provide brief description of the items or service needed"
                      class="mt-1 w-full border rounded-md px-3 py-2 text-sm"></textarea>
        </div>

        <h2 class="font-semibold mb-3">Items</h2>
        <table class="w-full text-sm mb-3">
            <thead class="text-gray-500 text-xs uppercase">
                <tr>
                    <th class="text-left py-1 w-6">#</th>
                    <th class="text-left py-1">Item/Service*</th>
                    <th class="text-left py-1">Description</th>
                    <th class="text-left py-1 w-16">Qty*</th>
                    <th class="text-left py-1 w-20">Unit</th>
                    <th class="text-left py-1 w-28">Unit Price*</th>
                    <th class="text-left py-1 w-24">Total</th>
                    <th class="w-8"></th>
                </tr>
            </thead>
            <tbody id="itemsBody"></tbody>
        </table>
        <button type="button" onclick="addRow()" class="text-brand text-sm font-medium">+ Add item</button>
    </div>

    <div>
        <div class="bg-white rounded-lg border p-5 sticky top-6">
            <h3 class="font-semibold mb-4">Requisiton summary</h3>
            <div class="flex justify-between text-sm py-1">
                <span class="text-gray-500">Subtotal</span><span id="sumSubtotal">$0.00</span>
            </div>
            <div class="flex justify-between text-sm py-1">
                <span class="text-gray-500">Tax (0%)</span><span id="sumTax">$0.00</span>
            </div>
            <div class="flex justify-between font-bold py-2 border-t mt-2">
                <span>Estimated Total</span><span id="sumTotal">$0.00</span>
            </div>
        </div>

        <div class="flex justify-end gap-3 mt-6">
            <a href="{{ route('approvals.index') }}" class="px-4 py-2 rounded-md border text-sm">Cancel</a>
            <button type="submit" name="draft" value="1" class="px-4 py-2 rounded-md border text-sm">Save draft</button>
        </div>
        <button type="submit" class="w-full mt-3 bg-blue-600 hover:bg-blue-700 text-white rounded-md py-2 text-sm font-medium">
            Submit
        </button>
    </div>
</div>
</form>

<template id="rowTemplate">
    <tr class="item-row border-t">
        <td class="py-2 row-index">1</td>
        <td class="py-2 pr-2">
            <input name="items[__i__][item_name]" required placeholder="Search item or service"
                   class="w-full border rounded-md px-2 py-1 text-sm">
        </td>
        <td class="py-2 pr-2">
            <input name="items[__i__][description]" placeholder="Enter description"
                   class="w-full border rounded-md px-2 py-1 text-sm">
        </td>
        <td class="py-2 pr-2">
            <input type="number" min="1" value="1" name="items[__i__][qty]" required
                   class="qty w-full border rounded-md px-2 py-1 text-sm">
        </td>
        <td class="py-2 pr-2">
            <select name="items[__i__][unit]" class="w-full border rounded-md px-2 py-1 text-sm">
                <option>pc</option><option>seat</option><option>license</option><option>box</option>
            </select>
        </td>
        <td class="py-2 pr-2">
            <input type="number" min="0" step="0.01" value="0.00" name="items[__i__][unit_price]" required
                   class="price w-full border rounded-md px-2 py-1 text-sm">
        </td>
        <td class="py-2 pr-2 row-total text-sm">$0.00</td>
        <td class="py-2 text-center">
            <button type="button" onclick="removeRow(this)" class="text-red-500">🗑</button>
        </td>
    </tr>
</template>

<script>
    let rowCount = 0;

    function addRow() {
        const tpl = document.getElementById('rowTemplate').innerHTML.replaceAll('__i__', rowCount);
        const tr = document.createElement('tbody');
        tr.innerHTML = tpl;
        document.getElementById('itemsBody').appendChild(tr.firstElementChild);
        rowCount++;
        reindexRows();
        bindRow(document.querySelectorAll('.item-row')[document.querySelectorAll('.item-row').length - 1]);
        recalc();
    }

    function removeRow(btn) {
        btn.closest('tr').remove();
        reindexRows();
        recalc();
    }

    function reindexRows() {
        document.querySelectorAll('.item-row').forEach((row, i) => {
            row.querySelector('.row-index').textContent = i + 1;
        });
    }

    function bindRow(row) {
        row.querySelectorAll('.qty, .price').forEach(el => el.addEventListener('input', recalc));
    }

    function recalc() {
        let subtotal = 0;
        document.querySelectorAll('.item-row').forEach(row => {
            const qty = parseFloat(row.querySelector('.qty').value) || 0;
            const price = parseFloat(row.querySelector('.price').value) || 0;
            const total = qty * price;
            row.querySelector('.row-total').textContent = '$' + total.toFixed(2);
            subtotal += total;
        });
        document.getElementById('sumSubtotal').textContent = '$' + subtotal.toFixed(2);
        document.getElementById('sumTax').textContent = '$0.00';
        document.getElementById('sumTotal').textContent = '$' + subtotal.toFixed(2);
    }

    // Start with two blank rows, like the reference screen
    addRow();
    addRow();
</script>
@endsection
