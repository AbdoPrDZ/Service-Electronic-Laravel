<style>
  #view-purchase .details {
    height: fit-content;
    display: flex;
    flex-direction: column;
    align-items: start;
  }
  #view-purchase .details span {
    color: #000;
    font-size: 15px;
    font-weight: bold;
  }
  #view-purchase .product-details {
    display: flex;
    height: fit-content;
    align-items: center;
  }
  #view-purchase .product-details .product-image {
    width: 145px;
    height: 160px;
    padding: 5px 10px;
    border-radius: 5px;
    background-color: #FEFEFE;
    box-shadow: rgb(60 64 67 / 30%) 0px 1px 2px 0px, rgb(60 64 67 / 15%) 0px 1px 3px 1px;
  }
  #view-purchase .product-details .product-image img {
    width: auto;
    max-width: 100%;
    height: 100%;
  }
  #view-purchase .product-details .details {
    margin: 0 0 0 20px;
  }
</style>

<div class="modal fade" id="view-purchase" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content form">
      <div class="modal-header">
        <h2 class="modal-title">معلومات طلب الشراء (#<span id="view-purchase-id"></span>)</h2>
      </div>
      <div class="modal-body">

        <div class="form-group">
          <h5>معلومات المشتري:</h5>
          <div class="form-control details" name="client_details" style="height: fit-content"></div>
        </div>
        <hr>

        <div class="form-group">
          <h5>معلومات البائع:</h5>
          <div class="form-control details" name="seller_details" style="height: fit-content"></div>
        </div>
        <hr>

        <div class="form-group">
          <h5>معلومات المنتج:</h5>
          <div class="product-details form-control" name="product_details">
            <div class="product-image">
              <img src="./file/public/logo">
            </div>
            <div class="details">
            </div>
          </div>
        </div>
        <hr>

        <div class="form-group">
          <h5>حالة الطلب:</h5>
          <div class="form-control" name="status"></div>
        </div>
        <hr>

        <div class="form-group" name="problem_report">
          <h5>تقرير المشتري:</h5>
          <textarea class="form-control" name="client_report" disabled></textarea>
          <h5>تقرير البائع:</h5>
          <textarea class="form-control" name="seller_report" disabled></textarea>
          <select class="form-control" name="answer">
            <option value="accept_all">قبول الكل</option>
            <option value="refuse_all">رفض الكل</option>
            <option value="accept_delivery_cost">إقتطاع الشخن</option>
          </select>
          <button class="btn btn-success" style="width: 100%;" name="answer">إجابة</button>
        </div>
        <hr>

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
