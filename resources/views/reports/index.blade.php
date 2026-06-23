@extends('layouts.dashboard')

@section('title', 'Rapports & Statistiques')
@section('page-title', 'Rapports & Statistiques')

{{-- NOTE : ce bloc nécessite que layouts/dashboard.blade.php contienne
     @stack('styles') dans le <head>, juste comme @stack('scripts') existe
     avant </body>. Si ce n'est pas déjà le cas, ajoute cette seule ligne
     dans le layout (aucune autre modification nécessaire). --}}
@push('styles')
<style>
  /* === Rapports & Statistiques — enrichissements visuels === */

  #reportToolbar .live-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #10b981;
    display: inline-block;
    animation: livePulse 2s infinite;
  }
  @keyframes livePulse {
    0%   { box-shadow: 0 0 0 0 rgba(16,185,129,.5); }
    70%  { box-shadow: 0 0 0 6px rgba(16,185,129,0); }
    100% { box-shadow: 0 0 0 0 rgba(16,185,129,0); }
  }

  @keyframes fadeSlideUp {
    from { opacity: 0; transform: translateY(14px); }
    to   { opacity: 1; transform: translateY(0); }
  }
  .row.g-3 > div { animation: fadeSlideUp .5s ease both; }
  .row.g-3 > div:nth-child(1) { animation-delay: .02s; }
  .row.g-3 > div:nth-child(2) { animation-delay: .06s; }
  .row.g-3 > div:nth-child(3) { animation-delay: .10s; }
  .row.g-3 > div:nth-child(4) { animation-delay: .14s; }
  .row.g-3 > div:nth-child(5) { animation-delay: .18s; }
  .row.g-3 > div:nth-child(6) { animation-delay: .22s; }
  .row.g-3 > div:nth-child(7) { animation-delay: .26s; }
  .row.g-3 > div:nth-child(8) { animation-delay: .30s; }

  /* KPI cards */
  .kpi-card {
    position: relative;
    overflow: hidden;
    transition: transform .25s ease, box-shadow .25s ease;
  }
  .kpi-card::before {
    content: "";
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 3px;
    opacity: 0;
    transition: opacity .25s ease;
  }
  .kpi-card:has(.text-primary)::before   { background: #3b82f6; }
  .kpi-card:has(.text-success)::before   { background: #10b981; }
  .kpi-card:has(.text-info)::before      { background: #06b6d4; }
  .kpi-card:has(.text-secondary)::before { background: #64748b; }
  .kpi-card:has(.text-warning)::before   { background: #f59e0b; }
  .kpi-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 10px 28px rgba(15, 23, 42, .10);
  }
  .kpi-card:hover::before { opacity: 1; }
  .kpi-icon { transition: transform .25s ease; }
  .kpi-card:hover .kpi-icon { transform: scale(1.08) rotate(-4deg); }
  .kpi-value { font-variant-numeric: tabular-nums; }

  /* Chart & table cards */
  .chart-card, .table-card {
    transition: box-shadow .25s ease;
  }
  .chart-card:hover, .table-card:hover {
    box-shadow: 0 10px 28px rgba(15, 23, 42, .08);
  }

  /* Tableaux triables / filtrables */
  table[data-sortable] thead th[data-sort] { cursor: pointer; user-select: none; }
  table[data-sortable] thead th[data-sort]:hover { color: #3b82f6; }
  .sort-ind { vertical-align: middle; }

  .table-filter:focus {
    box-shadow: 0 0 0 3px rgba(59,130,246,.15);
    border-color: #93c5fd;
  }

  /* Badge impayé/en retard qui respire un peu */
  @keyframes pulseBadge {
    0%, 100% { box-shadow: 0 0 0 0 rgba(220,53,69,.35); }
    50%      { box-shadow: 0 0 0 5px rgba(220,53,69,0); }
  }
  .badge.bg-danger { animation: pulseBadge 2s ease-in-out infinite; }

  /* Impression propre */
  @media print {
    .sidebar, .top-navbar, #reportToolbar .btn-group, .table-filter, .sort-ind {
      display: none !important;
    }
    .main-wrapper { margin-left: 0 !important; }
    .kpi-card, .chart-card, .table-card {
      box-shadow: none !important;
      border: 1px solid #cbd5e1 !important;
      animation: none !important;
    }
  }

  /* Respect des préférences d'accessibilité */
  @media (prefers-reduced-motion: reduce) {
    .row.g-3 > div, .kpi-card, .live-dot, .badge.bg-danger {
      animation: none !important;
      transition: none !important;
    }
  }
</style>
@endpush

@section('content')
<div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-2">
  <div>
    <h1>Rapports & Statistiques</h1>
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Accueil</a></li>
        <li class="breadcrumb-item active" aria-current="page">Rapports</li>
      </ol>
    </nav>
  </div>
  <div class="d-flex align-items-center gap-3 flex-wrap" id="reportToolbar">
    <div class="text-muted small d-flex align-items-center gap-2">
      <span class="live-dot" aria-hidden="true"></span>
      <i class="bi bi-calendar3"></i>{{ now()->translatedFormat('l d F Y') }}
    </div>
    <div class="btn-group btn-group-sm">
      <button type="button" class="btn btn-outline-secondary" onclick="window.location.reload()" title="Actualiser">
        <i class="bi bi-arrow-clockwise"></i>
      </button>
      <button type="button" class="btn btn-outline-secondary" onclick="window.print()" title="Imprimer">
        <i class="bi bi-printer"></i>
      </button>
    </div>
  </div>
</div>

<div class="row g-3 mb-4">
  @php
    $reportKpis = [
      ['label' => 'CA du jour', 'value' => number_format($stats['revenue_today'], 0, ',', ' ') . ' FCFA', 'raw' => $stats['revenue_today'], 'suffix' => 'FCFA', 'icon' => 'bi-currency-exchange', 'color' => 'bg-primary bg-opacity-10 text-primary'],
      ['label' => 'CA du mois', 'value' => number_format($stats['revenue_month'], 0, ',', ' ') . ' FCFA', 'raw' => $stats['revenue_month'], 'suffix' => 'FCFA', 'icon' => 'bi-graph-up-arrow', 'color' => 'bg-success bg-opacity-10 text-success'],
      ['label' => 'Ventes validées', 'value' => $stats['sales_count'], 'raw' => $stats['sales_count'], 'suffix' => '', 'icon' => 'bi-cart-check', 'color' => 'bg-info bg-opacity-10 text-info'],
      ['label' => 'Factures émises', 'value' => $stats['invoices_count'], 'raw' => $stats['invoices_count'], 'suffix' => '', 'icon' => 'bi-file-earmark-text', 'color' => 'bg-secondary bg-opacity-10 text-secondary'],
      ['label' => 'Factures payées', 'value' => $stats['paid_invoices_count'], 'raw' => $stats['paid_invoices_count'], 'suffix' => '', 'icon' => 'bi-wallet2', 'color' => 'bg-success bg-opacity-10 text-success'],
      ['label' => 'Impayés', 'value' => $stats['pending_invoices_count'], 'raw' => $stats['pending_invoices_count'], 'suffix' => '', 'icon' => 'bi-hourglass-split', 'color' => 'bg-warning bg-opacity-10 text-warning'],
      ['label' => 'Nouveaux clients (mois)', 'value' => $stats['new_customers_month'], 'raw' => $stats['new_customers_month'], 'suffix' => '', 'icon' => 'bi-person-plus', 'color' => 'bg-primary bg-opacity-10 text-primary'],
      ['label' => 'Clients totaux', 'value' => $stats['customers_count'], 'raw' => $stats['customers_count'], 'suffix' => '', 'icon' => 'bi-people', 'color' => 'bg-info bg-opacity-10 text-info'],
    ];
  @endphp

  @foreach($reportKpis as $kpi)
    <div class="col-6 col-md-4 col-xl-3">
      <div class="kpi-card">
        <div class="d-flex align-items-center gap-3">
          <div class="kpi-icon {{ $kpi['color'] }}">
            <i class="bi {{ $kpi['icon'] }}"></i>
          </div>
          <div>
            <div class="kpi-label">{{ $kpi['label'] }}</div>
            <div class="kpi-value" data-value="{{ $kpi['raw'] }}" data-suffix="{{ $kpi['suffix'] }}">{{ $kpi['value'] }}</div>
          </div>
        </div>
      </div>
    </div>
  @endforeach
</div>

<div class="row g-3 mb-4">
  <div class="col-lg-4">
    <div class="chart-card h-100">
      <div class="card-title"><i class="bi bi-pie-chart me-2"></i>Statut des factures</div>
      <canvas id="invoiceStatusChart" height="260"></canvas>
    </div>
  </div>
  <div class="col-lg-8">
    <div class="chart-card h-100">
      <div class="card-title"><i class="bi bi-bar-chart me-2"></i>Chiffre d'affaires par mois</div>
      <canvas id="salesByMonthChart" height="260"></canvas>
    </div>
  </div>
</div>

<div class="row g-3 mb-4">
  <div class="col-lg-6">
    <div class="chart-card h-100">
      <div class="card-title"><i class="bi bi-bar-chart-line me-2"></i>Ventes par catégorie</div>
      <canvas id="salesByCategoryChart" height="260"></canvas>
    </div>
  </div>
  <div class="col-lg-6">
    <div class="table-card h-100">
      <div class="p-3 border-bottom d-flex align-items-center justify-content-between gap-2 flex-wrap">
        <h6 class="mb-0 fw-semibold"><i class="bi bi-clock-history me-2"></i>Factures récentes</h6>
        <input type="text" class="form-control form-control-sm table-filter" style="max-width: 160px;" data-target="recentInvoicesTable" placeholder="Filtrer...">
      </div>
      <div class="table-responsive">
        <table class="table table-hover mb-0" id="recentInvoicesTable" data-sortable>
          <thead>
            <tr>
              <th>Numéro</th>
              <th>Client</th>
              <th class="text-end" data-sort="number">Montant <i class="bi bi-arrow-down-up small text-muted"></i></th>
              <th class="text-end">Statut</th>
            </tr>
          </thead>
          <tbody>
            @forelse($recentInvoices as $invoice)
              <tr>
                <td>{{ $invoice->invoice_number }}</td>
                <td>{{ $invoice->customer?->full_name ?? '—' }}</td>
                <td class="text-end" data-value="{{ $invoice->total_ttc }}">{{ number_format($invoice->total_ttc, 0, ',', ' ') }} FCFA</td>
                <td class="text-end">
                  @php
                    $invoiceStatus = $invoice->status instanceof App\Enums\InvoiceStatus
                        ? $invoice->status
                        : App\Enums\InvoiceStatus::from($invoice->status);
                  @endphp
                  <span class="badge {{ $invoiceStatus === App\Enums\InvoiceStatus::Paid ? 'bg-success' : ($invoiceStatus === App\Enums\InvoiceStatus::Issued ? 'bg-warning text-dark' : 'bg-danger') }}">
                    {{ $invoiceStatus->label() }}
                  </span>
                </td>
              </tr>
            @empty
              <tr class="empty-row">
                <td colspan="4" class="text-center text-muted py-4">Aucune facture récente</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<div class="row g-3">
  <div class="col-lg-6">
    <div class="table-card h-100">
      <div class="p-3 border-bottom d-flex align-items-center justify-content-between gap-2 flex-wrap">
        <h6 class="mb-0 fw-semibold"><i class="bi bi-trophy me-2"></i>Top clients</h6>
        <input type="text" class="form-control form-control-sm table-filter" style="max-width: 160px;" data-target="topClientsTable" placeholder="Filtrer...">
      </div>
      <div class="table-responsive">
        <table class="table table-hover mb-0" id="topClientsTable" data-sortable>
          <thead>
            <tr>
              <th>#</th>
              <th>Client</th>
              <th class="text-center" data-sort="number">Factures <i class="bi bi-arrow-down-up small text-muted"></i></th>
              <th class="text-end" data-sort="number">Montant <i class="bi bi-arrow-down-up small text-muted"></i></th>
            </tr>
          </thead>
          <tbody>
            @forelse($topCustomers as $index => $customer)
              <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $customer->full_name }}</td>
                <td class="text-center" data-value="{{ $customer->invoices_count }}"><span class="badge bg-primary">{{ $customer->invoices_count }}</span></td>
                <td class="text-end" data-value="{{ $customer->total_amount }}">{{ number_format($customer->total_amount, 0, ',', ' ') }} FCFA</td>
              </tr>
            @empty
              <tr class="empty-row">
                <td colspan="4" class="text-center text-muted py-4">Aucun client n’a encore passé de commande</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <div class="col-lg-6">
    <div class="table-card h-100">
      <div class="p-3 border-bottom d-flex align-items-center justify-content-between gap-2 flex-wrap">
        <h6 class="mb-0 fw-semibold"><i class="bi bi-people-fill me-2"></i>Vendeurs performants</h6>
        <input type="text" class="form-control form-control-sm table-filter" style="max-width: 160px;" data-target="salesByUserTable" placeholder="Filtrer...">
      </div>
      <div class="table-responsive">
        <table class="table table-hover mb-0" id="salesByUserTable" data-sortable>
          <thead>
            <tr>
              <th>#</th>
              <th>Vendeur</th>
              <th class="text-center" data-sort="number">Ventes <i class="bi bi-arrow-down-up small text-muted"></i></th>
              <th class="text-end" data-sort="number">Montant <i class="bi bi-arrow-down-up small text-muted"></i></th>
            </tr>
          </thead>
          <tbody>
            @forelse($salesByUser as $index => $user)
              <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $user->name }}</td>
                <td class="text-center" data-value="{{ $user->sales_count }}"><span class="badge bg-info">{{ $user->sales_count }}</span></td>
                <td class="text-end" data-value="{{ $user->total_amount }}">{{ number_format($user->total_amount, 0, ',', ' ') }} FCFA</td>
              </tr>
            @empty
              <tr class="empty-row">
                <td colspan="4" class="text-center text-muted py-4">Aucun vendeur enregistré</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
  // ---------- Réglages globaux Chart.js ----------
  Chart.defaults.font.family = "'Segoe UI', system-ui, sans-serif";
  Chart.defaults.color = '#64748b';
  Chart.defaults.animation.duration = 800;
  Chart.defaults.animation.easing = 'easeOutQuart';
  Chart.defaults.plugins.tooltip.backgroundColor = '#1e293b';
  Chart.defaults.plugins.tooltip.padding = 10;
  Chart.defaults.plugins.tooltip.cornerRadius = 8;
  Chart.defaults.plugins.tooltip.displayColors = false;

  const chartDefaults = { responsive: true, maintainAspectRatio: true };

  // Plugin maison : total affiché au centre des donuts
  const centerTextPlugin = {
    id: 'centerText',
    beforeDraw(chart) {
      const opts = chart.config.options.plugins.centerText;
      if (!opts || !opts.enabled) return;
      const { ctx, chartArea: { width, height, left, top } } = chart;
      const total = chart.data.datasets[0].data.reduce((a, b) => a + b, 0);
      ctx.save();
      ctx.textAlign = 'center';
      ctx.textBaseline = 'middle';
      ctx.font = "700 1.4rem 'Segoe UI', sans-serif";
      ctx.fillStyle = '#1e293b';
      ctx.fillText(total.toLocaleString('fr-FR'), left + width / 2, top + height / 2 - 8);
      ctx.font = "600 0.7rem 'Segoe UI', sans-serif";
      ctx.fillStyle = '#94a3b8';
      ctx.fillText(opts.label || '', left + width / 2, top + height / 2 + 14);
      ctx.restore();
    }
  };
  Chart.register(centerTextPlugin);

  // ---------- CA par mois (barres avec dégradé) ----------
  const monthCtx = document.getElementById('salesByMonthChart').getContext('2d');
  const barGradient = monthCtx.createLinearGradient(0, 0, 0, 260);
  barGradient.addColorStop(0, 'rgba(59,130,246,0.9)');
  barGradient.addColorStop(1, 'rgba(59,130,246,0.15)');

  new Chart(monthCtx, {
    type: 'bar',
    data: {
      labels: @json($salesByMonth['labels']),
      datasets: [{
        label: 'CA (FCFA)',
        data: @json($salesByMonth['data']),
        backgroundColor: barGradient,
        hoverBackgroundColor: 'rgba(59,130,246,1)',
        borderRadius: 10,
        maxBarThickness: 48,
      }]
    },
    options: {
      ...chartDefaults,
      plugins: { legend: { display: false } },
      scales: {
        y: { beginAtZero: true, grid: { color: '#f1f5f9' } },
        x: { grid: { display: false } }
      }
    }
  });

  // ---------- Ventes par catégorie ----------
  const categoryLabels = @json($salesByCategory['labels']);
  const categoryData = @json($salesByCategory['data']);
  const categoryColors = ['#3b82f6','#10b981','#f59e0b','#ef4444','#8b5cf6','#06b6d4','#ec4899'];

  new Chart(document.getElementById('salesByCategoryChart'), {
    type: 'doughnut',
    data: {
      labels: categoryLabels.length ? categoryLabels : ['Aucune donnée'],
      datasets: [{
        data: categoryData.length ? categoryData : [1],
        backgroundColor: categoryLabels.length ? categoryColors.slice(0, categoryLabels.length) : ['#e2e8f0'],
        borderWidth: 2,
        borderColor: '#fff',
        hoverOffset: 8,
      }]
    },
    options: {
      ...chartDefaults,
      cutout: '68%',
      plugins: {
        legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } },
        centerText: { enabled: true, label: 'Ventes' }
      }
    }
  });

  // ---------- Statut des factures ----------
  const invoiceLabels = @json($invoiceStatusSummary['labels']);
  const invoiceData = @json($invoiceStatusSummary['values']);
  const invoiceColors = ['#0d6efd','#198754','#ffc107','#dc3545'];

  new Chart(document.getElementById('invoiceStatusChart'), {
    type: 'doughnut',
    data: {
      labels: invoiceLabels.length ? invoiceLabels : ['Aucune donnée'],
      datasets: [{
        data: invoiceData.length ? invoiceData : [1],
        backgroundColor: invoiceLabels.length ? invoiceColors.slice(0, invoiceLabels.length) : ['#e2e8f0'],
        borderWidth: 2,
        borderColor: '#fff',
        hoverOffset: 8,
      }]
    },
    options: {
      ...chartDefaults,
      cutout: '68%',
      plugins: {
        legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } },
        centerText: { enabled: true, label: 'Factures' }
      }
    }
  });

  // ---------- Compteurs animés sur les KPI ----------
  const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  const numberFormatter = new Intl.NumberFormat('fr-FR');

  function animateKpiValue(el) {
    const end = parseFloat(el.dataset.value) || 0;
    const suffix = el.dataset.suffix ? ' ' + el.dataset.suffix : '';
    if (prefersReducedMotion) {
      el.textContent = numberFormatter.format(end) + suffix;
      return;
    }
    const duration = 900;
    const start = performance.now();
    function step(now) {
      const progress = Math.min((now - start) / duration, 1);
      const eased = 1 - Math.pow(1 - progress, 3);
      el.textContent = numberFormatter.format(Math.round(end * eased)) + suffix;
      if (progress < 1) requestAnimationFrame(step);
    }
    requestAnimationFrame(step);
  }
  document.querySelectorAll('.kpi-value[data-value]').forEach(animateKpiValue);

  // ---------- Filtre texte des tableaux ----------
  document.querySelectorAll('.table-filter').forEach(input => {
    input.addEventListener('input', () => {
      const table = document.getElementById(input.dataset.target);
      if (!table) return;
      const q = input.value.trim().toLowerCase();
      table.querySelectorAll('tbody tr:not(.empty-row)').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
      });
    });
  });

  // ---------- Tri des colonnes ----------
  document.querySelectorAll('table[data-sortable] thead th[data-sort]').forEach(th => {
    th.addEventListener('click', () => {
      const table = th.closest('table');
      const tbody = table.querySelector('tbody');
      const headerRow = th.parentNode;
      const index = Array.from(headerRow.children).indexOf(th);
      const asc = th.dataset.dir !== 'asc';

      headerRow.querySelectorAll('th').forEach(t => {
        t.dataset.dir = '';
        const ind = t.querySelector('.sort-ind');
        if (ind) ind.remove();
      });
      th.dataset.dir = asc ? 'asc' : 'desc';

      const rows = Array.from(tbody.querySelectorAll('tr')).filter(r => !r.classList.contains('empty-row'));
      rows.sort((a, b) => {
        const av = parseFloat((a.children[index].dataset.value ?? a.children[index].textContent)) || 0;
        const bv = parseFloat((b.children[index].dataset.value ?? b.children[index].textContent)) || 0;
        return asc ? av - bv : bv - av;
      });
      rows.forEach(r => tbody.appendChild(r));

      const ind = document.createElement('i');
      ind.className = `bi ${asc ? 'bi-caret-up-fill' : 'bi-caret-down-fill'} sort-ind ms-1 small`;
      th.appendChild(ind);
    });
  });
</script>
@endpush