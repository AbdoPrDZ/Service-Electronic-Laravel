import { io } from '../socket.io.esm.min.js';

// window.permissions = JSON.parse(CryptoJS.AES.decrypt(permissions, document.cookie.replace('adminAuth=', '')).toString(CryptoJS.enc.Utf8));

const socket = io.connect(':8000', {
  path: '/admin/',
  autoConnect: false,
});

var statuses = {
  accepted: '<span class="success">تم الموفقة</span>',
  refused: '<span class="danger">تم الرفض</span>',
  checking: '<span class="warning">قيد التحقق</span>',
  not_verifited: '<span class="danger">غير محققة</span>',
  verifited: '<span class="success">تم التحقق</span>',
};

const tabsTables = {
  users: [['all-users', 'users'], ['all-users-recharges', 'recharges'], ['all-users-withdrawes', 'withdrawes']],
  sellers: [['all-sellers', 'sellers'], ['all-new-sellers', 'new_sellers']],
  transfers: [['all-transfers', '*']],
  currencies: [['all-currencies', '*']],
  products: [['all-products', 'products'], ['all-categories', 'categories']],
  purchases: [['all-purchases', '*']],
  mails: [['all-mails', 'mails'], ['all-mails-templates', 'mails_templates']],
}

async function loadData(target) {
  var response = await $.get(`./admin/load/${target}`);
  var data = response.data;
  if(data) {
    StorageDatabase.collection(target).set(data);
    return data;
  }
}

async function loadNews(target) {
  var news = await $.get(`./admin/news/${target}`);
  console.log(target, news);
  window.news[target] = news.count ?? 0;
  countingNews(target);
}

function loadAllNews() {
  for (const target in window.news) {
    loadNews(target);
  }
}

async function readNews(target) {
  if(window.news[target] > 0) {
    console.log(await $.get(`./admin/news/${target}/read`));
    await loadNews(target);
  }
}

async function loadNotifications() {
  var notifications = await $.get(`./admin/notifications/all`);
  StorageDatabase.collection('notifications').set(notifications);
  clearNotifications()
  for (const id in notifications) {
    const notification = notifications[id];
    addNotification(notification.title, notification.message,
                    notification.created_at, `./file/admin/${notification.image_id}`,
                    `readNotification(${id})`);
  }
  countingNofiticaitons()
}

window.readNotification = async function (id) {
  const notification = StorageDatabase.collection('notifications').doc(id).get()
  $.get(`./admin/notifications/${id}/read`);
  loadNotifications();
  if(notification) {
    if(['new-transfer-created', 'new-withdraw-created', 'new-recharge-created'].indexOf(notification.name) != -1) {
      if(window.currentTabName != 'transfers') changeTab('transfers');
      var transfer = null;
      if(notification.name == 'new-transfer-created') {
        transfer = StorageDatabase.collection('transfers').doc(notification.data.transfer_id).get();
      } else {
        trnasfer = StorageDatabase.collection('users')
                         .doc(notification.name == 'new-withdraw-created' ? 'withdraws': 'recharges')
                         .doc(notification.data.transfer_id).get();
      }
      if(transfer) viewTransfer(transfer)
    }
  }
}

async function loadTab() {
  var data = await loadData(window.currentTabName);
  if(data) {
    tabsTables[window.currentTabName].forEach(table => {
      var tableData = data;
      if(table[1] != '*') tableData = data[table[1]];
      updateTable(tableData, table[0]);
    });
  }
}

async function changeTab(tabName) {
  readNews(window.currentTabName);
  $('#tab-loading').attr('class', 'loading show');
  $('#tab-content').attr('class', 'hide');
  if (!tabName) tabName = 'users';
  $(`#tab-content .tab-view-tab-item`).removeClass('selected');
  $(`#tab-content .tab-view-tab-item[for="${tabName}"]`).addClass('selected');
  $(`.sidebar .sidebar-item`).attr('class', 'sidebar-item');
  $(`.sidebar #${tabName}`).attr('class', 'sidebar-item active');
  window.history.pushState('', '', `?tab=${tabName}`);
  window.currentTabName = tabName;
  await loadTab();
  $('#tab-loading').attr('class', 'loading hide');
  $('#tab-content').attr('class', 'show');
}

