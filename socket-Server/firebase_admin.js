var admin = require("firebase-admin");
// var fcm = require('fcm-notification');
var serviceAccount = require("./service-account.json");


admin.initializeApp({
  credential: admin.credential.cert(serviceAccount),
  // databaseURL: "https://factory-system-lite-default-rtdb.firebaseio.com"
  databaseURL: "firebase-adminsdk-n9td3@service-3b4a7.iam.gserviceaccount.com"
})

// const certPath = admin.credential.cert(serviceAccount);
// var FCM = new fcm(certPath);

// module.exports.admin = FCM
module.exports.admin = admin
