
            <div id="all-currencies" class="custom-table tab-view-tab-item" checkbox="true" for="currencies">
              <h3 class="table-refresh" table="all-currencies">تحديث</h3>
              <div class="custom-table-header">
                <h2 class="custom-table-title">العملات</h2>
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
                      <input type="checkbox" id="all-currencies-select-all" class="select-all">
                      <label for="all-currencies-select-all"></label>
                    </span>
                  </th>
                  <th style="min-width: 50px; max-width: 50px;">#</th>
                  <th style="min-width: 130px;">الصورة</th>
                  <th style="min-width: 85px;">الاسم</th>
                  <th style="min-width: 50px; max-width: 50px;">الرمز</th>
                  <th style="min-width: 80px; max-width: 80px;">الرصيد</th>
                  <th style="min-width: 150px;">الاسعار</th>
                  <th style="min-width: 175px;">المحفظة</th>
                  <th style="min-width: 120px;">أنشئ في</th>
                  <th style="min-width: 120px; max-width: 120px;"></th>
                </thead>
                <tbody>
                </tbody>
                <tfoot>
                  <tr>
                    <th></th>
                    <th search="true">#</th>
                    <th></th>
                    <th search="true">الاسم</th>
                    <th search="true">الرمز</th>
                    <th search="true">الرصيد</th>
                    <th></th>
                    <th search="true">المحفظة</th>
                    <th search="true">أنشئ في</th>
                    <th></th>
                  </tr>
                </tfoot>
              </table>
            </div>
