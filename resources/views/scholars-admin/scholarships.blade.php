@extends('scholars-admin.layout')

@section('content')

<header class="custom-header">
    <div class="header-left">

        <!-- SIDE BUTTON MOVED HERE -->
<button type="submit" class="side-button header-btn">
    <img src="{{ asset('images/Side.png') }}" alt="Side" height="30">
</button>


        <div class="logo">
            <img src="{{ asset('images/NU_Logo.png') }}" alt="NU Logo" height="30">
            <span>Student Developments and Activities Office</span>
        </div>
    </div>

    <div class="header-right-group">
        <p>Welcome, {{ session('scholars_admin.first_name') }}!</p>
    </div>
</header>

  

@if(session('success'))
    <p style="color:green">{{ session('success') }}</p>
@endif


<!-- Filter Bar -->
<div class="p-3 mb-3 bg-white border rounded shadow-sm d-flex align-items-center justify-content-between">
  <div class="gap-2 d-flex align-items-center">
      <button type="button" class="btn btn-primary" onclick="openAddModal()">
        <i class="bi bi-plus-circle me-1"></i> Add Scholarship Type
      </button>
  </div> 
</div>


<!-- TABLE -->
<div id="Scholarship" class="px-5 py-4 container-fluid">

  <div class="rounded shadow-sm table-responsive">
    <table id="scholarshipsTable">
      <thead>
        <tr class="table-title-row">
          <th colspan="5" class="py-3 text-center fw-bold fs-5">Scholarship Type List</th>
        </tr>
        <tr>
          <th>Name</th>
          <th>GWA Requirement</th>
          <th>Grade Cutoff</th>
          <th>Scholarship Agreement PDF</th>
          <th>Action</th>
        </tr>
      </thead>

      <tbody>
        @foreach($types as $id => $type)
        <tr>
          <td class="text-center">{{ $type['scholarship_name'] }}</td>
          <td class="text-center">≥ {{ $type['grade_requirement'] }}</td>
          <td class="text-center">< {{ $type['eligibility_cutoff'] }}</td>

          <td class="text-center">
            @if(isset($type['requirements_pdf']))
              <a href="{{ $type['requirements_pdf'] }}" target="_blank" class="text-primary fw-semibold">
                View PDF
              </a>
            @else
              <span class="text-muted">No PDF</span>
            @endif
          </td>

          <td>
            <div class="d-flex justify-content-center">
<button class="btn btn-secondary ms-2"
        type="button"
        onclick='openManageModal("{{ $id }}", {!! json_encode($type) !!})'>
    <i class="bi bi-pencil-square me-1"></i> Edit
</button>
            </div>
          </td>

        </tr>
        @endforeach
      </tbody>
    </table>
  </div>

</div>



<!-- ---------------------------------------- -->
<!-- BOOTSTRAP ADD SCHOLARSHIP MODAL          -->
<!-- ---------------------------------------- -->
<div class="modal fade" id="addModal" tabindex="-1">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <div class="shadow modal-content rounded-4">

      <div class="modal-header">
        <h5 class="modal-title fw-bold">Add Scholarship Type</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <form method="POST" action="{{ route('scholars-admin.scholarships.store') }}" enctype="multipart/form-data">
        @csrf

        <div class="modal-body">

          <div class="mb-3">
            <label class="form-label">Scholarship Name</label>
            <input type="text" name="scholarship_name" class="rounded form-control" required>
          </div>

          <div class="mb-3">
            <label class="form-label">GWA Requirement</label>
            <input type="number" step="0.01" name="grade_requirement" class="rounded form-control" required>
          </div>

          <div class="mb-3">
            <label class="form-label">Grade Cutoff</label>
            <input type="number" step="0.01" name="eligibility_cutoff" class="rounded form-control" required>
          </div>

          <div class="mb-3">
            <label class="form-label">Scholarship PDF</label>
            <input type="file" name="requirements_pdf" accept="application/pdf" class="rounded form-control" required>
          </div>

        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Add</button>
        </div>

      </form>

    </div>
  </div>
