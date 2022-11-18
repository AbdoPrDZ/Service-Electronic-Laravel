<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    @include('admin.src.headers')
    <title>Service Electric | Admin Panel</title>
    <link href="{{ asset('resources/css/admin/main.css') }}?time={{ now() }}" rel="stylesheet">
    <link href="{{ asset('resources/css/admin/dashboard.css') }}?time={{ now() }}" rel="stylesheet">
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
              <h3><span class="danger">Service|</span><span class="success">Electric</span> </h3>
            </div>
            <div class="topbar-actions">
              <div class="dropdown dropdown-selecetion" id="person-dropdown">
                <div class="btn btn-primary btn-icon dropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                  <span class="material-symbols-sharp">person</span>
                </div>
                <div class="dropdown-menu">
                  <div class="short-profile">
                    {{-- <img src="{{ asset('resources/media/admin/admin.png') }}"> --}}
                    <img src="./file/admin_profile_default">
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
                <button type="button" class="btn btn-primary btn-icon dropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                  <span class="material-symbols-sharp">notifications</span>
                </button>
                <div class="dropdown-menu">
                </div>
              </div>
            </div>
          </div>

          <div class="main">
            <div class="sidebar">
              <div id="users" class="sidebar-item">
                <span class="material-symbols-sharp">person</span>
                <h3>Users</h3>
              </div>
              <div id="transfers" class="sidebar-item">
                <span class="material-symbols-sharp">currency_exchange</span>
                <h3>Transfers</h3>
              </div>
              <div id="currencies" class="sidebar-item">
                <span class="material-symbols-sharp">euro</span>
                <h3>Currencies</h3>
              </div>
              <div id="categories" class="sidebar-item">
                <span class="material-symbols-sharp">category</span>
                <h3>Categories</h3>
              </div>
              <div id="products" class="sidebar-item">
                <span class="material-symbols-sharp">store</span>
                <h3>Products</h3>
              </div>
            </div>

            <div id="tab-content" class="hide">
              @include('admin.tabs.users')
              @include('admin.tabs.transfers')
              @include('admin.tabs.currencies')
              @include('admin.tabs.categories')
              @include('admin.tabs.products')
            </div>

            <div id="tab-loading" class="loading show">
              <div class="loading-box">
                <img class="loading-img" src="{{ asset('resources/media/admin/loading.gif') }}">
                <p class="loading-message"></p>
              </div>
            </div>
          </div>
      </div>

      <div class="modal fade" id="view-user" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog" role="document">
          <div class="modal-content form">
            <div class="modal-header">
              <h2 class="modal-title">User Details(#<span id="view-user-id"></span>)</h2>
            </div>
            <div class="modal-body">
              @include('admin.views.view-user-form')
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-danger" action="close">Cancel</button>
            </div>
          </div>
        </div>
      </div>

      <div class="modal fade" id="view-transfer" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog" role="document">
          <div class="modal-content form">
            <div class="modal-header">
              <h2 class="modal-title">Transfer Details(#<span id="view-transfer-id"></span>)</h2>
            </div>
            <div class="modal-body">
              @include('admin.views.view-transfer-form')
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-danger" action="close">Cancel</button>
            </div>
          </div>
        </div>
      </div>

      <div class="modal fade" id="view-file" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog" role="document">
          <div class="modal-content form">
            <div class="modal-header">
              <h2 class="modal-title">File Details(#<span id="view-file-id"></span>)</h2>
            </div>
            <div class="modal-body">
              @include('admin.views.view-file-form')
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-primary" action="save">Save</button>
              <button type="button" class="btn btn-danger" action="close">Cancel</button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div id="alerts" class="noties topright"></div>

    <script src="{{ asset('/resources/js/admin/main.js') }}?time={{ now() }}"></script>
    <script src="{{ asset('/resources/js/address-input/index.js') }}?time={{ now() }}"></script>
    <script type="module" src="{{ asset('/resources/js/admin/dashboard.js') }}?time={{ now() }}"></script>

  </body>
</html>
