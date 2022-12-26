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
    this.client.on('auth', (token) => {this.auth(token)});
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

  // static async auth(client, next) {
  //   var headers = client.handshake.headers;
  //   Object.assign(headers, Environment.requestHeaders)
  //   // headers['host'] = 'localhost';
  //   // headers['Content-Type'] = 'application/json; charset=UTF-8';
  //   // headers['Accept'] = 'application/json';
  //   try {
  //     var {data} =  await axios.get(
  //       `${Environment.configures.manager.app_url}${Environment.configures.manager.path}${Environment.configures.manager.onConnect}`
  //         .replace('{clientId}', client.id),
  //       {headers: headers}
  //     );
  //     console.log(data, headers);
  //     if(data['success']) {
  //       client.routes = data['routes'];
  //       client.clientId = data['clientId'];
  //       return next();
  //     }
  //   } catch (error) {
  //     console.error(error)
  //     return next(Error(error))
  //   }
  //   return next(new Error('Unauthenticated'));
  // }

  async auth(token) {
    // var headers = this.client.handshake.headers;
    var headers = {}
    Object.assign(headers, Environment.requestHeaders)
    headers['Authorization'] = `Bearer ${token}`;
    try {
      var {data} =  await axios.get(
        `${Environment.configures.manager.app_url}${Environment.configures.manager.path}${Environment.configures.manager.onConnect}`
          .replace('{clientId}', this.client.id),
        {headers: headers}
      );
      console.log(data);
      if(data['success']) {
        this.client.routes = data['routes'];
        this.client.clientId = data['clientId'];
        if(Environment.clients[data['clientId']]) delete(Environment.clients[data['clientId']])
        Environment.clients[data['clientId']] = this;
        return this.client.emit('auth-resualt', {success: true, message: 'successfully connecting'})
      }
    } catch (error) {
      console.error(error)
      return this.client.emit('auth-resualt', {success: false, message: error})
    }
    this.client.emit('auth-resualt', {success: false, message: 'Unauthenticated'})
  }
}

module.exports = SocketClient;