function updateTable(values, tableId) {
  var table = initTable(`#${tableId} table`, [[1, 'desc']]);
  $(`#${tableId} table thead input[type="checkbox"].select-all`).prop('checked', false);
  table.clear();
  if (values) {
    for (const id in values) {
      var row = [];
      row.push(
        `<td>
          <span class="custom-checkbox">
            <input type="checkbox" id="${tableId}-chb-${id}">
            <label for="${tableId}-chb-${id}"></label>
          </span>
        </td>`);
      if(tableId == 'all-users') {
        row.push(`<td>${id}</td>`);
        row.push(`<td><div class="table-img"><img src="./file/admin/${values[id].profile_image_id}?t=${(new Date()).getTime()}"></div></td>`);
        row.push(`<td>${values[id].firstname} ${values[id].lastname}</td>`);
        row.push(`<td>${values[id].email}</td>`);
        row.push(`<td>${values[id].phone}</td>`);
        row.push(`<td>${values[id].balance == 0 ? '<span class="danger">0 SE</span>':`<span class="success">${values[id].balance} SE</span>`}</td>`);
        row.push(`<td>${values[id].checking_balance == 0 ? '<span class="danger">0 SE</span>':`<span class="warning">${values[id].checking_balance} SE</span>`}</td>`);
        row.push(`<td>${values[id].email_verified ? '<span class="success">محقق</span>': '<span class="danger">غير محقق</span>'}</td>`);
        row.push(`<td>${statuses[values[id].identity_status]}</td>`);
        row.push(`<td>${values[id].created_at}</td>`);
        row.push(`
          <td>
            <button class="btn btn-icon btn-secondary" action="view">
              <span class="material-symbols-sharp">open_in_new</span>
            </button>
            <button class="btn btn-icon btn-danger" action="delete"">
              <span class="material-symbols-sharp">delete</span>
            </button>
          </td>`);
      } else if(tableId == 'all-users-recharges') {
        row.push(`<td><div class="table-img"><img src="./file/admin/${values[id].user.profile_image_id}?t=${(new Date()).getTime()}"></div></td>`);
        row.push(`<td>
          ${values[id].user.firstname} ${values[id].user.lastname}<br>
          ${values[id].user.phone}<br>
          ${values[id].user.email}
        </td>`);
        row.push(`<td>${values[id].user.balance}</td>`);
        row.push(`<td>${values[id].id}</td>`);
        row.push(`<td>${values[id].sended_balance} ${values[id].sended_currency.char}</td>`);
        row.push(`<td>${values[id].received_balance} ${values[id].received_currency.char}</td>`);
        row.push(`<td>${values[id].created_at}</td>`);
        row.push(`<td>
          <button class="btn btn-icon btn-secondary" action="view">
            <span class="material-symbols-sharp">open_in_new</span>
          </button>
          <button class="btn btn-icon btn-danger" action="delete"">
            <span class="material-symbols-sharp">delete</span>
          </button>
        </td>`);
      } else if(tableId == 'all-users-withdrawes') {
        row.push(`<td><div class="table-img"><img src="./file/admin/${values[id].user.profile_image_id}?t=${(new Date()).getTime()}"></div></td>`);
        row.push(`<td>
          ${values[id].user.firstname} ${values[id].user.lastname}<br>
          ${values[id].user.phone}<br>
          ${values[id].user.email}
        </td>`);
        row.push(`<td>${values[id].user.balance}</td>`);
        row.push(`<td>${values[id].received_balance} ${values[id].received_currency.char}</td>`);
        row.push(`<td>${values[id].created_at}</td>`);
        row.push(`<td>
          <button class="btn btn-icon btn-secondary" action="view">
            <span class="material-symbols-sharp">open_in_new</span>
          </button>
          <button class="btn btn-icon btn-danger" action="delete"">
            <span class="material-symbols-sharp">delete</span>
          </button>
        </td>`);
      } else if(tableId == 'all-sellers' || tableId == 'all-new-sellers') {
        row.push(`<td>${id}</td>`);
        row.push(`<td><div class="table-img"><img src="./file/admin/${values[id].user.profile_image_id}?t=${(new Date()).getTime()}"></div></td>`);
        row.push(`<td>${values[id].user.firstname} ${values[id].user.lastname}</td>`);
        row.push(`<td>${values[id].store_name}</td>`);
        row.push(`<td>${values[id].user.email}</td>`);
        row.push(`<td>${values[id].user.phone}</td>`);
        row.push(`<td>${values[id].store_address}</td>`);
        row.push(`<td>${values[id].user.balance == 0 ? '<span class="danger">0 SE</span>':`<span class="success">${values[id].user.balance} SE</span>`}</td>`);
        row.push(`<td>${values[id].user.checking_balance == 0 ? '<span class="danger">0 SE</span>':`<span class="warning">${values[id].user.checking_balance} SE</span>`}</td>`);
        row.push(`<td>${statuses[values[id].status]}</td>`);
        row.push(`<td>${values[id].created_at}</td>`);
        row.push(`
          <td>
            <button class="btn btn-icon btn-secondary" action="view">
              <span class="material-symbols-sharp">open_in_new</span>
            </button>
            <button class="btn btn-icon btn-danger" action="delete"">
              <span class="material-symbols-sharp">delete</span>
            </button>
          </td>`);
      } else if(tableId == 'all-transfers') {
        row.push(`<td>${values[id].id}</td>`);
        row.push(`<td>${values[id].user.firstname} ${values[id].user.lastname}</td>`);
        row.push(`<td>${values[id].sended_balance} ${values[id].sended_currency.char}</td>`);
        row.push(`<td>${values[id].received_balance} ${values[id].received_currency.char}</td>`);
        row.push(`<td>${values[id].sended_currency.name} (${values[id].sended_currency.char})</td>`);
        row.push(`<td>${values[id].received_currency.name} (${values[id].received_currency.char})</td>`);
        row.push(`<td>${values[id].wallet ?? '<span class="danger">لا يوجد</span>'}</td>`);
        row.push(`<td>${statuses[values[id].status]}</td>`);
        row.push(`<td>${values[id].answered_at ?? '<span class="danger">لا يوجد</span>'}</td>`);
        row.push(`<td>${values[id].created_at}</td>`);
        row.push(`<td>
          <button class="btn btn-icon btn-secondary" action="view">
            <span class="material-symbols-sharp">open_in_new</span>
          </button>
          <button class="btn btn-icon btn-danger" action="delete"">
            <span class="material-symbols-sharp">delete</span>
          </button>
        </td>`);
      } else if(tableId == 'all-currencies') {
        row.push(`<td>${values[id].id}</td>`);
        row.push(`<td><div class="table-img"><img src="./file/admin/currency-${values[id].id}?t=${(new Date()).getTime()}"></div></td>`);
        row.push(`<td>${values[id].name}</td>`);
        row.push(`<td>${values[id].char}</td>`);
        row.push(`<td>
          ${values[id].platform_wallet.balance == 0 ?
          `<span class="danger">0 ${values[id].char}</span>` :
          `<span class="success">${values[id].platform_wallet.balance} ${values[id].char}</span>`
        }</td>`);
        var pricesHtml = '<select class="form-control" name="prices">';
        var dPrice = null;
        for (const currencyId in values[id].prices) {
          const price = values[id].prices[currencyId];
          if(currencyId == -1) var currency = values[id];
          else var currency = StorageDatabase.collection('currencies').doc(currencyId).get();
          if(dPrice == null) dPrice = `سعر الشراء: (${price.buy}${currency.char}) <br> سعر البيع: (${price.sell}${currency.char})`;
          pricesHtml += `<option value="سعر الشراء: (${price.buy}${currency.char}) <br> سعر البيع: (${price.sell}${currency.char})">
            ${currency.name}
          </option>`;
        }
        pricesHtml += '</select>';
        pricesHtml += `
          <br>
          <span class="category-name success" name="name">${dPrice}</span>
        `;
        row.push(`<td>${pricesHtml}</td>`);
        row.push(`<td>${values[id].wallet}</td>`);
        row.push(`<td>${values[id].created_at}</td>`);
        row.push(`<td>
          <button class="btn btn-icon btn-warning" action="edit"">
            <span class="material-symbols-sharp">edit</span>
          </button>
          <button class="btn btn-icon btn-danger" action="delete"">
            <span class="material-symbols-sharp">delete</span>
          </button>
        </td>`);
      } else if(tableId == 'all-categories') {
        row.push(`<td>${values[id].id}</td>`);
        row.push(`<td><div class="table-img"><img src="./file/admin/${values[id].image_id}?t=${(new Date()).getTime()}"></div></td>`);
        var namesHtml = '<select class="form-control" name="names">';
        var dName = null;
        for (const nameId in values[id].name) {
          const name = values[id].name[nameId];
          if(!dName) dName = name;
          namesHtml += `<option value="${name}">${nameId}</option>`;
        }
        namesHtml += '</select>';
        namesHtml += `
          <br>
          <span class="category-name">${dName}</span>
        `;
        row.push(`<td>${namesHtml}</td>`);
        row.push(`<td>${values[id].created_at}</td>`);
        row.push(`<td>
          <button class="btn btn-icon btn-warning" action="edit"">
            <span class="material-symbols-sharp">edit</span>
          </button>
          <button class="btn btn-icon btn-danger" action="delete"">
            <span class="material-symbols-sharp">delete</span>
          </button>
        </td>`);
      } else if(tableId == 'all-products') {
        row.push(`<td>${values[id].id}</td>`);
        row.push(`<td><div class="table-img"><img src="./file/admin/${values[id].images_ids[0]}?t=${(new Date()).getTime()}"></div></td>`);
        row.push(`<td>${values[id].name}</td>`);
        row.push(`<td>${values[id].seller.user.firstname} ${values[id].seller.user.lastname}</td>`);
        row.push(`<td>${values[id].price} SE</td>`);
        row.push(`<td>${values[id].category.name.en}</td>`);
        row.push(`<td>${values[id].description.substring(0, 100)}</td>`);
        row.push(`<td>${values[id].created_at}</td>`);
        row.push(`
          <td>
            <button class="btn btn-icon btn-secondary" action="view">
              <span class="material-symbols-sharp">open_in_new</span>
            </button>
            <button class="btn btn-icon btn-danger" action="delete"">
              <span class="material-symbols-sharp">delete</span>
            </button>
          </td>`);
      } else if(tableId == 'all-purchases') {
        row.push(`<td>${values[id].id}</td>`);
        row.push(`<td>${values[id].fullname} <br> ${values[id].phone}</td>`);
        row.push(`<td>${values[id].product.seller.user.fullname} <br> ${values[id].product.seller.user.phone}</td>`);
        row.push(`<td>${values[id].product.name}</td>`);
        row.push(`<td>${values[id].count}</td>`);
        row.push(`<td>${values[id].total_price} DZD</td>`);
        row.push(`<td>${values[id].address}</td>`);
        row.push(`<td>${values[id].created_at}</td>`);
        row.push(`
          <td>
            <button class="btn btn-icon btn-secondary" action="view">
              <span class="material-symbols-sharp">open_in_new</span>
            </button>
          </td>`);
      }
      table.row.add(row).node().id = `${tableId}-item-${id}`;
    }
  }
  table.draw();
}

