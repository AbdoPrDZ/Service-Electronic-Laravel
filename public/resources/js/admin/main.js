function $_GET(parameterName) {
  var result = null,
      tmp = [];
  location.search
      .substr(1)
      .split("&")
      .forEach(function (item) {
        tmp = item.split("=");
        if (tmp[0] === parameterName) result = decodeURIComponent(tmp[1]);
      });
  return result;
}

function setObjectValueIfExists(object, key, value) {
  if (value) object[key] = value;
}

function $on(target, eventName, callback) {
  $(document).on(eventName, target, callback);
}

function delay(delayInms) {
  return new Promise(resolve => {
    setTimeout(() => {
      resolve(2);
    }, delayInms);
  });
}

function textfilter(value) {
  var invalidChars = ['>', '<', '/', '\\', '`'];
  invalidChars.forEach((char) => {
    if (value.indexOf(char) > -1) {
      value = value.replaceAll(char, '');
    }
  });
  return value.replace( /\r?\n/g, "\r\n" );
}

function padTo2Digits(num) {
  return num.toString().padStart(2, '0');
}

function formatDate(date) {
  return (
    [
      date.getFullYear(),
      padTo2Digits(date.getMonth() + 1),
      padTo2Digits(date.getDate()),
    ].join('-') +
    ' ' +
    [
      padTo2Digits(date.getHours()),
      padTo2Digits(date.getMinutes()),
      padTo2Digits(date.getSeconds()),
    ].join(':')
  );
}

function getElementparent(element, index = 1) {
  for (let i = 0; i < index; i++) {
    element = $(element).parent().get(0);
  }
  return element;
}

function getElementChild(element, name, pos = null) {
  var child;
  var names = name.split('/');
  var poss = pos ? pos.split('/') : null;
  child = $(element).find(names[0]).get(poss && poss[0] ? poss[0] : 0);
  if (names.length > 1) {
    child = getElementChild(
      child,
      names.slice(1, names.length).join('/'),
      poss && poss.length > 1 ? poss.slice(1, poss.length).join('/') : null
    );
  }
  return child;
}

function getTableBodyItems(table) {
  var trs = $(getElementChild(table, 'tbody')).children();
  var items = [];
  for (let i = 0; i < trs.length; i++) {
    var input = getElementChild(trs[i], 'td/span/input', '0/0/0');
    if (input) items.push(input);
  }
  return items;
}

async function displayBodyContent() {
  await delay(800)
  $('#body-loading').attr('class', 'loading hide');
  $('#body-content').attr('class', 'show');
}

function initTable(table) {
  if ($.fn.dataTable.isDataTable(table)) {
    return $(table).DataTable();
  }
  return $(table).DataTable({
    bLengthChange: false,
    language: {
      emptyTable: 'لا توجد بيانات',
      info: "تم عرض من _START_ إلى _END_ من أصل _TOTAL_ عنصر",
      infoFiltered: " - تم إيجاد _TOTAL_ من أصل _MAX_ عنصر",
      infoEmpty: "لا توجد بيانات لعرضها",
      paginate: {
        next: 'التالي',
        previous: 'السابق'
      },
      sSearch: '',
      searchPlaceholder: 'للبحث من هنا',
      zeroRecords: 'لا توجد نتائج مطابقة'
    },
    initComplete: function () {
      this.find('tfoot th[search="true"]').each(function () {
        $(this).html(`<input type="text" class="form-control" placeholder="${$(this).text()}" />`);
      });
      this.api()
          .columns()
          .every(function () {
              var that = this;
              $('input', this.footer()).on('keyup change clear', function () {
                  if (that.search() !== this.value) {
                      that.search(this.value).draw();
                  }
              });
          });
    },
    drawCallback: function name() {
      this.api()
          .columns()
          .every(function () {
              var that = this;
              var header = $(this.header());
              var columnHtml = header.html();
              if (columnHtml.indexOf('<div') > -1) columnHtml = columnHtml.split('<div')[0].replaceAll('\n', '');
              if (header.attr('class').indexOf('sorting_asc') != -1 && header.attr('class').indexOf('no-sort') == -1) {
                header.html(`${columnHtml}\
                  <div class="sorting-header">
                    <span class="material-symbols-sharp">
                    keyboard_arrow_down
                    </span>
                  </div>
                `);
              } else if (header.attr('class').indexOf('sorting_desc') != -1 && header.attr('class').indexOf('no-sort') == -1) {
                header.html(`${columnHtml}\
                  <div class="sorting-header">
                    <span class="material-symbols-sharp">
                    keyboard_arrow_up
                    </span>
                  </div>
                `);
              } else if (header.attr('class').indexOf('no-sort') == -1) {
                header.html(columnHtml);
              }
          });
    }
  });
}

