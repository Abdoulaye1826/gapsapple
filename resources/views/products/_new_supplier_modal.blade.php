<div class="modal fade" id="newSupplierModal" tabindex="-1" aria-labelledby="newSupplierModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="newSupplierModalLabel">Nouveau fournisseur</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
      </div>
      <div class="modal-body">
        <form id="newSupplierForm">
          @csrf
          <div class="mb-3">
            <label for="new_supplier_name" class="form-label">Nom <span class="req">*</span></label>
            <input type="text" id="new_supplier_name" name="name" class="form-control" required>
            <div class="invalid-feedback" id="new_supplier_name_error"></div>
          </div>
          <div class="mb-3">
            <label for="new_supplier_phone" class="form-label">Téléphone <span class="req">*</span></label>
            <input type="text" id="new_supplier_phone" name="phone" class="form-control" placeholder="+221 77 123 45 67" required>
            <div class="invalid-feedback" id="new_supplier_phone_error"></div>
          </div>
          <div class="mb-3">
            <label for="new_supplier_email" class="form-label">Email</label>
            <input type="email" id="new_supplier_email" name="email" class="form-control">
            <div class="invalid-feedback" id="new_supplier_email_error"></div>
          </div>
          <div class="mb-3">
            <label for="new_supplier_address" class="form-label">Adresse</label>
            <input type="text" id="new_supplier_address" name="address" class="form-control">
            <div class="invalid-feedback" id="new_supplier_address_error"></div>
          </div>
          <div class="mb-3">
            <label for="new_supplier_country" class="form-label">Pays</label>
            <input type="text" id="new_supplier_country" name="country" class="form-control" value="Sénégal">
            <div class="invalid-feedback" id="new_supplier_country_error"></div>
          </div>
          <div class="mb-3">
            <div class="form-check form-switch">
              <input class="form-check-input" type="checkbox" id="new_supplier_is_active" name="is_active" value="1" checked>
              <label class="form-check-label" for="new_supplier_is_active">Fournisseur actif</label>
            </div>
            <div class="invalid-feedback" id="new_supplier_is_active_error"></div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
        <button type="button" class="btn btn-primary" id="saveNewSupplierButton">
          <i class="bi bi-check-lg me-1"></i>Créer
        </button>
      </div>
    </div>
  </div>
</div>