function viewUser(userValues) {
  $('#view-user .modal-title #view-user-id').html(userValues.id);
  $(`#view-user .modal-body .form-control[name="firstname"]`).val(userValues.firstname);
  $(`#view-user .modal-body .form-control[name="lastname"]`).val(userValues.lastname);
  $(`#view-user .modal-body .form-control[name="phone"]`).val(userValues.phone);
  $(`#view-user .modal-body .form-control[name="email"]`).val(userValues.email);
  $(`#view-user .modal-body .form-control[name="email_verified"]`).html(
    userValues.email_verified ?
    '<span class="success">محقق</span>':
    '<span class="danger">غير محقق</span>'
  );
  $(`#view-user .modal-body .form-control[name="status"] option`).attr('selected', false)
  $(`#view-user .modal-body .form-control[name="status"] option[value="${userValues.identity_status}"]`).attr('selected', true)
  $(`#view-user .modal-body .form-control[name="status-description"]`).html(statuses[userValues.identity_answer_description]);
  $(`#view-user .modal-body .form-control[name="created_at"]`).val(userValues.created_at);
  var images = [];
  userValues.verification_images_ids.forEach(image => {
    images.push(`./file/admin/${image}`);
  });
  window.currentUserImages = images
  $(`#view-user .modal-body button[name="preview-identity-images"]`).attr('onClick', `previewImages(${JSON.stringify(images)})`);

  $('#view-user input').prop('disabled', true);
  $(`#view-user .modal-body .form-control[name="status-description"]`).prop('disabled', false);
  $('#view-user textarea').prop('disabled', true);
  $('#view-user').modal('show');
}

