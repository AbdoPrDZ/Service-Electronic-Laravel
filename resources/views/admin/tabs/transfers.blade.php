
            <div id="all-transfers" class="custom-table tab-view-tab-item" checkbox="true" for="transfers">
              <h3 class="table-refresh" table="all-transfers">Refersh <span class="material-symbols-sharp"></span></h3>
              <div class="custom-table-header">
                <h2 class="custom-table-title">Transfers</h2>
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
                  <th style="min-width: 130px;">User</th>
                  <th style="min-width: 90px; max-width: 90px;">Sended Balance</th>
                  <th style="min-width: 90px; max-width: 90px;">Received Balance</th>
                  <th style="min-width: 80px; max-width: 80px;">Sended Currency</th>
                  <th style="min-width: 80px; max-width: 80px;">Received Currency</th>
                  <th style="min-width: 90px;">Wallet</th>
                  <th style="min-width: 80px; max-width: 80px;">Status</th>
                  <th style="min-width: 80px; max-width: 80px;">Ansowerd At</th>
                  <th style="min-width: 80px; max-width: 80px;">Created At</th>
                </thead>
                <tbody>
                  <tfoot>
                    <tr>
                      <th></th>
                      <th search="true">#</th>
                      <th search="true">User</th>
                      <th search="true">Sended Balance</th>
                      <th search="true">Received Balance</th>
                      <th search="true">Sended Currency</th>
                      <th search="true">Received Currency</th>
                      <th search="true">Wallet</th>
                      <th search="true">Status</th>
                      <th search="true">Ansowerd At</th>
                      <th search="true">Created At</th>
                    </tr>
                  </tfoot>
                </tbody>
              </table>
            </div>
