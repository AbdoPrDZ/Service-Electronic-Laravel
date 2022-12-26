const axios = require('axios');
const socketIO = require('socket.io');
const Environment = require('./environment');

class SocketClient {

  room;
  client;
  clientId;
  authed = false;
  routes = [];

  constructor(room, client) {
    this.room = room;
    this.client = client;
    this.clientId = client.clientId;
    this.client.on('event', (routeName, ...args) => {
      if(!authed) throw new Error('Unauthenticated');
      return this.emitToServer(routeName, ...args);
    });
    this.client.on('auth', (token) => {
      if(this.authed) throw new Error('already authed');
      return this.auth(token)
    });
    this.client.on('disconnect', () => {
      return this.disconnect()
    });
  }

  emitToClient(eventName, ...args) {
    try {
      this.client.emit(eventName, ...args);
      return {
        success: true,
        messaage: 'Successfully emiting',
      }
    } catch (error) {
      return {
        success: false,
        messaage: 'Invalid Client',
      }
    }
  }

  async emitToServer(routeName, ...args) {
    var headers = this.client.handshake.headers;
    Object.assign(headers, Environment.requestHeaders)
    headers[Environment.configures.manager.rooms[this.room].auth.auth_type] = token;
    try {
      console.log(`${Environment.configures.manager.app_url}${Environment.configures.manager.path}${Environment.configures.manager.from_bridge}`);
      var {data} =  await axios.post(
        `${Environment.configures.manager.app_url}${Environment.configures.manager.path}${Environment.configures.manager.from_bridge}`
          .replace('{room}', this.room)
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

  async auth(token) {
    this.client.handshake.headers[Environment.configures.manager.rooms[this.room].auth.auth_type] = token;
    var headers = this.client.handshake.headers;
    Object.assign(headers, Environment.requestHeaders)
    try {
      console.log(`${Environment.configures.manager.app_url}${Environment.configures.manager.path}/${this.room}${Environment.configures.manager.onConnect}`.replace('{clientId}', this.client.id));
      var {data} =  await axios.get(
        `${Environment.configures.manager.app_url}${Environment.configures.manager.path}/${this.room}${Environment.configures.manager.onConnect}`
          .replace('{clientId}', this.client.id),
        {headers: headers}
      );
      console.log(data);
      if(data['success']) {
        this.client.routes = data['routes'];
        this.client.clientId = data['clientId'];
        if(!Environment.clients[this.room]) Environment.clients[this.room] = {};
        if(Environment.clients[this.room][data['clientId']]) delete(Environment.clients[this.room][data['clientId']])
        Environment.clients[this.room][data['clientId']] = this;
        this.authed = true;
        return this.client.emit('auth-resualt', {success: true, message: 'successfully connecting'})
      }
    } catch (error) {
      console.error(error)
      return this.client.emit('auth-resualt', {success: false, message: error})
    }
    this.client.emit('auth-resualt', {success: false, message: 'Unauthenticated'})
  }

  async disconnect() {
    var headers = this.client.handshake.headers;
    Object.assign(headers, Environment.requestHeaders)
    try {
      var {data} =  await axios.get(
        `${Environment.configures.manager.app_url}${Environment.configures.manager.path}/${this.room}${Environment.configures.manager.onDisconnect}`
          .replace('{clientId}', this.client.id),
        {headers: headers}
      );
      console.log('data:', data);
    } catch (error) {
      console.log(error);
    }
    this.client.handshake.headers[Environment.configures.manager.rooms[this.room].auth.auth_type] = null;
    delete(Environment.clients[this.room][this.clientId]);
    delete(this.client);
    this.authed = false;
  }

}

module.exports = SocketClient;
