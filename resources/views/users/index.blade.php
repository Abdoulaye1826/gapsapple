@extends('layouts.dashboard')

@section('title', 'Utilisateurs')
@section('page-title', 'Gestion des utilisateurs')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-2">
  <div>
    <h1><i class="bi bi-people me-2"></i>Utilisateurs</h1>
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Accueil</a></li>
        <li class="breadcrumb-item active">Utilisateurs</li>
      </ol>
    </nav>
  </div>
  <a href="{{ route('users.create') }}" class="btn btn-primary">
    <i class="bi bi-plus-lg me-1"></i>Nouveau utilisateur
  </a>
</div>

<div class="mb-3">
  <span class="badge bg-primary fs-6">{{ $users->total() }} utilisateur(s)</span>
</div>

<div class="card border-0 shadow-sm mb-4">
  <div class="card-body">
    <form method="GET" action="{{ route('users.index') }}" class="row g-3 align-items-end">
      <div class="col-md-4">
        <label class="form-label small">Rechercher</label>
        <input type="text" name="search" class="form-control" placeholder="Nom, email, téléphone..."
               value="{{ $filters['search'] ?? '' }}">
      </div>
      <div class="col-md-3">
        <label class="form-label small">Rôle</label>
        <select name="role_id" class="form-select">
          <option value="">Tous</option>
          @foreach(App\Models\Role::orderBy('name')->get() as $role)
            <option value="{{ $role->id }}" {{ isset($filters['role_id']) && $filters['role_id'] == $role->id ? 'selected' : '' }}>{{ $role->name }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label small">Statut</label>
        <select name="is_active" class="form-select">
          <option value="">Tous</option>
          <option value="1" {{ isset($filters['is_active']) && $filters['is_active'] === '1' ? 'selected' : '' }}>Actifs</option>
          <option value="0" {{ isset($filters['is_active']) && $filters['is_active'] === '0' ? 'selected' : '' }}>Désactivés</option>
        </select>
      </div>
      <div class="col-md-2 text-end">
        <button type="submit" class="btn btn-outline-primary w-100"><i class="bi bi-search me-1"></i>Filtrer</button>
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
          <th>Email</th>
          <th>Rôle</th>
          <th>Téléphone</th>
          <th>Statut</th>
          <th class="text-end">Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse($users as $user)
          <tr>
            <td>{{ $user->name }}</td>
            <td>{{ $user->email }}</td>
            <td>{{ $user->role?->name ?? '—' }}</td>
            <td>{{ $user->phone ?? '—' }}</td>
            <td>
              <span class="badge {{ $user->is_active ? 'bg-success' : 'bg-secondary' }}">
                {{ $user->is_active ? 'Actif' : 'Désactivé' }}
              </span>
            </td>
            <td class="text-end">
              <a href="{{ route('users.edit', $user) }}" class="btn btn-sm btn-outline-primary" title="Modifier">
                <i class="bi bi-pencil"></i>
              </a>
              <form action="{{ route('users.destroy', $user) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer cet utilisateur ?')">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-sm btn-outline-danger">
                  <i class="bi bi-trash"></i>
                </button>
              </form>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="6" class="text-center text-muted py-4">Aucun utilisateur trouvé.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
  <div class="p-3 border-top">{{ $users->links() }}</div>
</div>
@endsection
