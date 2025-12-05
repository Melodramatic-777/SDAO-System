@extends('scholars-admin.layout')

@section('content')
  


  <!-- MAIN -->
  <main class="main" role="main">

    <!-- TOPBAR -->
    <div class="topbar">
      <div style="display:flex; gap:12px; align-items:center;">
        <h2 style="margin:0; color:var(--nu-blue);">Dashboard</h2>
        <div style="padding:6px 10px; border-radius:8px; background: linear-gradient(90deg,var(--nu-gold), #e4a000); font-weight:800; color:#062335; font-size:0.9rem;">Academic Year: {{ $recentYear ?? '—' }}</div>
      </div>
    </div>

    <!-- METRICS -->

  <!-- NEW TOP METRICS CARD (MATCHED STYLE) -->
<div class="card" style="padding:18px; margin-bottom:18px;">
    <div class="card-title" style="display:flex; justify-content:space-between; align-items:center;">
        <div style="font-weight:800; font-size:1.2rem; color:var(--muted);">
            Term: {{ $currentTerm ?? '—' }}
        </div>
    </div>

    <div class="status-list" style="margin-top:14px; display:grid; grid-template-columns:repeat(4,1fr); gap:12px;">

        <div class="status-item">
            <div>
                <div class="label">Total Scholars</div>
                <div style="font-size:0.85rem; color:var(--muted)">All programs</div>
            </div>
            <div><span class="pill" style="background:#eef6ff; color:var(--nu-blue);">{{ $total }}</span></div>
        </div>

        <div class="status-item">
            <div>
                <div class="label">Eligible</div>
                <div style="font-size:0.85rem; color:var(--muted)">Eligible this term</div>
            </div>
            <div><span class="pill eligible">{{ $eligible }}</span></div>
        </div>

        <div class="status-item">
            <div>
                <div class="label">Probationary</div>
                <div style="font-size:0.85rem; color:var(--muted)">Require review</div>
            </div>
            <div><span class="pill probation">{{ $probationary }}</span></div>
        </div>

        <div class="status-item">
            <div>
                <div class="label">Revoked</div>
                <div style="font-size:0.85rem; color:var(--muted)">Scholarships revoked</div>
            </div>
            <div><span class="pill revoked">{{ $revoked ?? 0 }}</span></div>
        </div>

    </div>
</div>


<!-- CHARTS + STATUS ROW -->
<section class="row" aria-label="Analytics" style="display:flex; gap:18px; align-items:flex-start;">

    <!-- LEFT: PROGRAM DISTRIBUTION -->
