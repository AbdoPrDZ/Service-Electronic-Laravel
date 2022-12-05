

const SocketIO = require('socket.io');
const http = require('http');
const express = require('express');
const SocketClient = require('./socket_client');
const Environment = require('./environment');
const bodyParser = require('body-parser');
const { admin } = require('./firebase_admin');

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
    var client = Environment.clients[request.params.clientId];
    var success = false;
    var message = '';
    if(client) {
      var res = Environment.clients[request.params.clientId].emitToClient(request.params.routeName, request.body);
      success = res.success
      message = res.message;
    } else {
      success = false;
      message = 'Client Id not found';
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
    //         imageUrl: 'http://abdopr.ddns.net/file/currency-12',
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
        console.log(error);
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

      Environment.socket = SocketIO(socketServer, {
        cors: {
          origin: '*',
          allowEIO3: true,
        },
        allowEIO3: true,
      });
      Environment.socket.use(SocketClient.auth);

      Environment.socket.on('connection', (client) => {
        new SocketClient(client);
      });
    });
  });
}

main();
