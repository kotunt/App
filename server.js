const WebSocket = require('ws');

const wss = new WebSocket.Server({ port: 3000 });

console.log('WebSocket server started on port 3000');

wss.on('connection', function connection(ws) {
  console.log('A new client connected!');

  ws.on('message', function incoming(message) {
    console.log('received: %s', message);
  });

  ws.send('Welcome to the WebSocket server!');
});