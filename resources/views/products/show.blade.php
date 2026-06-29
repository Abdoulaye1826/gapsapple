@extends('layouts.dashboard')

@section('title', $product->name)
@section('page-title', 'Détail produit')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-2">
  <div>
    <h1>{{ $product->name }}</h1>
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('products.index') }}">Produits</a></li>
        <li class="breadcrumb-item active">{{ $product->reference }}</li>
      </ol>
    </nav>
  </div>
  <div class="d-flex gap-2">
    <a href="{{ route('products.edit', $product) }}" class="btn btn-primary">
      <i class="bi bi-pencil me-1"></i>Modifier
    </a>
    <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">Retour</a>
  </div>
</div>

<div class="row g-4 u-animate">
  <div class="col-lg-4">
    <div class="detail-hero">
      @if($product->image)
        <img src="{{ asset('storage/'.$product->image) }}" alt="{{ $product->name }}" class="detail-hero__image" loading="lazy">
      @else
        <div class="detail-hero__placeholder">
          <i class="bi bi-controller"></i>
        </div>
      @endif
      <h2>{{ $product->name }}</h2>
      <div class="detail-hero__sub">{{ $product->brand ?? 'Marque non renseignée' }}</div>
      <div class="detail-hero__badges">
        <span class="badge bg-info">{{ $product->category?->name ?? 'Sans catégorie' }}</span>
        @if($product->is_active)
          <span class="badge bg-success">Actif</span>
        @else
          <span class="badge bg-secondary">Inactif</span>
        @endif
        @if($product->isOutOfStock())
          <span class="badge bg-danger">Rupture</span>
        @elseif($product->isLowStock())
          <span class="badge bg-warning text-dark">Stock faible</span>
        @endif
        @if($product->tracks_imei)
          <span class="badge bg-primary"><i class="bi bi-phone me-1"></i>Suivi IMEI</span>
        @endif
      </div>
    </div>
  </div>

  <div class="col-lg-8">
    <div class="detail-card mb-4">
      <div class="detail-card__header"><i class="bi bi-info-circle"></i>Informations</div>
      <div class="detail-card__body">
        <div class="detail-stat-grid mb-4">
          <div class="detail-stat">
            <div class="detail-stat__label"><i class="bi bi-upc"></i>Référence</div>
            <div class="detail-stat__value" style="font-size:.95rem;">{{ $product->reference }}</div>
          </div>
          <div class="detail-stat">
            <div class="detail-stat__label"><i class="bi bi-upc-scan"></i>Code-barres</div>
            <div class="detail-stat__value" style="font-size:.95rem;">{{ $product->barcode ?? '—' }}</div>
          </div>
          <div class="detail-stat">
            <div class="detail-stat__label"><i class="bi bi-truck"></i>Fournisseur</div>
            <div class="detail-stat__value" style="font-size:.95rem;">{{ $product->supplier?->name ?? '—' }}</div>
          </div>
          <div class="detail-stat">
            <div class="detail-stat__label"><i class="bi bi-boxes"></i>Stock</div>
            <div class="detail-stat__value {{ $product->isOutOfStock() ? 'text-danger' : '' }}">
              {{ $product->stock_quantity }} <span class="text-muted" style="font-size:.75rem;">/ min {{ $product->minimum_stock }}</span>
            </div>
          </div>
          <div class="detail-stat">
            <div class="detail-stat__label"><i class="bi bi-cash"></i>Prix achat</div>
            <div class="detail-stat__value">{{ number_format($product->purchase_price, 0, ',', ' ') }} FCFA</div>
          </div>
          <div class="detail-stat">
            <div class="detail-stat__label"><i class="bi bi-tag"></i>Prix vente</div>
            <div class="detail-stat__value text-copper">{{ number_format($product->sale_price, 0, ',', ' ') }} FCFA</div>
          </div>
          <div class="detail-stat detail-stat--accent">
            <div class="detail-stat__label"><i class="bi bi-graph-up-arrow"></i>Marge</div>
            <div class="detail-stat__value {{ $product->margin < 0 ? 'text-danger' : 'text-success' }}">
              {{ number_format($product->margin, 0, ',', ' ') }} FCFA
              <span style="font-size:.8rem;font-weight:600;">({{ $product->margin_rate }}%)</span>
            </div>
          </div>
        </div>

        @if($product->description)
          <div>
            <div class="detail-stat__label mb-2"><i class="bi bi-card-text"></i>Description</div>
            <p class="mb-0" style="color:var(--text);line-height:1.6;">{{ $product->description }}</p>
          </div>
        @endif
      </div>
    </div>

    @if($product->tracks_imei)
      <div class="detail-card mb-4">
        <div class="detail-card__header"><i class="bi bi-phone"></i>Historique des IMEI</div>
        <div class="table-responsive">
          <table class="table table-hover mb-0">
            <thead>
              <tr>
                <th>IMEI</th>
                <th>Statut</th>
                <th>Entré le</th>
                <th>Vendu le</th>
                <th>Client</th>
                <th>Facture</th>
              </tr>
            </thead>
            <tbody>
              @forelse($product->imeis as $imei)
                <tr>
                  <td class="font-monospace">{{ $imei->imei }}</td>
                  <td><span class="badge {{ $imei->status->badgeClass() }}">{{ $imei->status->label() }}</span></td>
                  <td>
                    {{ $imei->created_at->format('d/m/Y') }}
                    @if($imei->exchangeSale)
                      <br><small class="text-muted">Échange {{ $imei->exchangeSale->exchange_voucher_number }}</small>
                    @endif
                  </td>
                  <td>{{ $imei->sold_at?->format('d/m/Y') ?? '—' }}</td>
                  <td>{{ $imei->sale?->customer?->full_name ?? '—' }}</td>
                  <td>
                    @if($imei->sale?->invoice)
                      <a href="{{ route('invoices.print', $imei->sale->invoice) }}" target="_blank">{{ $imei->sale->invoice->invoice_number }}</a>
                    @else
                      —
                    @endif
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="6" class="text-center text-muted py-4">Aucun IMEI enregistré pour le moment.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    @endif

    @if($product->stockMovements->isNotEmpty())
      @include('products.partials.stock-movements')
    @endif
  </div>
</div>
@endsection
