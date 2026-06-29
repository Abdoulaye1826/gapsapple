@extends('layouts.dashboard')

@section('title', 'Modifier une facture')
@section('page-title', 'Modifier la facture')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-2">
  <div>
    <h1><i class="bi bi-receipt me-2"></i>Modifier la facture {{ $invoice->invoice_number }}</h1>
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Accueil</a></li>
        <li class="breadcrumb-item"><a href="{{ route('invoices.index') }}">Factures</a></li>
        <li class="breadcrumb-item active">Modifier</li>
      </ol>
    </nav>
  </div>
  <a href="{{ route('invoices.index') }}" class="btn btn-outline-secondary">
    <i class="bi bi-chevron-left me-1"></i>Retour
  </a>
</div>

<div class="form-shell u-animate">
  <form action="{{ route('invoices.update', $invoice) }}" method="POST" data-ui-form novalidate>
    @csrf
    @method('PUT')
    <div class="form-card">
      <div class="form-card__header">
        <h2><i class="bi bi-receipt"></i>Fiche facture</h2>
        <p class="form-card__subtitle">Mettez à jour la vente associée, les montants ou le statut de cette facture.</p>
      </div>
      <div class="form-card__body">
        @include('invoices._form', ['invoice' => $invoice])
      </div>
      <div class="form-card__footer">
        <a href="{{ route('invoices.index') }}" class="btn btn-outline-secondary"><i class="bi bi-x-lg me-1"></i>Annuler</a>
        <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Mettre à jour</button>
      </div>
    </div>
  </form>

  @php
    $amountPaid = $invoice->amount_paid;
    $remaining = $invoice->remaining_amount;
    $progress = (float) $invoice->total_ttc > 0 ? min(100, round(($amountPaid / (float) $invoice->total_ttc) * 100)) : 0;
  @endphp

  <div class="form-card mt-4">
    <div class="form-card__header">
      <h2><i class="bi bi-cash-stack"></i>Paiements</h2>
      <p class="form-card__subtitle">Enregistrez les encaissements reçus pour cette facture (Wave, Orange Money, Espèces).</p>
    </div>
    <div class="form-card__body">
      <div class="row g-3 mb-4">
        <div class="col-md-4">
          <div class="detail-stat">
            <div class="detail-stat__label"><i class="bi bi-receipt"></i>Total facture</div>
            <div class="detail-stat__value">{{ number_format($invoice->total_ttc, 0, ',', ' ') }} FCFA</div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="detail-stat detail-stat--accent">
            <div class="detail-stat__label"><i class="bi bi-check-circle"></i>Payé</div>
            <div class="detail-stat__value text-success">{{ number_format($amountPaid, 0, ',', ' ') }} FCFA</div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="detail-stat">
            <div class="detail-stat__label"><i class="bi bi-hourglass-split"></i>Reste à payer</div>
            <div class="detail-stat__value {{ $remaining > 0 ? 'text-danger' : 'text-success' }}">{{ number_format($remaining, 0, ',', ' ') }} FCFA</div>
          </div>
        </div>
      </div>

      <div class="progress mb-4" role="progressbar" aria-valuenow="{{ $progress }}" aria-valuemin="0" aria-valuemax="100" style="height:8px;">
        <div class="progress-bar bg-success" style="width: {{ $progress }}%"></div>
      </div>

      @if($remaining > 0.01 && $invoice->status->value !== 'cancelled')
        <form action="{{ route('invoices.payments.store', $invoice) }}" method="POST" data-ui-form novalidate class="row g-3 align-items-end mb-4 pb-4 border-bottom">
          @csrf
          <div class="col-md-3 field-group mb-0">
            <label for="payment_amount" class="form-label">Montant <span class="req">*</span></label>
            <input type="number" step="0.01" min="0.01" max="{{ $remaining }}" name="amount" id="payment_amount"
                   class="form-control" placeholder="{{ $remaining }}" required>
          </div>
          <div class="col-md-3 field-group mb-0">
            <label for="payment_method" class="form-label">Mode <span class="req">*</span></label>
            <select name="method" id="payment_method" class="form-select" required>
              <option value="wave">Wave</option>
              <option value="orange_money">Orange Money</option>
              <option value="cash">Espèces</option>
            </select>
          </div>
          <div class="col-md-3 field-group mb-0">
            <label for="payment_paid_at" class="form-label">Date <span class="req">*</span></label>
            <input type="date" name="paid_at" id="payment_paid_at" class="form-control" value="{{ now()->format('Y-m-d') }}" required>
          </div>
          <div class="col-md-3 field-group mb-0">
            <label for="payment_reference" class="form-label">Référence</label>
            <input type="text" name="reference" id="payment_reference" class="form-control" placeholder="N° transaction">
          </div>
          <div class="col-12">
            <button type="submit" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Enregistrer le paiement</button>
          </div>
        </form>
      @elseif($invoice->status->value === 'cancelled')
        <div class="alert alert-secondary">Cette facture est annulée : aucun paiement ne peut y être ajouté.</div>
      @else
        <div class="alert alert-success"><i class="bi bi-check-circle me-1"></i>Facture entièrement payée.</div>
      @endif

      <div class="table-responsive">
        <table class="table table-hover mb-0">
          <thead>
            <tr>
              <th>Date</th>
              <th>Mode</th>
              <th class="text-end">Montant</th>
              <th>Référence</th>
              <th>Enregistré par</th>
              <th class="text-end">Action</th>
            </tr>
          </thead>
          <tbody>
            @forelse($invoice->payments as $payment)
              <tr>
                <td>{{ $payment->paid_at->format('d/m/Y') }}</td>
                <td><span class="badge bg-light text-dark"><i class="bi {{ $payment->method->icon() }} me-1"></i>{{ $payment->method->label() }}</span></td>
                <td class="text-end amount">{{ number_format($payment->amount, 0, ',', ' ') }} FCFA</td>
                <td>{{ $payment->reference ?? '—' }}</td>
                <td>{{ $payment->recordedBy?->name ?? '—' }}</td>
                <td class="text-end">
                  <form action="{{ route('payments.destroy', $payment) }}" method="POST" class="d-inline"
                        onsubmit="return confirm('Supprimer ce paiement ? Le statut de la facture sera recalculé.')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                  </form>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="6" class="text-center text-muted py-4">Aucun paiement enregistré pour le moment.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
@endsection
