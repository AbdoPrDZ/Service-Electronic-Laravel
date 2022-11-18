
            <div id="all-users" class="custom-table tab-view-tab-item" checkbox="true" for="users">
              <h3 class="table-refresh" table="all-transfers">Refersh <span class="material-symbols-sharp"></span></h3>
              <div class="custom-table-header">
                <h2 class="custom-table-title">Users</h2>
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
                      <input type="checkbox" id="all-users-select-all" class="select-all">
                      <label for="all-users-select-all"></label>
                    </span>
                  </th>
                  <th style="min-width: 50px; max-width: 50px;">#</th>
                  <th style="min-width: 130px;">Fullname</th>
                  <th style="min-width: 100px;">Email</th>
                  <th style="min-width: 100px;">Phone</th>
                  <th style="min-width: 90px;">Balance</th>
                  <th style="min-width: 80px; max-width: 80px;">Email Verified</th>
                  <th style="min-width: 80px; max-width: 80px;">Identity Verifited</th>
                  <th style="min-width: 80px; max-width: 80px;">Created At</th>
                </thead>
                <tbody>
                  <tfoot>
                    <tr>
                      <th></th>
                      <th search="true">#</th>
                      <th search="true">Fullname</th>
                      <th search="true">Email</th>
                      <th search="true">Phone</th>
                      <th search="true">Balance</th>
                      <th search="true">Email Verified</th>
                      <th search="true">Identity Verifited</th>
                      <th search="true">Created At</th>
                    </tr>
                  </tfoot>
                </tbody>
              </table>
            </div>