</div>



<!-- ---------------------------------------- -->
<!-- BOOTSTRAP EDIT SCHOLARSHIP MODAL         -->
<!-- ---------------------------------------- -->
<div class="modal fade" id="manageModal" tabindex="-1">
  <div class="modal-dialog modal-xxl modal-dialog-centered">
    <div class="shadow modal-content rounded-4">

      <div class="modal-header">
        <h5 class="modal-title fw-bold">Edit Scholarship</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <form id="manageForm" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="modal-body">
          <div class="row g-4">

            <!-- LEFT -->
            <div class="col-md-6">

              <div class="mb-3">
                <label class="form-label">Scholarship Name</label>
                <input type="text" id="edit_name" name="scholarship_name" class="rounded form-control" required>
              </div>

              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label">GWA Requirement</label>
                  <input type="number" step="0.01" id="edit_grade" name="grade_requirement" class="rounded form-control" required>
                </div>

                <div class="col-md-6">
                  <label class="form-label">Grade Cutoff</label>
                  <input type="number" step="0.01" id="edit_cutoff" name="eligibility_cutoff" class="rounded form-control" required>
                </div>
              </div>

            </div>

            <!-- RIGHT -->
            <div class="col-md-6">

              <p class="mb-1 fw-bold">Current PDF:</p>
              <a id="edit_pdf" href="#" target="_blank" class="mb-2 text-primary fw-semibold d-block"></a>

              <div id="pdfPreview" class="border rounded d-none" style="height:350px;">
                <iframe id="pdfFrame" src="" width="100%" height="100%" class="rounded"></iframe>
              </div>

              <div class="mt-3">
                <label class="form-label">Update PDF (optional)</label>
                <input type="file" name="requirements_pdf" accept="application/pdf" class="rounded form-control">
              </div>

            </div>

          </div>
        </div>

        <div class="modal-footer d-flex justify-content-between">
          <button type="button" class="btn btn-danger" onclick="confirmDelete()">Delete</button>

          <div>
            <button type="submit" class="btn btn-primary">Update</button>
          </div>
        </div>

      </form>

      <form id="deleteForm" method="POST" class="d-none">
        @csrf
        @method('DELETE')
      </form>

    </div>
  </div>
</div>




<!-- ---------------------------------------- -->
<!-- JAVASCRIPT FOR MODALS                    -->
<!-- ---------------------------------------- -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>

function openAddModal() {
    let modal = new bootstrap.Modal(document.getElementById('addModal'));
    modal.show();
}


function openManageModal(id, type) {

    document.getElementById('manageForm').action = '/scholars-admin/scholarships/' + id;
    document.getElementById('deleteForm').action = '/scholars-admin/scholarships/' + id;

    document.getElementById('edit_name').value = type.scholarship_name ?? '';
    document.getElementById('edit_grade').value = type.grade_requirement ?? '';
    document.getElementById('edit_cutoff').value = type.eligibility_cutoff ?? '';

    if (type.requirements_pdf) {
        document.getElementById('edit_pdf').href = type.requirements_pdf;
        document.getElementById('edit_pdf').innerText = "View PDF";
        document.getElementById('pdfFrame').src = type.requirements_pdf;

        document.getElementById('pdfPreview').classList.remove("d-none");
    } else {
        document.getElementById('edit_pdf').innerText = "No PDF Available";
        document.getElementById('edit_pdf').removeAttribute("href");

        document.getElementById('pdfPreview').classList.add("d-none");
    }

    let modal = new bootstrap.Modal(document.getElementById('manageModal'));
    modal.show();
}

function confirmDelete() {
    if (confirm('Delete this scholarship?')) {
        document.getElementById('deleteForm').submit();
    }
}

</script>

@endsection
