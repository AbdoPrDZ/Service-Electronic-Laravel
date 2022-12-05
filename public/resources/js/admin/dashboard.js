// import { io } from '../socket.io.esm.min.js';

// window.permissions = JSON.parse(CryptoJS.AES.decrypt(permissions, document.cookie.replace('adminAuth=', '')).toString(CryptoJS.enc.Utf8));

// const socket = io.connect('', {
//   path: '/admin-socket/',
//   autoConnect: false,
// });

async function loadTable() {
  var response = await $.get(`./admin/load/${window.currentTabName}`);
  var data = response[window.currentTabName];
  if(data) {
    StorageDatabase.collection(window.currentTabName).set(data);
    updateTable(data, `all-${window.currentTabName}`);
  }
}

function updateTable(values, tableId) {
  var table = initTable(`#${tableId} table`);
  $(`#${tableId} table thead input[type="checkbox"].select-all`).prop('checked', false);
  table.clear();
  if (values) {
    for (const id in values) {
      var row = [];
      // if (permissions.user.d) {
        row.push(
          `<td>
            <span class="custom-checkbox">
              <input type="checkbox" id="${tableId}-chb-${id}">
              <label for="${tableId}-chb-${id}"></label>
            </span>
          </td>`);
      // }
      if(tableId == 'all-users') {
        row.push(`<td>${id}</td>`);
        row.push(`<td><div class="table-img"><img src="./file/admin/${values[id].profile_image_id}"></div></td>`);
        row.push(`<td>${values[id].firstname} ${values[id].lastname}</td>`);
        row.push(`<td>${values[id].email}</td>`);
        row.push(`<td>${values[id].phone}</td>`);
        row.push(`<td>${values[id].balance == 0 ? '<span class="danger">0 SE</span>':`<span class="success">${values[id].balance} SE</span>`}</td>`);
        row.push(`<td>${values[id].email_verified ? '<span class="success">Yes</span>': '<span class="danger">No</span>'}</td>`);
       } else if(tableId == 'all-transfers') {
        row.push(`<td>${values[id].id}</td>`);
        row.push(`<td>${values[id].user.firstname} ${values[id].user.lastname}</td>`);
        row.push(`<td>${values[id].sended_balance} ${values[id].sended_currency.char}</td>`);
        row.push(`<td>${values[id].received_balance} ${values[id].received_currency.char}</td>`);
        row.push(`<td>${values[id].sended_currency.name} (${values[id].sended_currency.char})</td>`);
        row.push(`<td>${values[id].received_currency.name} (${values[id].received_currency.char})</td>`);
        row.push(`<td>${values[id].wallet}</td>`);
        row.push(`<td>${statuses[values[id].status]}</td>`);
        row.push(`<td>${values[id].ansowerd_at ?? '<span class="danger">None</span>'}</td>`);
      } else if(tableId == 'all-currencies') {
        row.push(`<td>${values[id].id}</td>`);
        row.push(`<td><div class="table-img"><img src="./file/admin/currency-${values[id].id}"></div></td>`);
        row.push(`<td>${values[id].name}</td>`);
        row.push(`<td>${values[id].char}</td>`);
        // row.push(`<td>${values[id].max_receive}</td>`);
        var pricesHtml = '<select class="form-control" name="prices">';
        var dPrice;
        for (const currencyId in values[id].prices) {
            const price = values[id].prices[currencyId];
            if(currencyId == -1) var currency = values[id];
            else var currency = StorageDatabase.collection('currencies').doc(currencyId).get();
            if(!dPrice) dPrice = `buy: ${price.buy} ${currency.char} <bt> sell: ${price.sell} ${currency.char}`;
            pricesHtml += `<option value="Buy: ${price.buy} ${currency.char} <bt> Sell: ${price.sell} ${currency.char}">${currency.name}</option>`;
        }
        pricesHtml += '</select>';
        pricesHtml += `
          <br>
          <span class="category-name success" name="name">${dPrice}</span>
        `;
        // row.push(pricesHtml);
        row.push(`<td>${pricesHtml}</td>`);
        row.push(`<td>${values[id].wallet}</td>`);
      } else if(tableId == 'all-categories') {
        row.push(`<td>${values[id].id}</td>`);
        row.push(`<td><div class="table-img"><img src="./file/admin/${values[id].image_id}"></div></td>`);
        // row.push(`<td>${values[id].name}</td>`);
        var namesHtml = '<select class="form-control" name="names">';
        var dName;
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
      } else if(tableId == 'all-products') {
        console.log()
        row.push(`<td>${values[id].id}</td>`);
        row.push(`<td><div class="table-img"><img src="./file/admin/${values[id].images_ids[0]}"></div></td>`);
        row.push(`<td>${values[id].name}</td>`);
        row.push(`<td>${values[id].user.firstname} ${values[id].user.lastname}</td>`);
        row.push(`<td>${values[id].price} SE</td>`);
        row.push(`<td>${values[id].category}</td>`);
        row.push(`<td>${values[id].description.substring(0, 100)}</td>`);
      }
      row.push(`<td>${values[id].created_at}</td>`);
      var actions = [];
      // if (permissions.user.r) {
        actions.push(`
          <button class="btn btn-icon btn-secondary" action="view">
            <span class="material-symbols-sharp">open_in_new</span>
          </button>`);
      // }
      // if (permissions.user.u) {
      //   actions.push(`
      //     <button class="btn btn-icon btn-warning" action="edit"">
      //       <span class="material-symbols-sharp">edit</span>
      //     </button>`);
      // }
      // if (permissions.user.d) {
        actions.push(`
          <button class="btn btn-icon btn-danger" action="delete"">
            <span class="material-symbols-sharp">delete</span>
          </button>`);
      // }
      if (actions.length > 0) row.push(`<td>${actions.join('')}</td>`);
      table.row.add(row).node().id = `${tableId}-item-${id}`;
    }
  }
  table.draw();
}

