@extends('layouts.dashboard')

@section('title', 'Fournisseurs')
@section('page-title', 'Gestion des fournisseurs')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-2">
  <div>
    <h1><i class="bi bi-truck me-2"></i>Fournisseurs</h1>
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Accueil</a></li>
        <li class="breadcrumb-item active">Fournisseurs</li>
      </ol>
    </nav>
  </div>
  <a href="{{ route('suppliers.create') }}" class="btn btn-primary">
    <i class="bi bi-plus-lg me-1"></i>Nouveau fournisseur
  </a>
</div>

<div class="dashboard-summary-grid mb-4">
  <div class="dashboard-summary-card">
    <div class="summary-card-top">
      <span class="badge bg-primary">Total</span>
      <i class="bi bi-truck summary-icon text-primary"></i>
    </div>
    <div class="summary-card-value">{{ $summary['total'] }}</div>
    <div class="summary-card-label">Fournisseurs enregistrés</div>
  </div>

  <div class="dashboard-summary-card">
    <div class="summary-card-top">
      <span class="badge bg-success">Actifs</span>
      <i class="bi bi-check-circle summary-icon text-success"></i>
    </div>
    <div class="summary-card-value">{{ $summary['active'] }}</div>
    <div class="summary-card-label">Fournisseurs actifs</div>
  </div>

  <div class="dashboard-summary-card">
    <div class="summary-card-top">
      <span class="badge bg-secondary">Inactifs</span>
      <i class="bi bi-x-circle summary-icon text-muted"></i>
    </div>
    <div class="summary-card-value">{{ $summary['inactive'] }}</div>
    <div class="summary-card-label">Fournisseurs inactifs</div>
  </div>

  <div class="dashboard-summary-card">
    <div class="summary-card-top">
      <span class="badge bg-info">Affichage</span>
      <i class="bi bi-eye summary-icon text-info"></i>
    </div>
    <div class="summary-card-value">{{ $suppliers->count() }}</div>
    <div class="summary-card-label">Fournisseurs sur cette page</div>
  </div>
</div>

<div class="card border-0 shadow-sm mb-4 filter-card">
  <div class="card-body">
    <form method="GET" action="{{ route('suppliers.index') }}" class="row g-3 align-items-end">
      <div class="col-md-5">
        <label class="form-label small">Rechercher</label>
        <input type="text" name="search" class="form-control" placeholder="Nom, email, téléphone..."
               value="{{ $filters['search'] ?? '' }}">
      </div>
      <div class="col-md-3">
        <label class="form-label small">Statut</label>
        <select name="is_active" class="form-select">
          <option value="">Tous</option>
          <option value="1" @selected(($filters['is_active'] ?? '') === '1')>Actifs</option>
          <option value="0" @selected(($filters['is_active'] ?? '') === '0')>Inactifs</option>
        </select>
      </div>
      <div class="col-md-4 d-flex align-items-end gap-2">
        <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search me-1"></i>Filtrer</button>
        <a href="{{ route('suppliers.index') }}" class="btn btn-outline-secondary w-100">Réinitialiser</a>
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
          <th>Pays</th>
          <th>Statut</th>
          <th class="text-end">Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse($suppliers as $supplier)
          <tr>
            <td>{{ $supplier->name }}</td>
            <td>{{ $supplier->phone }}</td>
            <td>{{ $supplier->email ?? '—' }}</td>
            <td>{{ $supplier->country }}</td>
            <td>
              @if($supplier->is_active)
                <span class="badge bg-success">Actif</span>
              @else
                <span class="badge bg-secondary">Inactif</span>
              @endif
            </td>
            <td class="text-end">
              <a href="{{ route('suppliers.edit', $supplier) }}" class="btn btn-sm btn-outline-primary" title="Modifier">
                <i class="bi bi-pencil"></i>
              </a>
              <form action="{{ route('suppliers.destroy', $supplier) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer ce fournisseur ?')">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-sm btn-outline-danger">
                  <i class="bi bi-trash"></i>
                </button>
              </form>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="6" class="text-center text-muted py-4">Aucun fournisseur trouvé.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
  <div class="p-3 border-top">{{ $suppliers->links() }}</div>
</div>
@endsection
