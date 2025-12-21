@extends('volunteers-admin.layout')

@section('content')

<div class="container py-4">
    <h2 class="text-primary fw-bold">Contact Messages</h2>

    @php
        $statuses = ['all','new','replied','pending','other'];
        $statusCounts = [
            'all' => count($messages ?? []),
            'new' => collect($messages)->filter(fn($m) => ($m['status'] ?? 'new') === 'new')->count(),
            'replied' => collect($messages)->filter(fn($m) => ($m['status'] ?? '') === 'replied')->count(),
            'pending' => collect($messages)->filter(fn($m) => ($m['status'] ?? '') === 'pending')->count(),
            'other' => collect($messages)->filter(fn($m) => !in_array($m['status'] ?? '', ['new','replied','pending']))->count(),
        ];
    @endphp

    {{-- Status Filter Tabs --}}
    <div class="gap-4 mb-4 status-filter d-flex">
        @foreach($statuses as $status)
            @php
                $badgeClass = match($status) {
                    'all' => 'bg-secondary',
                    'new' => 'badge bg-primary ms-1',
                    'replied' => 'bg-success',
                    'pending' => 'bg-warning text-dark',
                    default => 'bg-secondary'
                };
                $activeClass = $status == 'all' ? 'active' : '';
            @endphp
            <span class="status-tab {{ $activeClass }}" data-status="{{ $status }}">
                {{ ucfirst($status) }}
                <span class="badge ms-1 {{ $badgeClass }}">{{ $statusCounts[$status] }}</span>
            </span>
        @endforeach
    </div>

    @if(empty($messages))
        <p>No message yet.</p>
    @else
        <table class="table mx-auto text-center align-middle table-bordered" id="messagesTable">
            <thead>
                <tr class="table-header-row">
                    <th>Name</th>
                    <th>Email</th>
                    <th>Subject</th>
                    <th>Message</th>
                    <th>Status</th>
                    <th>Date Submitted</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($messages as $id => $msg)
                    @php $status = $msg['status'] ?? 'new'; @endphp
                    <tr data-status="{{ $status }}">
                        <td>{{ $msg['first_name'] }} {{ $msg['last_name'] }}</td>
                        <td>{{ $msg['email'] }}</td>
                        <td>{{ $msg['subject'] ?? '—' }}</td>
                        <td>{{ Str::limit($msg['message'], 40) }}</td>
                        <td>
                            <span class="badge 
                                {{ $status === 'new' ? 'bg-warning text-dark' : ($status === 'replied' ? 'bg-success' : ($status === 'pending' ? 'bg-info text-dark' : 'bg-secondary')) }}">
                                {{ ucfirst($status) }}
                            </span>
                        </td>
                        <td>{{ $msg['submitted_at'] ?? '—' }}</td>
                        <td>
                            <button 
                                class="icon-btn" 
                                data-bs-toggle="modal"
                                data-bs-target="#messageModal"
                                data-id="{{ $id }}"
                                data-first_name="{{ $msg['first_name'] }}"
                                data-last_name="{{ $msg['last_name'] }}"
                                data-email="{{ $msg['email'] }}"
                                data-subject="{{ $msg['subject'] ?? '—' }}"
                                data-message="{{ $msg['message'] }}"
                                data-status="{{ $status }}"
                                data-submitted_at="{{ $msg['submitted_at'] ?? '—' }}"
                                data-replies='@json($msg['replies'] ?? [])'>
                                <i class="bi bi-eye fs-5"></i>
                            </button>
                        </td>
                    </tr>
                @endforeach

                {{-- Placeholder row when no messages are visible --}}
                <tr id="noMessagesRow" style="display: none;">
                    <td colspan="7">No message yet.</td>
                </tr>
            </tbody>
        </table>
    @endif
</div>

{{-- Bootstrap Modal --}}
<div class="modal fade" id="messageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">View Message</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="modal-content">
                {{-- Content populated via JS --}}
            </div>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('.icon-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        const replies = JSON.parse(btn.dataset.replies || '[]');
        let repliesHtml = '';

        if(replies.length > 0){
            repliesHtml = '<h5 class="mt-3 text-primary">Previous Replies</h5>';
            replies.forEach(reply => {
                repliesHtml += `<div class="p-3 mb-2 border rounded bg-light">
                                    <p style="white-space:pre-wrap;">${reply.message}</p>
                                    <small>Replied at: ${reply.replied_at ?? '—'}</small>
                                </div>`;
            });
        }

        // Map status to Bootstrap badge classes
        const statusClass = btn.dataset.status === 'new' ? 'bg-warning text-dark' :
                            btn.dataset.status === 'replied' ? 'bg-success' :
                            btn.dataset.status === 'pending' ? 'bg-info text-dark' :
                            'bg-secondary text-white';

        const modalHtml = `
            <p><strong>Name:</strong> ${btn.dataset.first_name} ${btn.dataset.last_name}</p>
            <p><strong>Email:</strong> ${btn.dataset.email}</p>
            <p><strong>Subject:</strong> ${btn.dataset.subject}</p>
            <p><strong>Message:</strong></p>
            <p style="white-space:pre-wrap; border:1px solid #ccc; padding:10px; border-radius:5px; background:white;">
                ${btn.dataset.message}
            </p>
            <p><strong>Status:</strong> <span class="badge ${statusClass}">${btn.dataset.status}</span></p>
            <p><strong>Submitted At:</strong> ${btn.dataset.submitted_at}</p>
            ${repliesHtml}
            <div class="mt-3">
                <h5 class="modal-title">Reply</h5>
                <form method="POST" action="/volunteers-admin/contactUs/${btn.dataset.id}/reply">
                    @csrf
                    <textarea name="reply_message" required class="mb-2 form-control" rows="3" placeholder="Type your reply here..."></textarea>
                    <div style="display:flex; justify-content:flex-end;">
                        <button type="submit" style="background-color: #28a745; color: white; border: none; padding: 0.5rem 1rem; border-radius: 0.25rem; cursor: pointer;">
                            Send Reply
                        </button>
                    </div>
                </form>
            </div>
        `;
        document.getElementById('modal-content').innerHTML = modalHtml;
    });
});

// Status Filter
document.querySelectorAll('.status-tab').forEach(tab => {
    tab.addEventListener('click', () => {
        const status = tab.dataset.status;
        const rows = document.querySelectorAll('#messagesTable tbody tr:not(#noMessagesRow)');
        let anyVisible = false;

        rows.forEach(row => {
            if(status === 'all' || row.dataset.status === status){
                row.style.display = '';
                anyVisible = true;
            } else {
                row.style.display = 'none';
            }
        });

        // Show placeholder if no rows visible
        document.getElementById('noMessagesRow').style.display = anyVisible ? 'none' : '';

        // Update active tab style
        document.querySelectorAll('.status-tab').forEach(t => t.classList.remove('active'));
        tab.classList.add('active');
    });
});
</script>

@endsection
