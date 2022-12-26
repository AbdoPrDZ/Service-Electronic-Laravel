

const SocketIO = require('socket.io');
const http = require('http');
const express = require('express');
const SocketClient = require('./socket_client');
const Environment = require('./environment');
const bodyParser = require('body-parser');
const { admin } = require('./firebase_admin');
const { exit } = require('process');
const cors = require("cors");
require('dotenv').config({path: '../.env'});

function hostMiddleware(host = '*', port = '*') {
  return (request, response, next) => {
    var requestHost = request.headers.host;
    var requestPort = '*'
    if(requestHost.indexOf(':')) {
      requestHost = request.headers.host.split(':')[0];
      requestPort = request.headers.host.split(':')[1];
    }
    console.log(host, port);
    console.log(requestHost, requestPort);
    console.log(request.headers['connection-key'])
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
  // Environment.app.use(bodyParser());
  Environment.app.use(bodyParser.urlencoded({ extended: false }));
  Environment.app.use(bodyParser.json());
  Environment.app.use(hostMiddleware('localhost'));

  var server = http.createServer(Environment.app);
  var socketServer = http.createServer(Environment.socketApp);

  // emit to client route
  Environment.app.post(`${Environment.configures.bridge.path}${Environment.configures.bridge.from_manager}`, (request, response) => {
    var success = false;
    var message = '';
    var room = request.params.room;
    var clientId = `${request.params.clientId}`;
    var roomClients = Environment.clients[room];
    if(roomClients) {
      console.log(roomClients, clientId)
      var client = roomClients[clientId];
      console.log(client)
      if(client) {
        console.log(request.params.routeName);
        var res = client.emitToClient(request.params.routeName, request.body);
        console.log(res);
        success = res.success
        message = res.message;
      } else {
        message = 'Client Id not found';
      }
    } else {
      message = 'Undifinde room';
    }
    return response.json({
      success: success,
      message: message,
    });
  });

  // push notification to client
  Environment.app.post(`${Environment.configures.bridge.path}${Environment.configures.bridge.push_notification}`, (request, response) => {
    const data = request.body;
    var message = JSON.parse(data[0]);
    console.log(message);
    // {
    //   message: {
    //     token: client.handshake.headers.messagingtoken,
    //     notification: {
    //       title: "Notification Title",
    //       body: "Notification Body ",
    //     },
    //     android: {
    //       notification: {
    //         priority: "high",
    //         icon: 'stock_ticker_update',
    //         sound: "default",
    //         color: '#7e55c3',
    //         imageUrl: 'http://abdopr.ddns.net/file/currency-4',
    //       }
    //     },
    //     data: {
    //       Nick: "Mario",
    //       Room: "PortugalVSDenmark",
    //     },
    //   },
    // };

    if(message) {
      admin.messaging().send(message).then(messagingResponse => {
        console.log(messagingResponse);
        response.json({
          success: true,
          message: 'Successfully sending message'
        });
      }).catch(error => {
        console.log('error: ', error);
        response.json({
          success: false,
          message: error,
        });
      });

    }
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
