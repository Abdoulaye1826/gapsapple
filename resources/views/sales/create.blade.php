@extends('layouts.dashboard')

@section('title', 'Nouvelle vente')
@section('page-title', 'Nouvelle vente')

@section('content')
<div class="page-header">
  <h1><i class="bi bi-plus-circle me-2"></i>Nouvelle vente</h1>
</div>

<div class="card border-0 shadow-sm">
  <div class="card-body">
    <form method="POST" action="{{ route('sales.store') }}">
      @csrf
      @include('sales._form')
      <div class="d-flex gap-2 mt-2">
        <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Enregistrer</button>
        <a href="{{ route('sales.index') }}" class="btn btn-outline-secondary">Annuler</a>
      </div>
    </form>
    @include('sales._new_customer_modal')
  </div>
</div>
@endsection
