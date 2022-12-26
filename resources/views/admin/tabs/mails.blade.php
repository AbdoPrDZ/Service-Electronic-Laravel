
            <div id="all-mails-templates" class="custom-table tab-view-tab-item" checkbox="true" for="mails">
              <h3 class="table-refresh" table="all-mails-templates">تحديث</h3>
              <div class="custom-table-header">
                <h2 class="custom-table-title">القوالب</h2>
                <div class="custom-table-header-actions">
                  {{-- <button class="btn btn-warning need-select" action="edit" disabled>
                    <span class="material-symbols-sharp">edit</span> Edit
                  </button>
                  <button class="btn btn-danger need-select" action="delete" disabled>
                    <span class="material-symbols-sharp">delete</span> Delete
                  </button> --}}
                  <button class="btn btn-primary" action="create">
                    <span class="material-symbols-sharp">add</span>إنشاء
                  </button>
                </div>
              </div>
              <table class="table table-bordered table-condensed table-hover table-striped text-center">
                <thead>
                  <th>
                    <span class="custom-checkbox">
                      <input type="checkbox" id="all-mails-templates-select-all" class="select-all">
                      <label for="all-mails-templates-select-all"></label>
                    </span>
                  </th>
                  <th style="min-width: 50px; max-width: 50px;">#</th>
                  <th style="min-width: 130px;">الكود المصدري</th>
                  <th style="min-width: 85px;">الكونترولر</th>
                  <th style="min-width: 50px; max-width: 50px;">البيانات</th>
                  <th style="min-width: 80px; max-width: 80px;">المرسل إليهم</th>
                  <th style="min-width: 150px;">الملحقات</th>
                  <th style="min-width: 120px;">أنشئ في</th>
                  <th style="min-width: 120px; max-width: 120px;"></th>
                </thead>
                <tbody>
                  <tfoot>
                    <tr>
                      <th></th>
                      <th search="true">#</th>
                      <th search="true">الكود المصدري</th>
                      <th search="true">الكونترولر</th>
                      <th search="true">البيانات</th>
                      <th search="true">المرسل إليهم</th>
                      <th search="true">الملحقات</th>
                      <th search="true">أنشئ في</th>
                      <th></th>
                    </tr>
                  </tfoot>
                </tbody>
              </table>
            </div>

            <div id="all-mails" class="custom-table tab-view-tab-item" checkbox="true" for="mails">
              <h3 class="table-refresh" table="all-mails">تحديث</h3>
              <div class="custom-table-header">
                <h2 class="custom-table-title">الإيمايلات</h2>
                <div class="custom-table-header-actions">
                  {{-- <button class="btn btn-warning need-select" action="edit" disabled>
                    <span class="material-symbols-sharp">edit</span> Edit
                  </button>
                  <button class="btn btn-danger need-select" action="delete" disabled>
                    <span class="material-symbols-sharp">delete</span> Delete
                  </button> --}}
                  <button class="btn btn-primary" action="create">
                    <span class="material-symbols-sharp">add</span>إنشاء
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
                  <th style="min-width: 130px;">الكود المصدري</th>
                  <th style="min-width: 85px;">الكونترولر</th>
                  <th style="min-width: 50px; max-width: 50px;">البيانات</th>
                  <th style="min-width: 80px; max-width: 80px;">المرسل إليهم</th>
                  <th style="min-width: 150px;">الملحقات</th>
                  <th style="min-width: 120px;">أنشئ في</th>
                  <th style="min-width: 120px; max-width: 120px;"></th>
                </thead>
                <tbody>
                  <tfoot>
                    <tr>
                      <th></th>
                      <th search="true">#</th>
                      <th search="true">الكود المصدري</th>
                      <th search="true">الكونترولر</th>
                      <th search="true">البيانات</th>
                      <th search="true">المرسل إليهم</th>
                      <th search="true">الملحقات</th>
                      <th search="true">أنشئ في</th>
                      <th></th>
                    </tr>
                  </tfoot>
                </tbody>
              </table>
            </div>
