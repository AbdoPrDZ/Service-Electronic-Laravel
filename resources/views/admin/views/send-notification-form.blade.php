<div class="modal fade" id="send-notification-modal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content form">
      <div class="modal-header">
        <h2 class="modal-title">إرسال رسالة</h2>
      </div>
      <div class="modal-body">

        <div class="form-group">
          <h5>الرسالة:</h5>
          <textarea type="text" class="form-control" name="message"></textarea>
        </div>
        <hr>

        <div class="form-group">
          <h5>لصورة:</h5>
          <button class="btn btn-img-picker" id="send-notification-image-picker">
            <span class="material-symbols-sharp pick-icon">add_a_photo</span>
          </button>
        </div>
        <hr>

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" action="send">إرسال</button>
        <button type="button" class="btn btn-danger" action="close">إلغاء</button>
      </div>
    </div>
  </div>
</div>