function viewTransfer(transferValues) {
  $('#view-transfer .modal-title #view-transfer-id').html(transferValues.id);
  $(`#view-transfer .modal-body .form-control[name="user"]`).val(`${transferValues.user.firstname} ${transferValues.user.lastname}`);
  $(`#view-transfer .modal-body .form-control[name="sended_balance"]`).val(`${transferValues.sended_balance} ${transferValues.sended_currency.char}`);
  $(`#view-transfer .modal-body .form-control[name="received_balance"]`).val(`${transferValues.received_balance} ${transferValues.received_currency.char}`);
  $(`#view-transfer .modal-body .form-control[name="sended_currency"]`).val(`${transferValues.sended_currency.name} (${transferValues.sended_currency.char})`);
  $(`#view-transfer .modal-body .form-control[name="received_currency"]`).val(`${transferValues.received_currency.name} (${transferValues.received_currency.char})`);
  $(`#view-transfer .modal-body .form-control[name="wallet"]`).val(transferValues.wallet);
  $('#view-transfer .modal-body .form-control[name="status"] option').attr('selected', false)
  $(`#view-transfer .modal-body .form-control[name="status"] option[value="${transferValues.status}"]`).attr('selected', true);
  $(`#view-transfer .modal-body .form-control[name="status-description"]`).val(transferValues.answer_description);
  $(`#view-transfer .modal-body .form-control[name="answered_at"]`).val(transferValues.answered_at ?? 'لا يوجد');
  $(`#view-transfer .modal-body .form-control[name="created_at"]`).val(transferValues.created_at);
  $(`#view-transfer .modal-body .form-control[name="proof"]`).html(
    transferValues.proof_id ?
    `<image src="./file/admin/${transferValues.proof_id}"></image>`:
    '<span class="danger center">لا يوجد</span>'
  );

  $('#view-transfer input').prop('disabled', true);
  $(`#view-transfer .modal-body .form-control[name="status-description"]`).prop('disabled', false);
  $('#view-transfer textarea').prop('disabled', true);
  $('#view-transfer').modal('show');
}

function viewSeller(sellerValues) {
  $('#view-seller .modal-title #view-seller-id').html(sellerValues.id);
  $(`#view-seller .modal-body .form-control[name="seller_fullname"]`).val(`${sellerValues.user.firstname} ${sellerValues.user.lastname}`);
  $(`#view-seller .modal-body .form-control[name="store_name"]`).val(`${sellerValues.store_name}`);
  $(`#view-seller .modal-body .form-control[name="seller_email"]`).val(`${sellerValues.user.email}`);
  $(`#view-seller .modal-body .form-control[name="seller_phone"]`).val(`${sellerValues.user.phone}`);
  $(`#view-seller .modal-body .form-control[name="store_address"]`).val(`${sellerValues.store_address}`);
  $(`#view-seller .modal-body .form-control[name="seller_balance"]`).val(`${sellerValues.user.balance}`);
  $(`#view-seller .modal-body .form-control[name="seller_checking_balance"]`).val(`${sellerValues.user.checking_balance}`);
  $('#view-seller .modal-body .form-control[name="status"] option').attr('selected', false)
  $(`#view-seller .modal-body .form-control[name="status"] option[value="${sellerValues.status}"]`).attr('selected', true);
  $(`#view-seller .modal-body .form-control[name="status-description"]`).val(sellerValues.answer_description);
  $(`#view-seller .modal-body .form-control[name="answered_at"]`).val(sellerValues.answered_at ?? 'لا يوجد');
  $(`#view-seller .modal-body .form-control[name="created_at"]`).val(sellerValues.created_at);

  var statesHtml = '';
  var dPrice = null;
  for (const stateId in sellerValues.delivery_prices) {
    const deliveryPrice = sellerValues.delivery_prices[stateId];
    if(!dPrice) dPrice = `office: ${deliveryPrice.office} | home: ${deliveryPrice.home}`;
    statesHtml += `<option value="${stateId}" office="${deliveryPrice.office}" home="${deliveryPrice.home}">
      ${window.countries['Algeria']['states'][stateId]['name']}
    </option>`;
  }
  $(`#view-seller .modal-body .form-control[name="delivery_states"]`).html(statesHtml);
  $('#view-seller .modal-body .form-control[name="delivery_state_price"]').html(dPrice)

  $('#view-seller input').prop('disabled', true);
  $(`#view-seller .modal-body .form-control[name="status-description"]`).prop('disabled', false);
  $('#view-seller textarea').prop('disabled', true);
  $('#view-seller').modal('show');
}

function countingNofiticaitons() {
  var notificationsCount = $('#notifications-dropdown .notification-item').length

  if(notificationsCount > 0) {
     $('#notifications-dropdown .badge').attr('class',
      $('#notifications-dropdown .badge').attr('class')
      .replace(' hide ', ' show ').replace(' hide', ' show')
    )
    $('#notifications-dropdown .badge').html(notificationsCount < 10 ? notificationsCount : '9+')
  } else {
    $('#notifications-dropdown .badge').attr('class',
     $('#notifications-dropdown .badge').attr('class')
     .replace(' show ', ' hide ').replace(' show', ' hide')
   )
   $('#notifications-dropdown .badge').html('');
  }
}

function countingNews(target) {
    if(window.news[target] > 0) {
      $(`#${target} .news-badge`).attr('class',
        $(`#${target} .news-badge`).attr('class')
        .replace(' hide ', ' show ').replace(' hide', ' show')
      )
    } else {
      $(`#${target} .news-badge`).attr('class',
        $(`#${target} .news-badge`).attr('class')
        .replace(' show ', ' hide ').replace('show ', ' hide')
      )
    }
}

async function onConnect() {
  loadAllNews()
  loadNotifications();
  var tabs = Object.keys(tabsTables);
  window.currentTabName = $_GET('tab') && tabs.indexOf($_GET('tab')) != -1 ? $_GET('tab') : (window.currentTabName || 'users');
  changeTab(currentTabName);

  $.get('./resources/js/address-input/countries.json?n').then((countries) => {
    window.countries = countries;
  })

  StorageDatabase.collection('users').set({});
  StorageDatabase.collection('transfers').set({});
  StorageDatabase.collection('currencies').set({});
  StorageDatabase.collection('notifications').set({});

  $on('.sidebar .sidebar-item', 'click', function() {
    if ($(this).attr('class').indexOf('active') != -1) return;
    changeTab($(this).attr('id'));
  });
  displayBodyContent();
}
function clearNotifications() {
  $('#notifications-dropdown .dropdown-menu').html('');
}

