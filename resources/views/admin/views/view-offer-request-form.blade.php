<style>
  #view-offer-request .details {
    height: fit-content;
    display: flex;
    flex-direction: column;
    align-items: start;
  }
  #view-offer-request .details span {
    color: #000;
    font-size: 15px;
    font-weight: bold;
  }
  #view-offer-request .details span span {
    font-weight: normal;
  }
  #view-offer-request .offer-details {
    display: flex;
    height: fit-content;
    align-items: center;
  }
  #view-offer-request .offer-details .offer-image {
    width: 145px;
    height: 160px;
    padding: 5px 10px;
    border-radius: 5px;
    background-color: #FEFEFE;
    box-shadow: rgb(60 64 67 / 30%) 0px 1px 2px 0px, rgb(60 64 67 / 15%) 0px 1px 3px 1px;
  }
  #view-offer-request .offer-details .offer-image img {
    width: -webkit-fill-available;
    height: 100%;
  }
  #view-offer-request .offer-details .details {
    margin: 0 0 0 20px;
  }
</style>

<div class="modal fade" id="view-offer-request" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content form">
      <div class="modal-header">
        <h2 class="modal-title">معلومات طلب العرض (#<span id="view-offer-request-id"></span>)</h2>
      </div>
      <div class="modal-body">

        <div class="form-group">
          <h5>معلومات العرض:</h5>
          <div class="offer-details form-control" name="offer_details">
            <div class="offer-image">
            </div>
            <div class="details">
            </div>
          </div>
        </div>
        <hr>

        <div class="form-group">
          <h5>العرض المختار:</h5>
          <div class="form-control" name="sub_offer" style="height: fit-content"></div>
        </div>
        <hr>

        <div class="form-group">
          <h5>المعلومات المستقبلة:</h5>
          <div class="form-control details" name="offer_fields" style="height: fit-content"></div>
        </div>
        <hr>

        <div class="form-group" name="answer_form">
          <h5>إجابة:</h5>
          <div class="select-group-tabs" id="offer-request-admin-answer">
            <select class="form-control select-group-tabs-select" name="admin-answer">
              <option value="accept" selected>قبول</option>
              <option value="refuse">رفض</option>
            </select>
            <div class="select-group-tabs-items">
              <div class="select-group-tabs-item selected" for="accept">
                <div class="data_fields"></div>
              </div>
              <div class="select-group-tabs-item" for="refuse">
                <div class="form-group">
                  <h5>سبب الرفض:</h5>
                  <textarea class="form-control" name="refuse_description"></textarea>
                </div>
              </div>
            </div>
          </div>
          <button class="btn btn-success" style="width: 100%;" action="submit">متابعة</button>
          <hr>
        </div>

        <div class="form-group">
          <h5>حالة الطلب:</h5>
          <div class="form-control" name="status"></div>
        </div>
        <hr>

        <div class="form-group" name="answer">
          <h5>المعلومات الإجاية:</h5>
          <div class="form-control details" name="answer_data" style="height: fit-content"></div>
          <hr>
        </div>

        <div class="form-group">
          <h5>أنشئ في:</h5>
          <div class="form-control" name="created_at"></div>
        </div>

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-danger" action="close">إلغاء</button>
      </div>
    </div>
  </div>
</div>
