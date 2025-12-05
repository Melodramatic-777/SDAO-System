@extends('volunteers-offices.layout')

@section('content')



<main class="p-4 container-fluid">
  <div class="row g-4">
    <div class="table-responsive">
<table class="table mx-auto text-center align-middle table-bordered">
  <thead>
    <tr class="table-title-row">
      <h2 class="mb-4 text-center">Volunteer Opportunities History</h2>
    </tr>
    <tr class="table-header-row">
      <th>#</th>
      <th>Date Submitted</th>
      <th>Title</th>
      <th>Submitted By</th>
      <th>View</th>
    </tr>
  </thead>
  <tbody>
    @forelse ($opportunities as $index => $opportunity)
      <tr class="table-header-row">
        <td>{{ $index + 1 }}</td>
              <td>
        {{ !empty($opportunity['submitted_at']) ? \Carbon\Carbon::parse($opportunity['submitted_at'])->format('F j, Y') : 'N/A' }}
      </td>  
        <td>{{ $opportunity['title'] }}</td>
        <td>{{ $opportunity['submitted_by_name'] ?? 'N/A' }}</td>

<td>
          <button 
            class="icon-btn"
            data-bs-toggle="modal"
            data-bs-target="#opportunityModal{{ $opportunity['opportunity_id'] }}"
            title="View Opportunity">
            <i class="bi bi-eye fs-5"></i>
          </button>
        </td>
      </tr>
    @empty
      <tr><td colspan="5" class="text-center text-muted">No opportunities found.</td></tr>
    @endforelse
  </tbody>
</table>

    </div>
  </div>

  {{-- ✅ All Modals --}}
  @foreach ($opportunities as $opportunity)
  <div class="modal fade" id="opportunityModal{{ $opportunity['opportunity_id'] }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered custom-wide-modal">
      <div class="modal-content" style="border-radius: 8px; padding: 1.5rem;">
        <button type="button" class="modal-close-x" data-bs-dismiss="modal">×</button>

        <div class="modal-body">
          {{-- Title --}}
          <h3 class="mb-4 text-center fw-bold">{{ $opportunity['title'] }}</h3>

          <hr>

          <div class="row">
            {{-- Details --}}
            <div class="col-md-4">
              <h6 class="fw-bold">Details</h6>
              <p><strong>Location:</strong> {{ $opportunity['location'] ?? 'N/A' }}</p>
              <p><strong>Submitted by:</strong> {{ $opportunity['submitted_by_name'] ?? 'N/A' }}</p>


@php 
    $s = $opportunity['schedule'][0] ?? null; 
@endphp

<h6 class="fw-bold">Schedule</h6>

<table class="table mt-2 text-center align-middle table-bordered">
    <thead class="table-light">
        <tr>
            <th>Date</th>
            <th>Start Time</th>
            <th>End Time</th>
            <th>Volunteers Needed</th>
            <th>Food Provided</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            {{-- DATE --}}
            <td>
                @if($s && !empty($s['date']))
                    {{ \Carbon\Carbon::parse($s['date'])->format('F j, Y') }}
                @else
                    N/A
                @endif
            </td>

            {{-- START TIME --}}
            <td>
                @if($s && !empty($s['start_time']))
                    {{ \Carbon\Carbon::parse($s['start_time'])->format('g:i A') }}
                @else
                    N/A
                @endif
            </td>

            {{-- END TIME --}}
            <td>
                @if($s && !empty($s['end_time']))
                    {{ \Carbon\Carbon::parse($s['end_time'])->format('g:i A') }}
                @else
                    N/A
                @endif
            </td>

            {{-- TARGET NUMBER --}}
            <td>{{ $s['target_number'] ?? 'N/A' }}</td>

            {{-- FOOD --}}
            <td>
                @php $food = strtolower($s['food_provided'] ?? 'no'); @endphp
                {{ $food === 'yes' ? 'Yes' : 'No' }}
            </td>
        </tr>
    </tbody>
</table>


            </div>

            {{-- Task Details --}}
            <div class="col-md-4">
              <h6 class="fw-bold">Task Details</h6>
              <ul>
                @foreach(explode("\n", $opportunity['task_details'] ?? '') as $task)
                  @if(trim($task) != '')
                    <li>{{ trim($task) }}</li>
                  @endif
                @endforeach
              </ul>
            </div>

            {{-- Skills Needed --}}
            <div class="col-md-4">
              <h6 class="fw-bold">Skills Needed</h6>
              <ul>
                @foreach(explode("\n", $opportunity['skills_needed'] ?? '') as $skill)
                  @if(trim($skill) != '')
                    <li>{{ trim($skill) }}</li>
                  @endif
                @endforeach
              </ul>
            </div>
          </div>

          <hr>

          {{-- Student Volunteers --}}
          <h5 class="fw-bold">
            Student Volunteers ({{ count($opportunity['participants'] ?? []) }})
          </h5>

          <div class="table-responsive">
<table class="table text-center align-middle table-bordered">
  <thead>
    <tr class="table-header-row">
      <th>Name</th>
      <th>Time In</th>
      <th>Time Out</th>
      <th>Total Hours</th>
      <th>Feedback</th>
    </tr>
  </thead>


              <tbody>
                @forelse($opportunity['participants'] ?? [] as $vol)
                  <tr>
                    <td>{{ $vol['name'] }}</td>
                    <td>{{ $vol['time_in'] ?? 'N/A' }}</td>
                    <td>{{ $vol['time_out'] ?? 'N/A' }}</td>
                    <td>{{ $vol['hours'] ?? 'N/A' }}</td>
                    <td>{{ $vol['feedback'] ?? 'N/A' }}</td>
                  </tr>
                @empty
                  <tr><td colspan="5" class="text-muted">No volunteers yet.</td></tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
  @endforeach
</main>

@endsection

