<div class="modal fade" id="create-currency" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content form">
      <div class="modal-header">
        <h2 class="modal-title">Create Currency</h2>
      </div>
      <div class="modal-body">

        <div class="form-group">
          <h5>Name:</h5>
          <input type="text" class="form-control" name="currency_name">
        </div>
        <hr>

        <div class="form-group">
          <h5>Char:</h5>
          <input type="text" class="form-control" name="currency_char">
        </div>
        <hr>

        <div class="form-group">
          <h5>Max Receive:</h5>
          <input type="number" class="form-control" name="currency_max_receive">
        </div>
        <hr>

        <div class="form-group">
          <h5>Wallet:</h5>
          <input type="text" class="form-control" name="currency_wallet">
        </div>
        <hr>

        <div class="form-group">
          <h5>Image:</h5>
          <button class="btn btn-img-picker" id="create-currency-image-picker">
            <span class="material-symbols-sharp pick-icon">add_a_photo</span>
          </button>
        </div>
        <hr>

        <div class="form-group">
          <h5>Prices:</h5>
          <div id="currency-prices" class="multi-input"
               inputs='[{"name": "currency_id", "text": "Currency ID", "type": "number"}, {"name": "buy_price", "text": "Buy Price", "type": "number"}, {"name": "sell_price", "text": "Sell Price", "type": "number"}]'
               add-btn-text="Add">
            <div class="multi-input-header"></div>
            <div class="multi-input-body"></div>
          </div>
        </div>
        <hr>

        <div class="form-group">
          <div class="custom-control custom-checkbox">
            <input type="checkbox" class="custom-control-input" id="proof-required" name="proof_required">
            <label class="custom-control-label" for="proof-required">Proof Is Required</label>
          </div>
        </div>
        <hr>

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" action="create">Create</button>
        <button type="button" class="btn btn-danger" action="close">Cancel</button>
      </div>
    </div>
  </div>
</div>
