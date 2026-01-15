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
            <div class="gap-2 d-flex align-items-center">


                <button id="startScanBtn" type="button" class="btn btn-primary">
                    <span id="btnText">Start Scan</span>
                    <span id="btnSpinner" class="spinner-border spinner-border-sm ms-2 d-none"></span>
                </button>


                <button id="retryBtn" type="button" class="btn btn-warning d-none">
                    Retry
                </button>


                <form id="assignFingerprintForm" method="POST"
                    action="{{ route('scholars-admin.assign-fingerprint.store', $selectedScholar['student_no']) }}"
                    class="gap-2 d-flex align-items-center">


                    @csrf


                    <!-- REQUIRED to locate exact scholar in Firebase -->




                    <!-- Slot ID from scanner -->
         <input id="slotId" type="hidden" name="fingerprint_slot_id" required>



                    <button id="submitBtn" type="submit" class="btn btn-success" disabled>
                        Save Slot
                    </button>


                </form>
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


            startBtn.addEventListener('click', async function (e) {
                e.preventDefault();
                retryBtn.classList.add('d-none');
                statusBox.innerHTML = '';
                setScanningState(true);


                try {
                    const resp = await fetch('/api/fingerprint/enroll', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({}),
                    });


                    const data = await resp.json();
                    setScanningState(false);


                    if (data.ok === false && data.error === "already_registered") {
                        scannerText.textContent = '❌ Already enrolled!';
                        scanner.querySelector('i').style.color = 'red';
                        showStatus('error', `Fingerprint already exists (ID: ${data.id}). Use different finger.`);
                        retryBtn.classList.remove('d-none');
                        return;
                    }


if (data.ok === true) {
    scannerText.textContent = '✅ Success!';
    scanner.querySelector('i').style.color = 'green';
    
    // Only show generic success message, no slot ID
    showStatus('success', `Fingerprint enrolled successfully.`);
    
    // Set the hidden slot ID for form submission
    document.getElementById('submitBtn').disabled = false;
    document.getElementById('slotId').value = data.id;
    return;
}



                    // enroll_failed - most common
                    scannerText.textContent = '❌ Scan failed';
                    scanner.querySelector('i').style.color = 'orange';
                    showStatus('warning', 'Poor finger placement. **Use SAME finger, SAME position/pressure.** Try again.');
                    retryBtn.classList.remove('d-none');


                } catch (err) {
                    setScanningState(false);
                    showStatus('error', 'Connection failed.');
                    retryBtn.classList.remove('d-none');
                }
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
            0% {
                box-shadow: 0 0 0 0 rgba(13, 110, 253, 0.4);
            }


            70% {
                box-shadow: 0 0 0 15px rgba(13, 110, 253, 0);
            }


            100% {
                box-shadow: 0 0 0 0 rgba(13, 110, 253, 0);
            }
        }
    </style>


@endsection



