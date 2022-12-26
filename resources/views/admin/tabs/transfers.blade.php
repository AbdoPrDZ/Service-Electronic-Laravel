
            <div id="all-transfers" class="custom-table tab-view-tab-item" checkbox="true" for="transfers">
              <h3 class="table-refresh" table="all-transfers">تحيدث</h3>
              <div class="custom-table-header">
                <h2 class="custom-table-title">التحويلات</h2>
                {{-- <div class="custom-table-header-actions">
                  <button class="btn btn-warning need-select" action="edit" disabled>
                    <span class="material-symbols-sharp">edit</span> Edit
                  </button>
                  <button class="btn btn-danger need-select" action="delete" disabled>
                    <span class="material-symbols-sharp">delete</span> Delete
                  </button>
                </div> --}}
              </div>
              <table class="table table-bordered table-condensed table-hover table-striped text-center">
                <thead>
                  <th>
                    <span class="custom-checkbox">
                      <input type="checkbox" id="all-transfers-select-all" class="select-all">
                      <label for="all-transfers-select-all"></label>
                    </span>
                  </th>
                  <th style="min-width: 50px; max-width: 50px;">#</th>
                  <th style="min-width: 130px;">المستخدم</th>
                  <th style="min-width: 90px; max-width: 90px;">الرصيد المستقبل</th>
                  <th style="min-width: 90px; max-width: 90px;">الرصيد المرسل</th>
                  <th style="min-width: 80px; max-width: 80px;">العملة المستقبلة</th>
                  <th style="min-width: 80px; max-width: 80px;">العملة المرسلة</th>
                  <th style="min-width: 90px;">المحفظة</th>
                  <th style="min-width: 80px; max-width: 80px;">الوضعية</th>
                  <th style="min-width: 80px; max-width: 80px;">تم الرد في</th>
                  <th style="min-width: 80px; max-width: 80px;">أنشئ في</th>
                  <th style="min-width: 80px; max-width: 80px;"></th>
                </thead>
                <tbody>
                  <tfoot>
                    <tr>
                      <th order="false"></th>
                      <th search="true">#</th>
                      <th search="true">المستخدم</th>
                      <th search="true">الرصيد المستقبل</th>
                      <th search="true">الرصيد المرسل</th>
                      <th search="true">العملة المرسلة</th>
                      <th search="true">العملة المرسلة</th>
                      <th search="true">المحفظة</th>
                      <th search="true">الوضعية</th>
                      <th search="true">تم الرد في</th>
                      <th search="true">أنشئ في</th>
                      <th></th>
                    </tr>
                  </tfoot>
                </tbody>
              </table>
            </div>
