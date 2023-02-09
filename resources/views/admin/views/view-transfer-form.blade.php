      <div class="modal fade" id="view-transfer" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog" role="document">
          <div class="modal-content form">
            <div class="modal-header">
              <h2 class="modal-title">معلومات التحويل(#<span id="view-transfer-id"></span>)</h2>
            </div>
            <div class="modal-body">

              <div class="form-group">
                <h5>المستخدم:</h5>
                <input type="text" class="form-control" name="user">
              </div>
              <hr>

              <div class="form-group">
                <h5>الرصيد المستقبل:</h5>
                <input type="currency" class="form-control" name="sended_balance">
              </div>
              <hr>

              <div class="form-group">
                <h5>الرصيد المرسل:</h5>
                <input type="currency" class="form-control" name="received_balance">
              </div>
              <hr>

              <div class="form-group">
                <h5>العملة المستقبلة:</h5>
                <input type="text" class="form-control" name="sended_currency">
              </div>
              <hr>

              <div class="form-group">
                <h5>العملة المرسلة:</h5>
                <input type="text" class="form-control" name="received_currency">
              </div>
              <hr>

              <div class="form-group">
                <h5>المعلومات:</h5>
                <div class="form-control" style="height: fit-content; text-align: start;" name="data"></div>
              </div>
              <hr>

              <div class="form-group">
                <h5>الوصل:</h5>
                <div class="form-control proof-image" name="proof"></div>
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
