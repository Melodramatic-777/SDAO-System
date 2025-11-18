<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <title>NU - SDAO Dashboard</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link href="https://fonts.googleapis.com/css2?family=Inter&display=swap" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

  <meta name="csrf-token" content="{{ csrf_token() }}">
  @vite('resources/css/volunteers.css')
  <style>
    :root{
      --nu-blue: #002f6c;
      --nu-gold: #f5b400;
      --muted: #6b7280;
      --card-bg: #ffffff;
      --page-bg: #f4f6fb;
      --glass: rgba(255,255,255,0.6);
      --radius: 12px;
      --shadow: 0 6px 18px rgba(15,23,42,0.08);
    }

    *{box-sizing:border-box}
    body{
      margin:0;
      font-family: 'Inter', system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial;
      background: linear-gradient(180deg,var(--page-bg) 0%, #eef3fb 100%);
      color: #0f172a;
      -webkit-font-smoothing:antialiased;
      -moz-osx-font-smoothing:grayscale;
      min-height:100vh;
      display:flex;
    }

   

    /* MAIN */
    .main {
      flex:1;
      padding: 20px 28px;
      display:flex;
      flex-direction:column;
      gap:18px;
      min-height:100vh;
    }

    /* TOP HEADER */
    .topbar {
      display:flex;
      justify-content:space-between;
      align-items:center;
      gap:16px;
    }
    .search {
      display:flex;
      align-items:center;
      gap:10px;
      background: #fff;
      padding:8px 12px;
      border-radius: 999px;
      box-shadow: var(--shadow);
      min-width:360px;
      border: 1px solid #e6eef9;
    }
    .search input{
      border:0; outline:0; width:100%; font-size:0.95rem;
    }
    .top-actions { display:flex; gap:10px; align-items:center; }

    .avatar {
      display:flex; align-items:center; gap:10px; background: white; padding:8px 12px; border-radius:999px;
      box-shadow: 0 6px 18px rgba(2,6,23,0.06);
    }
    .avatar img { height:36px; width:36px; border-radius:50%; object-fit:cover; }

    /* METRICS */
    .metrics {
      display:grid;
      grid-template-columns: repeat(4,1fr);
      gap:18px;
    }
    .metric-card{
      background:var(--card-bg);
      border-radius: var(--radius);
      padding:18px;
      box-shadow: var(--shadow);
      display:flex;
      justify-content:space-between;
      align-items:center;
      gap:12px;
      transition: transform .12s ease, box-shadow .12s ease;
    }
    .metric-card:hover { transform: translateY(-6px); box-shadow: 0 14px 30px rgba(2,6,23,0.08); }
    .metric-left { display:flex; flex-direction:column; gap:6px; }
    .metric-title { color:var(--muted); font-weight:600; font-size:0.9rem; }
    .metric-value { font-size:1.6rem; font-weight:800; color:var(--nu-blue); }

    .metric-badge {
      background: linear-gradient(90deg,var(--nu-gold), #e4a000);
      color: #05203a;
      padding:8px 12px;
      border-radius:10px;
      font-weight:700;
      box-shadow: 0 6px 20px rgba(245,180,0,0.12);
    }

    /* LAYOUT ROWS */
    .row {
      display:flex;
      gap:18px;
      align-items:flex-start;
    }
    .col-8 { flex: 0 0 66.66%; }
    .col-4 { flex: 0 0 33.33%; }

    .card {
      background: var(--card-bg);
      padding: 18px;
      border-radius: 14px;
      box-shadow: var(--shadow);
    }

    /* Chart area */
    .charts {
      display:flex;
      gap:18px;
      align-items:flex-start;
    }
    .chart-large { flex:1; min-height:340px; display:flex; flex-direction:column; gap:12px; }
.chart-small {
    width: 100% !important;          /* take full width of parent container */
    max-width: 100% !important;      /* prevent shrinking */
    min-height: 350px !important;    /* increase height for proportions */
    display: flex;
    justify-content: center;
    align-items: center;
}
    .card-title { font-weight:700; color:var(--muted); margin-bottom:8px; display:flex; justify-content:space-between; align-items:center; }

    /* Stats column (right) */
    .status-list { display:flex; flex-direction:column; gap:12px; }
    .status-item{
      display:flex; justify-content:space-between; align-items:center; gap:12px;
      padding:12px; border-radius:10px; background: linear-gradient(180deg,#fbfdff,#ffffff);
      border: 1px solid #f0f5fb;
    }
    .status-item .label { font-weight:700; color:#374151; }
    .pill { padding:6px 10px; border-radius:999px; font-weight:700; font-size:0.9rem; }

    .pill.eligible { background:#ecfdf5; color:#065f46; }
    .pill.probation { background:#fff7ed; color:#92400e; }
    .pill.revoked { background:#fff1f2; color:#991b1b; }

    /* NOTIFICATIONS */
    .notifications { display:flex; flex-direction:column; gap:10px; margin-top:8px; }
    .notification { padding:12px; border-radius:10px; background: linear-gradient(180deg,#ffffff,#fbfcff); border: 1px solid #f0f5fb; }

    /* FOOTER */
    .footer {
      margin-top:auto;
      font-size:0.85rem;
      color: #475569;
      padding:12px 0;
    }

    /* Responsive */
    @media (max-width:1100px){
      .sidebar{ display:none; }
      body{ padding-left: 0; }
      .metrics{ grid-template-columns: repeat(2,1fr); }
      .col-8, .col-4 { flex: 1 1 100%; }
      .chart-small{ width:100%; min-height:230px; }
    }
    @media (max-width:640px){
      .metrics{ grid-template-columns: 1fr; }
      .search{ min-width: 200px; }
    }


.chart-large {
    flex: none !important;
    width: 380px !important;
    min-height: 340px !important;
}

.chart-large canvas {
    max-width: 380px !important;
    max-height: 340px !important;
    margin: 0 auto;
}

.chart-small canvas {
    width: 100% !important;          /* chart fills container horizontally */
    height: auto !important;
    max-height: 320px !important;
}


#retentionChart {
    height: 420px !important;      /* chart canvas height */
    max-height: 420px !important;
    padding-top: 35px !important;  /* space for labels above bars */
}

.chart-small {
    min-height: 380px !important;  /* increase chart area */
}

.chart-small canvas {
    margin-top: 20px !important;
}

.row {
    width: 100%;
    display: flex;
    gap: 18px;
    align-items: stretch !important;
}

.col-3, .col-4, .col-5 {
    flex: 1 1 33.33%;
}

/* Ensure all cards inside stretch evenly */
.row > div {
    display: flex;
}

.row > div > .card {
    height: 100%;
    flex: 1;
    display: flex;
    flex-direction: column;
}


.col-5 .card {
    display: flex;
    flex-direction: column;
    justify-content: flex-start;
    padding-top: 20px !important;   /* reduce empty space */
}

.col-5 .card-title {
    margin-bottom: 20px !important;
}

.col-5 .status-list {
    margin-top: 0 !important;       /* remove unnecessary spacing */
}

.col-5 .status-item {
    margin-top: 4px !important;     /* tighten spacing between each item */
}
  </style>
</head>
<body>

  <header class="custom-header">
    <div class="header-left">
        <div class="logo">
            <img src="{{ asset('images/NU_Logo.png') }}" alt="NU Logo" height="30">
            <span>Student Developments and Activities Office - SDA Associate</span>
        </div>
    </div>
    <div class="header-right-group">
        <p>Welcome, {{ session('scholars_admin.first_name') }}!</p>
        <form method="POST" action="{{ route('admin.logout') }}">
            @csrf
            <button type="submit" class="side-button">
                <img src="{{ asset('images/Side.png') }}" alt="Side" height="30">
            </button>
        </form>
    </div>
</header>

<header>
    <nav>
        <a href="{{ route('scholars-admin.dashboard') }}">Dashboard</a>
        <a href="{{ route('scholars-admin.scholars_accounts') }}">Accounts</a>
        <a href="{{ route('scholars-admin.scholars') }}">Scholars</a>
        <a href="{{ route('scholars-admin.scholarships') }}">Scholarships</a>
    </nav>
</header>


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

        <!-- TITLE + FILTERS IN ONE ROW -->
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">

            <!-- Title -->
            <div class="card-title" style="font-weight:700; color:var(--muted);">
                Scholarship Distribution
            </div>

            <!-- Filters -->
            <div style="display:flex; gap:10px;">
                <select id="scholarYearFilter" class="form-select" style="width:180px;">
                    @foreach($allAcademicYears as $year)
                        <option value="{{ $year }}" {{ $year == $recentYear ? 'selected' : '' }}>
                            AY {{ $year }}
                        </option>
                    @endforeach
                </select>

                <select id="scholarTermFilter" class="form-select" style="width:120px;">
                    <option value="all">All Terms</option>
                    @foreach(($termsByYear[strval($recentYear)] ?? []) as $t)
                        <option value="{{ $t }}">{{ $t }}</option>
                    @endforeach
                </select>
            </div>

        </div>
        <!-- END TITLE+FILTER ROW -->

        <!-- Chart Wrapper -->
        <div style="flex:1; display:flex; justify-content:center; align-items:center; padding:10px;">
<canvas id="scholarChart" style="max-height:360px; width:100%;"></canvas>
        </div>

        <!-- Legend -->
        <div style="display:flex; flex-direction:column; gap:8px; margin-top:10px;">
            @foreach($byScholarship as $type => $count)
                <div style="
                    background:#fbfdff;
                    padding:8px 10px;
                    border-radius:10px;
                    border:1px solid #eef6ff;
                    font-weight:700;
                    color:var(--nu-blue);
                ">
                    {{ strtoupper($type) }}: {{ $count }}
                </div>
            @endforeach
        </div>

    </div>
</div>


    <!-- CENTER: RETENTION BY TERM -->
    <div class="col-4">
<div class="card">
<div class="card-title" style="display:flex; justify-content:space-between; align-items:center;">

    <span>Retention by Term</span>

    <!-- NEW Academic Year Filter for Retention Chart -->
    <select id="retentionYearFilter" class="form-select" style="width:180px;">
        @foreach($allAcademicYears as $year)
            <option value="{{ $year }}" {{ $year == $recentYear ? 'selected' : '' }}>
                AY {{ $year }}
            </option>
        @endforeach
    </select>

</div>         
   <div style="width:100%; padding-top:10px;">
                <canvas id="retentionChart" style="height:300px;"></canvas>
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


</body>
</html>