async function changeTab(tabName) {
  $('#tab-loading').attr('class', 'loading show');
  $('#tab-content').attr('class', 'hide');
  if (!tabName) tabName = 'users';
  $(`#tab-content .tab-view-tab-item`).removeClass('selected');
  $(`#tab-content .tab-view-tab-item[for="${tabName}"]`).addClass('selected');
  $(`.sidebar .sidebar-item`).attr('class', 'sidebar-item');
  $(`.sidebar #${tabName}`).attr('class', 'sidebar-item active');
  window.history.pushState('', '', `?tab=${tabName}`);
  window.currentTabName = tabName;
  await loadTable();
  $('#tab-loading').attr('class', 'loading hide');
  $('#tab-content').attr('class', 'show');
}

function loadUserFormValues(userValues) {
  $('#view-user .modal-title #view-user-id').html(userValues.id);
  $(`#view-user .modal-body .form-control[name="firstname"]`).val(userValues.firstname);
  $(`#view-user .modal-body .form-control[name="lastname"]`).val(userValues.lastname);
  $(`#view-user .modal-body .form-control[name="phone"]`).val(userValues.phone);
  $(`#view-user .modal-body .form-control[name="email"]`).val(userValues.email);
  $(`#view-user .modal-body .form-control[name="email_verified"]`).html(
    userValues.email_verified ?
    '<span class="success">Yes</span>':
    '<span class="danger">No</span>'
  );
  $(`#view-user .modal-body .form-control[name="created_at"]`).val(userValues.created_at);

  $('#view-user select').prop('disabled', true);
  $('#view-user input').prop('disabled', true);
  $('#view-user textarea').prop('disabled', true);
  $('#view-user').modal('show');
}

function loadTransferFormValues(transferValues) {
  $('#view-transfer .modal-title #view-transfer-id').html(transferValues.id);
  $(`#view-transfer .modal-body .form-control[name="user"]`).val(`${transferValues.user.firstname} ${transferValues.user.lastname}`);
  $(`#view-transfer .modal-body .form-control[name="sended_balance"]`).val(`${transferValues.sended_balance} ${transferValues.sended_currency.char}`);
  $(`#view-transfer .modal-body .form-control[name="received_balance"]`).val(`${transferValues.received_balance} ${transferValues.received_currency.char}`);
  $(`#view-transfer .modal-body .form-control[name="sended_currency"]`).val(`${transferValues.sended_currency.name} (${transferValues.sended_currency.char})`);
  $(`#view-transfer .modal-body .form-control[name="received_currency"]`).val(`${transferValues.received_currency.name} (${transferValues.received_currency.char})`);
  $(`#view-transfer .modal-body .form-control[name="wallet"]`).val(transferValues.wallet);
  $(`#view-transfer .modal-body .form-control[name="status"] option[value="${transferValues.status}"]`).attr('selected', true)
  $(`#view-transfer .modal-body .form-control[name="ansowerd_at"]`).val(transferValues.ansowerd_at ?? 'None');
  $(`#view-transfer .modal-body .form-control[name="ansower_description"]`).val(transferValues.ansower_description ?? 'None');
  $(`#view-transfer .modal-body .form-control[name="created_at"]`).val(transferValues.created_at);
  $(`#view-transfer .modal-body .form-control[name="proof"]`).html(
    transferValues.proof_id ?
    `<image src="./file/admin/${transferValues.proof_id}"></image>`:
    '<span class="danger center">None</span>'
  );

  // $('#view-transfer select').prop('disabled', true);
  $('#view-transfer input').prop('disabled', true);
  $('#view-transfer textarea').prop('disabled', true);
  // $('#view-transfer button').prop('disabled', true);
  $('#view-transfer').modal('show');
}

function onConnect() {
  var tabs = ['users', 'currencies', 'categories', 'transfers', 'products']
  window.currentTabName = $_GET('tab') && tabs.indexOf($_GET('tab')) != -1 ? $_GET('tab') : (window.currentTabName || 'users');
  changeTab(currentTabName);

  StorageDatabase.collection('users').set({});
  StorageDatabase.collection('transfers').set({});
  StorageDatabase.collection('currencies').set({});

  $on('.sidebar .sidebar-item', 'click', function() {
    if ($(this).attr('class').indexOf('active') != -1) return;
    changeTab($(this).attr('id'));
  });
  displayBodyContent();
}

