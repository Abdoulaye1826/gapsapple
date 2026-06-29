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
      <div class="mb-3">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <div class="text-muted">Ajoutez les produits de la vente</div>
          <button type="button" class="btn btn-sm btn-outline-primary" id="addSaleItemButton">
            <i class="bi bi-plus-lg"></i> Ajouter un produit
          </button>
        </div>
        <div class="btn-group price-tier-group" role="group" aria-label="Tarif applicable" id="globalPriceTierGroup">
          <button type="button" class="btn btn-outline-secondary price-tier-btn active" data-tier="client">client</button>
          <button type="button" class="btn btn-outline-secondary price-tier-btn" data-tier="fournisseur">Revendeur</button>
        </div>
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
          <div class="sale-item-row row g-3 align-items-end mb-2" data-price-tier="client">
            <div class="col-md-4">
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
              <input type="number" step="0.01" min="0" name="unit_price[]" class="form-control price-input @error('unit_price.' . $index) is-invalid @enderror"
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
    <div class="sale-item-row row g-3 align-items-end mb-2" data-price-tier="client">
      <div class="col-md-4">
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
        <input type="number" step="0.01" min="0" name="unit_price[]" class="form-control price-input" value="0" required>
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

</div>

<div id="exchangeFields" class="border rounded p-3 mb-3" style="display: {{ old('sale_type', $sale?->sale_type->value ?? 'vente') === 'echange' ? 'block' : 'none' }};">
  <h5 class="mb-3"><i class="bi bi-arrow-left-right me-2"></i>Produit apport&eacute; par le client</h5>

  {{-- Champ hidden pour stocker l'ID du produit s&eacute;lectionn&eacute; --}}
  <input type="hidden" id="exchange_product_id" name="exchange_product_id"
         value="{{ old('exchange_product_id', $sale?->exchange_details['product_id'] ?? '') }}">

  <div class="row">
    <div class="col-md-6 mb-3">
      <label for="exchange_product_search" class="form-label">Produit apport&eacute; <span class="text-danger">*</span></label>
      <div class="position-relative">
        <div class="input-group">
          <span class="input-group-text"><i class="bi bi-search"></i></span>
          <input type="text" class="form-control @error('exchange_product_id') is-invalid @enderror"
                 id="exchange_product_search" autocomplete="off"
                 placeholder="Tapez le nom, la r&eacute;f&eacute;rence ou la marque..."
                 value="@if(old('exchange_product_id', $sale?->exchange_details['product_id'] ?? '')){{ optional(\App\Models\Product::find(old('exchange_product_id', $sale?->exchange_details['product_id'] ?? '')))->reference }} — {{ optional(\App\Models\Product::find(old('exchange_product_id', $sale?->exchange_details['product_id'] ?? '')))->name }}@endif">
        </div>
        @error('exchange_product_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror

        {{-- Liste d'autocompl&eacute;tion --}}
        <div id="exchangeProductDropdown" class="list-group position-absolute w-100 shadow-sm" style="z-index: 1050; max-height: 250px; overflow-y: auto; display: none;"></div>
      </div>

      {{-- Produit s&eacute;lectionn&eacute; --}}
      <div id="exchangeProductSelected" class="alert alert-success d-flex align-items-center justify-content-between mt-2 py-2 px-3"
           style="display: {{ old('exchange_product_id', $sale?->exchange_details['product_id'] ?? '') ? 'flex' : 'none' }} !important;">
        <span id="exchangeProductSelectedText">
          @if(old('exchange_product_id', $sale?->exchange_details['product_id'] ?? ''))
            @php $selectedProduct = \App\Models\Product::find(old('exchange_product_id', $sale?->exchange_details['product_id'] ?? '')); @endphp
            @if($selectedProduct)
              <i class="bi bi-check-circle me-1"></i>
              <strong>{{ $selectedProduct->reference }}</strong> — {{ $selectedProduct->name }}
              @if($selectedProduct->brand) <span class="text-muted">({{ $selectedProduct->brand }})</span> @endif
            @endif
          @endif
        </span>
        <button type="button" class="btn btn-sm btn-outline-danger ms-2" id="exchangeProductClear">
          <i class="bi bi-x-lg"></i>
        </button>
      </div>

      {{-- Bouton ajouter un produit (visible quand aucun r&eacute;sultat) --}}
      <div id="exchangeProductNotFound" class="mt-2" style="display: none;">
        <div class="alert alert-warning py-2 px-3 d-flex align-items-center justify-content-between mb-0">
          <small><i class="bi bi-exclamation-triangle me-1"></i>Aucun produit trouv&eacute; pour cette recherche.</small>
          <button type="button" class="btn btn-sm btn-primary" id="openNewExchangeProductModal">
            <i class="bi bi-plus-circle me-1"></i>Ajouter un produit
          </button>
        </div>
      </div>
    </div>

    <div class="col-md-2 mb-3">
      <label for="exchange_quantity" class="form-label">Quantit&eacute; apport&eacute;e</label>
      <input type="number" step="1" min="1" class="form-control @error('exchange_quantity') is-invalid @enderror"
             id="exchange_quantity" name="exchange_quantity" value="{{ old('exchange_quantity', $sale?->exchange_details['quantity'] ?? 1) }}">
      @error('exchange_quantity')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
  </div>

  <div class="row">
    <div class="col-md-4 mb-3">
      <label for="exchange_added_amount" class="form-label">Montant ajout&eacute; par le client (FCFA)</label>
      <input type="number" step="0.01" min="0" class="form-control @error('exchange_added_amount') is-invalid @enderror"
             id="exchange_added_amount" name="exchange_added_amount" value="{{ old('exchange_added_amount', $sale?->exchange_details['added_amount'] ?? 0) }}">
      @error('exchange_added_amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
      <div class="form-text">Montant saisi manuellement, sans calcul automatique.</div>
    </div>
  </div>
</div>

@php
  $existingPaymentsCount = $sale?->invoice?->payments?->count() ?? 0;
@endphp
<div class="row">
  <div class="col-md-4 mb-3" id="totalColumn" style="display: {{ old('sale_type', $sale?->sale_type->value ?? 'vente') === 'echange' ? 'none' : 'block' }};">
    <label for="total_ttc" class="form-label">Total</label>
    <input type="number" step="0.01" min="0" class="form-control @error('total_ttc') is-invalid @enderror"
           id="total_ttc" name="total_ttc" value="{{ old('total_ttc', $sale?->total_ttc ?? 0) }}" readonly>
    @error('total_ttc')<div class="invalid-feedback">{{ $message }}</div>@enderror
    <div class="form-text">Le total est calculé automatiquement à partir des produits.</div>
  </div>
  <div class="col-md-4 mb-3">
    <label for="payment_method" class="form-label">Mode de paiement</label>
    <select id="payment_method" name="payment_method" class="form-select @error('payment_method') is-invalid @enderror">
      <option value="">— Non renseigné —</option>
      <option value="wave" @selected(old('payment_method') === 'wave')>Wave</option>
      <option value="orange_money" @selected(old('payment_method') === 'orange_money')>Orange Money</option>
      <option value="cash" @selected(old('payment_method') === 'cash')>Espèces</option>
    </select>
    @error('payment_method')<div class="invalid-feedback">{{ $message }}</div>@enderror
    @if($existingPaymentsCount > 0)
      <div class="form-text">Paiement déjà enregistré. Pour un complément, utilisez la fiche facture.</div>
    @else
      <div class="form-text">Enregistre automatiquement le paiement intégral à la validation.</div>
    @endif
  </div>
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

<div class="mb-3">
  <label for="notes" class="form-label">Observations</label>
  <textarea class="form-control @error('notes') is-invalid @enderror"
            id="notes" name="notes" rows="3">{{ old('notes', $sale->notes ?? '') }}</textarea>
  @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

@push('styles')
<style>
  #exchangeProductDropdown .list-group-item {
    cursor: pointer;
    transition: background-color 0.15s;
  }
  #exchangeProductDropdown .list-group-item:hover,
  #exchangeProductDropdown .list-group-item.active {
    background-color: #0d6efd;
    color: #fff;
  }
  #exchangeProductDropdown .list-group-item.active .text-muted {
    color: rgba(255,255,255,0.75) !important;
  }
</style>
@endpush

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function () {
    const saleTypeField = document.getElementById('sale_type');
    const exchangeFields = document.getElementById('exchangeFields');
    const addSaleItemButton = document.getElementById('addSaleItemButton');
    const saleItemsContainer = document.getElementById('saleItemsContainer');
    const saleItemTemplate = document.getElementById('saleItemTemplate');
    const productClientPrices = {
      @foreach($products as $product)
        {{ $product->id }}: {{ $product->sale_price }},
      @endforeach
    };
    const productSupplierPrices = {
      @foreach($products as $product)
        {{ $product->id }}: {{ $product->supplier_sale_price ?? $product->sale_price }},
      @endforeach
    };

    const totalColumn = document.getElementById('totalColumn');

    if (saleTypeField && exchangeFields) {
      saleTypeField.addEventListener('change', function () {
        const isEchange = this.value === 'echange';
        exchangeFields.style.display = isEchange ? 'block' : 'none';
        if (totalColumn) {
          totalColumn.style.display = isEchange ? 'none' : 'block';
        }
        calculateTotals();
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

      // Aucun calcul automatique pour les échanges : le total est géré côté serveur
      // à partir du montant ajouté par le client, saisi manuellement.
      if (saleTypeField && saleTypeField.value === 'echange') {
        return;
      }

      const totalField = document.getElementById('total_ttc');

      if (totalField) {
        totalField.value = Math.max(0, total).toFixed(2);
      }
    }

    let globalPriceTier = 'client';

    function applyPriceForTier(row) {
      const select = row.querySelector('select[name="product_id[]"]');
      const unitPriceInput = row.querySelector('.price-input');
      if (!select || !unitPriceInput) return;

      const productId = select.value;
      const prices = globalPriceTier === 'fournisseur' ? productSupplierPrices : productClientPrices;

      unitPriceInput.value = prices[productId] !== undefined ? Number(prices[productId]).toFixed(2) : 0;
      row.dataset.priceTier = globalPriceTier;
    }

    const globalPriceTierGroup = document.getElementById('globalPriceTierGroup');
    if (globalPriceTierGroup) {
      globalPriceTierGroup.querySelectorAll('.price-tier-btn').forEach(button => {
        button.addEventListener('click', function () {
          globalPriceTier = this.dataset.tier;
          globalPriceTierGroup.querySelectorAll('.price-tier-btn').forEach(btn => {
            btn.classList.toggle('active', btn === this);
          });

          saleItemsContainer.querySelectorAll('.sale-item-row').forEach(row => {
            applyPriceForTier(row);
          });
          calculateTotals();
        });
      });
    }

    function bindSaleItemEvents(container) {
      container.querySelectorAll('select[name="product_id[]"]').forEach(select => {
        select.addEventListener('change', function () {
          const row = this.closest('.sale-item-row');
          applyPriceForTier(row);
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

    // ───────────────────────────────────────────────────────────────
    // Modale création client (existant)
    // ───────────────────────────────────────────────────────────────
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

    // ───────────────────────────────────────────────────────────────
    // Autocomplétion produit retourné (échange)
    // ───────────────────────────────────────────────────────────────
    const exchangeSearchInput = document.getElementById('exchange_product_search');
    const exchangeProductIdField = document.getElementById('exchange_product_id');
    const exchangeDropdown = document.getElementById('exchangeProductDropdown');
    const exchangeProductSelected = document.getElementById('exchangeProductSelected');
    const exchangeProductSelectedText = document.getElementById('exchangeProductSelectedText');
    const exchangeProductNotFound = document.getElementById('exchangeProductNotFound');
    const exchangeProductClear = document.getElementById('exchangeProductClear');
    const openNewExchangeProductModal = document.getElementById('openNewExchangeProductModal');

    let exchangeSearchTimeout = null;
    let exchangeActiveIndex = -1;
    let exchangeLastResults = [];

    if (exchangeSearchInput) {
      // Recherche avec délai (debounce)
      exchangeSearchInput.addEventListener('input', function () {
        const query = this.value.trim();
        exchangeActiveIndex = -1;

        if (query.length < 2) {
          exchangeDropdown.style.display = 'none';
          exchangeProductNotFound.style.display = 'none';
          return;
        }

        clearTimeout(exchangeSearchTimeout);
        exchangeSearchTimeout = setTimeout(() => fetchExchangeProducts(query), 300);
      });

      // Navigation au clavier
      exchangeSearchInput.addEventListener('keydown', function (e) {
        const items = exchangeDropdown.querySelectorAll('.list-group-item');
        if (!items.length) return;

        if (e.key === 'ArrowDown') {
          e.preventDefault();
          exchangeActiveIndex = Math.min(exchangeActiveIndex + 1, items.length - 1);
          updateActiveItem(items);
        } else if (e.key === 'ArrowUp') {
          e.preventDefault();
          exchangeActiveIndex = Math.max(exchangeActiveIndex - 1, 0);
          updateActiveItem(items);
        } else if (e.key === 'Enter') {
          e.preventDefault();
          if (exchangeActiveIndex >= 0 && items[exchangeActiveIndex]) {
            items[exchangeActiveIndex].click();
          }
        } else if (e.key === 'Escape') {
          exchangeDropdown.style.display = 'none';
        }
      });

      // Fermer le dropdown au clic extérieur
      document.addEventListener('click', function (e) {
        if (!exchangeSearchInput.contains(e.target) && !exchangeDropdown.contains(e.target)) {
          exchangeDropdown.style.display = 'none';
        }
      });
    }

    function updateActiveItem(items) {
      items.forEach((item, idx) => {
        item.classList.toggle('active', idx === exchangeActiveIndex);
      });
      if (items[exchangeActiveIndex]) {
        items[exchangeActiveIndex].scrollIntoView({ block: 'nearest' });
      }
    }

    async function fetchExchangeProducts(query) {
      try {
        const response = await fetch(`{{ route('sales.exchange-products.search') }}?q=${encodeURIComponent(query)}`, {
          headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
          }
        });

        const products = await response.json();
        exchangeLastResults = products;
        exchangeDropdown.innerHTML = '';

        if (products.length === 0) {
          exchangeDropdown.style.display = 'none';
          exchangeProductNotFound.style.display = 'block';
          return;
        }

        exchangeProductNotFound.style.display = 'none';

        products.forEach((product, index) => {
          const item = document.createElement('button');
          item.type = 'button';
          item.className = 'list-group-item list-group-item-action py-2 px-3';
          item.innerHTML = `
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <strong>${product.reference}</strong> &mdash; ${product.name}
                ${product.brand ? '<span class="text-muted">(' + product.brand + ')</span>' : ''}
              </div>
              <span class="badge bg-secondary">${Number(product.sale_price).toLocaleString('fr-FR')} FCFA</span>
            </div>
          `;
          item.addEventListener('click', () => selectExchangeProduct(product));
          exchangeDropdown.appendChild(item);
        });

        exchangeDropdown.style.display = 'block';
      } catch (error) {
        console.error('Erreur lors de la recherche de produits :', error);
      }
    }

    function selectExchangeProduct(product) {
      exchangeProductIdField.value = product.id;
      exchangeSearchInput.value = product.reference + ' \u2014 ' + product.name;
      exchangeDropdown.style.display = 'none';
      exchangeProductNotFound.style.display = 'none';

      // Afficher le produit s\u00e9lectionn\u00e9
      exchangeProductSelectedText.innerHTML = `
        <i class="bi bi-check-circle me-1"></i>
        <strong>${product.reference}</strong> \u2014 ${product.name}
        ${product.brand ? '<span class="text-muted">(' + product.brand + ')</span>' : ''}
      `;
      exchangeProductSelected.style.display = 'flex';
    }

    // Effacer la s\u00e9lection
    if (exchangeProductClear) {
      exchangeProductClear.addEventListener('click', function () {
        exchangeProductIdField.value = '';
        exchangeSearchInput.value = '';
        exchangeProductSelected.style.display = 'none';
        exchangeProductSelectedText.innerHTML = '';
        exchangeSearchInput.focus();
      });
    }

    // ───────────────────────────────────────────────────────────────
    // Modale cr\u00e9ation produit d'\u00e9change
    // ───────────────────────────────────────────────────────────────
    if (openNewExchangeProductModal) {
      openNewExchangeProductModal.addEventListener('click', function () {
        // Pr\u00e9-remplir le nom avec la recherche en cours
        const nameField = document.getElementById('new_exchange_product_name');
        if (nameField && exchangeSearchInput.value.trim()) {
          nameField.value = exchangeSearchInput.value.trim();
        }
        const modalEl = document.getElementById('newExchangeProductModal');
        const modal = new bootstrap.Modal(modalEl);
        modal.show();
      });
    }

    const saveExchangeProductBtn = document.getElementById('saveNewExchangeProductButton');
    const newExchangeProductForm = document.getElementById('newExchangeProductForm');

    if (saveExchangeProductBtn && newExchangeProductForm) {
      saveExchangeProductBtn.addEventListener('click', async function () {
        const formData = new FormData(newExchangeProductForm);

        // R\u00e9initialiser les erreurs
        document.querySelectorAll('#newExchangeProductForm .invalid-feedback').forEach(el => {
          el.textContent = '';
        });
        document.querySelectorAll('#newExchangeProductForm .is-invalid').forEach(el => {
          el.classList.remove('is-invalid');
        });

        // D\u00e9sactiver le bouton pendant le traitement
        saveExchangeProductBtn.disabled = true;
        saveExchangeProductBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Enregistrement...';

        try {
          const response = await fetch('{{ route('sales.exchange-products.store') }}', {
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
                const input = document.getElementById(`new_exchange_product_${field}`);
                const feedback = document.getElementById(`new_exchange_product_${field}_error`);
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

          // S\u00e9lectionner automatiquement le nouveau produit
          selectExchangeProduct(data);

          // Fermer la modale
          const modalEl = document.getElementById('newExchangeProductModal');
          const modal = bootstrap.Modal.getInstance(modalEl);
          modal.hide();

          // R\u00e9initialiser le formulaire
          newExchangeProductForm.reset();

          // Masquer le message "aucun produit trouv\u00e9"
          exchangeProductNotFound.style.display = 'none';

        } catch (error) {
          console.error('Erreur lors de la cr\u00e9ation du produit :', error);
        } finally {
          saveExchangeProductBtn.disabled = false;
          saveExchangeProductBtn.innerHTML = '<i class="bi bi-check-lg me-1"></i>Enregistrer le produit';
        }
      });
    }
  });
</script>
@endpush