function messageDialog(dialogId, title, message, onActionClick, buttons = {OK: 'primary'}, cancelBtn = 'Cancel', onHidden = null) {
  dialogId = `${dialogId}-${Date.now()}`;
  $('#message-dialog-modal').attr('dialog-id', dialogId);
  $('#message-dialog-modal .modal-title').html(title);
  $('#message-dialog-modal .modal-body').html(message);
  var btnsHtml = '';
  for (const action in buttons) {
    btnsHtml += `<button type="button" class="btn btn-${buttons[action]}" action="${dialogId}-${action}">${action}</button>\n`;
    $on(`#message-dialog-modal[dialog-id="${dialogId}"] button[action="${dialogId}-${action}"]`, 'click', (_) => {
      onActionClick(action.replace(`${dialogId}-`, ''));
    });
  }
  btnsHtml += `<button type="button" class="btn btn-danger" action="close">${cancelBtn}</button>`
  $(`#message-dialog-modal .modal-footer`).html(btnsHtml);
  $('#message-dialog-modal').modal('show');

  $on(`#message-dialog-modal[dialog-id="${dialogId}"]`, 'onModalHidden', () => {
    if (onHidden) onHidden();
  });
}

async function alertMessage(alertId, title, message, type = 'success', dismissingAfter = 2500) {
  var alertHtml = `
  <div id="${alertId}" class="alert alert-${type} alert-dismissible fade show" role="alert">
    <strong>${title}</strong> ${message}
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
      <span aria-hidden="true">&times;</span>
    </button>
  </div>`;
  $('#alerts').append(alertHtml);
  $(`#alerts #${alertId}`).alert();
  if (dismissingAfter) {
    await delay(dismissingAfter);
    $(`#alerts #${alertId}`).alert('close');
  }
}

function initMultiInputWidget(element) {
  var inputs = JSON.parse($(element).attr('inputs'));
  var inputsHtml = '';
  inputs.forEach((input) => {
    inputsHtml += `<input type="${input.type}" name="${input.name}" class="form-control" placeholder="${input.text}">`;
  });
  inputsHtml += `<button class="btn btn-primary" action="add">${$(element).attr('add-btn-text') || 'إضافة'}</button>`;
  $(element).find('.multi-input-header').html(inputsHtml);
}

function addMultiInputItem(element, itemValues) {
  var itemValueHtml = '';
  for (const name in itemValues) {
    const value = itemValues[name];
    if (value == '') return;
    itemValueHtml += `<span class="multi-input-row-item" name="${name}">${value}</span>`;
  }
  itemValueHtml += `<button class="btn btn-icon" action="remove"><span class="material-symbols-sharp">close</span></button>`;
  $(element).find('.multi-input-body').append(`<div class="multi-input-row">${itemValueHtml}</div>`);
}

function getMultiInputValues(element) {
  const body = $(element).find('.multi-input-body').get(0);
  var values;
  if (body) {
    var rows = $(body).find('.multi-input-row');
    values = [];
    for (let i = 0; i < rows.length; i++) {
      const row = rows[i];
      var rowValues = {};
      var items = $(row).find('.multi-input-row-item');
      for (let j = 0; j < items.length; j++) {
        const item = items[j];
        rowValues[$(item).attr('name')] = $(item).text();
      }
      values.push(rowValues);
    }
  }
  return values;
}

$(document).ready(function() {
  $('[data-toggle="tooltip"]').tooltip();
  if ($('body').attr('auto-display') != 'false') {
    displayBodyContent();
  }
  var multiInputs = $('.multi-input');
  for (let i = 0; i < multiInputs.length; i++) {
    initMultiInputWidget(multiInputs[i]);
  }
});

$on('.multi-input-header button[action="add"]', 'click', function() {
  const element = getElementparent(this, 2);
  var inputs = $(element).find('.multi-input-header input');
  var itemValue = {};
  for (let i = 0; i < inputs.length; i++) {
    const input = inputs[i];
    const name = $(input).attr('name');
    const value = $(input).val();
    itemValue[name] = value;
  }
  addMultiInputItem(element, itemValue);
  $(element).find('.multi-input-header input').val('');
});

$on('.multi-input-body .multi-input-row button[action="remove"]', 'click', function() {
  $(this).parent().remove();
});

$on('table thead input[type="checkbox"].select-all', 'click', function() {
  var table = getElementparent(this, 9);
  if ($(table).attr('class').indexOf('custom-table') == -1) {
    console.error('invalid custom table');
    return;
  }
  $(table).find('tbody input[type="checkbox"]').prop('checked', this.checked);
  $(table).find('.custom-table-header .custom-table-header-actions .btn.need-select').attr('disabled', !this.checked);
});

$on('table tbody input[type="checkbox"]', 'click', function() {
  var table = getElementparent(this, 9);
  var items = $(table).find('table tbody input[type="checkbox"]');
  var enableActions = false;
  var checkedAll = true;
  for (let i = 0; i < items.length; i++) {
    if (!enableActions && items[i].checked || this.checked) enableActions = true;
    if (!items[i].checked) {
      checkedAll = false;
      break;
    }
  }
  $(table).find('table thead .select-all[type="checkbox"]').prop('checked', checkedAll);
  $(table).find('.custom-table-header .custom-table-header-actions .btn.need-select').attr('disabled', !enableActions);
});