<div class="col-3">
    <div class="card" style="height:100%; display:flex; flex-direction:column;">

        <!-- TITLE + FILTERS -->
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
            <div class="card-title" style="font-weight:700; color:var(--muted);">
                Scholarship Distribution
            </div>
            <div style="display:flex; gap:10px;">
                <select id="scholarYearFilter" class="form-select" style="width:180px;">
                    @foreach($allAcademicYears as $year)
                        <option value="{{ $year }}" {{ $year == $recentYear ? 'selected' : '' }}>A.Y {{ $year }}</option>
                    @endforeach
                </select>
                <select id="scholarTermFilter" class="form-select" style="width:120px;">
                    <option value="all">All Terms</option>
                    @foreach(($termsByYear[strval($recentYear)] ?? []) as $t)
                        <option value="{{ $t }}" {{ $t == $currentTerm ? 'selected' : '' }}>{{ $t }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- Chart -->
        <div style="flex:1; display:flex; flex-direction:column; justify-content:center; align-items:center; padding:10px;">
            <canvas id="scholarChart" style="max-height:360px; width:100%;"></canvas>

            <!-- Summary inside card -->
            <div style="margin-top:12px; text-align:center; color:var(--muted); font-size:0.9rem; line-height:1.4;">
                Academic Year: <strong>{{ $recentYear ?? '—' }}</strong><br>
                Term: <strong>{{ $currentTerm ?? 'All' }}</strong><br>
                Total Scholars: {{ $total }}<br>
                Scholarships are distributed among {{ count($byScholarship) }} categories.<br>
                Most common: <strong>{{ array_search(max($byScholarship), $byScholarship) }}</strong>.
            </div>
        </div>

        <!-- Legend -->
        <div style="display:flex; flex-direction:column; gap:8px; margin-top:10px;">
            @foreach($byScholarship as $type => $count)
                <div style="background:#fbfdff; padding:8px 10px; border-radius:10px; border:1px solid #eef6ff; font-weight:700; color:var(--nu-blue);">
                    {{ strtoupper($type) }}: {{ $count }}
                </div>
            @endforeach
        </div>

    </div>
</div>



    <!-- CENTER: RETENTION BY TERM -->
<div class="col-4">
    <div class="card" style="height:100%; display:flex; flex-direction:column;">

        <!-- Title + Filter -->
        <div class="card-title" style="display:flex; justify-content:space-between; align-items:center;">
            <span>Retention by Term</span>
            <select id="retentionYearFilter" class="form-select" style="width:180px;">
                @foreach($allAcademicYears as $year)
                    <option value="{{ $year }}" {{ $year == $recentYear ? 'selected' : '' }}>A.Y {{ $year }}</option>
                @endforeach
            </select>
        </div>         

        <!-- Chart -->
        <div style="width:100%; padding-top:10px;">
            <canvas id="retentionChart" style="height:300px;"></canvas>

            <!-- Summary inside card -->
            <div style="margin-top:12px; text-align:center; color:var(--muted); font-size:0.9rem; line-height:1.4;">
                Retention for {{ $recentYear ?? 'this academic year' }}:<br>
                Term 1 – {{ $termStats['1']['eligible'] }} eligible, {{ $termStats['1']['probationary'] }} probationary, {{ $termStats['1']['revoked'] }} revoked.<br>
                Term 2 – {{ $termStats['2']['eligible'] }} eligible, {{ $termStats['2']['probationary'] }} probationary, {{ $termStats['2']['revoked'] }} revoked.<br>
                Term 3 – {{ $termStats['3']['eligible'] }} eligible, {{ $termStats['3']['probationary'] }} probationary, {{ $termStats['3']['revoked'] }} revoked.
            </div>
        </div>

    </div>
</div>




    <!-- RIGHT: STATUS SUMMARY -->
    <div class="col-5">
        <div class="card" style="height:100%;">

            <div class="card-title">
                Summary
                <span style="font-weight:800; color:var(--nu-blue);">
                    Academic Year: {{ $recentYear ?? '—' }}
                </span>
            </div>

            <!-- Retention Rate -->
            <div style="
                background:#eef6ff;
                padding:12px;
                border-radius:10px;
                margin-bottom:12px;
                text-align:center;
                border:1px solid #d8e7ff;
            ">
                <div style="color:var(--muted); font-size:0.85rem;">Retention Rate</div>
                <div style="font-size:1.6rem; font-weight:800; color:var(--nu-blue);">
                    {{ $retentionRate }}%
                </div>
            </div>

            <div class="status-list" style="margin-top:12px;">
                <div class="status-item">
                    <div>
                        <div class="label">Eligible</div>
                        <div style="font-size:0.85rem; color:var(--muted)">
                            Students eligible this year
                        </div>
                    </div>
                    <div><span class="pill eligible">{{ $eligible }}</span></div>
                </div>

                <div class="status-item">
                    <div>
                        <div class="label">Probationary</div>
                        <div style="font-size:0.85rem; color:var(--muted)">
                            Require review
                        </div>
                    </div>
                    <div><span class="pill probation">{{ $probationary }}</span></div>
                </div>

                <div class="status-item">
                    <div>
                        <div class="label">Revoked</div>
                        <div style="font-size:0.85rem; color:var(--muted)">
                            Scholarships revoked
                        </div>
                    </div>
                    <div><span class="pill revoked">{{ $revoked ?? 0 }}</span></div>
                </div>
            </div>

        </div>
    </div>

</section>


  </main>

  <!-- SCRIPTS -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
/* ============================================
   Plugin: Draw percentage above bars (BIGGER)
============================================ */
const barValuePlugin = {
  id: 'barValuePlugin',
  afterDatasetsDraw(chart) {
    const { ctx } = chart;
    chart.data.datasets.forEach((dataset, datasetIndex) => {
      const meta = chart.getDatasetMeta(datasetIndex);
      meta.data.forEach((bar, index) => {
        const percent = dataset.percentages[index];
        if (!percent) return;
        ctx.save();
        ctx.font = 'bold 14px Inter';
        ctx.fillStyle = '#1e293b';
        ctx.textAlign = 'center';
        ctx.fillText(percent + "%", bar.x, bar.y - 6);
        ctx.restore();
      });
    });
  }
};

/* ============================================
   Plugin: Percent text INSIDE pie slices
============================================ */
const pieLabelPlugin = {
  id: 'pieLabelPlugin',
  afterDatasetsDraw(chart) {
    const { ctx } = chart;
    chart.data.datasets.forEach((dataset) => {
      const meta = chart.getDatasetMeta(0);
      meta.data.forEach((slice, index) => {
        const value = dataset.data[index];
        const total = dataset.data.reduce((a, b) => a + b, 0);
        const percent = total ? ((value / total) * 100).toFixed(1) : 0;
        const { x, y } = slice.tooltipPosition();
        ctx.save();
        ctx.font = "bold 15px Inter";
        ctx.fillStyle = "#1e293b";
        ctx.textAlign = "center";
        ctx.fillText(`${percent}%`, x, y);
        ctx.restore();
      });
    });
  }
};


/* ============================================
   RETENTION BAR CHART (NO CHANGES)
============================================ */
(function () {
  const ctx = document.getElementById('retentionChart').getContext('2d');
  const termLabels = ["Term 1", "Term 2", "Term 3"];

  const eligibleData = [
    {{ intval($termStats['1']['eligible'] ?? 0) }},
    {{ intval($termStats['2']['eligible'] ?? 0) }},
    {{ intval($termStats['3']['eligible'] ?? 0) }}
  ];
  const probationData = [
    {{ intval($termStats['1']['probationary'] ?? 0) }},
    {{ intval($termStats['2']['probationary'] ?? 0) }},
    {{ intval($termStats['3']['probationary'] ?? 0) }}
  ];
  const revokedData = [
    {{ intval($termStats['1']['revoked'] ?? 0) }},
    {{ intval($termStats['2']['revoked'] ?? 0) }},
    {{ intval($termStats['3']['revoked'] ?? 0) }}
  ];

  const totals = eligibleData.map((v, i) => v + probationData[i] + revokedData[i]);

  const eligiblePercent = eligibleData.map((v, i) => totals[i] ? Math.round((v / totals[i]) * 100) : 0);
  const probationPercent = probationData.map((v, i) => totals[i] ? Math.round((v / totals[i]) * 100) : 0);
  const revokedPercent = revokedData.map((v, i) => totals[i] ? Math.round((v / totals[i]) * 100) : 0);

  new Chart(ctx, {
    type: 'bar',
    data: {
      labels: termLabels,
      datasets: [
        { label: 'Eligible', data: eligibleData, percentages: eligiblePercent, backgroundColor: '#10b981', borderRadius: 6 },
        { label: 'Probationary', data: probationData, percentages: probationPercent, backgroundColor: '#f59e0b', borderRadius: 6 },
        { label: 'Revoked', data: revokedData, percentages: revokedPercent, backgroundColor: '#ef4444', borderRadius: 6 }
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { position: 'bottom' },
        tooltip: {
          callbacks: {
            label: ctx => `${ctx.dataset.label}: ${ctx.raw} (${ctx.dataset.percentages[ctx.dataIndex]}%)`
          }
        }
      },
      scales: { x: { grid: { display: false } }, y: { beginAtZero: true } }
    },
    plugins: [barValuePlugin]
  });
})();


/* ============================================
   SCHOLARSHIP PIE CHART + FILTER
============================================ */
(function () {
  const ctx = document.getElementById('scholarChart').getContext('2d');

  const labels = {!! json_encode(array_keys($byScholarship)) !!};
  const values = {!! json_encode(array_values($byScholarship)) !!};

  // store original values
  const originalData = [...values];

  // optional dynamic term grouping (placeholder)
const scholarshipByTerm = {!! json_encode($scholarshipByTerm) !!};


  const scholarChart = new Chart(ctx, {
    type: 'pie',
    data: {
      labels: labels,
      datasets: [{
        data: values,
        backgroundColor: ['#1d4ed8','#f59e0b','#10b981','#ef4444','#6366f1','#14b8a6'],
        borderWidth: 1
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: { legend: { position: 'bottom' } }
    },
    plugins: [pieLabelPlugin]
  });

  // dropdown filter (ONLY PIE CHART)
  document.getElementById('scholarTermFilter').addEventListener('change', function() {
    const term = this.value;

    if (term === "all") {
      scholarChart.data.datasets[0].data = [...originalData];
    } else {
      scholarChart.data.datasets[0].data = [...scholarshipByTerm[term]];
    }

    scholarChart.update();
  });

})();
</script>


@endsection
