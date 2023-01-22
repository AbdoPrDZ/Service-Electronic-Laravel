import { io } from '../socket.io.esm.min.js';

// window.permissions = JSON.parse(CryptoJS.AES.decrypt(permissions, document.cookie.replace('adminAuth=', '')).toString(CryptoJS.enc.Utf8));

const socket = io.connect(':8000', {
  path: '/admin/',
  autoConnect: false,
});

const statuses = {
  accepted: '<span class="success">تم الموفقة</span>',
  refused: '<span class="danger">تم الرفض</span>',
  checking: '<span class="warning">قيد التحقق</span>',
  not_verifited: '<span class="danger">غير محققة</span>',
  verifited: '<span class="success">تم التحقق</span>',
  waiting: '<span class="warning">في إنتظار موافقة البائع</span>',
  seller_accept: '<span class="success">تم الموافقة من طرف البائع</span>',
  seller_refuse: '<span class="danger">تم الرفض من طرف البائع</span>',
  waiting_client_accept: '<span class="warning">في إنتظار موافقة العميل</span>',
  client_accept: '<span class="success">تم الموافقة من طرف العميل</span>',
  client_refuse: '<span class="danger">تم الرفض من طرف العميل</span>',
  received: '<span class="success">تم الاستلام</span>',
  admin_ansowred: '<span class="success">تم الرد من طرف المدير</span>',
  waiting_admin_accept: '<span class="warning">في إنتظار موافقة المدير</span>',
  admin_accept: '<span class="success">تمت الموافقة من طرف المدير</span>',
  admin_refuse: '<span class="success">تم الرفض من طرف المدير</span>'
};

const tabsTables = {
  users: [['all-users', 'users'], ['all-users-recharges', 'recharges'], ['all-users-withdrawes', 'withdrawes']],
  sellers: [['all-sellers', 'sellers'], ['all-new-sellers', 'new_sellers']],
  transfers: [['all-transfers', '*']],
  currencies: [['all-currencies', '*']],
  products: [['all-products', 'products'], ['all-categories', 'categories']],
  purchases: [['all-purchases', '*']],
  offers: [['all-offers', 'offers'], ['all-offer-requests', 'offer_requests']],
  mails: [['all-mails', 'mails'], ['all-templates', 'templates']],
}

const notificationsNames = {
  'new-withdraw-created': 'users',
  'new-recharge-created': 'users',
  'new-user-created': 'users',
  'new-seller-created': 'sellers',
  'new-transfer-created': 'transfers',
  'new-currency-created': 'currencies',
  'new-category-created': 'products',
  'new-product-created': 'products',
  'new-product-solded': 'purchases',
  'new-offer-created': 'offers',
  'new-offer-request-created': 'offers',
  'new-template-created': 'mails',
  'new-mail-created': 'mails',
};

window.news = {
  'users': 0,
  'sellers': 0,
  'transfers': 0,
  'currencies': 0,
  'products': 0,
  'purchases': 0,
  'offers': 0,
  'mails': 0,
};

async function loadData(tabName) {
  var response = await $.get(`./admin/load/${tabName}`);
  if(response.data) {
    StorageDatabase.collection(tabName).set(response.data);
    return response.data;
  } else {
    console.log(response);
  }
}

