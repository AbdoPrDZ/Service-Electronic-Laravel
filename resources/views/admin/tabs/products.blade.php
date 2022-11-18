
            <div id="all-products" class="custom-table tab-view-tab-item" checkbox="true" for="products">
              <h3 class="table-refresh" table="all-products">Refersh <span class="material-symbols-sharp"></span></h3>
              <div class="custom-table-header">
                <h2 class="custom-table-title">Products</h2>
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
                  <th style="min-width: 130px;">Image</th>
                  <th style="min-width: 120px;">Name</th>
                  <th style="min-width: 100px; max-width: 100px;">User</th>
                  <th style="min-width: 50px; max-width: 50px;">Price</th>
                  <th style="min-width: 100px; max-width: 100px;">Category</th>
                  <th style="min-width: 130px;">Description</th>
                  <th style="min-width: 90px; max-width: 90px;">Created At</th>
                </thead>
                <tbody>
                  <tfoot>
                    <tr>
                      <th></th>
                      <th search="true">#</th>
                      <th></th>
                      <th search="true">Name</th>
                      <th search="true">User</th>
                      <th search="true">Price</th>
                      <th search="true">Category</th>
                      <th search="true">Description</th>
                      <th search="true">Created At</th>
                    </tr>
                  </tfoot>
                </tbody>
              </table>
            </div>
