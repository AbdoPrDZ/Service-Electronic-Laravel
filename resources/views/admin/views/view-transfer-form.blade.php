      <div class="modal fade" id="view-transfer" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog" role="document">
          <div class="modal-content form">
            <div class="modal-header">
              <h2 class="modal-title">Transfer Details(#<span id="view-transfer-id"></span>)</h2>
            </div>
            <div class="modal-body">

              <div class="form-group">
                <h5>User:</h5>
                <input type="text" class="form-control" name="user">
              </div>
              <hr>

              <div class="form-group">
                <h5>Sanded Balance:</h5>
                <input type="currency" class="form-control" name="sended_balance">
              </div>
              <hr>

              <div class="form-group">
                <h5>Received Balance:</h5>
                <input type="currency" class="form-control" name="received_balance">
              </div>
              <hr>

              <div class="form-group">
                <h5>Sended Currency:</h5>
                <input type="text" class="form-control" name="sended_currency">
              </div>
              <hr>

              <div class="form-group">
                <h5>Received Currency:</h5>
                <input type="text" class="form-control" name="received_currency">
              </div>
              <hr>

              <div class="form-group">
                <h5>Wallet:</h5>
                <input type="text" class="form-control" name="wallet">
              </div>
              <hr>

              <div class="form-group">
                <h5>Proof:</h5>
                <div class="form-control proof-image" name="proof"></div>
              </div>
              <hr>

              <div class="form-group">
                <h5>Status:</h5>
                <select class="form-control" name="status">
                  <option value="checking">Checking</option>
                  <option value="accepted">Accepted</option>
                  <option value="refused">Refused</option>
                </select>
                <br>
                <button class="btn btn-success" style="width: 100%;" name="change-status">Change Status</button>
              </div>
              <hr>

              <div class="form-group">
                <h5>Ansowerd At:</h5>
                <input type="datetime" class="form-control" name="ansowerd_at">
              </div>
              <hr>

              <div class="form-group">
                <h5>Ansowerd Description:</h5>
                <input type="text" class="form-control" name="ansower_description">
              </div>
              <hr>

              <div class="form-group">
                <h5>Created At:</h5>
                <input type="datetime" class="form-control" name="created_at">
              </div>

            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-danger" action="close">Cancel</button>
            </div>
          </div>
        </div>
      </div>