function addNotification(title, message, date, image, onClick) {
  const notificationsElement = $('#notifications-dropdown .dropdown-menu');
  notificationsElement.html(
    notificationsElement.html() +
    `<li class="notification-item" onClick="${onClick}">
      <div class="image"><img src="${image}"></div>
      <div class="content">
        <h3 class="title">${title}</h3>
        <p class="message">${message}</p>
      </div>
      <div class="date">${date}</div>
    </li>`
  );
  countingNofiticaitons();
}

window.connected = false;
$(document).ready(function() {
  socket.connect()
  socket.on('connect_error', (err) => {
    console.log(err)
    $('#body-loading .loading-message').css('display', 'block').html(`خطأ في الإتصال:<br> ${err.toString().replace('Error:', '')}`);
    socket.disconnect();
  });
  socket.on('disconnect', function () {
    console.log('Disconnected');
    window.connected = false;
  });
  socket.on('connect_failed', (err) => {
    console.log(`Connect failed: ${err}`);
    socket.disconnect();
  });
  socket.on('connect', (a) => {
    if(!window.connected) socket.emit('auth', `${$('meta[name="socket-token"]').attr('content')}`)
  });
  socket.on('auth-resualt', (args) => {
    if(args.success) {
      window.connected = true;
      onConnect();
    } else {
      $('#body-loading .loading-message').css('display', 'block').html(`خطأ في الإتصال:<br> ${args.message ?? args.error}`)
      socket.disconnect();
    };
  });
  socket.on('new-transfer-created', async (notification) => {
    console.log(notification)
    var args = JSON.parse(notification.args)
    console.log(args);
    loadTab('transfers');
    loadNews('transfers')
    loadNotifications();
    alertMessage("new-notification", notification.title, notification.message, 'success', 60000);
  });
  socket.on('new-product-solded', async (notification) => {
    console.log(notification)
    var args = JSON.parse(notification.args)
    console.log(args);
    loadTab('purchases');
    loadNews('purchases')
    loadNotifications();
    alertMessage("new-notification", notification.title, notification.message, 'success', 60000);
  });
  socket.on('notifications', (notification) => {
    console.log(notification)
    alertMessage("new-notification", notification.title, notification.message, '');
  });

  $on('#person-dropdown', 'onSelect[value="account"]', function(event, item) {
    console.log('goto account')
  });
  tinymce.init({
    selector: '#mail-template'
  });
});

$on('.custom-table h3.table-refresh', 'click', function() {
  const table = $(this).attr('table')
  changeTab(window.currentTabName)
});

$on('#all-users .custom-table-header-actions button[action="delete"]', 'click', function() {
  const tableId = getElementparent(this, 3).id;
  const inputs = $(getElementparent(this, 3)).find('table tbody tr input:checked');
  var usersIds = [];
  for (let i = 0; i < inputs.length; i++) {
    usersIds.push(inputs[i].id.replace(`${tableId}-chb-`, ''));
  }
  const btnHtml = $(this).html();
  $(this).html(`<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>Please wait...`);
  $(this).attr('disabled', true);
  messageDialog(
    'delete-user',
    'حذف الأفراد',
    'هل أنت متأكد من حذف هؤلاء الأفراد؟',
    (action) => {
      $('#message-dialog-modal').modal('hide');
    },
    {نعم: 'primary'},
    'إلغاء',
    () => {
      $(this).html(btnHtml);
      $(this).attr('disabled', false);
    }
  );
});
$on('#all-users table button[action="view"] ', 'click', function () {
  const rowId = getElementparent(this, 2).id.replace(`all-users-item-`, '');
  const user = StorageDatabase.collection('users').doc('users').doc(rowId).get();
  if(user) viewUser(user);
});
$on('#all-users table tr td button[action="delete"]', 'click', function () {
  const rowId = getElementparent(this, 2).id.replace(`all-users-item-`, '');
  const btnHtml = $(this).html();
  $(this).html(`<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>`);
  $(this).attr('disabled', true);
  messageDialog(
    'ask-delete-user',
    'Delete User',
    '<h4>Are you sure you wanti to delete user?</h4>',
    async (action) => {
      if(action == 'نعم') {
        var data = await $.ajax({
          url: `./admin/user/${rowId}/delete`,
          type: 'GET',
          headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
          dataType: 'JSON',
        });
        alertMessage('delete-user-response',
          `Delete User ${data['success'] ? 'Success' : 'Error'}`,
          data['message'] ?? 'Some things bad',
          data['success'] ? 'success': 'danger'
        )
        changeTab(window.currentTabName);
      }
      $('#message-dialog-modal').modal('hide');
      $(this).html(btnHtml);
      $(this).attr('disabled', false);
    },
    {نعم:'danger', Cancel: 'primary'},
    () => {
      $(this).html(btnHtml);
      $(this).attr('disabled', false);
    },
  );
});
// $on('#all-users-recharges table tr', 'click', function () {
//   const rowId = this.id.replace(`all-users-recharges-item-`, '');
//   const transfer = StorageDatabase.collection('users').doc('recharges').doc(rowId).get();
//   if(transfer) viewTransfer(transfer);
// });
$on('#all-users-recharges table tr td button[action="view"]', 'click', function () {
  const rowId = getElementparent(this, 2).id.replace(`all-users-recharges-item-`, '');
  const transfer = StorageDatabase.collection('users').doc('recharges').doc(rowId).get();
  if(transfer) viewTransfer(transfer);
});
$on('#all-users-withdrawes table tr td button[action="view"]', 'click', function () {
  const rowId = getElementparent(this, 2).id.replace(`all-users-withdrawes-item-`, '');
  const transfer = StorageDatabase.collection('users').doc('withdrawes').doc(rowId).get();
  if(transfer) viewTransfer(transfer);
});
$on(`#view-user .modal-body button[name="change-status"]`, 'click', function() {
  const id = $('#view-user .modal-title #view-user-id').html();
  const status = $('#view-user .modal-body .form-control[name="status"]').val();
  const description = $('#view-user .modal-body .form-control[name="status-description"]').val();
  $.ajax({
    url: `./admin/user/${id}/change_identity_status`,
    type: 'POST',
    headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
    data: {status: status, description: description},
    dataType: 'JSON',
    success: async function (data) {
      console.log(data)
      if(data.errors) {
        for (const field in data.errors) {
          data.errors[field].forEach(error => {
            alertMessage('change-user-status-message', 'Chane User Identity Status', error, 'danger');
          });
        }
      } else {
        alertMessage('change-user-status-message', 'Chane User Identity Status', data.message, data.success ? 'success' : 'danger');
      }
      $('#view-user').modal('hide');
      await loadTab();
    },
    catch: function (err) {console.log(err);},
    error: (error) => {
      console.log(error)
      console.log(error.responseText)
    }
  });
})

