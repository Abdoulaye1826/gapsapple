@extends('layouts.dashboard')

@section('title', 'Modifier vente')
@section('page-title', 'Modifier vente')

@section('content')
<div class="page-header">
  <h1><i class="bi bi-pencil me-2"></i>Modifier vente : {{ $sale->sale_number }}</h1>
  <nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="{{ route('sales.index') }}">Ventes</a></li>
      <li class="breadcrumb-item active">Modifier</li>
    </ol>
  </nav>
</div>

<div class="form-shell form-shell--wide u-animate">
  <form method="POST" action="{{ route('sales.update', $sale) }}" data-ui-form novalidate>
    @csrf @method('PUT')
    <div class="form-card">
      <div class="form-card__header">
        <h2><i class="bi bi-cart-check"></i>Détails de la transaction</h2>
        <p class="form-card__subtitle">Mettez à jour le client, les produits, et — pour un échange — le produit apporté. Les champs marqués <span class="req">*</span> sont obligatoires.</p>
      </div>
      <div class="form-card__body">
        @include('sales._form')
      </div>
      <div class="form-card__footer">
        <a href="{{ route('sales.index') }}" class="btn btn-outline-secondary"><i class="bi bi-x-lg me-1"></i>Annuler</a>
        <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Mettre à jour</button>
      </div>
    </div>
  </form>
</div>

@include('sales._new_customer_modal')
@include('sales._new_exchange_product_modal')
@endsection
