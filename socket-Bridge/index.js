

const SocketIO = require('socket.io');
const http = require('http');
const express = require('express');
const SocketClient = require('./socket_client');
const Environment = require('./environment');
const bodyParser = require('body-parser');
const { admin } = require('./firebase_admin');
const { env } = require('process');
require('dotenv').config({path: '../.env'});

function hostMiddleware(host = '*', port = '*') {
  return (request, response, next) => {
    var requestHost = request.headers.host;
    var requestPort = '*'
    if(requestHost.indexOf(':')) {
      requestHost = request.headers.host.split(':')[0];
      requestPort = request.headers.host.split(':')[1];
    }
    // console.log(host, port);
    // console.log(requestHost, requestPort);
    // console.log(request.headers['connection-key'])
    if((host == '*' || host == requestHost) && (port == '*' || port == requestPort)) {
      return next();
    }
    return response.status(404);
  };
}

async function main() {
  await Environment.getConfigures();

  Environment.app = express();
  Environment.socketApp = express();
  Environment.app.use(bodyParser.urlencoded({ extended: false }));
  Environment.app.use(bodyParser.json());
  Environment.app.use(hostMiddleware('localhost'));

  const server = http.createServer(Environment.app);
  const socketServer = http.createServer(Environment.socketApp);

  // emit to client route
  Environment.app.post(`${Environment.configures.bridge.path}${Environment.configures.bridge.from_manager}`, (request, response) => {
    var success = false;
    var message = '';
    if(Environment.clients[request.params.room]) {
      const client = Environment.clients[request.params.room][request.params.clientId];
      if(client) {
        console.log(request.params.routeName);
        const response = client.emitToClient(request.params.routeName, request.body);
        console.log(response);
        success = response.success
        message = response.message;
      } else {
        message = 'Undifinde Client Id';
      }
    } else {
      message = 'Undifinde room';
    }
    return response.json({
      success: success,
      message: message,
    });
  });

  // emit notification to client
  Environment.app.post(`${Environment.configures.bridge.path}${Environment.configures.bridge.emit_notification}`, (request, response) => {
    const notification = JSON.parse(request.body[0]);
    console.log(notification);
    var success = false;
    var message = '';
    if(notification) {
      if(Environment.clients[request.params.room]) {
        const client = Environment.clients[request.params.room][request.params.clientId];
        if(client) {
          const response = client.emitToClient('notifications', notification);
          console.log(response);
          success = response.success
          message = response.message;
        } else {
          message = 'Undifinde Client Id';
        }
      } else {
        message = 'Undifinde room';
      }
    }
    return response.json({
      success: success,
      message: message,
    });
  });

  // push notification to client
  Environment.app.post(`${Environment.configures.bridge.path}${Environment.configures.bridge.push_notification}`, async (request, response) => {
    const notification = JSON.parse(request.body[0]);
    console.log(notification);
    var success = false;
    var message = '';
    if(notification) {
      try {
        console.log(notification)
        const message  = {
          'token': notification.client.messaging_token,
          'notification': {
            'title': notification.title,
            'body': notification.message
          },
          'android': {
            'notification': {
              'priority': "high",
              'icon': 'stock_ticker_update',
              'sound': "default",
              'color': '#7e55c3',
              // 'imageUrl': env('APP_URL') + '/storage/defaults/logo.png',
            }
          },
          'data': {
            'notification_id': `${notification.id}`,
            ...notification.data
          }
        }
        console.log(message);
        const messagingResponse =  await admin.messaging().send(message);
        success =  true,
        message = 'Successfully sending message'
        console.log(messagingResponse);
      } catch (error) {
        console.log('error: ', error);
        message= error;
      }
    } else {
      message = 'Undefined notification';
    }
    return response.json({
      success: success,
      message: message,
    });
  });

  server.listen(Environment.configures.bridge.port, Environment.configures.bridge.host, (error) => {
    if(error) console.error(error)
    console.log('server started\n');
    console.log('server routes: ');
    var i = 1;
    Environment.app._router.stack.forEach(layer => {
      if (layer.route) {
        console.log(`${i}- '${layer.route.path}'`);
        i++;
      }
    });
    console.log('');
    socketServer.listen(Environment.configures.bridge.socket.port, Environment.configures.bridge.socket.host, async (error) => {
      if(error) console.error(error)

      for (const room in Environment.configures.bridge.rooms) {
        const path = Environment.configures.bridge.rooms[room];
        Environment.socket = SocketIO(socketServer, {
          cors: {
            origin: '*',
            allowEIO3: true,
          },
          allowEIO3: true,
          path: path
        });
        Environment.socket.on('connection', (client) => {
          new SocketClient(room, client);
        });
      }
    });
  });
}

main();
