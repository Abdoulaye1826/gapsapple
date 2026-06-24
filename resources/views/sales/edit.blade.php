@extends('layouts.dashboard')

@section('title', 'Modifier vente')
@section('page-title', 'Modifier vente')

@section('content')
<div class="page-header">
  <h1><i class="bi bi-pencil me-2"></i>Modifier vente : {{ $sale->sale_number }}</h1>
</div>

<div class="card border-0 shadow-sm">
  <div class="card-body">
    <form method="POST" action="{{ route('sales.update', $sale) }}">
      @csrf @method('PUT')
      @include('sales._form')
      <div class="d-flex gap-2 mt-2">
        <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Mettre à jour</button>
        <a href="{{ route('sales.index') }}" class="btn btn-outline-secondary">Annuler</a>
      </div>
    </form>
        @include('sales._new_customer_modal')
    @include('sales._new_exchange_product_modal')
  </div>
</div>
@endsection
