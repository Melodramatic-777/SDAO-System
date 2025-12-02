@extends('volunteers-admin.layout')

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
        <p>Welcome, {{ session('volunteers_admin.first_name') }}!</p>
    </div>
</header>



  <main class="p-4 container-fluid">
    <h2 class="mb-4 text-center">Opportunities by Office</h2>

    <div class="row g-4">
      @forelse ($groupedOpportunities as $office => $opportunities)
        <div class="col-md-4">
          <a href="{{ route('volunteers-admin.office-opportunities', ['office' => urlencode($office)]) }}" class="text-decoration-none text-dark">
            <div class="folder-card">
              <div class="folder-icon">
                <i class="bi bi-folder-fill"></i>
              </div>
              <div class="folder-info">
                <div class="folder-title">{{ $office }}</div>
                <div class="folder-count">{{ count($opportunities) }} Opportunities</div>
              </div>
            </div>
          </a>
        </div>
      @empty
        <p class="text-center text-muted">No opportunities found.</p>
      @endforelse
    </div>
  </main>

@endsection

