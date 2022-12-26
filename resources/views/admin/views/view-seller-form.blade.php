      <div class="modal fade" id="view-seller" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog" role="document">
          <div class="modal-content form">
            <div class="modal-header">
              <h2 class="modal-title">معلومات البائع (#<span id="view-seller-id"></span>)</h2>
            </div>
            <div class="modal-body">

              <div class="form-group">
                <h5>الاسم الكامل:</h5>
                <input type="text" class="form-control" name="seller_fullname">
              </div>
              <hr>

              <div class="form-group">
                <h5>الاسم المتجر:</h5>
                <input type="text" class="form-control" name="store_name">
              </div>
              <hr>

              <div class="form-group">
                <h5>البريد الإلكتروني:</h5>
                <input type="email" class="form-control" name="seller_email">
              </div>
              <hr>

              <div class="form-group">
                <h5>رقم الهاتف:</h5>
                <input type="phone" class="form-control" name="seller_phone">
              </div>
              <hr>

              <div class="form-group">
                <h5>عنوان المتجر:</h5>
                <input type="address" class="form-control" name="store_address">
              </div>
              <hr>

              <div class="form-group">
                <h5>الرصيد:</h5>
                <input type="number" class="form-control" name="seller_balance">
              </div>
              <hr>

              <div class="form-group">
                <h5>الرصيد القيد التحقق:</h5>
                <input type="number" class="form-control" name="seller_checking_balance">
              </div>
              <hr>

              <div class="form-group">
                <h5>الوضعية:</h5>
                <select class="form-control" name="status">
                  <option value="checking">فيد التحقق</option>
                  <option value="accepted">تم الموافقة</option>
                  <option value="refused">رفض</option>
                </select>
                <input type="text" class="form-control" placeholder="السبب" name="status-description">
                <br>
                <button class="btn btn-success" style="width: 100%;" name="change-status">تغير الوضعية</button>
              </div>
              <hr>

              <div class="form-group">
                <h5>تم الرد في:</h5>
                <input type="datetime" class="form-control" name="answered_at">
              </div>
              <hr>

              <div class="form-group">
                <h5>أسعار النقل:</h5>
                <select class="form-control" name="delivery_states">
                </select>
                <div class="form-control" name="delivery_state_price"></div>
              </div>
              <hr>

              <div class="form-group">
                <h5>أنشئ في:</h5>
                <input type="datetime" class="form-control" name="created_at">
              </div>

            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-danger" action="close">إلغاء</button>
            </div>
          </div>
        </div>
      </div>