var statuses = {
  accepted: '<span class="success">Accepted</span>',
  refused: '<span class="danger">Refused</span>',
  checking: '<span class="warning">Checking</span>',
};

$(document).ready(function() {
  onConnect()
  $on('#person-dropdown', 'onSelect[value="account"]', function(event, item) {
    console.log('goto account')
  });
});

$on('#all-currencies .custom-table-header-actions button[action="create"]', 'click', function() {
  $('#create-currency .btn-img-picker').html('<span class="material-symbols-sharp pick-icon">add_a_photo</span>');
  $('#create-currency input').val('');
  $('#create-currency').modal('show');
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
      // if (action == 'نعم') socket.emit('delete-users', usersIds);
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

$on('#tab-content #all-users table button[action="view"] ', 'click', function () {
  const rowId = getElementparent(this, 2).id.replace(`all-users-item-`, '');
  const user = StorageDatabase.collection('users').doc(rowId).get();
  if(user) loadUserFormValues(user);
});
$on('#tab-content #all-users table tr td button[action="delete"]', 'click', function () {
  const rowId = getElementparent(this, 2).id.replace(`all-users-item-`, '');
  console.log(rowId);
  const btnHtml = $(this).html();
  $(this).html(`<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>`);
  $(this).attr('disabled', true);
  messageDialog(
    'ask-delete-user',
    'Delete User',
    '<h4>Are you sure you wanti to delete user?</h4>',
    async (action) => {
      if(action == 'Yes') {
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
    {Yes:'danger', Cancel: 'primary'},
    () => {
      $(this).html(btnHtml);
      $(this).attr('disabled', false);
    },
  );
});

$on('#tab-content #all-currencies table tr td button[action="delete"]', 'click', function () {
  const rowId = getElementparent(this, 2).id.replace(`all-currencies-item-`, '');
  console.log(rowId);
  const btnHtml = $(this).html();
  $(this).html(`<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>`);
  $(this).attr('disabled', true);
  messageDialog(
    'ask-delete-currency',
    'Delete Currency',
    '<h4>Are you sure you want to delete Currency?</h4>',
    async (action) => {
      if(action == 'Yes') {
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
    {Yes:'danger', Cancel: 'primary'},
    () => {
      $(this).html(btnHtml);
      $(this).attr('disabled', false);
    },
  );
});

$on('#tab-content #all-transfers table tr', 'click', function () {
  const rowId = this.id.replace(`all-transfers-item-`, '');
  const transfer = StorageDatabase.collection('transfers').doc(rowId).get();
  if(transfer) loadTransferFormValues(transfer);
});

$on(`#view-transfer .modal-body button[name="change-status"]`, 'click', async function() {
  const id = $('#view-transfer .modal-title #view-transfer-id').html();
  const status = $('#view-transfer .modal-body .form-control[name="status"]').val();
  const description = $('#view-transfer .modal-body .form-control[name="description"]').val();
  // await $.get(`./admin/transfer/${id}/status/${status}`);
  $.ajax({
    url: `./admin/transfer/${id}/change_status`,
    type: 'POST',
    headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
    data: {status: status, description: description},
    dataType: 'JSON',
    success: async function (data) {
      // console.log(data)
      $('#view-transfer').modal('hide');
      await loadTable();
    },
    catch: function (err) {console.log(err);},
    error: async (error) => {
      console.log(error.responseText)
    }
  });
});

$on('#all-currencies table td select[name="prices"]', 'change', function() {
  var td = getElementparent(this, 1);
  $(getElementChild(td, 'span')).html($(this).val())
});
$on('#all-categories table td select[name="names"]', 'change', function() {
  var td = getElementparent(this, 1);
  $(getElementChild(td, 'span')).html($(this).val())
});

$on('.custom-table h3.table-refresh', 'click', function() {
  const table = $(this).attr('table')
  changeTab(window.currentTabName)
});

$on('#create-currency .btn[action="create"]', 'click', async function() {
  const values = {
    name: $('#create-currency input[name="currency_name"]').val(),
    char: $('#create-currency input[name="currency_char"]').val(),
    max_receive: $('#create-currency input[name="currency_max_receive"]').val(),
    wallet: $('#create-currency input[name="currency_wallet"]').val(),
    image: window.ImagePicker['create-currency-image-picker'],
    proof_required: $('#create-currency input[name="proof_required"]')[0].checked,
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
    $('#create-currency').modal('hide');
    alertMessage('create-currency-message', 'Create Currency Error', data['message'], 'success');
    changeTab(window.currentTabName);
  } else {
    for (const key in data['errors']) {
      const error = data['errors'][key];
      alertMessage('create-currency-message', 'Create Currency Error', error, 'danger');
    }
  }
});
