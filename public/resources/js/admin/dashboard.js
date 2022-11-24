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
    updateTable(data, `all-${window.currentTabName}`);
    StorageDatabase.collection(window.currentTabName).set(data);
  }
}

function updateTable(values, tableId) {
  var table = initTable(`#${tableId} table`);
  $(`#${tableId} table thead input[type="checkbox"].select-all`).prop('checked', false);
  if (values) {
    table.clear();
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
        row.push(`<td><div class="table-img"><img src="./file/${values[id].profile_image_id}"></div></td>`);
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
        row.push(`<td><div class="table-img"><img src="./file/currency-${values[id].id}"></div></td>`);
        row.push(`<td>${values[id].name}</td>`);
        row.push(`<td>${values[id].char}</td>`);
        row.push(`<td>${values[id].max_receive}</td>`);
        row.push(`<td>${values[id].wallet}</td>`);
      } else if(tableId == 'all-categories') {
        row.push(`<td>${values[id].id}</td>`);
        row.push(`<td><div class="table-img"><img src="./file/${values[id].image_id}"></div></td>`);
        row.push(`<td>${values[id].name}</td>`);
      } else if(tableId == 'all-products') {
        console.log()
        row.push(`<td>${values[id].id}</td>`);
        row.push(`<td><div class="table-img"><img src="./file/${values[id].images_ids[0]}"></div></td>`);
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
    table.draw();
  }
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
  // await delay(500);
  $('#tab-loading').attr('class', 'loading hide');
  $('#tab-content').attr('class', 'show');
  window.currentTabName = tabName;
  loadTable();
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
  $(`#view-transfer .modal-body .form-control[name="created_at"]`).val(transferValues.created_at);
  $(`#view-transfer .modal-body .form-control[name="proof"]`).html(
    transferValues.proof_id ?
    `<image src="./file/${transferValues.proof_id}"></image>`:
    '<span class="danger center">None</span>'
  );

  // $('#view-transfer select').prop('disabled', true);
  $('#view-transfer input').prop('disabled', true);
  $('#view-transfer textarea').prop('disabled', true);
  // $('#view-transfer button').prop('disabled', true);
  $('#view-transfer').modal('show');
}

function loadFileFormValues(fileValues) {
  $(`#view-file .modal-body .form-control[name="file_name"]`).val(fileValues.name);
  $(`#view-file .modal-body .form-control[name="file_path"]`).val(fileValues.path);
  $(`#view-file .modal-body .form-control[name="file_disk"] option[value="${fileValues.disk}"]`).attr('selected', true)
  $(`#view-file .modal-body .form-control[name="created_at"]`).val(fileValues.created_at);
  $('#view-file').modal('show');
}

function onConnect() {
  window.currentTabName = $_GET('tab') || 'users'
  changeTab(currentTabName);

  StorageDatabase.collection('users').set({});
  StorageDatabase.collection('transfers').set({});
  StorageDatabase.collection('currencies').set({});
  StorageDatabase.collection('files').set({});
  // socket.emit('start-listener', 'user');

  // socket.on('users-update', (requestId, type, users) => {
  //   if (type == 'delete') {
  //     users.forEach(userId => {
  //       ['users', 'students', 'retirees', 'workers'].forEach(collectionId => {
  //         const collection = StorageDatabase.collection(collectionId);
  //         if (collection.haveDocId(userId)) {
  //           collection.remove(userId);
  //           updateTable([userId], 'delete', `all-${collectionId}`);
  //         }
  //       });
  //     });
  //   } else {
  //     var students = {};
  //     var retirees = {};
  //     var workers = {};
  //     for (const userId in users) {
  //       if (users[userId].job == 'student') {
  //         StorageDatabase.collection('students').doc(userId).set(users[userId]);
  //         students[userId] = users[userId];
  //       } else if (users[userId].job == 'retired') {
  //         StorageDatabase.collection('retirees').doc(userId).set(users[userId]);
  //         retirees[userId] = users[userId];
  //       } else if (users[userId].job == 'worker') {
  //         StorageDatabase.collection('workers').doc(userId).set(users[userId]);
  //         workers[userId] = users[userId];
  //       }
  //       StorageDatabase.collection('users').doc(userId).set(users[userId]);
  //     }
  //     updateTable(users, type, 'all-users');
  //     updateTable(students, type, 'all-students');
  //     updateTable(retirees, type, 'all-retirees');
  //     updateTable(workers, type, 'all-workers');
  //   }
  // });

  // socket.on('user-create-result', (_, success, message) => {
  //   alertMessage(`create-user-${Date.now()}`, success ? 'نجاح:' : 'خطأ:', message, success ? 'success' : 'danger');
  //   $('#users-add button[action="add-user"]').html('إضافة الشخص').attr('disabled', false);
  //   $('#users-add input').val('');
  //   $('#users-add select option').attr('selected', false);
  //   $('#users-add select option:first-child').prop('selected', true).change();
  //   $('#users-add input[type="checkbox"]').prop('checked', false);
  // });

  // socket.on('user-update-result', (_, success, message) => {
  //   alertMessage(`update-user-${Date.now()}`, success ? 'نجاح:' : 'خطأ:', message, success ? 'success' : 'danger');
  //   $('#edit-user .modal-footer button.btn[action="user-save-edit"]').attr('disabled', false).html('حفظ التغييرات');
  //   if (success) {
  //     $('#edit-user').modal('hide');
  //     $('#edit-user input').val('');
  //     $('#edit-user select option').attr('selected', false);
  //     $('#edit-user select option:first-child').prop('selected', true).change();
  //     $('#edit-user input[type="checkbox"]').prop('checked', false);
  //   }
  // });

  // socket.on('user-delete-result', (_, success, message) => {
  //   alertMessage(`delete-user-${Date.now()}`, success ? 'نجاح:' : 'خطأ:', message, success ? 'success' : 'danger');
  // });

  // socket.on('users-delete-result', (_, success, message) => {
  //   alertMessage(`deletes-users-${Date.now()}`, success ? 'نجاح:' : 'خطأ:', message, success ? 'success' : 'danger');
  // });

  $on('.sidebar .sidebar-item', 'click', function() {
    if ($(this).attr('class').indexOf('active') != -1) return;
    changeTab($(this).attr('id'));
  });
  displayBodyContent();
}

