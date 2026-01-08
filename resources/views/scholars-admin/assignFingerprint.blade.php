@extends('scholars-admin.layout')

@section('content')

<div class="container py-4">

    <h3 class="mb-4">Assign Fingerprint</h3>

    <!-- Scholar Info -->
    <div class="p-4 mb-4 shadow-sm card">
        <h5>Scholar Information</h5>

        <p><strong>Student No:</strong> {{ $selectedScholar['student_no'] }}</p>
        <p><strong>Name:</strong>
            {{ $selectedScholar['first_name'] }}
            {{ $selectedScholar['middle_name'] ?? '' }}
            {{ $selectedScholar['last_name'] }}
        </p>
        <p><strong>School:</strong> {{ $selectedScholar['school'] }}</p>
        <p><strong>Program:</strong> {{ $selectedScholar['program'] }}</p>
    </div>

    <!-- Fingerprint Section -->
    <div class="p-4 shadow-sm card">
        <h5>Fingerprint Registration</h5>

        <p class="mb-3 text-muted">Place finger on the scanner and click start.</p>

        <!-- Fingerprint Scanner Visual -->
        <div id="scanner" class="mb-3 text-center scanner">
            <i class="bi bi-fingerprint" style="font-size: 4rem; color: #6c757d;"></i>
            <p id="scannerText" class="mt-2 text-muted">Waiting for scan...</p>
        </div>

        <!-- Status Messages -->
        <div id="scanStatus" class="mb-3"></div>

        <!-- Buttons -->
        <div class="d-flex align-items-center">
            <button id="startScanBtn" class="btn btn-primary me-2">
                <span id="btnText">Start Scan</span>
                <span id="btnSpinner" class="spinner-border spinner-border-sm ms-2 d-none"></span>
            </button>

            <button id="retryBtn" class="btn btn-warning d-none">Retry</button>

            <a href="{{ route('scholars-admin.scholars_accounts') }}" class="btn btn-secondary ms-auto">
                Submit
            </a>
        </div>
    </div>

</div>

{{-- UI Simulation Script --}}
<script>
document.addEventListener('DOMContentLoaded', function () {

    const startBtn = document.getElementById('startScanBtn');
    const retryBtn = document.getElementById('retryBtn');
    const statusBox = document.getElementById('scanStatus');
    const spinner = document.getElementById('btnSpinner');
    const btnText = document.getElementById('btnText');
    const scanner = document.getElementById('scanner');
    const scannerText = document.getElementById('scannerText');

    function setScanningState(isScanning) {
        startBtn.disabled = isScanning;
        spinner.classList.toggle('d-none', !isScanning);
        btnText.textContent = isScanning ? 'Scanning...' : 'Start Scan';
        scanner.classList.toggle('scanning', isScanning);
        scannerText.textContent = isScanning ? 'Scanning in progress...' : 'Waiting for scan...';
    }

    function showStatus(type, message) {
        let cls = 'alert-info';
        if (type === 'success') cls = 'alert-success';
        if (type === 'error') cls = 'alert-danger';

        statusBox.innerHTML = `<div class="alert ${cls}">${message}</div>`;
    }

    startBtn.addEventListener('click', function () {
        retryBtn.classList.add('d-none');
        statusBox.innerHTML = '';
        setScanningState(true);

        // 🔧 Simulation only
        setTimeout(() => {
            const success = Math.random() > 0.3; // 70% chance
            setScanningState(false);

            if (success) {
                scannerText.textContent = '✅ Fingerprint registered!';
                scanner.querySelector('i').style.color = 'green';
                showStatus('success', 'Fingerprint successfully registered!');
            } else {
                scannerText.textContent = '❌ Scan failed!';
                scanner.querySelector('i').style.color = 'red';
                showStatus('error', 'Scan failed. Finger not detected. Please try again.');
                retryBtn.classList.remove('d-none');
            }
        }, 2500);
    });

    retryBtn.addEventListener('click', function () {
        retryBtn.classList.add('d-none');
        statusBox.innerHTML = '';
        scanner.querySelector('i').style.color = '#6c757d';
        scannerText.textContent = 'Waiting for scan...';
    });

});
</script>

{{-- Optional scanner CSS --}}
<style>
.scanner {
    border: 2px dashed #6c757d;
    border-radius: 12px;
    padding: 2rem;
    transition: border-color 0.3s;
}

.scanner.scanning {
    border-color: #0d6efd;
    animation: pulse 1.2s infinite;
}

@keyframes pulse {
    0% { box-shadow: 0 0 0 0 rgba(13,110,253,0.4); }
    70% { box-shadow: 0 0 0 15px rgba(13,110,253,0); }
    100% { box-shadow: 0 0 0 0 rgba(13,110,253,0); }
}
</style>

@endsection
