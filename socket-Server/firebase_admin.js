var admin = require("firebase-admin");
// var serviceAccount = require("./service-account-my.json");
var serviceAccount = require("./service-account.json");


admin.initializeApp({
  credential: admin.credential.cert(serviceAccount),
  // databaseURL: "https://factory-system-lite-default-rtdb.firebaseio.com"
  databaseURL: "firebase-adminsdk-n9td3@service-3b4a7.iam.gserviceaccount.com"
})

module.exports.admin = admin