var statuses = {
  eccepted: '<span class="success">Eccepted</span>',
  refused: '<span class="danger">Refused</span>',
  checking: '<span class="warning">Checking</span>',
};

$(document).ready(function() {
  // socket.connect()
  // socket.on('connect_error', (err) => {
  //   $('#body-loading .loading-message').css('display', 'block').html(`خطأ في الإتصال:<br> ${err.toString().replace('Error:', '')}`);
  // });
  // socket.on('disconnect', function () {
  //   console.log('Disconnected');
  // });
  // socket.on('connect_failed', (err) => {
  //   console.log(`Connect failed: ${err}`);
  // });
  // socket.on('connect', onConnect);
  onConnect()
  $on('#person-dropdown', 'onSelect[value="account"]', function(event, item) {
    console.log('goto account')
  });
});

$on('#tab-content .custom-table-header .custom-table-header-actions button[action="delete"]', 'click', function() {
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
          url: './admin/user/delete',
          type: 'POST',
          headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
          data: {id: rowId},
          dataType: 'JSON',
        });
        alertMessage('delete-user-response',
          `Delete User ${data['success'] ? 'Success' : 'Error'}`,
          data['message'] ?? 'Some things bad',
          data['success'] ? 'success': 'danger'
        )
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
  // const user = StorageDatabase.collection('users').doc(rowId).get();
  // loadUserFormValues(user);
});

$on('#tab-content #all-transfers table tr', 'click', function () {
  const rowId = this.id.replace(`all-transfers-item-`, '');
  const transfer = StorageDatabase.collection('transfers').doc(rowId).get();
  if(transfer) loadTransferFormValues(transfer);
});
// $on('#tab-content #all-transfers table tr td button[action="view"]', 'click', function () {
//   const rowId = getElementparent(this, 2).id.replace(`all-transfers-item-`, '');
//   const transfer = StorageDatabase.collection('transfers').doc(rowId).get();
//   loadTransferFormValues(transfer);
// });
$on(`#view-transfer .modal-body button[name="change-status"]`, 'click', async function() {
  const id = $('#view-transfer .modal-title #view-transfer-id').html();
  const status = $('#view-transfer .modal-body .form-control[name="status"]').val();
  // await $.get(`./admin/transfer/${id}/status/${status}`);
  $.ajax({
    url: './admin/transfer/change_status',
    type: 'POST',
    headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
    data: {id: id, status: status},
    dataType: 'JSON',
    success: function (data) {
      // console.log(data)
      $('#view-transfer').modal('hide');
      loadTable();
    },
    catch: function (err) {console.log(err);},
    error: async (error) => {
      console.log(error.responseText)
    }
  });
});

// $on('#tab-content #all-files table tr', 'click', function () {
//   const rowId = this.id.replace(`all-files-item-`, '');
//   const file = StorageDatabase.collection('files').doc(rowId).get();
//   if(file) loadFileFormValues(file);
// });

$on('#tab-content #all-users table tr td button[action="edit"]', 'click', function () {
  const tableId = getElementparent(this, 8).id;
  const rowId = getElementparent(this, 2).id.replace(`${tableId}-item-`, '');
  const user = StorageDatabase.collection('users').doc(rowId).get();
  loadUserFormValues(user);

  $('#edit-user .modal-title #edit-user-id').html(rowId);
  $('#edit-user').modal('show');
});

