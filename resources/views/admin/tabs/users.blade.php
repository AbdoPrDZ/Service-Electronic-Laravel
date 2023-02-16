
            <div id="all-users-recharges" class="custom-table tab-view-tab-item" checkbox="true" for="users">
              <h3 class="table-refresh" table="all-users-recharges">تحديث <span class="material-symbols-sharp"></span></h3>
              <div class="custom-table-header">
                <h2 class="custom-table-title">إداعات المستخدمين</h2>
                <div class="custom-table-header-actions">
                  {{-- <button class="btn btn-danger need-select" action="delete" disabled>
                    <span class="material-symbols-sharp">delete</span> Delete
                  </button> --}}
                </div>
              </div>
              <table class="table table-bordered table-condensed table-hover table-striped text-center">
                <thead>
                  <th>
                    <span class="custom-checkbox">
                      <input type="checkbox" id="all-users-recharges-select-all" class="select-all">
                      <label for="all-users-recharges-select-all"></label>
                    </span>
                  </th>
                  {{-- <th style="min-width: 50px; max-width: 50px;">#</th> --}}
                  <th style="min-width: 130px;">الصورة</th>
                  <th style="min-width: 130px;">معلومات المستخدم</th>
                  <th style="min-width: 90px;">رصيد المستخدم</th>
                  <th style="min-width: 50px; max-width: 50px;">رقم التحويل</th>
                  <th style="min-width: 90px;">الرصيد المرسل</th>
                  <th style="min-width: 90px;">الرصيد المستقبل</th>
                  <th style="min-width: 80px; max-width: 80px;">أنشئ في</th>
                  <th style="min-width: 80px; max-width: 80px;"></th>
                </thead>
                <tbody>
                </tbody>
                <tfoot>
                  <tr>
                    <th></th>
                    {{-- <th search="true">#</th> --}}
                    <th></th>
                    <th search="true">معلومات المستخدم</th>
                    <th search="true">رصيد المستخدم</th>
                    <th search="true">رقم التحويل</th>
                    <th search="true">الرصيد المرسل</th>
                    <th search="true">الرصيد المستقبل</th>
                    <th search="true">أنشئ في</th>
                    <th></th>
                  </tr>
                </tfoot>
              </table>
            </div>

            <div id="all-users-withdrawes" class="custom-table tab-view-tab-item" checkbox="true" for="users">
              <h3 class="table-refresh" table="all-users-withdrawes">تحديث <span class="material-symbols-sharp"></span></h3>
              <div class="custom-table-header">
                <h2 class="custom-table-title">سحوبات المستخدمين</h2>
                <div class="custom-table-header-actions">
                  {{-- <button class="btn btn-danger need-select" action="delete" disabled>
                    <span class="material-symbols-sharp">delete</span> Delete
                  </button> --}}
                </div>
              </div>
              <table class="table table-bordered table-condensed table-hover table-striped text-center">
                <thead>
                  <th>
                    <span class="custom-checkbox">
                      <input type="checkbox" id="all-users-withdrawes-select-all" class="select-all">
                      <label for="all-users-withdrawes-select-all"></label>
                    </span>
                  </th>
                  {{-- <th style="min-width: 50px; max-width: 50px;">#</th> --}}
                  <th style="min-width: 130px;">الصورة</th>
                  <th style="min-width: 130px;">معلومات المستخدم</th>
                  <th style="min-width: 90px;">رصيد المستخدم</th>
                  <th style="min-width: 90px;">الرصيد المسحوب من المنصة</th>
                  <th style="min-width: 90px;">الرصيد المسحوب</th>
                  <th style="min-width: 80px; max-width: 80px;">أنشئ في </th>
                  <th style="min-width: 80px; max-width: 80px;"></th>
                </thead>
                <tbody>
                </tbody>
                <tfoot>
                  <tr>
                    <th></th>
                    {{-- <th search="true">#</th> --}}
                    <th></th>
                    <th search="true">معلومات المستخدم</th>
                    <th search="true">رصيد المستخدم</th>
                    <th search="true">الرصيد المسحوب من المنصة</th>
                    <th search="true">الرصيد المسحوب</th>
                    <th search="true">أنشئ في</th>
                    <th></th>
                  </tr>
                </tfoot>
              </table>
            </div>

            <div id="all-users" class="custom-table tab-view-tab-item" checkbox="true" for="users">
              <h3 class="table-refresh" table="all-users">تحديث</h3>
              <div class="custom-table-header">
                <h2 class="custom-table-title">المستخدمون</h2>
                <div class="custom-table-header-actions">
                  <button class="btn btn-success need-select" action="send-notification" disabled>
                    <span class="material-symbols-sharp">chat</span> إرسال رسالة
                  </button>
                  <button class="btn btn-warning need-select" action="clear-notification" disabled>
                    <span class="material-symbols-sharp">cached</span> حذف الرسائل
                  </button>
                  <button class="btn btn-danger need-select" action="delete" disabled>
                    <span class="material-symbols-sharp">delete</span> حذف
                  </button>
                </div>
              </div>
              <table class="table table-bordered table-condensed table-hover table-striped text-center">
                <thead>
                  <th>
                    <span class="custom-checkbox">
                      <input type="checkbox" id="all-users-select-all" class="select-all">
                      <label for="all-users-select-all"></label>
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
                  <th style="min-width: 80px; max-width: 80px;"></th>
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
                    <th></th>
                  </tr>
                </tfoot>
              </table>
            </div>
