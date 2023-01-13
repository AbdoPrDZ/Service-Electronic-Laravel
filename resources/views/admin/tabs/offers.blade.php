
            <div id="all-offers" class="custom-table tab-view-tab-item" checkbox="true" for="offers">
              <h3 class="table-refresh" table="all-offers">تحديث <span class="material-symbols-sharp"></span></h3>
              <div class="custom-table-header">
                <h2 class="custom-table-title">العروض</h2>
                <div class="custom-table-header-actions">
                  <button class="btn btn-primary need-select" action="create">
                    <span class="material-symbols-sharp">add</span> إنشاء
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
                      <input type="checkbox" id="all-offers-select-all" class="select-all">
                      <label for="all-offers-select-all"></label>
                    </span>
                  </th>
                  <th style="min-width: 50px; max-width: 50px;">#</th>
                  <th style="min-width: 130px;">الصورة</th>
                  <th style="min-width: 130px;">العنوان</th>
                  <th style="min-width: 130px;">الوصف</th>
                  <th style="min-width: 130px;">العروض المرفقة</th>
                  <th style="min-width: 130px;">المعلومات المطلوبة</th>
                  <th style="min-width: 130px;">المعلومات المرسلة</th>
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
                    <th search="true">الوصف</th>
                    <th search="true">العروض المرفقة</th>
                    <th search="true">المعلومات المطلوبة</th>
                    <th search="true">المعلومات المرسلة</th>
                    <th search="true">أنشئ في</th>
                    <th></th>
                  </tr>
                </tfoot>
              </table>
            </div>

            <div id="all-offer-requests" class="custom-table tab-view-tab-item" checkbox="true" for="offers">
              <h3 class="table-refresh" table="all-offer-requests">تحديث</h3>
              <div class="custom-table-header">
                <h2 class="custom-table-title">طلبات العروض</h2>
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
                      <input type="checkbox" id="all-offer-requests-select-all" class="select-all">
                      <label for="all-offer-requests-select-all"></label>
                    </span>
                  </th>
                  <th style="min-width: 50px; max-width: 50px;">#</th>
                  <th style="min-width: 110px; max-width: 110px;">صورة العرض</th>
                  <th style="min-width: 80px;">العرض</th>
                  <th style="min-width: 130px;">المعلومات المرسلة</th>
                  <th style="min-width: 90px;">العرض المرفق المحدد</th>
                  <th style="min-width: 80px;">السعر الإجمالي</th>
                  <th style="min-width: 130px;">الحالة</th>
                  <th style="min-width: 90px; max-width: 90px;">أنشئ في</th>
                  <th style="min-width: 120px; max-width: 120px;"></th>
                </thead>
                <tbody>
                </tbody>
                <tfoot>
                  <tr>
                    <th></th>
                    <th search="true">#</th>
                    <th></th>
                    <th search="true">العرض</th>
                    <th search="true">المعلومات المرسلة</th>
                    <th search="true">العرض المرفق المحدد</th>
                    <th search="true">السعر الإجمالي</th>
                    <th search="true">الحالة</th>
                    <th search="true">أنشئ في</th>
                    <th></th>
                  </tr>
                </tfoot>
              </table>
            </div>
