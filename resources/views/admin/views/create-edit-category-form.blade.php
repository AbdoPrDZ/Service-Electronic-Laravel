<div class="modal fade" id="create-edit-category" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content form">
      <div class="modal-header">
        <h2 class="modal-title">إنشاء نوع</h2>
      </div>
      <div class="modal-body">

        <div class="form-group">
          <h5>الأسماء:</h5>
          <div id="category-names" class="multi-input"
               inputs='[{"name": "lang_code", "text": "رمز اللغة", "type": "string"}, {"name": "text", "text": "الاسم", "type": "text"}]'
               add-btn-text="إضافة">
            <div class="multi-input-header"></div>
            <div class="multi-input-body"></div>
          </div>
        </div>
        <hr>

        <div class="form-group">
          <h5>الصورة:</h5>
          <button class="btn btn-img-picker" id="category-image-picker">
            <span class="material-symbols-sharp pick-icon">add_a_photo</span>
          </button>
        </div>
        <hr>

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" action="create">إنشاء</button>
        <button type="button" class="btn btn-danger" action="close">إلغاء</button>
      </div>
    </div>
  </div>
</div>
