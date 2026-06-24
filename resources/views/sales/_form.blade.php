<div class="row">
  <div class="col-md-6 mb-3">
    <label for="customer_id" class="form-label">Client</label>
    <div class="d-flex gap-2 align-items-start">
      <select id="customer_id" name="customer_id" class="form-select @error('customer_id') is-invalid @enderror">
        <option value="">— Client anonyme —</option>
        @foreach($customers as $customer)
          <option value="{{ $customer->id }}" @selected(old('customer_id', $sale?->customer_id ?? '') == $customer->id)>
            {{ $customer->full_name }}
          </option>
        @endforeach
      </select>
      <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#newCustomerModal">
        <i class="bi bi-person-plus"></i>
      </button>
    </div>
    @error('customer_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
  </div>
  <div class="col-md-4 mb-3">
    <label for="sale_type" class="form-label">Type de transaction <span class="text-danger">*</span></label>
    <select id="sale_type" name="sale_type" class="form-select @error('sale_type') is-invalid @enderror" required>
      <option value="vente" @selected(old('sale_type', $sale?->sale_type->value ?? 'vente') === 'vente')>Vente</option>
      <option value="echange" @selected(old('sale_type', $sale?->sale_type->value ?? '') === 'echange')>Échange</option>
    </select>
    @error('sale_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
  </div>
  <div class="col-md-6 mb-3">
    <label for="sale_date_display" class="form-label">Date de vente</label>
    <input type="text" readonly class="form-control" id="sale_date_display"
           value="{{ old('sale_date', $sale?->sale_date?->format('Y-m-d H:i:s') ?? now()->format('Y-m-d H:i:s')) }}">
    <div class="form-text">La date est générée automatiquement par le serveur.</div>
  </div>
</div>

<div class="row">
  <div class="col-12 mb-3">
    <label class="form-label">Produits</label>
    <div class="card p-3">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="text-muted">Ajoutez les produits de la vente</div>
        <button type="button" class="btn btn-sm btn-outline-primary" id="addSaleItemButton">
          <i class="bi bi-plus-lg"></i> Ajouter un produit
        </button>
      </div>
      <div id="saleItemsContainer">
        @php
          $oldProductIds = old('product_id', $sale?->items->pluck('product_id')->toArray() ?? []);
          $oldQuantities = old('quantity', $sale?->items->pluck('quantity')->toArray() ?? []);
          $oldUnitPrices = old('unit_price', $sale?->items->pluck('unit_price')->toArray() ?? []);

          $saleItems = collect(is_array($oldProductIds) ? $oldProductIds : [$oldProductIds])
              ->map(function ($productId, $index) use ($oldQuantities, $oldUnitPrices) {
                  return [
                      'product_id' => $productId,
                      'quantity' => is_array($oldQuantities) ? ($oldQuantities[$index] ?? 1) : 1,
                      'unit_price' => is_array($oldUnitPrices) ? ($oldUnitPrices[$index] ?? 0) : ($oldUnitPrices ?? 0),
                  ];
              });

          if ($saleItems->isEmpty()) {
              $saleItems = collect([['product_id' => '', 'quantity' => 1, 'unit_price' => 0]]);
          }
        @endphp

        @foreach($saleItems as $index => $saleItem)
          <div class="sale-item-row row g-3 align-items-end mb-2">
            <div class="col-md-5">
              <label class="form-label">Produit</label>
              <select name="product_id[]" class="form-select @error('product_id.' . $index) is-invalid @enderror" required>
                <option value="">— Sélectionnez un produit —</option>
                @foreach($products as $product)
                  <option value="{{ $product->id }}" @selected((int) $saleItem['product_id'] === $product->id)>
                    {{ $product->reference }} — {{ $product->name }} @if($product->stock_quantity !== null)({{ $product->stock_quantity }} en stock)@endif
                  </option>
                @endforeach
              </select>
              @error('product_id.' . $index)<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-2">
              <label class="form-label">Prix unitaire</label>
              <input type="number" step="0.01" min="0" name="unit_price[]" class="form-control @error('unit_price.' . $index) is-invalid @enderror"
                     value="{{ old('unit_price.' . $index, $saleItem['unit_price'] ?? 0) }}" required>
              @error('unit_price.' . $index)<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-2">
              <label class="form-label">Quantité</label>
              <input type="number" step="1" min="1" name="quantity[]" class="form-control @error('quantity.' . $index) is-invalid @enderror"
                     value="{{ $saleItem['quantity'] }}" required>
              @error('quantity.' . $index)<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-2">
              <label class="form-label">Total</label>
              <input type="text" class="form-control line-total" value="0" readonly>
            </div>
            <div class="col-md-1 text-end">
              <button type="button" class="btn btn-outline-danger btn-remove-item" style="margin-top: 32px;">
                <i class="bi bi-trash"></i>
              </button>
            </div>
          </div>
        @endforeach
      </div>
    </div>
  </div>

  <template id="saleItemTemplate">
    <div class="sale-item-row row g-3 align-items-end mb-2">
      <div class="col-md-5">
        <label class="form-label">Produit</label>
        <select name="product_id[]" class="form-select" required>
          <option value="">— Sélectionnez un produit —</option>
          @foreach($products as $product)
            <option value="{{ $product->id }}">
              {{ $product->reference }} — {{ $product->name }} @if($product->stock_quantity !== null)({{ $product->stock_quantity }} en stock)@endif
            </option>
          @endforeach
        </select>
      </div>
      <div class="col-md-2">
        <label class="form-label">Prix unitaire</label>
        <input type="number" step="0.01" min="0" name="unit_price[]" class="form-control" value="0" required>
      </div>
      <div class="col-md-2">
        <label class="form-label">Quantité</label>
        <input type="number" step="1" min="1" name="quantity[]" class="form-control" value="1" required>
      </div>
      <div class="col-md-2">
        <label class="form-label">Total</label>
        <input type="text" class="form-control line-total" value="0" readonly>
      </div>
      <div class="col-md-1 text-end">
        <button type="button" class="btn btn-outline-danger btn-remove-item" style="margin-top: 32px;">
          <i class="bi bi-trash"></i>
        </button>
      </div>
    </div>
  </template>

  <div class="col-md-4 mb-3">
    <label for="status" class="form-label">Statut <span class="text-danger">*</span></label>
    <select id="status" name="status" class="form-select @error('status') is-invalid @enderror" required>
      <option value="draft" @selected(old('status', $sale?->status->value ?? 'draft') === 'draft')>Brouillon</option>
      <option value="validated" @selected(old('status', $sale?->status->value ?? '') === 'validated')>Validée</option>
      <option value="cancelled" @selected(old('status', $sale?->status->value ?? '') === 'cancelled')>Annulée</option>
    </select>
    @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
  </div>
</div>

<div id="exchangeFields" class="border rounded p-3 mb-3" style="display: {{ old('sale_type', $sale?->sale_type->value ?? 'vente') === 'echange' ? 'block' : 'none' }};">
  <h5 class="mb-3">Produit retourné</h5>
  <div class="row">
    <div class="col-md-6 mb-3">
      <label for="exchange_product_id" class="form-label">Produit retourné existant</label>
      <select id="exchange_product_id" name="exchange_product_id" class="form-select @error('exchange_product_id') is-invalid @enderror">
        <option value="">— Aucun produit existant —</option>
        @foreach($products as $product)
          <option value="{{ $product->id }}" @selected(old('exchange_product_id', $sale?->exchange_details['product_id'] ?? '') == $product->id)>
            {{ $product->reference }} — {{ $product->name }}
          </option>
        @endforeach
      </select>
      @error('exchange_product_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
      <div class="form-text">Choisissez un produit retourné s'il existe dans le catalogue.</div>
    </div>
    <div class="col-md-2 mb-3">
      <label for="exchange_quantity" class="form-label">Quantité</label>
      <input type="number" step="1" min="1" class="form-control @error('exchange_quantity') is-invalid @enderror"
             id="exchange_quantity" name="exchange_quantity" value="{{ old('exchange_quantity', $sale?->exchange_details['quantity'] ?? 1) }}">
      @error('exchange_quantity')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4 mb-3">
      <label for="exchange_product_condition" class="form-label">État du produit</label>
      <select id="exchange_product_condition" name="exchange_product_condition" class="form-select @error('exchange_product_condition') is-invalid @enderror">
        <option value="">— Sélectionnez —</option>
        <option value="neuf" @selected(old('exchange_product_condition', $sale?->exchange_details['condition'] ?? '') === 'neuf')>Neuf</option>
        <option value="tres_bon_etat" @selected(old('exchange_product_condition', $sale?->exchange_details['condition'] ?? '') === 'tres_bon_etat')>Très bon état</option>
        <option value="bon_etat" @selected(old('exchange_product_condition', $sale?->exchange_details['condition'] ?? '') === 'bon_etat')>Bon état</option>
        <option value="defectueux" @selected(old('exchange_product_condition', $sale?->exchange_details['condition'] ?? '') === 'defectueux')>Défectueux</option>
      </select>
      @error('exchange_product_condition')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
  </div>

  <div class="row">
    <div class="col-md-6 mb-3">
      <label for="exchange_product_name" class="form-label">Nom du produit retourné</label>
      <input type="text" class="form-control @error('exchange_product_name') is-invalid @enderror"
             id="exchange_product_name" name="exchange_product_name" value="{{ old('exchange_product_name', $sale?->exchange_details['name'] ?? '') }}">
      @error('exchange_product_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
      <div class="form-text">Saisissez le nom si le produit retourné n'existe pas dans le catalogue.</div>
    </div>
    <div class="col-md-3 mb-3">
      <label for="exchange_product_reference" class="form-label">Référence du produit</label>
      <input type="text" class="form-control @error('exchange_product_reference') is-invalid @enderror"
             id="exchange_product_reference" name="exchange_product_reference" value="{{ old('exchange_product_reference', $sale?->exchange_details['reference'] ?? '') }}">
      @error('exchange_product_reference')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-3 mb-3">
      <label for="exchange_product_brand" class="form-label">Marque</label>
      <input type="text" class="form-control @error('exchange_product_brand') is-invalid @enderror"
             id="exchange_product_brand" name="exchange_product_brand" value="{{ old('exchange_product_brand', $sale?->exchange_details['brand'] ?? '') }}">
      @error('exchange_product_brand')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
  </div>

  <div class="row">
    <div class="col-md-4 mb-3">
      <label for="exchange_category_id" class="form-label">Catégorie</label>
      <select id="exchange_category_id" name="exchange_category_id" class="form-select @error('exchange_category_id') is-invalid @enderror">
        <option value="">— Choisir une catégorie —</option>
        @foreach($categories as $category)
          <option value="{{ $category->id }}" @selected(old('exchange_category_id', $sale?->exchange_details['category_id'] ?? '') == $category->id)>
            {{ $category->name }}
          </option>
        @endforeach
      </select>
      @error('exchange_category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4 mb-3">
      <label for="exchange_product_estimated_value" class="form-label">Valeur estimée</label>
      <input type="number" step="0.01" min="0" class="form-control @error('exchange_product_estimated_value') is-invalid @enderror"
             id="exchange_product_estimated_value" name="exchange_product_estimated_value" value="{{ old('exchange_product_estimated_value', $sale?->exchange_details['estimated_value'] ?? '') }}">
      @error('exchange_product_estimated_value')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
  </div>

  <div class="mb-3">
    <label for="exchange_product_description" class="form-label">Description du produit retourné</label>
    <textarea class="form-control @error('exchange_product_description') is-invalid @enderror"
              id="exchange_product_description" name="exchange_product_description" rows="2">{{ old('exchange_product_description', $sale?->exchange_details['description'] ?? '') }}</textarea>
    @error('exchange_product_description')<div class="invalid-feedback">{{ $message }}</div>@enderror
  </div>
</div>

<div class="row">
  <div class="col-md-4 mb-3">
    <label for="discount_amount" class="form-label">Remise (FCFA)</label>
    <input type="number" step="0.01" min="0" class="form-control @error('discount_amount') is-invalid @enderror"
           id="discount_amount" name="discount_amount" value="{{ old('discount_amount', $sale?->discount_amount ?? 0) }}">
    @error('discount_amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
  </div>
  <div class="col-md-4 mb-3">
    <label for="total_ttc" class="form-label">Total</label>
    <input type="number" step="0.01" min="0" class="form-control @error('total_ttc') is-invalid @enderror"
           id="total_ttc" name="total_ttc" value="{{ old('total_ttc', $sale?->total_ttc ?? 0) }}" readonly>
    @error('total_ttc')<div class="invalid-feedback">{{ $message }}</div>@enderror
    <div class="form-text">Le total est calculé automatiquement à partir des produits et de la remise.</div>
  </div>
</div>

<div class="mb-3">
  <label for="notes" class="form-label">Notes</label>
  <textarea class="form-control @error('notes') is-invalid @enderror"
            id="notes" name="notes" rows="3">{{ old('notes', $sale->notes ?? '') }}</textarea>
  @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function () {
    const saleTypeField = document.getElementById('sale_type');
    const exchangeFields = document.getElementById('exchangeFields');
    const addSaleItemButton = document.getElementById('addSaleItemButton');
    const saleItemsContainer = document.getElementById('saleItemsContainer');
    const saleItemTemplate = document.getElementById('saleItemTemplate');
    const productPrices = {
      @foreach($products as $product)
        {{ $product->id }}: {{ $product->sale_price }},
      @endforeach
    };

    if (saleTypeField && exchangeFields) {
      saleTypeField.addEventListener('change', function () {
        exchangeFields.style.display = this.value === 'echange' ? 'block' : 'none';
      });
    }

    function calculateTotals() {
      const rows = saleItemsContainer.querySelectorAll('.sale-item-row');
      let total = 0;

      rows.forEach(row => {
        const quantityInput = row.querySelector('input[name="quantity[]"]');
        const unitPriceInput = row.querySelector('input[name="unit_price[]"]');
        const lineTotalInput = row.querySelector('.line-total');

        const quantity = parseFloat(quantityInput?.value || 0) || 0;
        const unitPrice = parseFloat(unitPriceInput?.value || 0) || 0;
        const lineTotal = quantity * unitPrice;

        if (lineTotalInput) {
          lineTotalInput.value = lineTotal.toFixed(2).replace('.', ',');
        }

        total += lineTotal;
      });

      const discount = parseFloat(document.getElementById('discount_amount')?.value || 0) || 0;
      const netTotal = Math.max(0, total - discount);
      const totalField = document.getElementById('total_ttc');

      if (totalField) {
        totalField.value = netTotal.toFixed(2);
      }
    }

    function bindSaleItemEvents(container) {
      container.querySelectorAll('select[name="product_id[]"]').forEach(select => {
        select.addEventListener('change', function () {
          const row = this.closest('.sale-item-row');
          const unitPriceInput = row.querySelector('input[name="unit_price[]"]');

          if (unitPriceInput) {
            const productId = this.value;
            unitPriceInput.value = productPrices[productId] !== undefined ? Number(productPrices[productId]).toFixed(2) : 0;
          }

          calculateTotals();
        });
      });

      container.querySelectorAll('input[name="quantity[]"], input[name="unit_price[]"]').forEach(input => {
        input.addEventListener('input', calculateTotals);
      });
      container.querySelectorAll('.btn-remove-item').forEach(button => {
        button.addEventListener('click', function () {
          const row = this.closest('.sale-item-row');
          if (row) {
            row.remove();
            calculateTotals();
          }
        });
      });
    }

    if (addSaleItemButton && saleItemTemplate) {
      addSaleItemButton.addEventListener('click', function () {
        const clone = saleItemTemplate.content.cloneNode(true);
        saleItemsContainer.appendChild(clone);
        bindSaleItemEvents(saleItemsContainer.lastElementChild);
        calculateTotals();
      });
    }

    bindSaleItemEvents(saleItemsContainer);
    calculateTotals();

    const saveCustomerButton = document.getElementById('saveNewCustomerButton');
    const newCustomerForm = document.getElementById('newCustomerForm');
    const customerSelect = document.getElementById('customer_id');

    if (saveCustomerButton && newCustomerForm && customerSelect) {
      saveCustomerButton.addEventListener('click', async function () {
        const formData = new FormData(newCustomerForm);

        document.querySelectorAll('#newCustomerForm .invalid-feedback').forEach(el => {
          el.textContent = '';
        });
        document.querySelectorAll('#newCustomerForm .is-invalid').forEach(el => {
          el.classList.remove('is-invalid');
        });

        const response = await fetch('{{ route('customers.store') }}', {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json',
          },
          body: formData,
        });

        const data = await response.json();

        if (!response.ok) {
          if (data.errors) {
            Object.entries(data.errors).forEach(([field, messages]) => {
              const input = document.getElementById(`new_customer_${field}`);
              const feedback = document.getElementById(`new_customer_${field}_error`);
              if (input) {
                input.classList.add('is-invalid');
              }
              if (feedback) {
                feedback.textContent = messages.join(' ');
              }
            });
          }
          return;
        }

        const option = document.createElement('option');
        option.value = data.id;
        option.textContent = data.full_name;
        option.selected = true;
        customerSelect.appendChild(option);

        const modal = bootstrap.Modal.getInstance(document.getElementById('newCustomerModal'));
        modal.hide();
        newCustomerForm.reset();
      });
    }
  });
</script>
@endpush
