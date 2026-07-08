@extends('layouts.app')

@section('title', 'Route for Approval')

@section('content')
<div class="text-xs text-gray-400 mb-3 flex items-center gap-1">
    <a href="{{ route('requisitions.create') }}" class="text-brand">Create requisition</a>
    <span>&gt;</span>
    <span class="text-brand">{{ $requisition->code }}</span>
    <span>&gt;</span>
    <span>Route for Approval</span>
</div>

<div class="grid grid-cols-3 gap-6">

    <div class="col-span-2 bg-white rounded-lg border p-6">
        <h1 class="text-xl font-bold mb-1">Route Requisition for Approval</h1>
        <p class="text-sm text-gray-500 mb-6">Select approvers and define the approval flow for this requisition</p>

        <form action="{{ route('requisitions.route.store', $requisition->id) }}" method="POST" id="routeForm">
            @csrf

            <h2 class="font-semibold mb-3">Approval workflow</h2>
            <p class="text-xs text-gray-400 mb-4">Define the people who need to review and approve this requisition</p>

            <div id="stepsList" class="space-y-4"></div>

            <button type="button" onclick="addStep()" class="text-brand text-sm font-medium mt-4">+ Add Approval Step</button>

            <div class="mt-8">
                <h3 class="text-sm font-semibold mb-2">Additional Options</h3>
                <label class="flex items-center gap-2 text-sm mb-1">
                    <input type="radio" name="workflow_type" value="sequential" checked onchange="renderPreview()">
                    <span><strong>Sequential Type</strong> &mdash; Approvers review one at a time in order</span>
                </label>
                <label class="flex items-center gap-2 text-sm">
                    <input type="radio" name="workflow_type" value="parallel" onchange="renderPreview()">
                    <span><strong>Parallel</strong> &mdash; Approval review simultaneously</span>
                </label>
            </div>

            <div class="flex justify-end gap-3 mt-8">
                <a href="{{ route('requisitions.create') }}" class="px-4 py-2 rounded-md border text-sm">Cancel</a>
                <button type="submit" class="px-4 py-2 rounded-md bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium">
                    Submit for approval
                </button>
            </div>
        </form>
    </div>

    <div class="space-y-6">
        <div class="bg-white rounded-lg border p-5">
            <h3 class="font-semibold mb-4">Approval Workflow Preview</h3>
            <div id="previewList" class="space-y-4 text-sm"></div>
        </div>

        <div class="bg-white rounded-lg border p-5">
            <h3 class="font-semibold mb-3">Requisiton summary</h3>
            <div class="text-sm space-y-2">
                <div class="flex justify-between"><span class="text-gray-500">Requisition ID</span><span>{{ $requisition->code }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Requestor</span><span>{{ $requisition->requestor }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Department</span><span>{{ $requisition->department }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Date created</span><span>{{ \Carbon\Carbon::parse($requisition->created_at)->format('M d, Y') }}</span></div>
                <div class="flex justify-between font-semibold"><span>Total Amount</span><span>${{ number_format($requisition->total, 2) }}</span></div>
            </div>
        </div>
    </div>
</div>

<template id="stepTemplate">
    <div class="step-row border rounded-md p-4 flex items-start gap-3">
        <div class="w-6 h-6 rounded-full bg-brand text-white text-xs flex items-center justify-center step-num shrink-0 mt-1">1</div>
        <div class="flex-1 grid grid-cols-2 gap-3">
            <div>
                <input class="role-label w-full border rounded-md px-2 py-1 text-sm font-medium" placeholder="Approval step name (e.g. Manager Approval)">
                <label class="text-xs text-gray-400 flex items-center gap-1 mt-1">
                    <input type="checkbox" class="required-check" checked> Required
                </label>
            </div>
            <div>
                <input class="approver-name w-full border rounded-md px-2 py-1 text-sm" placeholder="Approver name">
            </div>
        </div>
        <button type="button" onclick="removeStep(this)" class="text-red-500 mt-1">🗑</button>
    </div>
</template>

<script>
    const existingSteps = {{ json_encode(
        collect($requisition->approval_steps ?? [])
            ->map(fn($s) => [
                'role_label' => $s->role_label,
                'approver_name' => $s->approver_name,
                'required' => (bool) $s->required,
            ])
            ->values()
            ->all()
    ) }};

    const defaultSteps = [
        { role_label: 'Manager Approval', approver_name: 'Marketing Manager', required: true },
        { role_label: 'Department Head Approval', approver_name: 'Director of Marketing', required: true },
        { role_label: 'Finance Approval', approver_name: 'Finance Manager', required: true },
    ];

    function addStep(data = null) {
        const tpl = document.getElementById('stepTemplate').content.cloneNode(true);
        const row = tpl.querySelector('.step-row');
        if (data) {
            row.querySelector('.role-label').value = data.role_label || '';
            row.querySelector('.approver-name').value = data.approver_name || '';
            row.querySelector('.required-check').checked = data.required !== false;
        }
        row.querySelectorAll('input').forEach(el => el.addEventListener('input', renderPreview));
        document.getElementById('stepsList').appendChild(row);
        renumberSteps();
        renderPreview();
    }

    function removeStep(btn) {
        btn.closest('.step-row').remove();
        renumberSteps();
        renderPreview();
    }

    function renumberSteps() {
        document.querySelectorAll('.step-row').forEach((row, i) => {
            row.querySelector('.step-num').textContent = i + 1;
        });
    }

    function renderPreview() {
        const list = document.getElementById('previewList');
        list.innerHTML = '';
        const wfType = document.querySelector('input[name=workflow_type]:checked').value;
        document.querySelectorAll('.step-row').forEach((row, i) => {
            const name = row.querySelector('.role-label').value || `Step ${i + 1}`;
            const approver = row.querySelector('.approver-name').value || 'Unassigned';
            list.insertAdjacentHTML('beforeend', `
                <div class="flex items-center gap-3">
                    <div class="w-6 h-6 rounded-full bg-brand text-white text-xs flex items-center justify-center">${i + 1}</div>
                    <div class="flex-1">
                        <div class="font-medium">${approver}</div>
                        <div class="text-xs text-gray-400">${name}</div>
                    </div>
                    <span class="text-xs px-2 py-0.5 rounded bg-orange-100 text-orange-600">${wfType === 'parallel' && i > 0 ? 'Pending' : 'Pending'}</span>
                </div>
            `);
        });
    }

    // Bootstrap: existing steps if this requisition already has some, else the 3-step default matching the reference screen
    (existingSteps.length ? existingSteps : defaultSteps).forEach(s => addStep(s));

    // Build the hidden inputs on submit so the payload matches what RequisitionController@routeStore expects
    document.getElementById('routeForm').addEventListener('submit', function () {
        document.querySelectorAll('.step-row').forEach((row, i) => {
            const wrap = document.createElement('div');
            wrap.innerHTML = `
                <input type="hidden" name="steps[${i}][role_label]" value="${row.querySelector('.role-label').value}">
                <input type="hidden" name="steps[${i}][approver_name]" value="${row.querySelector('.approver-name').value}">
                <input type="hidden" name="steps[${i}][required]" value="${row.querySelector('.required-check').checked ? 1 : 0}">
            `;
            this.appendChild(wrap);
        });
    });
</script>
@endsection
