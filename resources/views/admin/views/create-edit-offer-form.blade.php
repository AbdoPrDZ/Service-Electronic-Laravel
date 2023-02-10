<div class="modal fade" id="create-edit-offer" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content form">
      <div class="modal-header">
        <h2 class="modal-title">إنشاء نوع</h2>
      </div>
      <div class="modal-body">

        <div class="form-group">
          <h5>العنوان(En):</h5>
          <input type="text" class="form-control" name="offer_title_en">
        </div>
        <hr>

        <div class="form-group">
          <h5>العنوان(Ar):</h5>
          <input type="text" class="form-control" name="offer_title_ar">
        </div>
        <hr>

        <div class="form-group">
          <h5>الوصف(En):</h5>
          <textarea class="form-control" name="offer_description_en"></textarea>
        </div>
        <hr>

        <div class="form-group">
          <h5>الوصف(Ar):</h5>
          <textarea class="form-control" name="offer_description_ar"></textarea>
        </div>
        <hr>

        <div class="form-group">
          <h5>العروض المرفقة:</h5>
          <div id="offer-sub-offers" class="multi-input"
               inputs='[{"name": "name", "text": "اسم العرض", "type": "string"},
                        {"name": "title_en", "text": "عنوان (En)", "type": "string"},
                        {"name": "title_ar", "text": "عنوان (Ar)", "type": "string"},
                        {"name": "price", "text": "السعر", "type": "string"}]'
               add-btn-text="إضافة">
            <div class="multi-input-header"></div>
            <div class="multi-input-body"></div>
          </div>
        </div>
        <hr>

        <div class="form-group">
          <h5>المعلومات المطلوبة:</h5>
          <div id="offer-fields" class="multi-input"
               inputs='[{"name": "name", "text": "الاسم", "type": "string"},
                        {"name": "title_en", "text": "عنوان (En)", "type": "string"},
                        {"name": "title_ar", "text": "عنوان (Ar)", "type": "string"},
                        {"name": "validate", "text": "القواعد", "type": "select", "options": "$rules"}]'
               add-btn-text="إضافة">
            <div class="multi-input-header"></div>
            <div class="multi-input-body"></div>
          </div>
        </div>
        <hr>

        <div class="form-group">
          <h5>المعلومات:</h5>
          <div id="offer-data" class="multi-input"
               inputs='[{"name": "name", "text": "الاسم", "type": "string"},
                        {"name": "title_en", "text": "العنوان(En)", "type": "string"},
                        {"name": "title_ar", "text": "العنوان(Ar)", "type": "string"},
                        {{-- {"name": "type", "text": "النوع", "type": "select", "options": "$inputTypes"}, --}}
                        {"name": "validate", "text": "القواعد", "type": "select", "options": "$rules"}]'
               add-btn-text="إضافة">
            <div class="multi-input-header"></div>
            <div class="multi-input-body"></div>
          </div>
        </div>
        <hr>

        <div class="form-group">
          <h5>الصورة:</h5>
          <button class="btn btn-img-picker" id="offer-image-picker">
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
