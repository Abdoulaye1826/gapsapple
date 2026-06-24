@extends('layouts.dashboard')

@section('title', 'Clients')
@section('page-title', 'Gestion des clients')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-2">
  <div>
    <h1><i class="bi bi-people me-2"></i>Clients</h1>
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Accueil</a></li>
        <li class="breadcrumb-item active">Clients</li>
      </ol>
    </nav>
  </div>
  <a href="{{ route('customers.create') }}" class="btn btn-primary">
    <i class="bi bi-plus-lg me-1"></i>Nouveau client
  </a>
</div>

<div class="dashboard-summary-grid mb-4">
  <div class="dashboard-summary-card">
    <div class="summary-card-top">
      <span class="badge bg-primary">Total</span>
      <i class="bi bi-people summary-icon text-primary"></i>
    </div>
    <div class="summary-card-value">{{ $summary['total'] }}</div>
    <div class="summary-card-label">Clients enregistrés</div>
  </div>

  <div class="dashboard-summary-card">
    <div class="summary-card-top">
      <span class="badge bg-info">Factures</span>
      <i class="bi bi-receipt summary-icon text-info"></i>
    </div>
    <div class="summary-card-value">{{ $summary['with_invoices'] }}</div>
    <div class="summary-card-label">Clients avec factures</div>
  </div>

  <div class="dashboard-summary-card">
    <div class="summary-card-top">
      <span class="badge bg-secondary">Sans facture</span>
      <i class="bi bi-person-dash summary-icon text-muted"></i>
    </div>
    <div class="summary-card-value">{{ $summary['without_invoices'] }}</div>
    <div class="summary-card-label">Clients sans facture</div>
  </div>

  <div class="dashboard-summary-card">
    <div class="summary-card-top">
      <span class="badge bg-info">Affichage</span>
      <i class="bi bi-eye summary-icon text-info"></i>
    </div>
    <div class="summary-card-value">{{ $customers->count() }}</div>
    <div class="summary-card-label">Clients sur cette page</div>
  </div>
</div>

<div class="card border-0 shadow-sm mb-4 filter-card">
  <div class="card-body">
    <form method="GET" action="{{ route('customers.index') }}" class="row g-3 align-items-end">
      <div class="col-md-7">
        <label class="form-label small">Rechercher</label>
        <input type="text" name="search" class="form-control" placeholder="Nom, email, téléphone..."
               value="{{ $filters['search'] ?? '' }}">
      </div>
      <div class="col-md-5 d-flex align-items-end gap-2">
        <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search me-1"></i>Filtrer</button>
        <a href="{{ route('customers.index') }}" class="btn btn-outline-secondary w-100">Réinitialiser</a>
      </div>
    </form>
  </div>
</div>

<div class="table-card">
  <div class="table-responsive">
    <table class="table table-hover mb-0">
      <thead>
        <tr>
          <th>Nom</th>
          <th>Téléphone</th>
          <th>Email</th>
          <th>Ville</th>
          <th>Factures</th>
          <th>Inscrit le</th>
          <th class="text-end">Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse($customers as $customer)
          <tr>
            <td>{{ $customer->full_name }}</td>
            <td>{{ $customer->phone }}</td>
            <td>{{ $customer->email ?? '—' }}</td>
            <td>{{ $customer->city ?? '—' }}</td>
            <td>
              <span class="badge bg-info text-dark">{{ $customer->invoices_count }}</span>
              @if($customer->invoices_count > 0)
                <a href="{{ route('invoices.index', ['customer_id' => $customer->id]) }}" class="btn btn-sm btn-link p-0 ms-2">Voir</a>
              @endif
            </td>
            <td>{{ $customer->registered_at?->format('d/m/Y') ?? '—' }}</td>
            <td class="text-end">
              <a href="{{ route('customers.edit', $customer) }}" class="btn btn-sm btn-outline-primary" title="Modifier">
                <i class="bi bi-pencil"></i>
              </a>
              <form action="{{ route('customers.destroy', $customer) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer ce client ?')">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-sm btn-outline-danger">
                  <i class="bi bi-trash"></i>
                </button>
              </form>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="6" class="text-center text-muted py-4">Aucun client trouvé.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
  <div class="p-3 border-top">{{ $customers->links() }}</div>
</div>
@endsection