async function readNews(tabName) {
  if(window.news[tabName] > 0) {
    socket.emit('read-news', tabName);
    socket.emit('news');
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
  $.get(`./admin/notifications/${id}/read`).then((readRes) => {
    if(!readRes.success) console.log(readRes);
    loadNotifications();
  });
  if(notification) {
    const tab = notificationsNames[notification.name];
    if(window.currentTabName != tab) changeTab(tab);
  }
}

async function loadTab(tabName = null) {
  tabName = tabName ?? window.currentTabName;
  const data = await loadData(tabName);
  if(data) {
    tabsTables[tabName].forEach(table => {
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
            <button class="btn btn-icon btn-success" action="send_notification">
              <span class="material-symbols-sharp">chat</span>
            </button>
            <button class="btn btn-icon btn-secondary" action="view">
              <span class="material-symbols-sharp">open_in_new</span>
            </button>
            <a class="btn btn-icon btn-secondary" action="details" href="./admin/user/${id}/details" target="_blank">
              <span class="material-symbols-sharp">history</span>
            </a>
            <button class="btn btn-icon btn-danger" action="delete">
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
          <button class="btn btn-icon btn-danger" action="delete">
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
        row.push(`<td>${values[id].sended_balance} ${values[id].sended_currency.char}</td>`);
        row.push(`<td>${values[id].received_balance} ${values[id].received_currency.char}</td>`);
        row.push(`<td>${values[id].created_at}</td>`);
        row.push(`<td>
          <button class="btn btn-icon btn-secondary" action="view">
            <span class="material-symbols-sharp">open_in_new</span>
          </button>
          <button class="btn btn-icon btn-danger" action="delete">
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
            <button class="btn btn-icon btn-danger" action="delete">
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
          <button class="btn btn-icon btn-danger" action="delete">
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
          <button class="btn btn-icon btn-warning" action="edit">
            <span class="material-symbols-sharp">edit</span>
          </button>
          <button class="btn btn-icon btn-danger" action="delete">
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
          <button class="btn btn-icon btn-warning" action="edit">
            <span class="material-symbols-sharp">edit</span>
          </button>
          <button class="btn btn-icon btn-danger" action="delete">
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
            <button class="btn btn-icon btn-danger" action="delete">
              <span class="material-symbols-sharp">delete</span>
            </button>
          </td>`);
      } else if(tableId == 'all-purchases') {
        row.push(`<td>${values[id].id}</td>`);
        row.push(`<td>${values[id].fullname} <br> ${values[id].phone}</td>`);
        row.push(`<td>${values[id].product.seller.user.fullname} <br> ${values[id].product.seller.user.phone}</td>`);
        row.push(`<td>${values[id].product.name}</td>`);
        row.push(`<td>${values[id].count}</td>`);
        row.push(`<td>${values[id].total_price} SE</td>`);
        row.push(`<td>${values[id].address}</td>`);
        row.push(`<td>${values[id].created_at}</td>`);
        row.push(`
        <td>
          <button class="btn btn-icon btn-secondary" action="view">
            <span class="material-symbols-sharp">open_in_new</span>
          </button>
        </td>`);
      } else if(tableId == 'all-offers') {
        row.push(`<td>${values[id].id}</td>`);
        row.push(`<td><div class="table-img"><img src="./file/admin/offer-${values[id].id}?t=${(new Date()).getTime()}"></div></td>`);
        row.push(`<td>En: "${values[id].title.en}"<br>Ar: "${values[id].title.ar}"</td>`);
        row.push(`<td>En: "${values[id].description.en}"<br>Ar: "${values[id].description.ar}"</td>`);
        var subOffers = [];
        for (const name in values[id].sub_offers) {
          const subOffer = values[id].sub_offers[name];
          subOffers.push(`${subOffer.name}: ${subOffer.price} SE`)
        }
        row.push(`<td>${subOffers.join('<br>')}</td>`);
        row.push(`<td>${Object.keys(values[id].fields).join('<br>')}</td>`);
        row.push(`<td>${Object.keys(values[id].data).join('<br>')}</td>`);
        row.push(`<td>${values[id].created_at}</td>`);
        row.push(`
          <td>
          <button class="btn btn-icon btn-warning" action="edit">
            <span class="material-symbols-sharp">edit</span>
          </button>
          <button class="btn btn-icon btn-danger" action="delete">
            <span class="material-symbols-sharp">delete</span>
          </button>
          </td>`);
      } else if(tableId == 'all-offer-requests') {
        row.push(`<td>${values[id].id}</td>`);
        row.push(`<td><div class="table-img"><img src="./file/admin/offer-${values[id].offer_id}?t=${(new Date()).getTime()}"></div></td>`);
        row.push(`<td>${values[id].offer.title.en}</td>`);
        var fieldsData = [];
        for (const name in values[id].fields) {
          fieldsData.push(`<span class="bold">${values[id].offer.fields[name].title_en}</span>: ${values[id].fields[name]}`)
        }
        row.push(`<td>${fieldsData.join('<br>')}</td>`);
        row.push(`<td>${values[id].sub_offer}</td>`);
        row.push(`<td>${values[id].total_price} SE</td>`);
        row.push(`<td>${statuses[values[id].status]}</td>`);
        row.push(`<td>${values[id].created_at}</td>`);
        row.push(`
        <td>
          <button class="btn btn-icon btn-secondary" action="view">
            <span class="material-symbols-sharp">open_in_new</span>
          </button>
          <button class="btn btn-icon btn-danger" action="delete">
            <span class="material-symbols-sharp">delete</span>
          </button>
        </td>`);
      } else if(tableId == 'all-templates') {
        row.push(`<td>${values[id].name}</td>`);
        var args = [];
        values[id].args.forEach(arg => {
          args.push(arg.name);
        });
        row.push(`<td>${args.join('<br>')}</td>`);
        row.push(`<td>${values[id].created_at}</td>`);
        row.push(`<td>
          <button class="btn btn-icon btn-warning" action="edit">
            <span class="material-symbols-sharp">edit</span>
          </button>
          <button class="btn btn-icon btn-danger" action="delete">
            <span class="material-symbols-sharp">delete</span>
          </button>
        </td>`);
      } else if(tableId == 'all-mails') {
        row.push(`<td>${values[id].id}</td>`);
        row.push(`<td>${values[id].template.name}</td>`);
        row.push(`<td>${values[id].title}</td>`);
        var data = [];
        Object.keys(values[id].data).forEach(key => {
          data.push(`${key}: ${values[id].data[key]}`);
        });
        row.push(`<td>${data.join('<br>')}</td>`);
        row.push(`<td>${values[id].targets.join('<br>')}</td>`);
        row.push(`<td>${values[id].created_at}</td>`);
        row.push(`<td>
          <button class="btn btn-icon btn-secondary" action="view">
            <span class="material-symbols-sharp">open_in_new</span>
          </button>
          <button class="btn btn-icon btn-danger" action="delete">
            <span class="material-symbols-sharp">delete</span>
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

function viewPurchase(purchase) {
  $('#view-purchase .modal-title #view-purchase-id').html(purchase.id);
  $(`#view-purchase .modal-body .form-control[name="client_details"]`).html(`
    <span>
      Fullname:
      <span style="font-weight: normal">
        ${purchase.fullname}
      </span>
    </span>
    <span>
      Phone:
      <span style="font-weight: normal">
        ${purchase.phone}
      </span>
    </span>
    <span>
      Address:
      <span style="font-weight: normal">
        ${purchase.address}
      </span>
    </span>
  `);
  $(`#view-purchase .modal-body .form-control[name="seller_details"]`).html(`
    <span>
      Fullname:
      <span style="font-weight: normal">
        ${purchase.product.seller.user.fullname}
      </span>
    </span>
    <span>
      Store name:
      <span style="font-weight: normal">
        ${purchase.product.seller.store_name}
      </span>
    </span>
    <span>
      Strore address:
      <span style="font-weight: normal">
        ${purchase.product.seller.store_address}
      </span>
    </span>
    <span>
      Phone:
       <span style="font-weight: normal">
        ${purchase.product.seller.user.phone}
      </span>
    </span>
  `);
  $(`#view-purchase .modal-body .form-control[name="product_details"]`).html(`
    <div class="product-image">
      <img src="./file/admin/${purchase.product.images_ids[0]}">
    </div>
    <div class="details">
      <span>
        Name: <span style="font-weight: normal"> ${purchase.product.name}</span>
      </span>
      <span>
        Category: <span style="font-weight: normal"> ${purchase.product.category.name.en}</span>
      </span>
      <span>
        Price: <span style="font-weight: normal"> ${purchase.product.price} SE</span>
      </span>
    </div>
  `);
  if(purchase.status == 'seller_reported' || purchase.status == 'client_refuse') {
    $(`#view-purchase .modal-body .form-group[name="problem_report"]`).css('display', 'block');
    $(`#view-purchase .modal-body
      .form-group[name="problem_report"]
      .form-control[name="${purchase.status == 'client_refuse' ? 'client_report' : 'seller_report'}"]`
      ).html(purchase.delivery_steps.receive[1]);
  } else {
    $(`#view-purchase .modal-body .form-group[name="problem_report"]`).css('display', 'none');
  }
  $(`#view-purchase .modal-body .form-control[name="status"]`).html(statuses[purchase.status]);
  $(`#view-purchase .modal-body .form-control[name="created_at"]`).html(purchase.created_at);

  $('#view-purchase').modal('show');
}

function viewOfferRequest(offerRequest) {
  // offer request id
  $('#view-offer-request .modal-title #view-offer-request-id').html(offerRequest.id);
  // offer details
  var subOffers = [];
  for (const name in offerRequest.offer.sub_offers) {
    const subOffer = offerRequest.offer.sub_offers[name];
    subOffers.push(`
    <span>
      ${subOffer.title_en}: <span style="font-weight: normal"> ${subOffer.price}</span>
    </span>
    `)
  }
  $(`#view-offer-request .modal-body .form-control[name="offer_details"]`).html(`
    <div class="offer-image">
      <img src="./file/admin/offer-${offerRequest.offer_id}">
    </div>
    <div class="details">
      <span>
        Name: <span> ${offerRequest.offer.title.en}</span>
      </span>
      <h6 class="bold" style="margin-top: 5px;">Sub Offers: </h6>
      <div class="details" style="margin-left: 10px;">
        ${subOffers.join('')}
      </div>
    </div>
  `);
  // sub offer
  $(`#view-offer-request .modal-body .form-control[name="sub_offer"]`).html(offerRequest.sub_offer);
  // fields data
  var fieldsData = [];
  for (const name in offerRequest.fields) {
    const field = offerRequest.fields[name];
    fieldsData.push(`<span>
      ${offerRequest.offer.fields[name].title_en}: <span>${field}</span>
    </span>`);
  }
  $(`#view-offer-request .modal-body .form-control[name="offer_fields"]`).html(fieldsData.join(''));
  if(offerRequest.status == 'waiting_admin_accept') {
    $('#view-offer-request .modal-body .form-group[name="answer"]').css('display', 'none');
    // data fields
    $('#view-offer-request .modal-body .form-group[name="answer_form"]').css('display', 'block');
    var fields = [];
    for (const name in offerRequest.offer.data) {
      const item = offerRequest.offer.data[name];
      fields.push(
        item.type == 'textarea' ?
        `<textarea class="form-control" name="${name}" placeholder="${item.title_en}"></textarea>`
        :`
        <input type="${item.type}" class="form-control" name="${name}" placeholder="${item.title_en}">
      `);
    }
    $(`#view-offer-request .modal-body .form-group[name="answer_form"] .data_fields`).html(fields.join(''));
  } else {
    $('#view-offer-request .modal-body .form-group[name="answer_form"]').css('display', 'none');
  }
  $(`#view-offer-request .modal-body .form-control[name="status"]`).html(statuses[offerRequest.status]);
  if(offerRequest.status == 'admin_accept') {
    // answer
    $('#view-offer-request .modal-body .form-group[name="answer"]').css('display', 'block');
    var answerData = [
    ];
    for (const name in offerRequest.data) {
      answerData.push(`<span>
        ${offerRequest.offer.data[name].title_en}: <span>${offerRequest.data[name]}</span>
      </span>`);
    }
    $('#view-offer-request .modal-body .form-group[name="answer"] .form-control[name="answer_data"]').html(answerData.join(''));
  } else {
    // answer
    $('#view-offer-request .modal-body .form-group[name="answer"]').css('display', 'bliock');
    $('#view-offer-request .modal-body .form-group[name="answer"] .form-control[name="answer_data"]').html(`
      <span>
        سبب الرفض: <span>${offerRequest.exchange.answer_description}</span>
      </span>
    `);
  }
  $(`#view-offer-request .modal-body .form-control[name="created_at"]`).html(offerRequest.created_at);
  $('#view-offer-request').modal('show');
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

function countingNews(tabName) {
  if(window.news[tabName] > 0) {
    $(`#${tabName} .news-badge`).attr('class',
      $(`#${tabName} .news-badge`).attr('class')
      .replace(' hide ', ' show ')
      .replace(' hide', ' show')
      .replace('hide ', 'show ')
    )
  } else {
    $(`#${tabName} .news-badge`).attr('class',
      $(`#${tabName} .news-badge`).attr('class')
      .replace(' show ', ' hide ')
      .replace(' show', ' hide')
      .replace('show ', 'hide ')
    )
  }
}

async function onConnect() {
  socket.emit('news');
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
    `<li class="notification-item" onClick="${onClick}">
      <div class="image"><img src="${image}"></div>
      <div class="content">
        <h3 class="title">${title}</h3>
        <p class="message">${message}</p>
      </div>
      <div class="date">${date}</div>
    </li>` +
    notificationsElement.html()
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
    window.location.href = "./admin/logout";
    window.connected = false;
  });
  socket.on('connect_failed', (err) => {
    console.log(`Connect failed: ${err}`);
    socket.disconnect();
  });
  socket.on('connect', (_) => {
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

  socket.on('news-updated', (args) => {
    window.news = args;
    for (const tabName in window.news) {
      try {
        countingNews(tabName);
      } catch (error) {
        console.log(tabName, error)
      }
    }
  });

  socket.on('news-readed', (args) => {
    const tabName = args['tabName'];
    window.news[tabName] = 0;
    countingNews(tabName);
  });

  socket.on('emails-deleted', (_) => {
    if(window.currentTabName == 'mails') loadTab('mails');
    else loadData('mails');
  });

  socket.on('notifications', (notification) => {
    console.log(notification);
    loadNotifications();
    socket.emit('news');
    alertMessage("new-notification", notification.title, notification.message, 'success', 60000);
  });

  $on('#person-dropdown', 'onSelect[value="account"]', function(event, item) {
    console.log('goto account')
  });
});

$on('.custom-table h3.table-refresh', 'click', function() {
  const table = $(this).attr('table')
  changeTab(window.currentTabName)
});

$on('#all-users .custom-table-header-actions button[action="delete"]', 'click', function() {
  const inputs = $('#all-users').find('table tbody tr input:checked');
  var usersIds = [];
  for (let i = 0; i < inputs.length; i++) {
    usersIds.push(inputs[i].id.replace(`all-users-chb-`, ''));
  }
  const btnHtml = $(this).html();
  $(this).html(`<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>يرجى الإنتظار...`);
  $(this).attr('disabled', true);
  messageDialog(
    'delete-user',
    'حذف الأفراد',
    'هل أنت متأكد من حذف هؤلاء الأفراد؟',
    (action) => {
      $('#message-dialog-modal').modal('hide');
    },
    {نعم: 'primary', إلغاء: 'danger'},
    () => {
      $(this).html(btnHtml);
      $(this).attr('disabled', false);
    }
  );
});
$on('#all-users table tr td button[action="view"] ', 'click', function () {
  const rowId = getElementparent(this, 2).id.replace(`all-users-item-`, '');
  const user = StorageDatabase.collection('users').doc('users').doc(rowId).get();
  if(user) viewUser(user);
});
$on('#all-users table tr td button[action="send_notification"] ', 'click', function () {
  const rowId = getElementparent(this, 2).id.replace(`all-users-item-`, '');
  const user = StorageDatabase.collection('users').doc('users').doc(rowId).get();
  if(!user) return;
  $('#send-notification-modal .modal-title').html(`إرسال الرسالة للمستخدم #${rowId}`)
  $('#send-notification-modal').modal('show');
});
$on('#send-notification-modal .btn[action="send"]', 'click', async function() {
  loadingDialog('create-template', 'إرسال الرسالة', 'يرجى الإنتظار لحين إرسال الرسالة...');
  const userId = $('#send-notification-modal .modal-title').html().split('#')[1];
  const formData = new FormData();
  formData.append('message', $('#send-notification-modal textarea[name="message"]').val());
  formData.append('image', window.ImagePicker['send-notification-image-picker']);
  const data = await $.ajax({
    url: `./admin/user/${userId}/send_notification`,
    type: 'POST',
    headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
    contentType: false,
    processData: false,
    data: formData,
  });
  console.log(data);
  if(data.success) {
    $('#send-notification-modal').modal('hide');
    window.ImagePicker['send-notification-image-picker'] = undefined;
    alertMessage('send-notification', 'إرسال رسالة', data.message, 'success');
    changeTab(window.currentTabName);
  } else {
    for (const key in data.errors) {
      const error = data.errors[key];
      alertMessage('send-notification', 'إرسال رسالة', error, 'danger');
    }
  }
  $('#loading-dialog-modal').modal('hide');
});
$on('#all-users table tr td button[action="delete"]', 'click', function () {
  const rowId = getElementparent(this, 2).id.replace(`all-users-item-`, '');
  const btnHtml = $(this).html();
  $(this).html(`<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>`);
  $(this).attr('disabled', true);
  messageDialog(
    'ask-delete-user',
    'حذف المستخدم',
    '<h4>هل أنت متأكد من أنك تريد حذف هذا المستخدم?</h4>',
    async (action) => {
      if(action == 'نعم') {
        loadingDialog('create-template', 'حذف المستخدم', 'يرجى الإنتظار لحين حذف المستخدم...');
        const data = await $.ajax({
          url: `./admin/user/${rowId}/delete`,
          type: 'DELETE',
          headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
          dataType: 'JSON',
        });
        $('#message-dialog-modal').modal('hide');
        alertMessage('delete-user-response',
          `حذف المستخدم`,
          data.message ?? 'Some things bad',
          data.success ? 'success': 'danger'
        )
        changeTab(window.currentTabName);
        $('#loading-dialog-modal').modal('hide');
      } else {
        $('#message-dialog-modal').modal('hide');
      }
    },
    {نعم:'danger', لا: 'primary'},
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
$on(`#view-user .modal-body button[name="change-status"]`, 'click', async function() {
  loadingDialog('create-template', 'تغيير حالة المستخدم', 'يرجى الإنتظار لحين تغيير حالة المستخدم ...');
  const id = $('#view-user .modal-title #view-user-id').html();
  const status = $('#view-user .modal-body .form-control[name="status"]').val();
  const description = $('#view-user .modal-body .form-control[name="status-description"]').val();
  const data = await $.ajax({
    url: `./admin/user/${id}/change_identity_status`,
    type: 'POST',
    headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
    data: {status: status, description: description},
    dataType: 'JSON',
  });
  console.log(data)
  if(data.errors) {
    for (const field in data.errors) {
      data.errors[field].forEach(error => {
        alertMessage('change-user-status-message', 'تغيير حالة الهوية للمستخدم', error, 'danger');
      });
    }
  } else {
    alertMessage('change-user-status-message', 'تغيير حالة الهوية للمستخدم');
  }
  $('#view-user').modal('hide');
  await loadTab();
  $('#loading-dialog-modal').modal('hide');
});

$on('#all-new-sellers table tr td button[action="view"]', 'click', function () {
  const rowId = getElementparent(this, 2).id.replace(`all-new-sellers-item-`, '');
  const seller = StorageDatabase.collection('sellers').doc('new_sellers').doc(rowId).get();
  if(seller) viewSeller(seller);
});
$on(`#view-seller .modal-body button[name="change-status"]`, 'click', async function() {
  loadingDialog('create-template', 'تغيير حالة البائع', 'يرجى الإنتظار لحين تغيير حالة البائع ...');
  const id = $('#view-seller .modal-title #view-seller-id').html();
  const data = await $.ajax({
    url: `./admin/seller/${id}/change_status`,
    type: 'POST',
    headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
    data: {
      status: $('#view-seller .modal-body .form-control[name="status"]').val(),
      description: $('#view-seller .modal-body .form-control[name="status-description"]').val(),
    },
    dataType: 'JSON',
  });
  console.log(data)
  if(data.errors) {
    for (const field in data.errors) {
      data.errors[field].forEach(error => {
        alertMessage('change-seller-status-message', 'تغيير حالة البائع', error, 'danger');
      });
    }
  } else {
    alertMessage('change-seller-status-message', 'تغيير حالة البائع', data.message, data.success ? 'success' : 'danger');
  }
  $('#view-seller').modal('hide');
  await loadTab();
  $('#loading-dialog-modal').modal('hide');
});
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
    'حذف العملة',
    '<h4>هل أنت تأكد من أنك تريد حذف هذه العملة?</h4>',
    async (action) => {
      if(action == 'نعم') {
        loadingDialog('delete-currency', 'حذف العملة', 'يرجى الإنتظار لحين حذف العملة...');
        const data = await $.ajax({
          url: `./admin/currency/${rowId}/delete`,
          type: 'DELETE',
          headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
          dataType: 'JSON',
        });
        alertMessage('delete-currency-response',
          `حذف العملة`,
          data.message ?? 'يوجد خطأ',
          data.success ? 'success': 'danger'
        )
        changeTab(window.currentTabName);
        $('#loading-dialog-modal').modal('hide');
      } else {
        $('#message-dialog-modal').modal('hide');
      }
    },
    {نعم:'danger', لا: 'primary'},
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
  loadingDialog('create-template', 'إنشاء العملة', 'يرجى الإنتظار لحين إنشاء العملة...');
  const formData = new FormData();
  formData.append('name', $('#create-edit-currency input[name="currency_name"]').val());
  formData.append('char', $('#create-edit-currency input[name="currency_char"]').val());
  formData.append('balance', $('#create-edit-currency input[name="currency_balance"]').val());
  formData.append('wallet', $('#create-edit-currency input[name="currency_wallet"]').val());
  formData.append('image', window.ImagePicker['currency-image-picker']);
  formData.append('proof_is_required', $('#create-edit-currency input[name="proof_is_required"]')[0].checked);
  formData.append('prices', JSON.stringify(getMultiInputValues('#currency-prices')));
  const data = await $.ajax({
    url: './admin/currency/create_currency',
    type: 'POST',
    headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
    contentType: false,
    processData: false,
    data: formData,
  });
  console.log(data);
  if(data.success) {
    $('#create-edit-currency').modal('hide');
    window.ImagePicker['currency-image-picker'] = undefined;
    alertMessage('create-currency-message', 'إنشاء عملة', data.message, 'success');
    changeTab(window.currentTabName);
  } else {
    for (const key in data.errors) {
      const error = data.errors[key];
      alertMessage('create-currency-message', 'إنشاء عملة', error, 'danger');
    }
  }
  $('#loading-dialog-modal').modal('hide');
});
$on('#create-edit-currency .btn[action="edit"]', 'click', async function() {
  loadingDialog('create-template', 'تعديل العملة', 'يرجى الإنتظار لحين تعديل العملة...');
  const id = $('#create-edit-currency .modal-title').html().split('#')[1];
  const formData = new FormData();
  formData.append('name', $('#create-edit-currency input[name="currency_name"]').val());
  formData.append('char', $('#create-edit-currency input[name="currency_char"]').val());
  formData.append('balance', $('#create-edit-currency input[name="currency_balance"]').val());
  formData.append('wallet', $('#create-edit-currency input[name="currency_wallet"]').val());
  if(window.ImagePicker['currency-image-picker']) formData.append('image', window.ImagePicker['currency-image-picker']);
  formData.append('proof_is_required', $('#create-edit-currency input[name="proof_is_required"]')[0].checked);
  formData.append('prices', JSON.stringify(getMultiInputValues('#currency-prices')));
  const data = await $.ajax({
    url: `./admin/currency/${id}/edit`,
    type: 'POST',
    headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
    contentType: false,
    processData: false,
    data: formData,
  });
  console.log(data);
  if(data.success) {
    $('#create-edit-currency').modal('hide');
    alertMessage('edit-currency-message', 'تعديل العملة', data.message, 'success');
    changeTab(window.currentTabName);
  } else {
    for (const key in data.errors) {
      const error = data.errors[key];
      alertMessage('edit-currency-message', 'تعديل العملة', error, 'danger');
    }
  }
  $('#loading-dialog-modal').modal('hide');
});

$on('#all-transfers table tr td button[action="view"]', 'click', function () {
  const rowId = getElementparent(this, 2).id.replace(`all-transfers-item-`, '');
  const transfer = StorageDatabase.collection('transfers').doc(rowId).get();
  if(transfer) viewTransfer(transfer);
});
$on('#all-transfers table tr td button[action="delete"]', 'click', function () {
  const rowId = getElementparent(this, 2).id.replace(`all-transfers-item-`, '');
  const btnHtml = $(this).html();
  $(this).html(`<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>`);
  $(this).attr('disabled', true);
  messageDialog(
    'ask-delete-transfer',
    'حذف التحويل',
    '<h4>هل أنت متأكد من أنك تريد حذف هذا التحويل?</h4>',
    async (action) => {
      if(action == 'نعم') {
        loadingDialog('create-template', 'حذف التحويل', 'يرجى الإنتظار لحين حذف التحويل...');
        const data = await $.ajax({
          url: `./admin/transfer/${rowId}/delete`,
          type: 'DELETE',
          headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
          dataType: 'JSON',
        });
        console.log(data);
        $('#message-dialog-modal').modal('hide');
        alertMessage('delete-transfer-response',
          'حذف التحويل',
          data.message ?? 'يوجد خطأ',
          data.success ? 'success': 'danger'
        )
        changeTab(window.currentTabName);
        $('#loading-dialog-modal').modal('hide');
      } else {
        $('#message-dialog-modal').modal('hide');
      }
    },
    {نعم: 'danger', إلغاء: 'primary'},
    () => {
      $(this).html(btnHtml);
      $(this).attr('disabled', false);
    },
  );
});
$on(`#view-transfer .modal-body button[name="change-status"]`, 'click', async function() {
  loadingDialog('create-template', 'تغيير حالة تحويل', 'يرجى الإنتظار لحين تغيير حالة تحويل...');
  const id = $('#view-transfer .modal-title #view-transfer-id').html();
  const data = await $.ajax({
    url: `./admin/transfer/${id}/change_status`,
    type: 'POST',
    headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
    data: {
      status: $('#view-transfer .modal-body .form-control[name="status"]').val(),
      description: $('#view-transfer .modal-body .form-control[name="status-description"]').val(),
    },
    dataType: 'JSON',
  });
  console.log(data)
  alertMessage('change-transfer-status-message', 'تغيير حالة التحويل', data.message, data.success ? 'success' : 'danger');
  $('#view-transfer').modal('hide');
  await loadTab();
  $('#loading-dialog-modal').modal('hide');
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
  const category = StorageDatabase.collection('products').doc('categories').doc(rowId).get();

  $('#create-edit-category .modal-title').html(`تعديل النوع #${rowId}`);
  const button = $($('#create-edit-category .modal-footer button[action="edit"]')[0] ?? $('#create-edit-category .modal-footer button[action="create"]')[0])
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
  const btnHtml = $(this).html();
  $(this).html(`<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>`);
  $(this).attr('disabled', true);
  messageDialog(
    'ask-delete-category',
    'حذف النوع',
    '<h4>هل أنت متأكد من أنك تريد حذف هذا النوع?</h4>',
    async (action) => {
      if(action == 'نعم') {
        loadingDialog('create-template', 'حذف النوع', 'يرجى الإنتظار لحين حذف النوع...');
        const data = await $.ajax({
          url: `./admin/category/${rowId}/delete`,
          type: 'DELETE',
          headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
          dataType: 'JSON',
        });
        console.log(data);
        $('#message-dialog-modal').modal('hide');
        alertMessage('delete-category-response',
          'حذف النوع',
          data.message ?? 'يوجد خطأ',
          data.success ? 'success': 'danger'
        )
        changeTab(window.currentTabName);
        $('#loading-dialog-modal').modal('hide');
      } else {
        $('#message-dialog-modal').modal('hide');
      }
    },
    {نعم: 'danger', إلغاء: 'primary'},
    () => {
      $(this).html(btnHtml);
      $(this).attr('disabled', false);
    },
  );
});
$on('#create-edit-category .btn[action="create"]', 'click', async function() {
  loadingDialog('create-template', 'إنشاء النوع', 'يرجى الإنتظار لحين إنشاء النوع...');
  const formData = new FormData();
  formData.append('image', window.ImagePicker['category-image-picker']);
  formData.append('names', JSON.stringify(getMultiInputValues('#category-names')));
  const data = await $.ajax({
    url: './admin/category/create_category',
    type: 'POST',
    headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
    contentType: false,
    processData: false,
    data: formData,
  });
  console.log(data);
  if(data.success) {
    $('#create-edit-category').modal('hide');
    window.ImagePicker['category-image-picker'] = undefined;
    alertMessage('create-category-message', 'إنشاء نوع', data.message, 'success');
    changeTab(window.currentTabName);
  } else {
    for (const key in data.errors) {
      const error = data.errors[key];
      alertMessage('create-category-message', 'إنشاء نوع', error, 'danger');
    }
  }
  $('#loading-dialog-modal').modal('hide');
});
$on('#create-edit-category .btn[action="edit"]', 'click', async function() {
  loadingDialog('create-template', 'تعديل النوع', 'يرجى الإنتظار لحين تعديل النوع...');
  const id = $('#create-edit-category .modal-title').html().split('#')[1];
  const formData = new FormData();
  formData.append('image', window.ImagePicker['category-image-picker']);
  formData.append('names', JSON.stringify(getMultiInputValues('#category-names')));
  const data = await $.ajax({
    url: `./admin/category/${id}/edit`,
    type: 'POST',
    headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
    contentType: false,
    processData: false,
    data: formData,
  });
  console.log(data);
  if(data.success) {
    $('#create-edit-category').modal('hide');
    window.ImagePicker['category-image-picker'] = undefined;
    alertMessage('create-category-message', 'تعديل النوع', data.message, 'success');
    changeTab(window.currentTabName);
  } else {
    for (const key in data.errors) {
      const error = data.errors[key];
      alertMessage('create-category-message', 'تعديل النوع', error, 'danger');
    }
  }
  $('#loading-dialog-modal').modal('hide');
});

$on('#all-purchases table tr td button[action="view"]', 'click', function() {
  const rowId = getElementparent(this, 2).id.replace(`all-purchases-item-`, '');
  const purchase = StorageDatabase.collection('purchases').doc(rowId).get();
  if(purchase) viewPurchase(purchase);
});
$on('#view-purchase .modal-body button[name="answer"]', 'click', async function() {
  loadingDialog('create-template', 'الإجابة', 'يرجى الإنتظار لحين الإجابة...');
  const id = $('#view-purchase .modal-title #view-purchase-id').html();
  const data = $.ajax({
    url: `./admin/purchase/${id}/answer`,
    type: 'POST',
    headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
    data: {answer: $('#view-purchase .modal-body .form-control[name="answer"]').val()},
    dataType: 'JSON',
  });
  console.log(data)
  alertMessage('answer-purchase-message', 'إجابة المدير', data.message, data.success ? 'success' : 'danger');
  $('#view-purchase').modal('hide');
  await loadTab();
  $('#loading-dialog-modal').modal('hide');
});

$on('#all-offers .custom-table-header-actions button[action="create"]', 'click', function() {
  $('#create-edit-offer .btn-img-picker').html('<span class="material-symbols-sharp pick-icon">add_a_photo</span>');
  $('#create-edit-offer .form-control').val('');
  clearMultiInputValues('#offer-sub-offers');
  clearMultiInputValues('#offer-fields');
  clearMultiInputValues('#offer-data');
  var button = $($('#create-edit-offer .modal-footer button[action="edit"]')[0] ?? $('#create-edit-offer .modal-footer button[action="create"]')[0])
  button.html('إنشاء');
  button.attr('action', 'create');
  $('#create-edit-offer').modal('show');
});
$on('#all-offers table tr td button[action="edit"]', 'click', function () {
  const rowId = getElementparent(this, 2).id.replace(`all-offers-item-`, '');
  const offer = StorageDatabase.collection('offers').doc('offers').doc(rowId).get();

  $('#create-edit-offer .modal-title').html(`تعديل العرض #${rowId}`);
  const button = $($('#create-edit-offer .modal-footer button[action="edit"]')[0] ?? $('#create-edit-offer .modal-footer button[action="create"]')[0])
  button.html('تعديل');
  button.attr('action', 'edit');
  $('#create-edit-offer .btn-img-picker').html('<span class="material-symbols-sharp pick-icon">add_a_photo</span>');
  $('#create-edit-offer .form-control').val('');
  $('#create-edit-offer .form-control[name="offer_title_en"]').val(offer.title.en);
  $('#create-edit-offer .form-control[name="offer_title_ar"]').val(offer.title.ar);
  $('#create-edit-offer .form-control[name="offer_description_en"]').val(offer.description.en);
  $('#create-edit-offer .form-control[name="offer_description_ar"]').val(offer.description.ar);
  $('#create-edit-offer .form-group .btn.btn-img-picker').html(`<img src="./file/admin/${offer.image_id}">`);
  clearMultiInputValues('#offer-sub-offers');
  for (const name in offer.sub_offers) {
    addMultiInputItem('#offer-sub-offers', offer.sub_offers[name]);
  }
  clearMultiInputValues('#offer-fields');
  for (const name in offer.fields) {
    addMultiInputItem('#offer-fields', offer.fields[name]);
  }
  clearMultiInputValues('#offer-data');
  for (const name in offer.data) {
    addMultiInputItem('#offer-data', offer.data[name]);
  }
  $('#create-edit-offer').modal('show');
});
$on('#all-offers table tr td button[action="delete"]', 'click', function () {
  const rowId = getElementparent(this, 2).id.replace(`all-offers-item-`, '');
  const btnHtml = $(this).html();
  $(this).html(`<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>`);
  $(this).attr('disabled', true);
  messageDialog(
    'ask-delete-offer',
    'حذف العرض',
    '<h4>هل أنت متأكد من أنك تريد حذف هذا العرض?</h4>',
    async (action) => {
      if(action == 'نعم') {
        loadingDialog('create-template', 'حذف العرض', 'يرجى الإنتظار لحين حذف العرض...');
        const data = await $.ajax({
          url: `./admin/offer/${rowId}/delete`,
          type: 'DELETE',
          headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
          dataType: 'JSON',
        });
        console.log(data);
        $('#message-dialog-modal').modal('hide');
        alertMessage('delete-offer-response',
          'حذف النوع',
          data.message ?? 'يوجد خطأ',
          data.success ? 'success': 'danger'
        )
        changeTab(window.currentTabName);
        $('#loading-dialog-modal').modal('hide');
      } else {
        $('#message-dialog-modal').modal('hide');
      }
    },
    {نعم: 'danger', إلغاء: 'primary'},
    () => {
      $(this).html(btnHtml);
      $(this).attr('disabled', false);
    },
  );
});
$on('#create-edit-offer .modal-footer button[action="create"]', 'click', async function() {
  loadingDialog('create-template', 'إنشاء العرض', 'يرجى الإنتظار لحين إنشاء العرض...');
  const formData = new FormData();
  formData.append('title_en', $('#create-edit-offer input[name="offer_title_en"]').val());
  formData.append('title_ar', $('#create-edit-offer input[name="offer_title_ar"]').val());
  formData.append('description_en', $('#create-edit-offer input[name="offer_description_en"]').val());
  formData.append('description_ar', $('#create-edit-offer input[name="offer_description_ar"]').val());
  formData.append('sub_offers', JSON.stringify(getMultiInputValues('#offer-sub-offers')));
  formData.append('fields', JSON.stringify(getMultiInputValues('#offer-fields')));
  formData.append('data', JSON.stringify(getMultiInputValues('#offer-data')));
  formData.append('image', window.ImagePicker['offer-image-picker']);
  const data = await $.ajax({
    url: './admin/offer/create',
    type: 'POST',
    headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
    contentType: false,
    processData: false,
    data: formData,
  });
  console.log(data);
  if(data.success) {
    $('#create-edit-offer').modal('hide');
    window.ImagePicker['offer-image-picker'] = undefined;
    alertMessage('create-offer-message', 'إنشاء عرض', data.message, 'success');
    changeTab(window.currentTabName);
  } else {
    for (const key in data.errors) {
      const error = data.errors[key];
      alertMessage('create-offer-message', 'إنشاء عرض', error, 'danger');
    }
  }
  $('#loading-dialog-modal').modal('hide');
});
$on('#create-edit-offer .modal-footer button[action="edit"]', 'click', async function() {
  loadingDialog('create-template', 'إنشاء العرض', 'يرجى الإنتظار لحين إنشاء العرض...');
  const id = $('#create-edit-offer .modal-title').html().split('#')[1];
  const formData = new FormData();
  formData.append('title_en', $('#create-edit-offer input[name="offer_title_en"]').val());
  formData.append('title_ar', $('#create-edit-offer input[name="offer_title_ar"]').val());
  formData.append('description_en', $('#create-edit-offer input[name="offer_description_en"]').val());
  formData.append('description_ar', $('#create-edit-offer input[name="offer_description_ar"]').val());
  formData.append('sub_offers', JSON.stringify(getMultiInputValues('#offer-sub-offers')));
  formData.append('fields', JSON.stringify(getMultiInputValues('#offer-fields')));
  formData.append('data', JSON.stringify(getMultiInputValues('#offer-data')));
  if(window.ImagePicker['offer-image-picker']) formData.append('image', window.ImagePicker['offer-image-picker']);
  const data = await $.ajax({
    url: `./admin/offer/${id}/edit`,
    type: 'POST',
    headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
    contentType: false,
    processData: false,
    data: formData,
  });
  if(data.success) {
    $('#create-edit-offer').modal('hide');
    window.ImagePicker['offer-image-picker'] = undefined;
    alertMessage('create-offer-message', 'تعديل العرض', data.message, 'success');
    changeTab(window.currentTabName);
  } else {
    for (const key in data.errors) {
      const error = data.errors[key];
      alertMessage('create-offer-message', 'تعديل العرض', error, 'danger');
    }
  }
  $('#loading-dialog-modal').modal('hide');
});

$on('#all-offer-requests table tr td button[action="view"]', 'click', function () {
  const rowId = getElementparent(this, 2).id.replace(`all-offer-requests-item-`, '');
  const offerRequest = StorageDatabase.collection('offers').doc('offer_requests').doc(rowId).get();
  if(offerRequest) viewOfferRequest(offerRequest);
});
$on('#view-offer-request .form-group[name="answer_form"] .btn[action="submit"]', 'click',async function() {
  loadingDialog('create-template', 'لإجابة الطلب', 'يرجى الإنتظار لحين لإجابة الطلب...');
  const id = $('#view-offer-request #view-offer-request-id').html();
  const offerRequest = StorageDatabase.collection('offers').doc('offer_requests').doc(id).get();
  const formData = new FormData();
  const answer = $('#view-offer-request .form-group[name="answer_form"] .form-control[name="admin-answer"]').val();
  formData.append('answer', answer);
  if(answer == 'accept') {
    for (const name in offerRequest.offer.data) {
      formData.append(name, $(`#view-offer-request .modal-body .form-group[name="answer_form"] .data_fields .form-control[name="${name}"]`).val());
    }
  } else {
    formData.append('description', $(`#view-offer-request .modal-body .form-group[name="answer_form"] .form-control[name="refuse_description"]`).val());
  }
  const data = await $.ajax({
    url: `./admin/offer_request/${id}/answer`,
    type: 'POST',
    headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
    contentType: false,
    processData: false,
    data: formData,
  });
  if(data.success) {
    $('#view-offer-request').modal('hide');
    alertMessage('answer-offer-request-message', 'إجابة طلب العرض', data.message, 'success');
    changeTab(window.currentTabName);
  } else {
    console.log(data);
    if(data.message) {
      alertMessage('answer-offer-request-message', 'إجابة طلب العرض', error, 'danger');
    } else {
      for (const key in data.errors) {
        const error = data.errors[key];
        alertMessage('answer-offer-request-message', 'إجابة طلب العرض', error, 'danger');
      }
    }
  }
  $('#loading-dialog-modal').modal('hide');
});

$on('#all-templates .custom-table-header-actions button[action="create"]', 'click', function() {
  $('#create-edit-template .modal-body input').val('');
  $('#create-edit-template .modal-body #template-editor textarea[name="template-content"]').val('');
  $('#create-edit-template .modal-body #template-editor .preview').html('');
  // templateEditor.html.set('');
  clearMultiInputValues('#template-args');
  $('#create-edit-template .modal-title').html(`إنشاء القالب`);
  var button = $($('#create-edit-template .modal-footer button[action="edit"]')[0] ??
               $('#create-edit-template .modal-footer button[action="create"]')[0])
  button.html('إنشاء');
  button.attr('action', 'create');
  $('#create-edit-template').modal('show');
});
$on('#all-templates tr td button[action="edit"]', 'click', function () {
  const rowId = getElementparent(this, 2).id.replace(`all-templates-item-`, '');
  const template = StorageDatabase.collection('mails').doc('templates').doc(rowId).get();

  $('#create-edit-template .modal-title').html(`تعديل القالب #${rowId}`);
  const button = $($('#create-edit-template .modal-footer button[action="edit"]')[0] ?? $('#create-edit-template .modal-footer button[action="create"]')[0])
  button.html('تعديل');
  button.attr('action', 'edit');
  $('#create-edit-template input[name="template_name"]').val(template.name);
  $('#create-edit-template select[name="template_type"]').val(template.type);
  // templateEditor.html.set(template.content);
  $('#create-edit-template .modal-body #template-editor textarea[name="template-content"]').val(template.content);
  $('#create-edit-template .modal-body #template-editor .preview').html(template.content);
  clearMultiInputValues('#template-args');
  template.args.forEach(arg => {
    addMultiInputItem('#template-args', arg);
  })

  $('#create-edit-template').modal('show');
});
$on('#all-templates tr td button[action="delete"]', 'click', function () {
  const rowId = getElementparent(this, 2).id.replace(`all-templates-item-`, '');
  const btnHtml = $(this).html();
  $(this).html(`<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>`);
  $(this).attr('disabled', true);
  messageDialog(
    'ask-delete-template',
    'حذف القالب',
    '<h4>هل أنت متأكد من أنك تريد حذف هذا القالب?</h4>',
    async (action) => {
      if(action == 'نعم') {
        loadingDialog('create-template', 'حذف القالب', 'يرجى الإنتظار لحين حذف القالب...');
        const data = await $.ajax({
          url: `./admin/template/${rowId}/delete`,
          type: 'DELETE',
          headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
          dataType: 'JSON',
        });
        $('#message-dialog-modal').modal('hide');
        console.log(data);
        alertMessage('delete-template-response',
          'حذف القالب',
          data.message ?? 'يوجد خطأ',
          data.success ? 'success': 'danger'
        )
        changeTab(window.currentTabName);
        $('#loading-dialog-modal').modal('hide');
      } else {
        $('#message-dialog-modal').modal('hide');
      }
    },
    {نعم: 'danger', إلغاء: 'primary'},
    () => {
      $(this).html(btnHtml);
      $(this).attr('disabled', false);
    },
  );
});
$on('#create-edit-template .modal-footer .btn[action="create"]', 'click', async function() {
  loadingDialog('create-template', 'إنشاء القالب', 'يرجى الإنتظار لحين إنشاء القالب...');
  const formData = new FormData();
  formData.append('name', $('#create-edit-template input[name="template_name"]').val());
  formData.append('type', $('#create-edit-template select[name="template_type"]').val());
  // formData.append('source_code', templateEditor.html.get());
  formData.append('source_code',$('#create-edit-template .modal-body #template-editor textarea[name="template-content"]').val());
  formData.append('args', JSON.stringify(getMultiInputValues('#template-args')));
  const data = await $.ajax({
    url: './admin/template/create',
    type: 'POST',
    headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
    contentType: false,
    processData: false,
    data: formData,
  });
  console.log(data);
  if(data.success) {
    $('#create-edit-template').modal('hide');
    alertMessage('create-template-message', 'إنشاء قالب', data.message, 'success');
    changeTab(window.currentTabName);
  } else {
    for (const key in data.errors) {
      const error = data.errors[key];
      alertMessage('create-template-message', 'إنشاء قالب', error, 'danger');
    }
  }
  $('#loading-dialog-modal').modal('hide');
});
$on('#create-edit-template .modal-footer .btn[action="edit"]', 'click', async function() {
  loadingDialog('create-template', 'تعديل القالب', 'يرجى الإنتظار لحين تعديل القالب...');
  const id = $('#create-edit-template .modal-title').html().split('#')[1];
  const formData = new FormData();
  formData.append('name', $('#create-edit-template input[name="template_name"]').val());
  formData.append('type', $('#create-edit-template select[name="template_type"]').val());
  // formData.append('source_code', templateEditor.html.get());
  formData.append('source_code',$('#create-edit-template .modal-body #template-editor textarea[name="template-content"]').val());
  formData.append('args', JSON.stringify(getMultiInputValues('#template-args')));

  const data = await $.ajax({
    url: `./admin/template/${id}/edit`,
    type: 'POST',
    headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
    contentType: false,
    processData: false,
    data: formData,
  });
  console.log(data);
  if(data.success) {
    $('#create-edit-template').modal('hide');
    alertMessage('edit-template-message', 'تعديل القالب', data.message, 'success');
    changeTab(window.currentTabName);
  } else {
    for (const key in data.errors) {
      const error = data.errors[key];
      alertMessage('edit-template-message', 'تعديل القالب', error, 'danger');
    }
  }
  $('#loading-dialog-modal').modal('hide');
});
window.switchTab = function(button, target) {
  if(target == 'preview') {
    $('#template-editor .content textarea').css('display', 'none');
    $('#template-editor .content .preview').css('display', 'block');
    $(button).attr('class', 'btn btn-success btn-icon');
    $(button).attr('onClick', 'switchTab(this, "textarea")');
    $(button).html('<span class="material-symbols-sharp">code</span>');
  } else {
    $('#template-editor .content textarea').css('display', 'block');
    $('#template-editor .content .preview').css('display', 'none');
    $(button).attr('class', 'btn btn-primary btn-icon');
    $(button).attr('onClick', 'switchTab(this, "preview")');
    $(button).html('<span class="material-symbols-sharp">visibility</span>');
  }
}
$on('#template-editor .content textarea', 'change keyup paste', function() {
  $('#template-editor .content .preview').html($('#template-editor .content textarea').val())
  // $('#template-editor .content .preview').contents().find('body').html($('#template-editor .content textarea').val())
});

$on('#all-mails .custom-table-header-actions button[action="create"]', 'click', async function() {
  const btnHtml = $(this).html();
  $(this).html(`<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>يرجى الإنتظار...`);
  $(this).attr('disabled', true);
  $('#create-mail input').val('');
  await loadData('users');
  var table = initTable(`#create-mail-all-users table`, [[1, 'desc']]);
  table.clear();
  const users = StorageDatabase.collection('users').doc('users').get();
  for (const id in users) {
    const user = users[id];
    var row = [];
    row.push(
      `<td>
        <span class="custom-checkbox">
          <input type="checkbox" id="#create-mail-all-users-chb-${id}">
          <label for="#create-mail-all-users-chb-${id}"></label>
        </span>
      </td>`);
    row.push(`<td>${user.id}</td>`);
    row.push(`<td><div class="table-img"><img src="./file/admin/${user.profile_image_id}?t=${(new Date()).getTime()}"></div></td>`);
    row.push(`<td>${user.firstname} ${user.lastname}</td>`);
    row.push(`<td>${user.email}</td>`);
    row.push(`<td>${user.phone}</td>`);
    row.push(`<td>${user.balance == 0 ? '<span class="danger">0 SE</span>':`<span class="success">${user.balance} SE</span>`}</td>`);
    row.push(`<td>${user.checking_balance == 0 ? '<span class="danger">0 SE</span>':`<span class="warning">${user.checking_balance} SE</span>`}</td>`);
    row.push(`<td>${user.email_verified ? '<span class="success">محقق</span>': '<span class="danger">غير محقق</span>'}</td>`);
    row.push(`<td>${statuses[user.identity_status]}</td>`);
    row.push(`<td>${user.created_at}</td>`);
    table.row.add(row).node().id = `#create-mail-all-users-item-${id}`;
  }
  table.draw();
  const items = StorageDatabase.collection('mails').doc('templates').get();
  var templates = '';
  var fields = null;
  for (const id in items) {
    const template = items[id];
    if(fields == null) {
      fields = '';
      template.args.forEach(arg => {
        console.log(arg)
        fields += `
          <input type="${arg.type}" class="form-control" name="${arg.name}" placeholder="${arg.name}">
        `;
      });
    }
    templates += `<option value="${id}">${template.name}</option>`
  }
  $('#create-mail .form-group[name="data"] .data_fields').html(fields);
  $('#create-mail select[name="template"]').html(templates);
  $('#create-mail').modal('show');
});
$on('#all-mails .custom-table-header-actions button[action="delete"]', 'click', function() {
  const inputs = $('#all-mails').find('table tbody tr input:checked');
  var mailsIds = [];
  for (let i = 0; i < inputs.length; i++) {
    mailsIds.push(parseInt(inputs[i].id.replace(`all-mails-chb-`, '')));
  }
  const btnHtml = $(this).html();
  $(this).html(`<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>يرجى الإنتظار...`);
  $(this).attr('disabled', true);
  messageDialog(
    'delete-mails',
    'حذف الإمايلات',
    'هل أنت متأكد من حذف هؤلاء الإمايلات?',
    async (action) => {
      if(action == 'نعم') {
        loadingDialog('create-template', 'حذف الإمايات', 'يرجى الإنتظار لحين حذف الإمايات...');
        const formData = new FormData();
        formData.append('ids', JSON.stringify(mailsIds));
        const data = await $.ajax({
          url: './admin/mail/delete_mails',
          type: 'POST',
          headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
          contentType: false,
          processData: false,
          data: formData,
        });
        console.log(data);
        if(data.success) {
          $('#delete-mails').modal('hide');
          alertMessage('delete-mails-message', 'حذف الإيمايلات', data.message, 'success');
          changeTab(window.currentTabName);
        } else {
          for (const key in data.errors) {
            const error = data.errors[key];
            alertMessage('delete-mails-message', 'حذف الإيمايلات', error, 'danger');
          }
        }
        $('#loading-dialog-modal').modal('hide');
      } else {
        $('#message-dialog-modal').modal('hide');
      }
    },
    {نعم: 'primary', إلغاء: 'danger'},
    () => {
      $(this).html(btnHtml);
      $(this).attr('disabled', false);
    }
  );
});
$on('#all-mails', 'onRowActionClick[action="delete"]', function(event, row) {
  const id = $(row).attr('id').replace('all-mails-item-', '');
  const mail = StorageDatabase.collection('mails').doc('mails').doc(id).get()
  console.log(mail);
  // const btnHtml = $(this).html();
  // $(this).html(`<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>`);
  // $(this).attr('disabled', true);
  messageDialog(
    'ask-delete-mail',
    'حذف البريد الإلكتروني',
    '<h4>هل أنت متأكد من أنك تريد حذف هذا البريد الإلكتروني?</h4>',
    async (action) => {
      if(action == 'نعم') {
        loadingDialog('delete-mail', 'حذف البريد الإلكتروني', 'يرجى الإنتظار لحين حذف البريد الإلكتروني...');
        const data = await $.ajax({
          url: `./admin/mail/${id}/delete`,
          type: 'DELETE',
          headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
          dataType: 'JSON',
        });
        $('#message-dialog-modal').modal('hide');
        alertMessage('delete-user-response',
          `حذف البريد الإلكتروني`,
          data.message ?? 'Some things bad',
          data.success ? 'success': 'danger'
        )
        changeTab(window.currentTabName);
        $('#loading-dialog-modal').modal('hide');
      } else {
        $('#message-dialog-modal').modal('hide');
      }
    },
    {نعم: 'danger', لا: 'primary'},
    () => {
      // $(this).html(btnHtml);
      // $(this).attr('disabled', false);
    },
  );
});
$on('#create-mail select[name="template"]', 'change', function() {
  const template = StorageDatabase.collection('mails').doc('templates').doc($(this).val()).get()
  console.log(template)
  var fields = '';
  template.args.forEach(arg => {
    fields += `
      <input type="${arg.type}" class="form-control" name="${arg.name}" placeholder="${arg.name}">
    `;
  });
  $('#create-mail .form-group[name="data"] .data_fields').html(fields);
});
$on('#create-mail .modal-footer .btn[action="create"]', 'click', async function() {
  loadingDialog('create-template', 'إرسال البريد الإلكتروني', 'يرجى الإنتظار لحين إرسال البريد الإلكتروني...');
  const formData = new FormData();
  formData.append('title', $('#create-mail input[name="title"]').val());
  formData.append('template_id', $('#create-mail select[name="template"]').val());
  var items = $('#create-mail .form-group[name="data"] .data_fields .form-control');
  var fields = {};
  for (let index = 0; index < items.length; index++) {
    const field = items[index];
    fields[$(field).attr('name')] = $(field).val();
  }
  formData.append('data', JSON.stringify(fields));
  const inputs = $('#create-mail #create-mail-all-users table tbody tr input:checked');
  var users = [];
  for (let index = 0; index < inputs.length; index++) {
    const user = getElementparent(inputs[index], 3);
    users.push(parseInt($(user).attr('id').replace('#create-mail-all-users-item-', '')));
  }
  formData.append('targets', JSON.stringify(users));

  const data = await $.ajax({
    url: './admin/mail/create',
    type: 'POST',
    headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
    contentType: false,
    processData: false,
    data: formData,
  });
  console.log(data);
  if(data.success) {
    $('#create-mail').modal('hide');
    alertMessage('create-mail-message', 'إنشاء قالب', data.message, 'success');
    changeTab(window.currentTabName);
  } else {
    for (const key in data.errors) {
      const error = data.errors[key];
      alertMessage('create-mail-message', 'إنشاء قالب', error, 'danger');
    }
  }
  $('#loading-dialog-modal').modal('hide');
});
$on('#create-mail', 'onHidden', function() {
  $('#all-mails .custom-table-header-actions button[action="create"]').html('<span class="material-symbols-sharp">add</span>إنشاء');
  $('#all-mails .custom-table-header-actions button[action="create"]').attr('disabled', true);
});