$on('#tab-content table tr td button[action="delete-user"]', 'click', function () {
  const tableId = getElementparent(this, 8).id;
  const rowId = getElementparent(this, 2).id.replace(`${tableId}-item-`, '');
  const btnHtml = $(this).html();
  $(this).html(`<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>`);
  $(this).attr('disabled', true);
  messageDialog(
    'delete-user',
    'حذف الفرد',
    'هل أنت متأكد من حذف الفرد؟',
    (action) => {
      // if (action == 'نعم') socket.emit('delete-user', rowId);
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

$on('#edit-user .modal-footer button[action="user-save-edit"]', 'click', function() {
  const btn = $('#edit-user .modal-footer button[action="user-save-edit"]');
  btn.attr('disabled', true);
  btn.html(`
    <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
    يرجى الإنتظار...
  `);

  var userValues = {
    id: $('#edit-user #edit-user-id').html(),
    family_id: $('#edit-user .form-control[name="family_id"]').val(),
    firstname: $('#edit-user .form-control[name="firstname"]').val(),
    phone: $('#edit-user .form-control[name="phone"]').val(),
    email: $('#edit-user .form-control[name="email"]').val(),
    country: $('#edit-user .form-control[name="country"]').val(),
    state: $('#edit-user .form-control[name="state"]').val(),
    address: $('#edit-user .form-control[name="address"]').val(),
  };

  userValues.isMarried = $('#edit-user #edit-user-is-married')[0].checked;
  if (userValues.isMarried) {
    userValues.children = getMultiInputValues('#edit-user #edit-user-user-children')
  }
  userValues.job = $('#edit-user #edit-user-user-job select').val();
  if (userValues.job == 'worker') {
    setObjectValueIfExists(userValues, 'job_name', $('#edit-user #edit-user-user-job input[name="job_name"]').val());
    setObjectValueIfExists(userValues, 'job_address', $('#edit-user #edit-user-user-job input[name="job_address"]').val());
  } else if (userValues.job == 'student') {
    setObjectValueIfExists(userValues, 'student_collage', $('#edit-user #edit-user-user-job input[name="student_collage"]').val());
    setObjectValueIfExists(userValues, 'student_speciality', $('#edit-user #edit-user-user-job input[name="student_speciality"]').val());
    setObjectValueIfExists(userValues, 'student_level', $('#edit-user #edit-user-user-job input[name="student_level"]').val());
  }
  userValues.more_data = getMultiInputValues('#edit-user #edit-user-more-data');
  // socket.emit('update-user', userValues);
});

$on('.create-modal button.btn', 'click', function() {
  var inputs = $(getElementparent(this, 4)).find('.form-control');
  var userValues = {};
  for (let i = 0; i < inputs.length; i++) {
    const input = $(inputs[i]);
    userValues[input.attr('name')] = input.val();
  }
  console.log($(this).attr('action'), userValues)
  // socket.emit($(this).attr('action'), userValues);
});

$on('#users-add', 'keydown', (event) => {
  if (event.keyCode == 13) {
    $('#users-add button[action="add-user"]').focus();
  }
});

$on('#users-add button[action="add-user"]', 'click', async function() {
  $(this).html(`
    <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
    يرجى الإنتظار...
  `);
  $(this).attr('disabled', true);
  var userValues = {
    family_id: $('#users-add .form-control[name="family_id"]').val(),
    firstname: $('#users-add .form-control[name="firstname"]').val(),
    phone: $('#users-add .form-control[name="phone"]').val(),
    email: $('#users-add .form-control[name="email"]').val(),
    country: $('#users-add .form-control[name="country"]').val(),
    state: $('#users-add .form-control[name="state"]').val(),
    address: $('#users-add .form-control[name="address"]').val(),
  };

  userValues.isMarried = $('#is-married')[0].checked;
  if (userValues.isMarried) {
    userValues.children = getMultiInputValues('#user-children')
  }
  userValues.job = $('#users-add #user-job select').val();
  if (userValues.job == 'worker') {
    setObjectValueIfExists(userValues, 'job_name', $('#users-add #user-job input[name="job_name"]').val());
    setObjectValueIfExists(userValues, 'job_address', $('#users-add #user-job input[name="job_address"]').val());
  } else if (userValues.job == 'student') {
    setObjectValueIfExists(userValues, 'student_collage', $('#users-add #user-job input[name="student_collage"]').val());
    setObjectValueIfExists(userValues, 'student_speciality', $('#users-add #user-job input[name="student_speciality"]').val());
    setObjectValueIfExists(userValues, 'student_level', $('#users-add #user-job input[name="student_level"]').val());
  }
  userValues.more_data = getMultiInputValues('#users-add #more-data');
  // await delay(300);
  // socket.emit('create-user', userValues);
});