$on('.custom-table table tbody tr .btn', 'click', function() {
  var tableId = $(getElementparent(this, 8)).attr('id');
  var row = getElementparent(this, 2);
  var action = $(this).attr('action');
  $('.custom-table').trigger('onCustomTableRowActionClick', [tableId, action, row]);
  $('.custom-table').trigger(`onCustomTableRowActionClick[action="${action}"]`, [tableId, row]);
  $('.custom-table').trigger(`onCustomTableRowActionClick[id="${tableId}", action="${action}"]`, [row]);

  $(`#${tableId}`).trigger('onRowActionClick', [action, row]);
  $(`#${tableId}`).trigger(`onRowActionClick[action="${action}"]`, [row]);
});

$on('.text-copy', 'mouseover', function() {
  $(this).attr('data-toggle', 'tooltip')
         .attr('data-placement', 'top')
         .attr('title', `Tab to copy "${$(this).attr('value')}"`);
});

$on('.text-hint', 'mouseover', function() {
  $(this).attr('data-toggle', 'tooltip')
         .attr('data-placement', 'top')
         .attr('title', $(this).attr('value'));
});

$on('.text-copy', 'click', function() {
  var $temp = $("<input>");
  $("body").append($temp);
  $temp.val($(this).attr('value')).select();
  document.execCommand("copy");
  $temp.remove();
});

$on('.modal .modal-content .modal-footer button.btn', 'click', function() {
  var modal = getElementparent(this, 4);
  var modalId = $(modal).attr('id');
  var action = $(this).attr('action');
  var inputs = $(`#${modalId} .form-control`);
  var modalValues = {};
  for (let i = 0; i < inputs.length; i++) {
    modalValues[inputs[i].name] = inputs[i].value;
  }
  $('.modal').trigger('onModalActionClick', [modalId, action, modalValues]);
  $('.modal').trigger(`onModalActionClick[id="${modalId}" action="${action}"]`, [modalValues]);
  $('.modal').trigger(`onModalActionClick[action="${action}"]`, [modalId, modalValues]);
  $('.modal').trigger(`onModalActionClick`, [modalId, action, modalValues]);
  $('.modal').trigger(`onModalActionClick[action="${action}"]`, [modalId, modalValues]);
  $(`#${modalId}`).trigger('onActionClick', [action, modalValues]);
  $(`#${modalId}`).trigger(`onActionClick[action="${action}"]`, [modalValues]);
  $(`#${modalId}`).trigger(`onActionClick`, [action, modalValues]);
  $(`#${modalId}`).trigger(`onActionClick[action="${action}"]`, [modalValues]);
});

$on('.modal', 'hidden.bs.modal', function (e) {
  const modalId = $(this).attr('id');
  $(`#${modalId}`).trigger('onModalHidden');
  $(`.modal`).trigger(`onModalHidden[id="${modalId}"]`);
});

$on('.modal', 'onModalActionClick[action="close"]', function(_, modalId) {
  $(`#${modalId}`).modal('hide');
});

$on('.dropdown.dropdown-selecetion .dropdown-menu .dropdown-item', 'click', function() {
  var dropdownId = $(getElementparent(this, 2)).attr('id');
  var itemValue = $(this).attr('value');
  $(`.dropdown.dropdown-selecetion`).trigger('onDropDownSelect', [dropdownId, itemValue, this]);
  $('.dropdown.dropdown-selecetion').trigger(`onDropDownSelect[id="${dropdownId}"]`, [itemValue, this]);
  $('.dropdown.dropdown-selecetion').trigger(`onDropDownSelect[id="${dropdownId}", value="${itemValue}"]`, [this]);
  $('.dropdown.dropdown-selecetion').trigger(`onDropDownSelect[value="${itemValue}"]`, [dropdownId, this]);
  $(`#${dropdownId}`).trigger('onSelect', [itemValue, this]);
  $(`#${dropdownId}`).trigger(`onSelect[value="${itemValue}"]`, [this]);
});

$on('.text-url', 'click', function() {
  var url = $(this).attr('value');
  window.open(url, '_blank');
});

$on('.select-group-tabs-select', 'change', function() {
  var selectGroupId = getElementparent(this, 1).id;
  $(`#${selectGroupId} .select-group-tabs-item`).removeClass('selected');
  $(`#${selectGroupId} .select-group-tabs-item[for="${this.value}"]`).addClass('selected');
});

$on('input', 'change, keyup', function() {
  this.value = textfilter(this.value);
});
$on('textarea', 'change, keyup', function() {
  this.value = textfilter(this.value);
});

$.valHooks.input = {
  get: function(element) {
    return textfilter(element.value);
  },
  set: function(el, val) {
    el.value = textfilter(val);
    return el
  }
};
$.valHooks.textarea = {
  get: function(element) {
    return textfilter(element.value);
  },
  set: function(el, val) {
    console.log(el.value)
    el.value = textfilter(val);
    return el
  }
};