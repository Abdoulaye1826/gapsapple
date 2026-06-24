<div class="modal fade" id="newExchangeProductModal" tabindex="-1" aria-labelledby="newExchangeProductModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="newExchangeProductModalLabel">
          <i class="bi bi-plus-circle me-2"></i>Ajouter un nouveau produit
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
      </div>
      <div class="modal-body">
        <div class="alert alert-info d-flex align-items-center" role="alert">
          <i class="bi bi-info-circle me-2"></i>
          <small>Ce produit sera ajout&eacute; au catalogue et s&eacute;lectionn&eacute; automatiquement comme produit retourn&eacute;.</small>
        </div>
        <form id="newExchangeProductForm">
          @csrf
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="new_exchange_product_name" class="form-label">Nom du produit <span class="text-danger">*</span></label>
              <input type="text" id="new_exchange_product_name" name="name" class="form-control" required>
              <div class="invalid-feedback" id="new_exchange_product_name_error"></div>
            </div>
            <div class="col-md-6 mb-3">
              <label for="new_exchange_product_reference" class="form-label">R&eacute;f&eacute;rence</label>
              <input type="text" id="new_exchange_product_reference" name="reference" class="form-control" placeholder="G&eacute;n&eacute;r&eacute;e automatiquement si vide">
              <div class="invalid-feedback" id="new_exchange_product_reference_error"></div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="new_exchange_product_category_id" class="form-label">Cat&eacute;gorie <span class="text-danger">*</span></label>
              <select id="new_exchange_product_category_id" name="category_id" class="form-select" required>
                <option value="">— S&eacute;lectionnez —</option>
                @foreach($categories as $category)
                  <option value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
              </select>
              <div class="invalid-feedback" id="new_exchange_product_category_id_error"></div>
            </div>
            <div class="col-md-6 mb-3">
              <label for="new_exchange_product_brand" class="form-label">Marque</label>
              <input type="text" id="new_exchange_product_brand" name="brand" class="form-control">
              <div class="invalid-feedback" id="new_exchange_product_brand_error"></div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-4 mb-3">
              <label for="new_exchange_product_sale_price" class="form-label">Valeur estim&eacute;e (Prix de vente) <span class="text-danger">*</span></label>
              <input type="number" step="0.01" min="0" id="new_exchange_product_sale_price" name="sale_price" class="form-control" required>
              <div class="invalid-feedback" id="new_exchange_product_sale_price_error"></div>
            </div>
            <div class="col-md-4 mb-3">
              <label for="new_exchange_product_purchase_price" class="form-label">Prix d'achat</label>
              <input type="number" step="0.01" min="0" id="new_exchange_product_purchase_price" name="purchase_price" class="form-control" value="0">
              <div class="invalid-feedback" id="new_exchange_product_purchase_price_error"></div>
            </div>
            <div class="col-md-4 mb-3">
              <label for="new_exchange_product_stock_quantity" class="form-label">Quantit&eacute; en stock <span class="text-danger">*</span></label>
              <input type="number" step="1" min="0" id="new_exchange_product_stock_quantity" name="stock_quantity" class="form-control" value="1" required>
              <div class="invalid-feedback" id="new_exchange_product_stock_quantity_error"></div>
            </div>
          </div>
          <div class="mb-3">
            <label for="new_exchange_product_description" class="form-label">Description</label>
            <textarea id="new_exchange_product_description" name="description" class="form-control" rows="2"></textarea>
            <div class="invalid-feedback" id="new_exchange_product_description_error"></div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
        <button type="button" class="btn btn-primary" id="saveNewExchangeProductButton">
          <i class="bi bi-check-lg me-1"></i>Enregistrer le produit
        </button>
      </div>
    </div>
  </div>
</div>
