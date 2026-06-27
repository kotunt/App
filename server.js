const { WebSocketServer } = require('ws');
const { createClient } = require('redis');

const wss = new WebSocketServer({ port: 8080 });
const redisSubscriber = createClient({
    // The 'redis' hostname comes from docker-compose
    url: 'redis://redis:6379'
});

const REALTIME_CHANNEL = 'realtime_updates';

async function setup() {
    redisSubscriber.on('error', (err) => console.error('Redis Client Error', err));
    await redisSubscriber.connect();
    console.log('Connected to Redis for subscribing.');

    // Subscribe to the channel
    await redisSubscriber.subscribe(REALTIME_CHANNEL, (message) => {
        console.log(`Received message from ${REALTIME_CHANNEL}:`, message);
        // Broadcast the message to all connected WebSocket clients
        wss.clients.forEach(client => {
            if (client.readyState === client.OPEN) {
                client.send(message);
            }
        });
    });

    console.log(`Subscribed to Redis channel: ${REALTIME_CHANNEL}`);
}

wss.on('connection', ws => {
    console.log('Client connected');

    ws.on('message', message => {
        // For now, we just log messages from clients.
        // You could implement logic here if clients need to send data.
        console.log('Received from client:', message.toString());
    });

    ws.on('close', () => {
        console.log('Client disconnected');
    });

    ws.on('error', (error) => {
        console.error('WebSocket error:', error);
    });
});

console.log('WebSocket server is running on port 8080');
setup().catch(console.error);