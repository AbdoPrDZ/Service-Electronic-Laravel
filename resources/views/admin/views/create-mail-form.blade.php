<div class="modal fade" id="create-mail" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog" style="min-width: 95vw;" role="document">
    <div class="modal-content form">
      <div class="modal-header">
        <h2 class="modal-title">إرسال الإيمايل</h2>
      </div>
      <div class="modal-body">

        <div class="form-group">
          <h5>العنوان:</h5>
          <input type="text" class="form-control" name="title">
        </div>
        <hr>

        <div class="form-group">
          <h5>القالب:</h5>
          <select name="template" class="form-control">
          </select>
        </div>
        <hr>

        <div class="form-group" name="data">
          <h5>البيانات:</h5>
          <div class="data_fields"></div>
        </div>
        <hr>

        <div id="create-mail-all-users" class="custom-table" style="overflow: auto" checkbox="true">
          <div class="custom-table-header">
            <h5>المستخدمون:</h5>
          </div>
          <table class="table table-bordered table-condensed table-hover table-striped text-center">
            <thead>
              <th>
                <span class="custom-checkbox">
                  <input type="checkbox" id="create-mail-all-users-select-all" class="select-all">
                  <label for="create-mail-all-users-select-all"></label>
                </span>
              </th>
              <th style="min-width: 50px; max-width: 50px;">#</th>
              <th style="min-width: 130px;">الصورة</th>
              <th style="min-width: 130px;">الاسم الكامل</th>
              <th style="min-width: 100px;">البريد الالكتروني</th>
              <th style="min-width: 100px;">رقم الهاتف</th>
              <th style="min-width: 90px;">الرصيد</th>
              <th style="min-width: 90px;">رصيد قيد التحقق</th>
              <th style="min-width: 80px; max-width: 80px;">وضعية البريد الإلكتروني</th>
              <th style="min-width: 80px; max-width: 80px;">وضعية الهوية</th>
              <th style="min-width: 92px; max-width: 92px;">أنشئ في</th>
            </thead>
            <tbody>
            </tbody>
            <tfoot>
              <tr>
                <th></th>
                <th search="true">#</th>
                <th></th>
                <th search="true">الاسم الكامل</th>
                <th search="true">البريد الالكتروني</th>
                <th search="true">رقم الهاتف</th>
                <th search="true">الرصيد</th>
                <th search="true">رصيد قيد التحقق</th>
                <th search="true">وضعية البريد الإلكتروني</th>
                <th search="true">وضعية الهوية</th>
                <th search="true">أنشئ في</th>
              </tr>
            </tfoot>
          </table>
        </div>

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" action="create">إنشاء</button>
        <button type="button" class="btn btn-danger" action="close">إلغاء</button>
      </div>
    </div>
  </div>
</div>
