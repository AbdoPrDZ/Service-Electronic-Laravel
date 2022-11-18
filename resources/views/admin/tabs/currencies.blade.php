
            <div id="all-currencies" class="custom-table tab-view-tab-item" checkbox="true" for="currencies">
              <h3 class="table-refresh" table="all-currencies">Refersh <span class="material-symbols-sharp"></span></h3>
              <div class="custom-table-header">
                <h2 class="custom-table-title">Currencies</h2>
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
                      <input type="checkbox" id="all-currencies-select-all" class="select-all">
                      <label for="all-currencies-select-all"></label>
                    </span>
                  </th>
                  <th style="min-width: 50px; max-width: 50px;">#</th>
                  <th style="min-width: 130px;">Image</th>
                  <th style="min-width: 130px;">Name</th>
                  <th style="min-width: 50px; max-width: 50px;">Char</th>
                  <th style="min-width: 100px; max-width: 100px;">Max Receive</th>
                  <th style="min-width: 100px;">Wallet</th>
                  <th style="min-width: 120px;">Created At</th>
                </thead>
                <tbody>
                  <tfoot>
                    <tr>
                      <th></th>
                      <th search="true">#</th>
                      <th></th>
                      <th search="true">Name</th>
                      <th search="true">Char</th>
                      <th search="true">Max Receive</th>
                      <th search="true">Wallet</th>
                      <th search="true">Created At</th>
                    </tr>
                  </tfoot>
                </tbody>
              </table>
            </div>
