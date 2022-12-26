<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <meta name="socket-token" content="{{ $socketToken }}" />
    @include('admin.src.headers')
    <title>Service Electronic | Admin Panel</title>
    <link href="{{ asset('resources/css/admin/main.css') }}?time={{ now() }}" rel="stylesheet">
    <link href="{{ asset('resources/css/admin/dashboard.css') }}?time={{ now() }}" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.3.1/tinymce.min.js" integrity="sha512-eV68QXP3t5Jbsf18jfqT8xclEJSGvSK5uClUuqayUbF5IRK8e2/VSXIFHzEoBnNcvLBkHngnnd3CY7AFpUhF7w==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
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
              <img src="{{ asset('resources/media/logo.png') }}?time={{ now() }}">
              <h3><span class="danger">Service|</span><span class="success">Electronic</span> </h3>
            </div>
            <div class="topbar-actions">
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
                  <!-- <div class="dropdown-divider"></div> -->
                  <!-- <div class="dropdown-item" value="account"></div> -->
                  <div class="dropdown-divider"></div>
                  <a href="./admin/logout" class="dropdown-item">تسجيل الخروج</a>
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
                <span class="material-symbols-sharp">category
                  <span class="news-badge position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger hide"></span>
                </span>
                <h3>مبيعات المنتجات</h3>
              </div>
              <div id="mails" class="sidebar-item">
                <span class="material-symbols-sharp">mail
                  <span class="news-badge position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger hide"></span>
                </span>
                <h3>الإيمايلات</h3>
              </div>
              <div id="settings" class="sidebar-item">
                <span class="material-symbols-sharp">settings
                  <span class="news-badge position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger hide"></span>
                </span>
                <h3>الإعدادات</h3>
              </div>
            </div>

            <div id="tab-content" class="hide">
              @include('admin.tabs.users')
              @include('admin.tabs.sellers')
              @include('admin.tabs.transfers')
              @include('admin.tabs.currencies')
              @include('admin.tabs.products')
              @include('admin.tabs.purchases')
              @include('admin.tabs.mails')
              @include('admin.tabs.settings')
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
      @include('admin.views.view-transfer-form')
      @include('admin.views.view-seller-form')
      @include('admin.views.create-edit-currency-form')
      @include('admin.views.create-edit-category-form')
      @include('admin.views.create-edit-mail-template-form')

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
    <script src="{{ asset('/resources/js/address-input/index.js') }}?time={{ now() }}"></script>
    <script type="module" src="{{ asset('/resources/js/admin/dashboard.js') }}?time={{ now() }}"></script>
    <script>
      window.news = {
        'users': 0,
        'sellers': 0,
        'transfers': 0,
        'currencies': 0,
        'products': 0,
        'purchases': 0,
        'mails': 0,
        'settings': 0,
      };
    </script>

  </body>
</html>