$on('#all-new-sellers table tr td button[action="view"]', 'click', function () {
  const rowId = getElementparent(this, 2).id.replace(`all-new-sellers-item-`, '');
  const seller = StorageDatabase.collection('sellers').doc('new_sellers').doc(rowId).get();
  if(seller) viewSeller(seller);
});
$on(`#view-seller .modal-body button[name="change-status"]`, 'click', function() {
  const id = $('#view-seller .modal-title #view-seller-id').html();
  const status = $('#view-seller .modal-body .form-control[name="status"]').val();
  const description = $('#view-seller .modal-body .form-control[name="status-description"]').val();
  $.ajax({
    url: `./admin/seller/${id}/change_status`,
    type: 'POST',
    headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
    data: {status: status, description: description},
    dataType: 'JSON',
    success: async function (data) {
      console.log(data)
      if(data.errors) {
        for (const field in data.errors) {
          data.errors[field].forEach(error => {
            alertMessage('change-seller-status-message', 'Chane Seller Status', error, 'danger');
          });
        }
      } else {
        alertMessage('change-seller-status-message', 'Chane Seller Status', data.message, data.success ? 'success' : 'danger');
      }
      $('#view-seller').modal('hide');
      await loadTab();
    },
    catch: function (err) {console.log(err);},
    error: (error) => {
      console.log(error)
      console.log(error.responseText)
    }
  });
})
$on('#view-seller .modal-body .form-control[name="delivery_states"]', 'change', function() {

  $('#view-seller .modal-body .form-control[name="delivery_state_price"]').html(`office: ${$(this).attr('office')} | home: ${$(this).attr('home')}`)
});

