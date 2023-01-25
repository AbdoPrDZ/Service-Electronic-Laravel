const axios = require('axios');
const Environment = require('./environment');

class SocketClient {

  room;
  client;
  authed = false;
  routes = [];

  constructor(room, client) {
    this.room = room;
    this.client = client;
    this.client.onAny((event, ...args) => {
      if(event == 'auth') {
        if(this.authed) return new Error('already authed');
        return this.auth(...args);
      } else if(event == 'disconnect') {
        return this.disconnect()
      } else {
        if(!this.authed) return new Error('Unauthenticated');
        if(this.client.routes.indexOf(event) == -1) return new Error(`Undefined event(${event})`);
        return this.emitToServer(event, ...args);
      }
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
      console.log('SocketClient:35', error);
      return {
        success: false,
        messaage: 'Invalid Client',
      }
    }
  }

  async emitToServer(routeName, ...args) {
    var headers = this.client.handshake.headers;
    Object.assign(headers, Environment.requestHeaders)
    try {
      const {data} = await axios.post(
        `${Environment.configures.manager.app_url}${Environment.configures.manager.path}/${this.room}${Environment.configures.manager.from_bridge}`
          .replace('{clientId}', this.client.id)
          .replace('{routeName}', routeName),
        {args: args},
        {headers: headers},
      );
      return data;
    } catch (error) {
      console.error('SocketClient:55', error);
      return new Error(error);
    }
  }

  async auth(token) {
    this.client.handshake.headers[Environment.configures.manager.rooms[this.room].auth.auth_type] = token;
    var headers = this.client.handshake.headers;
    Object.assign(headers, Environment.requestHeaders)
    try {
      const {data} =  await axios.get(
        `${Environment.configures.manager.app_url}${Environment.configures.manager.path}/${this.room}${Environment.configures.manager.onConnect}`
          .replace('{clientId}', this.client.id),
        {headers: headers}
      );
      if(data['success']) {
        this.client.routes = data['routes'];
        this.client.id = data['clientId'];
        if(!Environment.clients[this.room]) Environment.clients[this.room] = {};
        if(Environment.clients[this.room][data['clientId']]) delete(Environment.clients[this.room][data['clientId']])
        Environment.clients[this.room][data['clientId']] = this;
        this.authed = true;
        return this.client.emit('auth-resualt', {success: true, message: 'successfully connecting'})
      }
    } catch (error) {
      console.error('SocketClient:82', error)
      return this.client.emit('auth-resualt', {success: false, message: error})
    }
    this.client.emit('auth-resualt', {success: false, message: 'Unauthenticated'})
  }

  async disconnect() {
    var headers = this.client.handshake.headers;
    Object.assign(headers, Environment.requestHeaders)
    try {
      const {data} = await axios.get(
        `${Environment.configures.manager.app_url}${Environment.configures.manager.path}/${this.room}${Environment.configures.manager.onDisconnect}`
          .replace('{clientId}', this.client.id),
        {headers: headers}
      );
    } catch (error) {
      console.log('SocketClient:99', error);
    }
    delete(Environment.clients[this.room][this.client.id]);
    delete(this.client);
    this.authed = false;
  }

}

module.exports = SocketClient;
