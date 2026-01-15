@extends('scholars-admin.layout')

@section('content')
<div class="container py-4">
    <h3 class="mb-4">Time In / Time Out</h3>

    <div class="p-4 mb-4 shadow-sm card">
        <h5>Select Action</h5>
        <div class="gap-3 d-flex">
            <button id="timeInBtn" class="btn btn-success">Time In</button>
            <button id="timeOutBtn" class="btn btn-danger">Time Out</button>
        </div>
    </div>

    <div class="p-4 shadow-sm card">
        <h5>Scanner Status</h5>
        <div id="scanner" class="mb-3 text-center scanner">
            <i class="bi bi-fingerprint" style="font-size: 4rem; color: #6c757d;"></i>
            <p id="scannerText" class="mt-2 text-muted">Waiting for scan...</p>
        </div>

        <div id="scanStatus"></div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const scannerText = document.getElementById('scannerText');
    const statusBox = document.getElementById('scanStatus');
    const scannerIcon = document.querySelector('#scanner i');

    async function startScan(action) {
        scannerIcon.style.color = '#0d6efd';
        scannerText.textContent = 'Scanning...';
        statusBox.innerHTML = '';

        try {
            const resp = await fetch('/api/fingerprint/scan', {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ action: action })
            });

            const data = await resp.json();

       if (data.ok) {
    scannerText.textContent = '✅ Scan successful';
    scannerIcon.style.color = 'green';
    statusBox.innerHTML = `<div class="alert alert-success">
        ${action.replace('_',' ')} recorded at ${data.datetime}
    </div>`;
}
 else {
                scannerText.textContent = '❌ Scan failed';
                scannerIcon.style.color = 'red';
                statusBox.innerHTML = `<div class="alert alert-danger">${data.error}</div>`;
            }
        } catch (err) {
            console.error(err);
            scannerText.textContent = '❌ Connection failed';
            scannerIcon.style.color = 'orange';
            statusBox.innerHTML = `<div class="alert alert-warning">Cannot reach scanner</div>`;
        }
    }

    document.getElementById('timeInBtn').addEventListener('click', () => startScan('time_in'));
    document.getElementById('timeOutBtn').addEventListener('click', () => startScan('time_out'));
});
</script>


<style>
.scanner {
    border: 2px dashed #6c757d;
    border-radius: 12px;
    padding: 2rem;
    transition: border-color 0.3s;
}
</style>
@endsection