$on('#all-currencies .custom-table-header-actions button[action="create"]', 'click', function() {
  $('#create-edit-currency .btn-img-picker').html('<span class="material-symbols-sharp pick-icon">add_a_photo</span>');
  $('#create-edit-currency input').val('');
  clearMultiInputValues('#currency-prices');
  var button = $($('#create-edit-currency .modal-footer button[action="edit"]')[0] ?? $('#create-edit-currency .modal-footer button[action="create"]')[0])
  button.html('إنشاء');
  button.attr('action', 'create');
  $('#create-edit-currency').modal('show');
});
$on('#all-currencies table tr td button[action="edit"]', 'click', function () {
  const rowId = getElementparent(this, 2).id.replace(`all-currencies-item-`, '');
  const currency = StorageDatabase.collection('currencies').doc(rowId).get();

  $('#create-edit-currency .modal-title').html(`Edit Currnecy #${rowId}`);
  var button = $($('#create-edit-currency .modal-footer button[action="edit"]')[0] ?? $('#create-edit-currency .modal-footer button[action="create"]')[0])
  button.html('Edit');
  button.attr('action', 'edit');
  $('#create-edit-currency .btn-img-picker').html('<span class="material-symbols-sharp pick-icon">add_a_photo</span>');
  $('#create-edit-currency input').val('');
  clearMultiInputValues('#currency-prices');

  $('#create-edit-currency input[name="currency-id"]').val(currency.id);
  $('#create-edit-currency input[name="currency_name"]').val(currency.name);
  $('#create-edit-currency input[name="currency_char"]').val(currency.char);
  $('#create-edit-currency input[name="currency_balance"]').val(currency.platform_wallet.balance);
  $('#create-edit-currency input[name="currency_max_receive"]').val(currency.max_receive);
  $('#create-edit-currency input[name="currency_wallet"]').val(currency.wallet);
  $('#create-edit-currency .form-group .btn.btn-img-picker').html(`<img src="./file/public/currency-${currency.id}">`);
  for (const currencyId in currency.prices) {
    const price = currency.prices[currencyId];
    addMultiInputItem('#currency-prices', {
      currency_id: currencyId,
      buy_price: price.buy,
      sell_price: price.sell,
    });
  }
  $('#create-edit-currency input[name="proof_is_required"]')[0].checked = currency.proof_is_required;
  $('#create-edit-currency').modal('show');
});
$on('#all-currencies table tr td button[action="delete"]', 'click', function () {
  const rowId = getElementparent(this, 2).id.replace(`all-currencies-item-`, '');
  const btnHtml = $(this).html();
  $(this).html(`<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>`);
  $(this).attr('disabled', true);
  messageDialog(
    'ask-delete-currency',
    'Delete Currency',
    '<h4>Are you sure you want to delete Currency?</h4>',
    async (action) => {
      if(action == 'نعم') {
        var data = await $.ajax({
          url: `./admin/currency/${rowId}/delete`,
          type: 'GET',
          headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
          dataType: 'JSON',
        });
        alertMessage('delete-currency-response',
          `Delete Currency ${data['success'] ? 'Success' : 'Error'}`,
          data['message'] ?? 'Some things bad',
          data['success'] ? 'success': 'danger'
        )
        changeTab(window.currentTabName);
      }
      $('#message-dialog-modal').modal('hide');
      $(this).html(btnHtml);
      $(this).attr('disabled', false);
    },
    {نعم:'danger', Cancel: 'primary'},
    () => {
      $(this).html(btnHtml);
      $(this).attr('disabled', false);
    },
  );
});
$on('#all-currencies table td select[name="prices"]', 'change', function() {
  var td = getElementparent(this, 1);
  $(getElementChild(td, 'span')).html($(this).val())
});
$on('#create-edit-currency .btn[action="create"]', 'click', async function() {
  const values = {
    name: $('#create-edit-currency input[name="currency_name"]').val(),
    char: $('#create-edit-currency input[name="currency_char"]').val(),
    balance: $('#create-edit-currency input[name="currency_balance"]').val(),
    max_receive: $('#create-edit-currency input[name="currency_max_receive"]').val(),
    wallet: $('#create-edit-currency input[name="currency_wallet"]').val(),
    image: window.ImagePicker['currency-image-picker'],
    proof_is_required: $('#create-edit-currency input[name="proof_is_required"]')[0].checked,
    prices: getMultiInputValues('#currency-prices'),
  };
  const formData = new FormData();
  for (const key in values) {
    const value = values[key];
    if(value && key == 'prices') formData.append(key, JSON.stringify(value));
    else formData.append(key, value);
  }

  var data = await $.ajax({
    url: './admin/currency/create_currency',
    type: 'POST',
    headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
    contentType: false,
    processData: false,
    data: formData,
  });
  console.log(data);
  if(data['success']) {
    $('#create-edit-currency').modal('hide');
    window.ImagePicker['currency-image-picker'] = undefined;
    alertMessage('create-currency-message', 'Create Currency', data['message'], 'success');
    changeTab(window.currentTabName);
  } else {
    for (const key in data['errors']) {
      const error = data['errors'][key];
      alertMessage('create-currency-message', 'Create Currency', error, 'danger');
    }
  }
});
$on('#create-edit-currency .btn[action="edit"]', 'click', async function() {
  const values = {
    id: $('#create-edit-currency input[name="currency-id"]').val(),
    name: $('#create-edit-currency input[name="currency_name"]').val(),
    char: $('#create-edit-currency input[name="currency_char"]').val(),
    balance: $('#create-edit-currency input[name="currency_balance"]').val(),
    max_receive: $('#create-edit-currency input[name="currency_max_receive"]').val(),
    wallet: $('#create-edit-currency input[name="currency_wallet"]').val(),
    proof_is_required: $('#create-edit-currency input[name="proof_is_required"]')[0].checked,
    prices: getMultiInputValues('#currency-prices'),
  };
  if(window.ImagePicker['currency-image-picker']) values.image = window.ImagePicker['currency-image-picker']
  console.log(values);
  const formData = new FormData();
  for (const key in values) {
    const value = values[key];
    if(value && key == 'prices') formData.append(key, JSON.stringify(value));
    else formData.append(key, value);
  }

  var data = await $.ajax({
    url: `./admin/currency/${values.id}/edit`,
    type: 'POST',
    headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
    contentType: false,
    processData: false,
    data: formData,
  });
  console.log(data);
  if(data['success']) {
    $('#create-edit-currency').modal('hide');
    alertMessage('edit-currency-message', 'Edit Currency', data['message'], 'success');
    changeTab(window.currentTabName);
  } else {
    for (const key in data['errors']) {
      const error = data['errors'][key];
      alertMessage('edit-currency-message', 'Edit Currency', error, 'danger');
    }
  }
});

$on('#all-transfers table tr td button[action="view"]', 'click', function () {
  const rowId = getElementparent(this, 2).id.replace(`all-transfers-item-`, '');
  const transfer = StorageDatabase.collection('transfers').doc(rowId).get();
  if(transfer) viewTransfer(transfer);
});
$on('#all-transfers table tr td button[action="delete"]', 'click', function () {
  const rowId = getElementparent(this, 2).id.replace(`all-transfers-item-`, '');
  console.log(rowId);
  const btnHtml = $(this).html();
  $(this).html(`<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>`);
  $(this).attr('disabled', true);
  messageDialog(
    'ask-delete-transfer',
    'حذف التحويل',
    '<h4>هل أنت متأكد من أنك تريد حذف هذا التحويل?</h4>',
    async (action) => {
      if(action == 'نعم') {
        var data = await $.ajax({
          url: `./admin/transfer/${rowId}/delete`,
          type: 'GET',
          headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
          dataType: 'JSON',
        });
        console.log(data);
        alertMessage('delete-transfer-response',
          'حذف التحويل',
          data['message'] ?? 'يوجد خطأ',
          data['success'] ? 'success': 'danger'
        )
        changeTab(window.currentTabName);
      }
      $('#message-dialog-modal').modal('hide');
      $(this).html(btnHtml);
      $(this).attr('disabled', false);
    },
    {نعم: 'danger', إلغاء: 'primary'},
    () => {
      $(this).html(btnHtml);
      $(this).attr('disabled', false);
    },
  );
});
$on(`#view-transfer .modal-body button[name="change-status"]`, 'click', function() {
  const id = $('#view-transfer .modal-title #view-transfer-id').html();
  const status = $('#view-transfer .modal-body .form-control[name="status"]').val();
  const description = $('#view-transfer .modal-body .form-control[name="status-description"]').val();
  $.ajax({
    url: `./admin/transfer/${id}/change_status`,
    type: 'POST',
    headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
    data: {status: status, description: description},
    dataType: 'JSON',
    success: async function (data) {
      console.log(data)
      alertMessage('change-transfer-status-message', 'Create Currency', data['message'], data.success ? 'success' : 'danger');
      $('#view-transfer').modal('hide');
      await loadTab();
    },
    catch: function (err) {console.log(err);},
    error: async (error) => {
      console.log(error.responseText)
    }
  });
});

