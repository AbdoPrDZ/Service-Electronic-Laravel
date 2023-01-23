<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <meta name="socket-token" content="{{ $socketToken }}" />
    @include('admin.src.headers')
    <link href="{{ asset('resources/css/admin/main.css') }}" rel="stylesheet">
    <link href="{{ asset('resources/css/admin/dashboard.css') }}" rel="stylesheet">
    <title>Service Electronic | Admin Panel</title>
  </head>
  <body auto-display="false">
    <div id="body-loading" class="loading show">
      <div class="loading-box">
        <img class="loading-img" src="{{ asset('resources/media/admin/loading.gif') }}">
        <p class="loading-message"></p>
      </div>
    </div>

    <div id="body-content" class="hide">
      <div id="dashboard">
          <div class="topbar">
            <div class="topbar-logo">
              <img src="{{ asset('resources/media/logo.png') }}">
              <h3><span class="danger">Service|</span><span class="success">Electronic</span> </h3>
            </div>
            <div class="topbar-actions">
              @if (!is_null($admin->balance))
                <h2 class=" {{ $admin->balance != 0 ? 'success' : 'danger' }}" style="font-size: 14px;font-weight: bold;">
                  {{ $admin->balance ?? 0 }} SE
                </h2>
              @endif
              <div class="dropdown dropdown-selecetion" id="person-dropdown">
                <div class="btn btn-primary btn-icon dropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                  <span class="material-symbols-sharp">person</span>
                </div>
                <div class="dropdown-menu">
                  <div class="short-profile">
                    <img src="./file/admin/admin_profile_default">
                    <span>
                      <h2><?= $admin->username ?></h2>
                    </span>
                  </div>
                  <div class="dropdown-divider"></div>
                  <a href="./admin/setting" class="dropdown-item" style="display: flex;">
                    <span class="material-symbols-sharp" style="margin-right: 10px">settings</span>
                    الإعدادات
                  </a>
                  <div class="dropdown-divider"></div>
                  <a href="./admin/logout" class="dropdown-item" style="display: flex;">
                    <span class="material-symbols-sharp" style="margin-right: 10px">logout</span>
                    تسجيل الخروج
                  </a>
                </div>
              </div>
              <div class="dropdown" id="notifications-dropdown">
                <button type="button" class="btn btn-primary btn-icon dropdown position-relative" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                  <span class="material-symbols-sharp">notifications</span>
                  <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger hide"></span>
                </button>
                <ul class="dropdown-menu dropdown-menu-right">
                </ul>
              </div>
            </div>
          </div>

          <div class="main">
            <div class="sidebar">
              <div id="users" class="sidebar-item">
                <span class="material-symbols-sharp">person
                  <span class="news-badge position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger hide"></span>
                </span>
                <h3>المستخدمون</h3>
              </div>
              <div id="sellers" class="sidebar-item">
                <span class="material-symbols-sharp">store
                  <span class="news-badge position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger hide"></span>
                </span>
                <h3>البائعون</h3>
              </div>
              <div id="transfers" class="sidebar-item">
                <span class="material-symbols-sharp">currency_exchange
                  <span class="news-badge position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger hide"></span>
                </span>
                <h3>التحويلات</h3>
              </div>
              <div id="currencies" class="sidebar-item">
                <span class="material-symbols-sharp">euro
                  <span class="news-badge position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger hide"></span>
                </span>
                <h3>العملات</h3>
              </div>
              <div id="products" class="sidebar-item">
                <span class="material-symbols-sharp">shopping_cart
                  <span class="news-badge position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger hide"></span>
                </span>
                <h3>المنتجات</h3>
              </div>
              <div id="purchases" class="sidebar-item">
                <span class="material-symbols-sharp">shopping_bag
                  <span class="news-badge position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger hide"></span>
                </span>
                <h3>مبيعات المنتجات</h3>
              </div>
              <div id="offers" class="sidebar-item">
                <span class="material-symbols-sharp">style
                  <span class="news-badge position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger hide"></span>
                </span>
                <h3>العروض</h3>
              </div>
              <div id="mails" class="sidebar-item">
                <span class="material-symbols-sharp">mail
                  <span class="news-badge position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger hide"></span>
                </span>
                <h3>الإيمايلات</h3>
              </div>
            </div>

            <div id="tab-content" class="hide">
              @include('admin.tabs.users')
              @include('admin.tabs.sellers')
              @include('admin.tabs.transfers')
              @include('admin.tabs.currencies')
              @include('admin.tabs.products')
              @include('admin.tabs.purchases')
              @include('admin.tabs.offers')
              @include('admin.tabs.mails')
            </div>

            <div id="tab-loading" class="loading show">
              <div class="loading-box">
                <img class="loading-img" src="{{ asset('resources/media/admin/loading.gif') }}">
                <p class="loading-message"></p>
              </div>
            </div>
          </div>
      </div>

      @include('admin.views.view-user-form')
      @include('admin.views.send-notification-form')
      @include('admin.views.view-transfer-form')
      @include('admin.views.view-seller-form')
      @include('admin.views.view-purchase-form')

      @include('admin.views.create-edit-currency-form')
      @include('admin.views.create-edit-category-form')
      @include('admin.views.create-edit-offer-form')
      @include('admin.views.view-offer-request-form')
      @include('admin.views.create-edit-template-form')
      @include('admin.views.create-mail-form')

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

      <div class="modal fade" id="loading-dialog-modal" tabindex="-1" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog" role="document">
          <div class="modal-content form">
            <div class="modal-header">
              <h2 class="modal-title"></h2>
            </div>
            <div class="modal-body">
              <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
              <span class="dialog-message">يرجى الإنتظار...</span>
            </div>
          </div>
        </div>
      </div>

      <div class="modal fade" id="images-preview-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog" role="document">
          <div class="modal-content form">
            <div class="modal-header">
              <h2 class="modal-title">عرض الصور</h2>
            </div>
            <div class="modal-body">
              <div id="images-preivew-carousel" class="carousel slide" data-ride="carousel">
                <ol class="carousel-indicators">
                </ol>
                <div class="carousel-inner">
                </div>
                <a class="carousel-control-prev" href="#images-preivew-carousel" role="button" data-slide="prev">
                  <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                  <span class="sr-only">Previous</span>
                </a>
                <a class="carousel-control-next" href="#images-preivew-carousel" role="button" data-slide="next">
                  <span class="carousel-control-next-icon" aria-hidden="true"></span>
                  <span class="sr-only">Next</span>
                </a>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-danger" action="close">Cancel</button>
            </div>
          </div>
        </div>
      </div>

    </div>

    <script src="{{ asset('/resources/js/admin/main.js') }}?time={{ now() }}"></script>
    <script type="module" src="{{ asset('/resources/js/admin/dashboard.js') }}?time={{ now() }}"></script>
    <script>
      window.rules = [
        ['required|string', 'text'],
        ['required|number', 'number'],
        ['required|numeric', 'float'],
        ['required|email', 'email'],
        ['required|phone', 'phone'],
      ];
      window.inputTypes = [
        ['text', 'text'],
        ['textarea', 'multi line text'],
        ['datetime', 'datetime'],
        ['phone', 'phone'],
        ['email', 'email'],
        ['address', 'address'],
        ['number', 'number'],
      ];
    </script>

  </body>
</html>
