const axios = require('axios');
const socketIO = require('socket.io');
const Environment = require('./environment');

class SocketClient {

  client;
  clientId;
  routes = [];

  constructor(client) {
    this.client = client;
    this.clientId = client.clientId;
    this.client.on('event', (routeName, ...args) => {
      return this.emitToServer(routeName, ...args);
    });
    if(Environment.clients[this.clientId]) delete(Environment.clients[this.clientId])
    Environment.clients[this.clientId] = this;
  }

  emitToClient(eventName, ...args) {
    this.client.emit(eventName, ...args);
    return {
      success: true,
      messaage: 'Successfully emiting',
    }
  }

  async emitToServer(routeName, ...args) {
    var headers = this.client.handshake.headers;
    Object.assign(headers, Environment.requestHeaders)
    // headers['host'] = 'localhost';
    // headers['Content-Type'] = 'application/json; charset=UTF-8';
    // headers['Accept'] = 'application/json';
    try {
      console.log(`${Environment.configures.manager.app_url}${Environment.configures.manager.path}${Environment.configures.manager.from_bridge}`);
      var {data} =  await axios.post(
        `${Environment.configures.manager.app_url}${Environment.configures.manager.path}${Environment.configures.manager.from_bridge}`
          .replace('{clientId}', this.clientId)
          .replace('{routeName}', routeName),
        {args: args},
        {headers: headers},
      );
      console.log('emittoserver:', data);
    } catch (error) {
      console.error(error)
      return Error(error);
    }
  }

  static async auth(client, next) {
    var headers = client.handshake.headers;
    Object.assign(headers, Environment.requestHeaders)
    // headers['host'] = 'localhost';
    // headers['Content-Type'] = 'application/json; charset=UTF-8';
    // headers['Accept'] = 'application/json';
    try {
      var {data} =  await axios.get(
        `${Environment.configures.manager.app_url}${Environment.configures.manager.path}${Environment.configures.manager.onConnect}`
          .replace('{clientId}', client.id),
        {headers: headers}
      );
      console.log(data);
      if(data['success']) {
        client.routes = data['routes'];
        client.clientId = data['clientId'];
        return next();
      }
    } catch (error) {
      console.error(error)
      return next(Error(error))
    }
    return next(new Error('Unauthenticated'));
  }
}

module.exports = SocketClient;
