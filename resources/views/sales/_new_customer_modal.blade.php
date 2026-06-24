<div class="modal fade" id="newCustomerModal" tabindex="-1" aria-labelledby="newCustomerModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="newCustomerModalLabel">Nouveau client</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
      </div>
      <div class="modal-body">
        <form id="newCustomerForm">
          @csrf
          <div class="mb-3">
            <label for="new_customer_full_name" class="form-label">Nom complet</label>
            <input type="text" id="new_customer_full_name" name="full_name" class="form-control">
            <div class="invalid-feedback" id="new_customer_full_name_error"></div>
          </div>
          <div class="mb-3">
            <label for="new_customer_phone" class="form-label">Téléphone</label>
            <input type="text" id="new_customer_phone" name="phone" class="form-control">
            <div class="invalid-feedback" id="new_customer_phone_error"></div>
          </div>
          <div class="mb-3">
            <label for="new_customer_email" class="form-label">Email</label>
            <input type="email" id="new_customer_email" name="email" class="form-control">
            <div class="invalid-feedback" id="new_customer_email_error"></div>
          </div>
          <div class="mb-3">
            <label for="new_customer_address" class="form-label">Adresse</label>
            <input type="text" id="new_customer_address" name="address" class="form-control">
            <div class="invalid-feedback" id="new_customer_address_error"></div>
          </div>
          <div class="mb-3">
            <label for="new_customer_city" class="form-label">Ville</label>
            <input type="text" id="new_customer_city" name="city" class="form-control">
            <div class="invalid-feedback" id="new_customer_city_error"></div>
          </div>
          <div class="mb-3">
            <label for="new_customer_registered_at" class="form-label">Date d'inscription</label>
            <input type="date" id="new_customer_registered_at" name="registered_at" value="{{ now()->format('Y-m-d') }}" class="form-control">
            <div class="invalid-feedback" id="new_customer_registered_at_error"></div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
        <button type="button" class="btn btn-primary" id="saveNewCustomerButton">Créer</button>
      </div>
    </div>
  </div>
</div>
