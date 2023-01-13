
            <div id="all-templates" class="custom-table tab-view-tab-item" checkbox="true" for="mails">
              <h3 class="table-refresh" table="all-templates">تحديث</h3>
              <div class="custom-table-header">
                <h2 class="custom-table-title">القوالب</h2>
                <div class="custom-table-header-actions">
                  <button class="btn btn-primary" action="create">
                    <span class="material-symbols-sharp">add</span>إنشاء
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
                      <input type="checkbox" id="all-templates-select-all" class="select-all">
                      <label for="all-templates-select-all"></label>
                    </span>
                  </th>
                  <th style="min-width: 130px;">الاسم</th>
                  <th style="min-width: 120px;">البيانات</th>
                  <th style="min-width: 120px;">أنشئ في</th>
                  <th style="min-width: 120px; max-width: 120px;"></th>
                </thead>
                <tbody>
                </tbody>
                <tfoot>
                  <tr>
                    <th></th>
                    <th search="true">الاسم</th>
                    <th search="true">البيانات</th>
                    <th search="true">أنشئ في</th>
                    <th></th>
                  </tr>
                </tfoot>
              </table>
            </div>

            <div id="all-mails" class="custom-table tab-view-tab-item" checkbox="true" for="mails">
              <h3 class="table-refresh" table="all-mails">تحديث</h3>
              <div class="custom-table-header">
                <h2 class="custom-table-title">الإيمايلات</h2>
                <div class="custom-table-header-actions">
                  <button class="btn btn-primary" action="create">
                    <span class="material-symbols-sharp">add</span>إنشاء
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
                      <input type="checkbox" id="all-mails-select-all" class="select-all">
                      <label for="all-mails-select-all"></label>
                    </span>
                  </th>
                  <th style="min-width: 50px; max-width: 50px;">#</th>
                  <th style="min-width: 130px;">القالب</th>
                  <th style="min-width: 130px;">العنوان</th>
                  <th style="min-width: 120px;">البيانات</th>
                  <th style="min-width: 100px;">المرسل إليهم</th>
                  <th style="min-width: 120px;">أنشئ في</th>
                  <th style="min-width: 120px; max-width: 120px;"></th>
                </thead>
                <tbody>
                </tbody>
                <tfoot>
                  <tr>
                    <th></th>
                    <th search="true">#</th>
                    <th search="true">القالب</th>
                    <th search="true">العنوان</th>
                    <th search="true">البيانات</th>
                    <th search="true">المرسل إليهم</th>
                    <th search="true">أنشئ في</th>
                    <th></th>
                  </tr>
                </tfoot>
              </table>
            </div>
