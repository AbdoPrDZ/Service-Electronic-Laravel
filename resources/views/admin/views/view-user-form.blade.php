
      <div class="modal fade" id="view-user" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog" role="document">
          <div class="modal-content form">
            <div class="modal-header">
              <h2 class="modal-title">معلومات المستخدم(#<span id="view-user-id"></span>)</h2>
            </div>
            <div class="modal-body">

              <div class="form-group">
                <h5>ااسم الاول:</h5>
                <input type="text" class="form-control" name="firstname">
              </div>
              <hr>

              <div class="form-group">
                <h5>الاسم الثاني:</h5>
                <input type="text" class="form-control" name="lastname">
              </div>
              <hr>

              <div class="form-group">
                <h5>البريد الالكتروني:</h5>
                <input type="email" class="form-control" name="email">
              </div>
              <hr>

              <div class="form-group">
                <h5>رقم الهاتف:</h5>
                <input type="text" class="form-control" name="phone">
              </div>
              <hr>

              <div class="form-group">
                <h5>وضعية البريد الالكتروني</h5>
                <div class="form-control" name="email_verified"></div>
              </div>
              <hr>

              {{-- <div class="form-group">
                <h5>وضعية الهوية</h5>
                <div class="form-control" name="identity_verifited"></div>
              </div>
              <hr> --}}

              {{-- <div class="form-group">
              </div>
              <hr> --}}

              <div class="form-group">
                <h5>وضعية الهوية:</h5>
                <select class="form-control" name="status">
                  <option value="checking">غير محقق</option>
                  <option value="checking">فيد التحقق</option>
                  <option value="verifited">تم التحقق</option>
                  <option value="refused">رفض</option>
                </select>
                <input type="text" class="form-control" placeholder="السبب" name="status-description">
                <br>
                <button class="btn btn-primary" name="preview-identity-images" style="width: 100%;" onclick="">عرض صور الهوية</button>
                <br>
                <button class="btn btn-success" style="width: 100%;" name="change-status">تغير وضعية الهوية</button>
              </div>
              <hr>

              <div class="form-group">
                <h5>أنشئ في</h5>
                <input type="text" class="form-control" name="created_at">
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-danger" action="close">Cancel</button>
            </div>
          </div>
        </div>
      </div>
