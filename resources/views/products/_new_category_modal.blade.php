<div class="modal fade" id="newCategoryModal" tabindex="-1" aria-labelledby="newCategoryModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="newCategoryModalLabel">Nouvelle catégorie</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
      </div>
      <div class="modal-body">
        <form id="newCategoryForm">
          @csrf
          <div class="mb-3">
            <label for="new_category_name" class="form-label">Nom <span class="req">*</span></label>
            <input type="text" id="new_category_name" name="name" class="form-control" required>
            <div class="invalid-feedback" id="new_category_name_error"></div>
          </div>
          <div class="mb-3">
            <label for="new_category_description" class="form-label">Description</label>
            <textarea id="new_category_description" name="description" class="form-control" rows="2"></textarea>
            <div class="invalid-feedback" id="new_category_description_error"></div>
          </div>
          <div class="mb-3">
            <div class="form-check form-switch">
              <input class="form-check-input" type="checkbox" id="new_category_is_active" name="is_active" value="1" checked>
              <label class="form-check-label" for="new_category_is_active">Catégorie active</label>
            </div>
            <div class="invalid-feedback" id="new_category_is_active_error"></div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
        <button type="button" class="btn btn-primary" id="saveNewCategoryButton">
          <i class="bi bi-check-lg me-1"></i>Créer
        </button>
      </div>
    </div>
  </div>
</div>
