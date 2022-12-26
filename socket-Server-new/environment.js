const util = require('util');
const exec = util.promisify(require('child_process').exec);

class Environment {
  static socketApp;
  static app;
  static socket;
  static clients = {}
  static configures = {};
  static requestHeaders = {
    'Content-Type':'application/json; charset=UTF-8',
    Accept:'application/json; charset=UTF-8',
  };

  static async getConfigures() {
    Environment.configures = JSON.parse((await exec('php -r "echo json_encode(include \'config/socket_bridge_new.php\');"')).stdout);
    Environment.requestHeaders.host = Environment.configures.bridge.host;
    Environment.requestHeaders['Connection-Key'] = Environment.configures.connection_key;
  }
}

module.exports = Environment;
