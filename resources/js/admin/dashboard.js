// import {createApp} from 'vue';
// import io from 'socket.io-client';
// const dashboardApp = createApp({
//   el: "#admin-dashboard"
// });

// const socket = io.connect(':8000', {
//   path: '/admin/',
//   autoConnect: false,
// });

// window.statuses = {
//   accepted: '<span class="success">تم الموفقة</span>',
//   refused: '<span class="danger">تم الرفض</span>',
//   checking: '<span class="warning">قيد التحقق</span>',
//   not_verifited: '<span class="danger">غير محققة</span>',
//   verifited: '<span class="success">تم التحقق</span>',
//   waiting: '<span class="warning">في إنتظار موافقة البائع</span>',
//   seller_accept: '<span class="success">تم الموافقة من طرف البائع</span>',
//   seller_refuse: '<span class="danger">تم الرفض من طرف البائع</span>',
//   waiting_client_accept: '<span class="warning">في إنتظار موافقة العميل</span>',
//   client_accept: '<span class="success">تم الموافقة من طرف العميل</span>',
//   client_refuse: '<span class="danger">تم الرفض من طرف العميل</span>',
//   received: '<span class="success">تم الاستلام</span>',
//   admin_ansowred: '<span class="success">تم الرد من طرف المدير</span>',
//   waiting_admin_accept: '<span class="warning">في إنتظار موافقة المدير</span>',
//   admin_accept: '<span class="success">تمت الموافقة من طرف المدير</span>',
//   admin_refuse: '<span class="success">تم الرفض من طرف المدير</span>'
// };

// window.tabsTables = {
//   users: [['all-users', 'users'], ['all-users-recharges', 'recharges'], ['all-users-withdrawes', 'withdrawes']],
//   sellers: [['all-sellers', 'sellers'], ['all-new-sellers', 'new_sellers']],
//   transfers: [['all-transfers', '*']],
//   currencies: [['all-currencies', '*']],
//   products: [['all-products', 'products'], ['all-categories', 'categories']],
//   purchases: [['all-purchases', '*']],
//   offers: [['all-offers', 'offers'], ['all-offer-requests', 'offer_requests']],
//   mails: [['all-mails', 'mails'], ['all-templates', 'mails_templates']],
// }

// window.news = {
//   'users': 0,
//   'sellers': 0,
//   'transfers': 0,
//   'currencies': 0,
//   'products': 0,
//   'purchases': 0,
//   'offers': 0,
//   'mails': 0,
//   'settings': 0,
// };

// var connected = false;
// $(document).ready(function() {
//   socket.connect();
//   socket.on('connect_error', (err) => {
//     console.log(err)
//     $('#body-loading .loading-message').css('display', 'block').html(`خطأ في الإتصال:<br> ${err.toString().replace('Error:', '')}`);
//     socket.disconnect();
//   });
//   socket.on('disconnect', function () {
//     console.log('Disconnected');
//     window.location.href = "./admin/logout";
//     connected = false;
//   });
//   socket.on('connect_failed', (err) => {
//     console.log(`Connect failed: ${err}`);
//     socket.disconnect();
//   });
//   socket.on('connect', (a) => {
//     if(!connected) socket.emit('auth', `${$('meta[name="socket-token"]').attr('content')}`)
//   });
//   socket.on('auth-resualt', (args) => {
//     if(args.success) {
//       connected = true;
//       window.onConnect();
//     } else {
//       $('#body-loading .loading-message').css('display', 'block').html(`خطأ في الإتصال:<br> ${args.message ?? args.error}`)
//       socket.disconnect();
//     };
//   });
//   socket.on('new-transfer-created', async (notification) => {
//     console.log(notification)
//     var args = JSON.parse(notification.args)
//     console.log(args);
//     loadTab('transfers');
//     loadNews('transfers')
//     loadNotifications();
//     alertMessage("new-notification", notification.title, notification.message, 'success', 60000);
//   });
//   socket.on('new-product-solded', async (notification) => {
//     console.log(notification)
//     var args = JSON.parse(notification.args)
//     console.log(args);
//     loadTab('purchases');
//     loadNews('purchases')
//     loadNotifications();
//     alertMessage("new-notification", notification.title, notification.message, 'success', 60000);
//   });
//   socket.on('notifications', (notification) => {
//     console.log(notification)
//     alertMessage("new-notification", notification.title, notification.message, '');
//   });
// });
