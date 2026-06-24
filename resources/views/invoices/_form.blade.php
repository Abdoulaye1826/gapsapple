<div class="row g-3">
  <div class="col-md-6">
    <label for="sale_id" class="form-label">Vente associée</label>
    <select name="sale_id" id="sale_id" class="form-select @error('sale_id') is-invalid @enderror">
      <option value="">Sélectionnez une vente</option>
      @foreach($sales as $saleOption)
        <option value="{{ $saleOption->id }}" @selected(old('sale_id', $invoice?->sale_id) == $saleOption->id)>
          {{ $saleOption->sale_number }} — {{ $saleOption->customer?->full_name ?? 'Client anonyme' }}
        </option>
      @endforeach
    </select>
    @error('sale_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
  </div>

  <div class="col-md-6">
    <label for="customer_id" class="form-label">Client</label>
    <select name="customer_id" id="customer_id" class="form-select @error('customer_id') is-invalid @enderror" disabled>
      <option value="">Sélectionnez une vente</option>
      @foreach($customers as $customer)
        <option value="{{ $customer->id }}" @selected(old('customer_id', $invoice?->customer_id) == $customer->id)>
          {{ $customer->full_name }}
        </option>
      @endforeach
    </select>
    <div class="form-text">Le client est récupéré automatiquement depuis la vente sélectionnée.</div>
    @error('customer_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
  </div>

  <div class="col-md-4">
    <label for="issued_at" class="form-label">Date d'émission</label>
    <input type="date" name="issued_at" id="issued_at" value="{{ old('issued_at', $invoice?->issued_at?->format('Y-m-d') ?? now()->format('Y-m-d')) }}" class="form-control @error('issued_at') is-invalid @enderror">
    @error('issued_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
  </div>

  <div class="col-md-4">
    <label for="subtotal_ht" class="form-label">Sous-total HT</label>
    <input type="number" step="0.01" name="subtotal_ht" id="subtotal_ht" value="{{ old('subtotal_ht', $invoice?->subtotal_ht ?? 0) }}" class="form-control @error('subtotal_ht') is-invalid @enderror">
    @error('subtotal_ht')<div class="invalid-feedback">{{ $message }}</div>@enderror
  </div>

  <div class="col-md-4">
    <label for="tax_amount" class="form-label">Taxe</label>
    <input type="number" step="0.01" name="tax_amount" id="tax_amount" value="{{ old('tax_amount', $invoice?->tax_amount ?? 0) }}" class="form-control @error('tax_amount') is-invalid @enderror">
    @error('tax_amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
  </div>

  <div class="col-md-4">
    <label for="total_ttc" class="form-label">Total TTC</label>
    <input type="number" step="0.01" name="total_ttc" id="total_ttc" value="{{ old('total_ttc', $invoice?->total_ttc ?? 0) }}" class="form-control @error('total_ttc') is-invalid @enderror">
    @error('total_ttc')<div class="invalid-feedback">{{ $message }}</div>@enderror
  </div>

  <div class="col-md-4">
    <label for="status" class="form-label">Statut</label>
    <select name="status" id="status" class="form-select @error('status') is-invalid @enderror">
      <option value="issued" @selected(old('status', $invoice?->status->value ?? 'issued') === 'issued')>Émise</option>
      <option value="paid" @selected(old('status', $invoice?->status->value ?? '') === 'paid')>Payée</option>
      <option value="cancelled" @selected(old('status', $invoice?->status->value ?? '') === 'cancelled')>Annulée</option>
    </select>
    @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
  </div>

  <div class="col-md-8">
    <label for="pdf_path" class="form-label">Chemin du PDF</label>
    <input type="text" name="pdf_path" id="pdf_path" value="{{ old('pdf_path', $invoice?->pdf_path ?? '') }}" class="form-control @error('pdf_path') is-invalid @enderror" placeholder="Optionnel">
    @error('pdf_path')<div class="invalid-feedback">{{ $message }}</div>@enderror
  </div>
</div>
