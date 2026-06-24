@extends('layouts.dashboard')

@section('title', 'Catégories')
@section('page-title', 'Gestion des catégories')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-2">
  <div>
    <h1><i class="bi bi-tags me-2"></i>Catégories</h1>
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Accueil</a></li>
        <li class="breadcrumb-item active">Catégories</li>
      </ol>
    </nav>
  </div>
  <a href="{{ route('categories.create') }}" class="btn btn-primary">
    <i class="bi bi-plus-lg me-1"></i>Nouvelle catégorie
  </a>
</div>

<div class="dashboard-summary-grid mb-4">
  <div class="dashboard-summary-card">
    <div class="summary-card-top">
      <span class="badge bg-primary">Total</span>
      <i class="bi bi-tags summary-icon text-primary"></i>
    </div>
    <div class="summary-card-value">{{ $categories->total() }}</div>
    <div class="summary-card-label">Catégories totales</div>
  </div>

  <div class="dashboard-summary-card">
    <div class="summary-card-top">
      <span class="badge bg-success">Actives</span>
      <i class="bi bi-check-circle summary-icon text-success"></i>
    </div>
    <div class="summary-card-value">{{ $summary['active'] }}</div>
    <div class="summary-card-label">Catégories actives</div>
  </div>

  <div class="dashboard-summary-card">
    <div class="summary-card-top">
      <span class="badge bg-secondary">Inactives</span>
      <i class="bi bi-x-circle summary-icon text-muted"></i>
    </div>
    <div class="summary-card-value">{{ $summary['inactive'] }}</div>
    <div class="summary-card-label">Catégories inactives</div>
  </div>

  <div class="dashboard-summary-card">
    <div class="summary-card-top">
      <span class="badge bg-info">Affichage</span>
      <i class="bi bi-eye summary-icon text-info"></i>
    </div>
    <div class="summary-card-value">{{ $categories->count() }}</div>
    <div class="summary-card-label">Catégories sur cette page</div>
  </div>
</div>

<div class="card border-0 shadow-sm mb-4 filter-card">
  <div class="card-body">
    <form method="GET" action="{{ route('categories.index') }}" class="row g-3 align-items-end">
      <div class="col-md-5">
        <label class="form-label small">Rechercher</label>
        <input type="text" name="search" class="form-control" placeholder="Nom ou slug..."
               value="{{ request('search') }}">
      </div>
      <div class="col-md-3">
        <label class="form-label small">Statut</label>
        <select name="is_active" class="form-select">
          <option value="">Tous</option>
          <option value="1" @selected(request('is_active') === '1')>Actives</option>
          <option value="0" @selected(request('is_active') === '0')>Inactives</option>
        </select>
      </div>
      <div class="col-md-4 d-flex align-items-end gap-2">
        <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search me-1"></i>Filtrer</button>
        <a href="{{ route('categories.index') }}" class="btn btn-outline-secondary w-100">Réinitialiser</a>
      </div>
    </form>
  </div>
</div>

<div class="table-card">
  <div class="table-responsive">
    <table class="table table-hover mb-0">
      <thead>
        <tr>
          <th>#</th>
          <th>Nom</th>
          <th>Slug</th>
          <th class="text-center">Produits</th>
          <th>Statut</th>
          <th class="text-end">Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse($categories as $category)
          <tr>
            <td>{{ $category->id }}</td>
            <td class="fw-medium">{{ $category->name }}</td>
            <td><code>{{ $category->slug }}</code></td>
            <td class="text-center">
              <span class="badge bg-secondary">{{ $category->products_count }}</span>
            </td>
            <td>
              @if($category->is_active)
                <span class="badge bg-success">Active</span>
              @else
                <span class="badge bg-secondary">Inactive</span>
              @endif
            </td>
            <td class="text-end">
              <a href="{{ route('categories.edit', $category) }}" class="btn btn-sm btn-outline-primary" title="Modifier">
                <i class="bi bi-pencil"></i>
              </a>
              <form action="{{ route('categories.destroy', $category) }}" method="POST" class="d-inline"
                    onsubmit="return confirm('Supprimer cette catégorie ?')">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-sm btn-outline-danger" title="Supprimer"
                        {{ $category->products_count > 0 ? 'disabled' : '' }}>
                  <i class="bi bi-trash"></i>
                </button>
              </form>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="6" class="text-center text-muted py-4">Aucune catégorie trouvée</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
  @if($categories->hasPages())
    <div class="p-3 border-top">{{ $categories->links() }}</div>
  @endif
</div>
@endsection
