<?php
  $statues = [
    'accepted' => ['class' => 'success', 'text' => 'تم الموافقة'],
    'refused' => ['class' => 'danger', 'text' => 'تم الرفض'],
    'checking' => ['class' => 'checking', 'text' => 'قيد التحقق'],
  ];
  $transferTypes = [
    'recharge' => 'إيداع',
    'withdraw' => 'سحب',
    'transfer' => 'تحويل',
  ]
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    @include('admin.src.headers')
    <link href="{{ asset('resources/css/admin/main.css') }}" rel="stylesheet">
    <link href="{{ asset('resources/css/admin/user-details.css') }}" rel="stylesheet">
    <title>User Details #{{ $user->id }}</title>
  </head>
  <body>
    <div class="topbar">
      <div class="topbar-logo">
        <img src="{{ asset('resources/media/logo.png') }}">
        <h3><span class="danger">Service|</span><span class="success">Electronic</span> </h3>
      </div>
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
              <img src="../../../file/admin/admin_profile_default">
              <span>
                <h2><?= $admin->username ?></h2>
              </span>
            </div>
            <div class="dropdown-divider"></div>
            <a href="../../../admin/logout" class="dropdown-item" style="display: flex;">
              <span class="material-symbols-sharp" style="margin-right: 10px">logout</span>
              تسجيل الخروج
            </a>
          </div>
        </div>

      </div>
    </div>
    <div class="body">
      <div class="content">
        <h1>User Datails (#{{ $user->id }})</h1>
        <div class="user-details">
          <div class="user">
            <img class="user-image" src="../../../file/admin/{{ $user->profile_image_id }}">
            <h2>{{ $user->fullname }}</h2>
          </div>
          <div class="details">
            <h2>User Details:</h2>
            <span class="detail-item">
              <span>Firstname: </span>{{ $user->firstname }}<br>
            </span>
            <span class="detail-item">
              <span>Lastname: </span>{{ $user->lastname }}<br>
            </span>
            <span class="detail-item">
              <span>Phone: </span><a href="tel:{{ $user->phone }}">{{ $user->phone }}</a><br>
            </span>
            <span class="detail-item">
              <span>Email: </span><a href="mailto:{{ $user->email }}">{{ $user->email }}</a><br>
            </span>
            <span class="detail-item">
              <span>Email Status: </span>
              <?php echo $user->email_verified_at ? '<span class="success">محقق</span>' : '<span class="danger">غير محقق</span>' ?>
              <br>
            </span>
            <span class="detail-item">
              <span>Identity Status: </span>
              <?php echo $user->identity_verifited_at ? '<span class="success">محققة</span>' : '<span class="danger">غير محققة</span>' ?>
              <br>
            </span>
            <span class="detail-item">
              <span>Identity Status: </span>{{ $user->identity_status }}<br>
            </span>
            <span class="detail-item">
              <span>Identity Answer Descriptioin: </span>{{ $user->identity_answer_description ?? '--' }}<br>
            </span>
            <span class="detail-item">
              <span>Wallet: </span>#{{ $user->wallet_id }}<br>
            </span>
            <span class="detail-item">
              <span>is Seller: </span><?php echo !is_null($user->seller)? '<span class="success">نعم</span>': '<span class="danger">لا</span>' ?><br>
            </span>
            <span class="detail-item">
              <span>Balance: </span><span class="success">{{ $user->wallet->balance }} DZD</span><br>
            </span>
            <span class="detail-item">
              <span>Joined at: </span>{{ $user->created_at }}<br>
            </span>
            @if (!is_null($user->seller))
            <hr>
            <h2>Store Detials:</h2>
            <span class="detail-item">
              <span>Store Name: </span>{{ $user->seller->store_name }}<br>
              <span>Store Address: </span>{{ $user->seller->store_address }}<br>
              <span>Answered description: </span>{{ $user->seller->answer_description ?? '--'}}<br>
              <span>Status: </span><span class="<?php echo $statues[$user->seller->status]['class'] ?>"><?php echo $statues[$user->seller->status]['text'] ?></span><br>
              <span>Answered at: </span>{{ $user->seller->answered_at }}<br>
              <span>Created at: </span>{{ $user->seller->created_at }}<br>
            </span>
            @endif
          </div>
        </div>
        <hr>
        <h1>Activity Log</h1>
        <div id="all-user-transfers" class="custom-table tab-view-tab-item" checkbox="true">
          <div class="custom-table-header">
            <h2 class="custom-table-title">التحويلات</h2>
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
              <th style="min-width: 90px; max-width: 90px;">الرصيد المستقبل</th>
              <th style="min-width: 90px; max-width: 90px;">الرصيد المرسل</th>
              <th style="min-width: 80px; max-width: 80px;">العملة المستقبلة</th>
              <th style="min-width: 80px; max-width: 80px;">العملة المرسلة</th>
              <th style="min-width: 90px;">النوع</th>
              <th style="min-width: 90px;">المحفظة</th>
              <th style="min-width: 80px; max-width: 80px;">الوضعية</th>
              <th style="min-width: 80px; max-width: 80px;">تم الرد في</th>
              <th style="min-width: 80px; max-width: 80px;">أنشئ في</th>
            </thead>
            <tbody>
              @foreach ($transfers as $transfer)
              <tr>
                <td>
                  <span class="custom-checkbox">
                    <input type="checkbox" id="all-user-transfers-chb-${{ $transfer->id }}">
                    <label for="all-user-transfers-chb-${{ $transfer->id }}"></label>
                  </span>
                </td>
                <td>{{ $transfer->id }}</td>
                <td>
                  <span class="<?php echo $transfer->sended_balance == 0? 'danger' : 'success' ?>">
                    {{ $transfer->sended_balance }} {{ $transfer->sended_currency->char }}
                  </span>
                </td>
                <td>
                  <span class="<?php echo $transfer->sended_balance == 0? 'danger' : 'success' ?>">
                    {{ $transfer->received_balance }} {{ $transfer->received_currency->char }}
                  </span>
                </td>
                <td>{{ $transfer->sended_currency->name }} ({{ $transfer->sended_currency->char }})</td>
                <td>{{ $transfer->received_currency->name }} ({{ $transfer->received_currency->char }})</td>
                <td>
                  {{ $transferTypes[$transfer->for_what] }}
                </td>
                <td><?php echo $transfer->wallet ?? '<span class="danger">لا يوجد</span>' ?></td>
                <td><span class="<?php echo $statues[$transfer->status]['class'] ?>"><?php echo $statues[$transfer->status]['text'] ?></span></td>
                <td>{{ $transfer->answered_at }}</td>
                <td>{{ $transfer->created_at }}</td>
              </tr>
              @endforeach
            </tbody>
            <tfoot>
              <tr>
                <th order="false"></th>
                <th search="true">#</th>
                <th search="true">الرصيد المستقبل</th>
                <th search="true">الرصيد المرسل</th>
                <th search="true">العملة المرسلة</th>
                <th search="true">العملة المرسلة</th>
                <th search="true">النوع</th>
                <th search="true">المحفظة</th>
                <th search="true">الوضعية</th>
                <th search="true">تم الرد في</th>
                <th search="true">أنشئ في</th>
              </tr>
            </tfoot>
          </table>
        </div>

        <div id="all-user-purchases" class="custom-table tab-view-tab-item" checkbox="true">
          <h3 class="table-refresh" table="all-user-purchases">تحديث</h3>
          <div class="custom-table-header">
            <h2 class="custom-table-title">المشتريات</h2>
          </div>
          <table class="table table-bordered table-condensed table-hover table-striped text-center">
            <thead>
              <th>
                <span class="custom-checkbox">
                  <input type="checkbox" id="all-user-purchases-select-all" class="select-all">
                  <label for="all-user-purchases-select-all"></label>
                </span>
              </th>
              <th style="min-width: 50px; max-width: 50px;">#</th>
              <th style="min-width: 80px;">المنتج</th>
              <th style="min-width: 50px; max-width: 50px;">الكمية</th>
              <th style="min-width: 150px;">السعر الإجمالي</th>
              <th style="min-width: 120px;">عنوان الشحن</th>
              <th style="min-width: 120px;">طلب في</th>
              <th style="min-width: 120px; max-width: 120px;"></th>
            </thead>
            <tbody>
              @foreach ($purchases as $purchase)
              <td>
                <span class="custom-checkbox">
                  <input type="checkbox" id="all-user-transfers-chb-${{ $purchase->id }}">
                  <label for="all-user-transfers-chb-${{ $purchase->id }}"></label>
                </span>
              </td>
              <td>{{ $purchase->id }}</td>
              <td>{{ $purchase->product->name }}</td>
              <td>{{ $purchase->count }}</td>
              <td>{{ $purchase->total_price }}</td>
              <td>{{ $purchase->address }}</td>
              <td>{{ $purchase->created_at }}</td>
              @endforeach
            </tbody>
            <tfoot>
              <tr>
                <th></th>
                <th search="true">#</th>
                <th search="true">المنتج</th>
                <th search="true">الكمية</th>
                <th search="true">السعر الإجمالي</th>
                <th search="true">عنوان الشحن</th>
                <th search="true">طلب في</th>
                <th></th>
              </tr>
            </tfoot>
          </table>
        </div>

        @if ($user->seller)

        <h1>Seller Log</h1>

        <div id="all-user-products" class="custom-table tab-view-tab-item" checkbox="true">
          <div class="custom-table-header">
            <h2 class="custom-table-title">المنتجات</h2>
          </div>
          <table class="table table-bordered table-condensed table-hover table-striped text-center">
            <thead>
              <th>
                <span class="custom-checkbox">
                  <input type="checkbox" id="all-products-select-all" class="select-all">
                  <label for="all-products-select-all"></label>
                </span>
              </th>
              <th style="min-width: 50px; max-width: 50px;">#</th>
              <th style="min-width: 130px;">الصورة</th>
              <th style="min-width: 120px;">الاسم</th>
              <th style="min-width: 80px;">السعر</th>
              <th style="min-width: 100px; max-width: 100px;">النوع</th>
              <th style="min-width: 130px;">الوصف</th>
              <th style="min-width: 90px; max-width: 90px;">أنشئ في</th>
            </thead>
            <tbody>
              @foreach ($products as $product)
              <tr>
                <td>
                  <span class="custom-checkbox">
                    <input type="checkbox" id="all-user-products-chb-{{ $product->id }}">
                    <label for="all-user-products-chb-{{ $product->id }}"></label>
                  </span>
                </td>
                <td>{{ $product->id }}</td>
                <td><div class="table-img"><img src="../../../file/admin/{{ $product->images_ids[0].'?'.time() }}"></div></td>
                <td>{{ $product->name }}</td>
                <td>{{ $product->price }} DZD</td>
                <td>{{ $product->category->name['en']}}</td>
                <td>{{ $product->description }} DZD</td>
                <td>{{ $product->created_at }}</td>
              </tr>
              @endforeach
            </tbody>
            <tfoot>
              <tr>
                <th></th>
                <th search="true">#</th>
                <th></th>
                <th search="true">الاسم</th>
                <th search="true">السعر</th>
                <th search="true">النوع</th>
                <th search="true">الوصف</th>
                <th search="true">أنشئ في</th>
              </tr>
            </tfoot>
          </table>
        </div>

        <div id="all-seller-purchases" class="custom-table tab-view-tab-item" checkbox="true">
          <h3 class="table-refresh" table="all-seller-purchases">تحديث</h3>
          <div class="custom-table-header">
            <h2 class="custom-table-title">المبيعات</h2>
          </div>
          <table class="table table-bordered table-condensed table-hover table-striped text-center">
            <thead>
              <th>
                <span class="custom-checkbox">
                  <input type="checkbox" id="all-seller-purchases-select-all" class="select-all">
                  <label for="all-seller-purchases-select-all"></label>
                </span>
              </th>
              <th style="min-width: 50px; max-width: 50px;">#</th>
              <th style="min-width: 130px;">المشتري</th>
              <th style="min-width: 80px;">المنتج</th>
              <th style="min-width: 50px; max-width: 50px;">الكمية</th>
              <th style="min-width: 150px;">السعر الإجمالي</th>
              <th style="min-width: 120px;">عنوان الشحن</th>
              <th style="min-width: 120px;">طلب في</th>
              <th style="min-width: 120px; max-width: 120px;"></th>
            </thead>
            <tbody>
              @foreach ($sellerPurchases as $purchase)
              <td>
                <span class="custom-checkbox">
                  <input type="checkbox" id="all-seller-transfers-chb-${{ $purchase->id }}">
                  <label for="all-seller-transfers-chb-${{ $purchase->id }}"></label>
                </span>
              </td>
              <td>{{ $purchase->id }}</td>
              <td>{{ $purchase->fullname}}<br>{{ $purchase->phone }}</td>
              <td>{{ $purchase->product->name }}</td>
              <td>{{ $purchase->count }}</td>
              <td>{{ $purchase->total_price }}</td>
              <td>{{ $purchase->address }}</td>
              <td>{{ $purchase->created_at }}</td>
              @endforeach
            </tbody>
            <tfoot>
              <tr>
                <th></th>
                <th search="true">#</th>
                <th search="true">المشتري</th>
                <th search="true">المنتج</th>
                <th search="true">الكمية</th>
                <th search="true">السعر الإجمالي</th>
                <th search="true">عنوان الشحن</th>
                <th search="true">طلب في</th>
                <th></th>
              </tr>
            </tfoot>
          </table>
        </div>

        @endif
      </div>
    </div>
    <script src="{{ asset('/resources/js/admin/main.js') }}?time={{ now() }}"></script>
    <script>
      initTable('#all-user-transfers table', [[1, 'desc']]).draw();
      initTable('#all-user-products table', [[1, 'desc']]).draw();
      initTable('#all-user-purchases table', [[1, 'desc']]).draw();
      initTable('#all-seller-purchases table', [[1, 'desc']]).draw();
    </script>
  </body>
</html>
