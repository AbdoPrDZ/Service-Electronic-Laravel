<link href="https://cdn.jsdelivr.net/npm/froala-editor@latest/css/froala_editor.pkgd.min.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/froala-editor@latest/js/froala_editor.pkgd.min.js"></script>

<style>
  #template-editor .content {
    height: 70vh;
    background-color: #fff;
    border-radius: 5px;
    border: solid 1px #ccc;
    overflow: auto;
  }
  #template-editor .preivew {
    width: 100%;
    height: 100%;
  }
  #template-editor textarea {
    font-family: "Courier New", Courier, monospace;
    font-size: 14px;
    height: 100%;
    background-color: #6a6a6a;
    color: #b8d9ff;
  }
  #template-editor button {
    position: absolute;
    right: 20px;
    margin-top: 5px;
  }
</style>

<div class="modal fade" id="create-edit-template" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog" style="min-width: 95vw;" role="document">
    <div class="modal-content form">
      <div class="modal-header">
        <h2 class="modal-title">إنشاء قالب</h2>
      </div>
      <div class="modal-body">

        <div class="form-group">
          <h5>الاسم</h5>
          <input type="text" class="form-control" name="template_name">
        </div>
        <hr>

        <div class="form-group">
          <h5>النوع</h5>
          <select class="form-control" name="template_type">
            <option value="mail">Mail</option>
            <option value="export">Export</option>
          </select>
        </div>
        <hr>

        <div class="form-group" id="template-editor">
          <h5>الكود المصدري</h5>
          <button class="btn btn-primary btn-icon" onclick="switchTab(this, 'preview')"><span class="material-symbols-sharp">visibility</span></button>
          <div class="content">
            <textarea name="template-content" class="form-control" style="font-family: 'Courier New', Courier, monospace;"></textarea>
            <div class="preview" style="display: none;">
            </div>
          </div>
          <hr>
        </div>

        {{-- <div class="form-group">
          <textarea style="min-height: 40vh; max-height: 60vh;" id="template"></textarea>
          <script>
            window.templateEditor = new FroalaEditor('#template');
          </script>
        </div>
        <hr> --}}

        <div class="form-group">
          <h5>البيانات:</h5>
          <div id="template-args" class="multi-input"
               inputs='[{"name": "name", "text": "الرمز في الكود المصدري", "type": "text"}, {"name": "type", "text": "انوع", "type": "select", "options": "$inputTypes"}]'
               add-btn-text="إضافة">
            <div class="multi-input-header"></div>
            <div class="multi-input-body"></div>
          </div>
        </div>
        <hr>

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" action="create">إنشاء</button>
        <button type="button" class="btn btn-danger" action="close">إلغاء</button>
      </div>
    </div>
  </div>
</div>
