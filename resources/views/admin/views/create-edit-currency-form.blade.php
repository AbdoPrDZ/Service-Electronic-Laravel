<div class="modal fade" id="create-edit-currency" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content form">
      <div class="modal-header">
        <h2 class="modal-title">إنشاء العملة</h2>
      </div>
      <div class="modal-body">

        <div class="form-group">
          <h5>الاسم:</h5>
          <input type="text" class="form-control" name="currency_name">
        </div>
        <hr>

        <div class="form-group">
          <h5>الرمز:</h5>
          <input type="text" class="form-control" name="currency_char">
        </div>
        <hr>

        <div class="form-group">
          <h5>الرصيد:</h5>
          <input type="number" class="form-control" name="currency_balance">
        </div>
        <hr>


        <div class="form-group">
          <h5>المحفظة:</h5>
          <input type="text" class="form-control" name="currency_wallet">
        </div>
        <hr>

        <div class="form-group">
          <h5>لصورة:</h5>
          <button class="btn btn-img-picker" id="currency-image-picker">
            <span class="material-symbols-sharp pick-icon">add_a_photo</span>
          </button>
        </div>
        <hr>

        <div class="form-group">
          <h5>الأسعار:</h5>
          <div id="currency-prices" class="multi-input"
               inputs='[{"name": "currency_id", "text": "رقم العملة", "type": "number"}, {"name": "buy_price", "text": "سعر الشراء", "type": "number"}, {"name": "sell_price", "text": "سعر البيع", "type": "number"}]'
               {{-- inputs='[{"name": "currency_id", "text": "رقم العملة", "type": "number"}, {"name": "price", "text": "السعر", "type": "number"}]' --}}
               add-btn-text="إضافة">
            <div class="multi-input-header"></div>
            <div class="multi-input-body"></div>
          </div>
        </div>
        <hr>

        <div class="form-group">
          <div class="custom-control custom-checkbox">
            <input type="checkbox" class="custom-control-input" id="proof-required" name="proof_is_required">
            <label class="custom-control-label" for="proof-required">صورة الوصل مطلوبة</label>
          </div>
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
