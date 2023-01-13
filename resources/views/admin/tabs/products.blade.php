
            <div id="all-categories" class="custom-table tab-view-tab-item" checkbox="true" for="products">
              <h3 class="table-refresh" table="all-categories">تحديث <span class="material-symbols-sharp"></span></h3>
              <div class="custom-table-header">
                <h2 class="custom-table-title">الأنواع</h2>
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
                      <input type="checkbox" id="all-categories-select-all" class="select-all">
                      <label for="all-categories-select-all"></label>
                    </span>
                  </th>
                  <th style="min-width: 50px; max-width: 50px;">#</th>
                  <th style="min-width: 130px;">الصورة</th>
                  <th style="min-width: 130px;">الاسماء</th>
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
                    <th search="true">الأسماء</th>
                    <th search="true">أنشئ في</th>
                    <th></th>
                  </tr>
                </tfoot>
              </table>
            </div>

            <div id="all-products" class="custom-table tab-view-tab-item" checkbox="true" for="products">
              <h3 class="table-refresh" table="all-products">تحديث</h3>
              <div class="custom-table-header">
                <h2 class="custom-table-title">المنتجات</h2>
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
                      <input type="checkbox" id="all-products-select-all" class="select-all">
                      <label for="all-products-select-all"></label>
                    </span>
                  </th>
                  <th style="min-width: 50px; max-width: 50px;">#</th>
                  <th style="min-width: 130px;">الصورة</th>
                  <th style="min-width: 120px;">الاسم</th>
                  <th style="min-width: 100px; max-width: 100px;">البائع</th>
                  <th style="min-width: 50px; max-width: 50px;">السعر</th>
                  <th style="min-width: 100px; max-width: 100px;">النوع</th>
                  <th style="min-width: 130px;">الوصف</th>
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
                    <th search="true">الاسم</th>
                    <th search="true">البائع</th>
                    <th search="true">السعر</th>
                    <th search="true">النوع</th>
                    <th search="true">الوصف</th>
                    <th search="true">أنشئ في</th>
                    <th></th>
                  </tr>
                </tfoot>
              </table>
            </div>
