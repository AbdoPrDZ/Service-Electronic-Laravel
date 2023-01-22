<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @include('admin.src.headers')
    <link href="{{ asset('resources/css/admin/main.css') }}" rel="stylesheet">
    <title>تسجيل الدخول</title>
  </head>
  <body>
    <form method="post" action="" class="form" novalidate>
      @csrf
      <h2 class="text-center">تسجيل الدخول</h2>

      <div class="form-group">
        <h5>البريد الإلكتروني:</h5>
        <input type="text" class="form-control <?= key_exists('email', $invalidates) ? 'is-invalid': '' ?>" name="email" value="<?= $email ?>" required>
        <div class="invalid-feedback ">
          <?= key_exists('email', $invalidates) ? $invalidates['email'] : '' ?>
        </div>
      </div>

      <div class="form-group" name="password">
        <h5>كلمة السر:</h5>
        <div class="input-group">
          <input type="password" class="form-control <?= key_exists('password', $invalidates) ? 'is-invalid': '' ?>" name="password" value="<?= $password ?>" required>
          <div class="btn input-group-text" style="border-radius: 0px 5px 5px 0px; padding: 0 5px;">
            <span class="material-symbols-sharp" style="font-size: 20px">visibility</span>
          </div>
          <div class="invalid-feedback ">
            <?= key_exists('password', $invalidates) ? $invalidates['password'] : '' ?>
          </div>
        </div>
      </div>

      <!-- <div class="form-group" style="float: left; font-size: 1rem; font-weight: 500; margin: 0px auto 20px auto;">
        <div class="custom-control custom-checkbox">
          <input type="checkbox" class="custom-control-input" id="remember-me" name="remember_me" value="true" <%= 'checked' ? values.remember_me : '' %>>
          <label class="custom-control-label" for="remember-me">Remember me</label>
        </div>
      </div> -->

      <input type="submit" class="btn form-btn btn-primary btn-lg btn-block" value="تسجيل الدخول" name="login"/>
      <br>
      @include('admin.src.footer')
    </form>
    <div id="alerts" class="noties topright"></div>
    <script src="{{ asset('/resources/js/admin/main.js') }}"></script>
    <script>
      $on('.form-group[name="password"] .btn.input-group-text', 'click', function() {
        var nextType = $('.form-control[name="password"]').attr('type') == 'password' ? 'text' : 'password';
        $('.form-control[name="password"]').attr('type', nextType)
        $(this).html(`<span class="material-symbols-sharp" style="font-size: 20px">${nextType == 'text' ? 'visibility_off' : 'visibility'}</span>`)
      })
    </script>
  </body>
</html>
