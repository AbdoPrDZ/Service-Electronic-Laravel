<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    @include('admin.src.headers')
    <link href="{{ asset('resources/css/admin/main.css') }}" rel="stylesheet">
    <link href="{{ asset('resources/css/admin/settings.css') }}" rel="stylesheet">
    <title>Service Electronic | Settings</title>
  </head>
  <body>
    <div class="topbar">
      <a class="topbar-logo" href="./">
        <img src="{{ asset('resources/media/logo.png') }}">
        <h3><span class="danger">Service|</span><span class="success">Electronic</span> </h3>
      </a>
      <div class="topbar-actions">
        @if (!is_null($admin->balance))
          <h2 class=" {{ $admin->balance != 0 ? 'success' : 'danger' }}" style="font-size: 14px;font-weight: bold;">
            {{ $admin->balance ?? 0 }} DZD
          </h2>
        @endif
        <div class="dropdown dropdown-selecetion" id="person-dropdown">
          <div class="btn btn-primary btn-icon dropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <span class="material-symbols-sharp">person</span>
          </div>
          <div class="dropdown-menu">
            <div class="short-profile">
              <img src="../file/admin/admin_profile_default">
              <span>
                <h2><?= $admin->username ?></h2>
              </span>
            </div>
            <div class="dropdown-divider"></div>
            <a href="../admin/logout" class="dropdown-item" style="display: flex;">
              <span class="material-symbols-sharp" style="margin-right: 10px">logout</span>
              تسجيل الخروج
            </a>
          </div>
        </div>

      </div>
    </div>

    <div class="body">
      <div class="content form">
        <h2>الإعدادات</h2>

        <div class="form-group" name="platform_currency">
          <h5>عملة المنصة:</h5>
          <select class="form-control">
            @foreach ($currencies as $id => $currency)
              <option value="{{ $id }}" @if($id == $platformCurrencyId) selected @endif>{{ $currency->name }}</option>
            @endforeach
          </select>
          <br>
          <button class="btn btn-success" action="save" onclick="saveSetting(this, 'platform_currency')" style="width: 100%;">حفظ</button>
        </div>
        <hr>

        <div class="form-group" name="display_currency">
          <h5>عملة عرض قيمة الرصيد:</h5>
          <select class="form-control">
            @foreach ($currencies as $id => $currency)
              <option value="{{ $id }}" @if($id == $displayCurrencyId) selected @endif>{{ $currency->name }}</option>
            @endforeach
          </select>
          <br>
          <button class="btn btn-success" action="save" onclick="saveSetting(this, 'display_currency')" style="width: 100%;">حفظ</button>
        </div>
        <hr>

        <div class="form-group" name="email_verification_template">
          <h5>قالب التحقق من البريد الإلكتروني:</h5>
          <select class="form-control">
            @foreach ($templates as $name => $template)
              <option value="{{ $name }}" @if($name == $emailVerificationTemplateId) selected @endif>{{ $template->name }}</option>
            @endforeach
          </select>
          <br>
          <button class="btn btn-success" action="save" onclick="saveSetting(this, 'email_verification_template')" style="width: 100%;">حفظ</button>
        </div>
        <hr>

        <div class="form-group" name="user_recharge_template">
          <h5>قالب البريد الإلكتروني لشحن رصيد الحساب:</h5>
          <select class="form-control">
            @foreach ($templates as $name => $template)
              <option value="{{ $name }}" @if($name == $userRechargeTemplateId) selected @endif>{{ $template->name }}</option>
            @endforeach
          </select>
          <br>
          <button class="btn btn-success" action="save" onclick="saveSetting(this, 'user_recharge_template')" style="width: 100%;">حفظ</button>
        </div>
        <hr>

        <div class="form-group" name="user_withdraw_template">
          <h5>قالب البريد الإلكتروني لسحب رصيد الحساب:</h5>
          <select class="form-control">
            @foreach ($templates as $name => $template)
              <option value="{{ $name }}" @if($name == $userWithdrawTemplateId) selected @endif>{{ $template->name }}</option>
            @endforeach
          </select>
          <br>
          <button class="btn btn-success" action="save" onclick="saveSetting(this, 'user_withdraw_template')" style="width: 100%;">حفظ</button>
        </div>
        <hr>

        <div class="form-group" name="user_credit_receive_template">
          <h5>قالب البريد الإلكتروني للتحويل بين المستخدمين:</h5>
          <select class="form-control">
            @foreach ($templates as $name => $template)
              <option value="{{ $name }}" @if($name == $userCreditReceiveTemplateId) selected @endif>{{ $template->name }}</option>
            @endforeach
          </select>
          <br>
          <button class="btn btn-success" action="save" onclick="saveSetting(this, 'user_credit_receive_template')" style="width: 100%;">حفظ</button>
        </div>
        <hr>

        <div class="form-group" name="user_identity_confirm_template">
          <h5>قالب البريد الإلكتروني لنتيج التحقق من الخوية:</h5>
          <select class="form-control">
            @foreach ($templates as $name => $template)
              <option value="{{ $name }}" @if($name == $userIdentityConfirmTemplateId) selected @endif>{{ $template->name }}</option>
            @endforeach
          </select>
          <br>
          <button class="btn btn-success" action="save" onclick="saveSetting(this, 'user_credit_receive_template')" style="width: 100%;">حفظ</button>
        </div>
        <hr>

        <div class="form-group" name="commission">
          <h5>عمولة المنتجات:</h5>
          <input type="number" class="form-control" value="{{ $commission }}">
          <br>
          <button class="btn btn-success" action="save" onclick="saveSetting(this, 'commission')" style="width: 100%;">حفظ</button>
        </div>
        <hr>

        <div class="form-group">
          <h5>حالة الخدمات:</h5>
          <div class="custom-control custom-checkbox">
            <input type="checkbox" class="custom-control-input" id="transfers-service" name="transfers_servince_is_active" {{ $servicesStatus['transfers'] == 'active' ? 'checked' : '' }}>
            <label class="custom-control-label" for="transfers-service">خدمة التحويلات مفعلة</label>
          </div>
          <div class="custom-control custom-checkbox">
            <input type="checkbox" class="custom-control-input" id="offers-service" name="offers_servince_is_active" {{ $servicesStatus['offers'] == 'active' ? 'checked' : '' }}>
            <label class="custom-control-label" for="offers-service">خدمة العروض مفعلة</label>
          </div>
          <div class="custom-control custom-checkbox">
            <input type="checkbox" class="custom-control-input" id="store-service" name="store_servince_is_active" {{ $servicesStatus['store'] == 'active' ? 'checked' : '' }}>
            <label class="custom-control-label" for="store-service">خدمة المتجر مفعلة</label>
          </div>
          <br>
          <button class="btn btn-success" action="save" onclick="saveSetting(this, 'services_status')" style="width: 100%;">حفظ</button>
        </div>

      </div>

      <div id="alerts" class="noties topright"></div>

      <div class="modal fade" id="message-dialog-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog" role="document">
          <div class="modal-content form">
            <div class="modal-header">
              <h2 class="modal-title"></h2>
            </div>
            <div class="modal-body">
            </div>
            <div class="modal-footer">
            </div>
          </div>
        </div>
      </div>

    </div>

    <script src="{{ asset('/resources/js/admin/main.js') }}?time={{ now() }}"></script>
    <script>
      function saveSetting(button, name) {
        const actions = {
          platform_currency: {
            title: 'تغيير عملة المنصة',
            message: 'هل أنت متأكد من تغيير عملة المنصة؟',
            defualtValue: {{ $platformCurrencyId }}
          },
          display_currency: {
            title: 'تغيير عملة عرض قيمة الرصيد',
            message: 'هل أنت متأكد من تغيير عملة عرض قيمة الرصيد',
            defualtValue: {{ $displayCurrencyId }}
          },
          email_verification_template: {
            title: 'تغيير قالب التحقق من البريد الإلكتروني',
            message: 'هل أنت متأكد من تغيير قالب التحقق من البريد الإلكتروني',
            defualtValue: "{{ $emailVerificationTemplateId }}",
          },
          user_recharge_template: {
            title: 'تغيير قالب شحن الرصيد',
            message: 'هل أنت متأكد من تغيير قالب شحن الرصيد',
            defualtValue: "{{ $userRechargeTemplateId }}",
          },
          user_withdraw_template: {
            title: 'تغيير قالب سحب الرصيد',
            message: 'هل أنت متأكد من تغيير قالب سحب الرصيد',
            defualtValue: "{{ $userWithdrawTemplateId }}",
          },
          user_credit_receive_template: {
            title: 'تغيير قالب إستقبال الرصيد',
            message: 'هل أنت متأكد من تغيير قالب إستقبال الرصيد',
            defualtValue: "{{ $userCreditReceiveTemplateId }}",
          },
          user_identity_confirm_template: {
            title: 'تغيير قالب التحقق من الهوية',
            message: 'هل أنت متأكد من تغيير قالب التحقق من الهوية',
            defualtValue: "{{ $userIdentityConfirmTemplateId }}",
          },
          commission: {
            title: 'تغيير قيمة العمولة',
            message: 'هل أنت متأكد من تغيير قيمة العمولة',
            defualtValue: {{ $commission }}
          },
          services_status: {
            title: 'تغيير حالة الخدمات',
            message: 'عل أنت متأكد من تغيير حالة الخدمات',
            defualtValue: <?php echo json_encode($servicesStatus) ?>
          }
        }
        const value = name == 'services_status' ? JSON.stringify({
          transfers: $('input[name="transfers_servince_is_active"]').prop('checked') ? 'active' : 'deactivate',
          offers: $('input[name="offers_servince_is_active"]').prop('checked') ? 'active' : 'deactivate',
          store: $('input[name="store_servince_is_active"]').prop('checked') ? 'active' : 'deactivate',
        }) : $(`.form-group[name="${name}"] .form-control`).val();
        console.log(value);
        if(value == actions[name].defualtValue) return;
        const btnHtml = $(button).html();
        $(button).html(`<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>يرجى الإنتظار...`);
        $(button).attr('disabled', true);
        messageDialog(
          'save-settings',
          actions[name].title,
          actions[name].message,
          async (action) => {
            if(action == 'نعم') {
              const formData = new FormData();
              formData.append('name', name);
              formData.append('value', value);
              var data = await $.ajax({
                url: './setting/edit',
                type: 'POST',
                headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                contentType: false,
                processData: false,
                data: formData,
              });
              console.log(data);
              $('#message-dialog-modal').modal('hide');
              if(data.success) {
                alertMessage('save-setting-message', actions[name].title, data.message, 'success');
              } else {
                if(data.message) {
                  alertMessage('save-setting-message', actions[name].title, data.message, 'danger');
                } else {
                  for (const key in data.errors) {
                    const error = data.errors[key];
                    alertMessage('save-setting-message', actions[name].title, error, 'danger');
                  }
                }
              }
            } else {
              $('#message-dialog-modal').modal('hide');
            }
          },
          {نعم: 'primary', إلغاء: 'danger'},
          () => {
            $(button).html(btnHtml);
            $(button).attr('disabled', false);
          }
        );
      }
    </script>

  </body>
</html>
