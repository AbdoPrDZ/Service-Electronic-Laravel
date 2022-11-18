<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @include('admin.src.headers')
    <link href="{{ asset('resources/css/admin/main.css') }}?time={{ now() }}" rel="stylesheet">
    <title>تسجيل الدخول</title>
  </head>
  <body>
    <form method="post" action="" class="form" novalidate>
      @csrf
      <h2 class="text-center">تسجيل الدخول</h2>

      <div class="form-group">
        <h5>البريد الإلكتروني:</h5>
        <input type="text" class="form-control <?= isset($invalidates->email) && !is_null($invalidates->email) ? 'is-invalid': '' ?>" name="email" value="<?= $email ?>" required>
        <div class="invalid-feedback ">
            <?= json_encode($invalidates)?>
          <?= isset($invalidates->email) && !is_null($invalidates->email) ? $invalidates->email: '' ?>
        </div>
      </div>

      <div class="form-group">
        <h5>كلمة السر:</h5>
        <input type="password" class="form-control <?= isset($invalidates->password) && !is_null($invalidates->password) ? 'is-invalid': '' ?>" name="password" value="<?= $password ?>" required>
        <div class="invalid-feedback ">
            <?= isset($invalidates->password) && !is_null($invalidates->password) ? $invalidates->password: '' ?>
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
    <script src="{{ asset('/resources/js/admin/main.js') }}?time={{ now() }}"></script>
    @if (isset($messages) && !is_null($messages))
        <script>
            @foreach ($messages as $message)
                alertMessage(
                  'login-message-{{ $message["name"] }}',
                  "{{ $message['title'] }}",
                  "{{ $message['text'] }}",
                  "{{ $message['type'] }}"
                )
            @endforeach
        </script>
    @endif
  </body>
</html>