$on('#all-categories .custom-table-header-actions button[action="create"]', 'click', function() {
  $('#create-edit-category .btn-img-picker').html('<span class="material-symbols-sharp pick-icon">add_a_photo</span>');
  $('#create-edit-category input').val('');
  clearMultiInputValues('#category-names');
  var button = $($('#create-edit-category .modal-footer button[action="edit"]')[0] ?? $('#create-edit-category .modal-footer button[action="create"]')[0])
  button.html('إنشاء');
  button.attr('action', 'create');
  $('#create-edit-category').modal('show');
});
$on('#all-categories table td select[name="names"]', 'change', function() {
  var td = getElementparent(this, 1);
  $(getElementChild(td, 'span')).html($(this).val())
});
$on('#all-categories table tr td button[action="edit"]', 'click', function () {
  const rowId = getElementparent(this, 2).id.replace(`all-categories-item-`, '');
  var category = StorageDatabase.collection('products').doc('categories').doc(rowId).get();

  $('#create-edit-category .modal-title').html(`تعجيل النوع #${rowId}`);
  var button = $($('#create-edit-category .modal-footer button[action="edit"]')[0] ?? $('#create-edit-category .modal-footer button[action="create"]')[0])
  button.html('تعديل');
  button.attr('action', 'edit');
  $('#create-edit-category .btn-img-picker').html('<span class="material-symbols-sharp pick-icon">add_a_photo</span>');
  $('#create-edit-category input').val('');
  $('#create-edit-category .form-group .btn.btn-img-picker').html(`<img src="./file/public/${category.image_id}">`);
  clearMultiInputValues('#category-names');
  for (const langCode in category.name) {
    const name = category.name[langCode];
    addMultiInputItem('#category-names', {
      lang_code: langCode,
      text: name,
    });
  }
  $('#create-edit-category').modal('show');
});
$on('#all-categories table tr td button[action="delete"]', 'click', function () {
  const rowId = getElementparent(this, 2).id.replace(`all-categories-item-`, '');
  console.log(rowId);
  const btnHtml = $(this).html();
  $(this).html(`<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>`);
  $(this).attr('disabled', true);
  messageDialog(
    'ask-delete-category',
    'حذف النوع',
    '<h4>هل أنت متأكد من أنك تريد حذف هذا النوع?</h4>',
    async (action) => {
      if(action == 'نعم') {
        var data = await $.ajax({
          url: `./admin/category/${rowId}/delete`,
          type: 'GET',
          headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
          dataType: 'JSON',
        });
        console.log(data);
        alertMessage('delete-category-response',
          'حذف النوع',
          data['message'] ?? 'يوجد خطأ',
          data['success'] ? 'success': 'danger'
        )
        changeTab(window.currentTabName);
      }
      $('#message-dialog-modal').modal('hide');
      $(this).html(btnHtml);
      $(this).attr('disabled', false);
    },
    {نعم: 'danger', إلغاء: 'primary'},
    () => {
      $(this).html(btnHtml);
      $(this).attr('disabled', false);
    },
  );
});
$on('#create-edit-category .btn[action="create"]', 'click', async function() {
  const values = {
    image: window.ImagePicker['category-image-picker'],
    names: getMultiInputValues('#category-names'),
  };
  console.log(values);
  const formData = new FormData();
  for (const key in values) {
    const value = values[key];
    if(value && key == 'names') formData.append(key, JSON.stringify(value));
    else formData.append(key, value);
  }


  var data = await $.ajax({
    url: './admin/category/create_category',
    type: 'POST',
    headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
    contentType: false,
    processData: false,
    data: formData,
  });
  console.log(data);
  if(data['success']) {
    $('#create-edit-category').modal('hide');
    window.ImagePicker['category-image-picker'] = undefined;
    alertMessage('create-category-message', 'Create Currency', data['message'], 'success');
    changeTab(window.currentTabName);
  } else {
    for (const key in data['errors']) {
      const error = data['errors'][key];
      alertMessage('create-category-message', 'Create Currency', error, 'danger');
    }
  }
});
$on('#create-edit-category .btn[action="edit"]', 'click', async function() {
  const id = $('#create-edit-category .modal-title').html().split('#')[1];
  const values = {
    image: window.ImagePicker['category-image-picker'],
    names: getMultiInputValues('#category-names'),
  };
  console.log(values);
  const formData = new FormData();
  for (const key in values) {
    const value = values[key];
    if(value && key == 'names') formData.append(key, JSON.stringify(value));
    else formData.append(key, value);
  }

  var data = await $.ajax({
    url: `./admin/category/${id}/edit`,
    type: 'POST',
    headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
    contentType: false,
    processData: false,
    data: formData,
  });
  console.log(data);
  if(data['success']) {
    $('#create-edit-category').modal('hide');
    window.ImagePicker['category-image-picker'] = undefined;
    alertMessage('create-category-message', 'Create Currency', data['message'], 'success');
    changeTab(window.currentTabName);
  } else {
    for (const key in data['errors']) {
      const error = data['errors'][key];
      alertMessage('create-category-message', 'Create Currency', error, 'danger');
    }
  }
});

$on('#all-mails-templates .custom-table-header-actions button[action="create"]', 'click', function() {
  $('#create-edit-mail input').val('');
  var button = $($('#create-edit-mail-template .modal-footer button[action="edit"]')[0] ??
               $('#create-edit-mail-template .modal-footer button[action="create"]')[0])
  button.html('إنشاء');
  button.attr('action', 'create');
  $('#create-edit-mail-template').modal('show');
});
