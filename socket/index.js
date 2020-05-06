'use strict'
//requiriendo dependencias
/*pm2 start index.js --name "node-chat"
pm2 start node_modules/react-scripts/bin/react-scripts.js --name "reacj_frontend-chat" -- start
*/

/const ENVIRONMENT	=	'development';
//const ENVIRONMENT	=	'production';

const fs = require('fs');
const http = require('http');
const https = require('https');
const express = require('express')
const socketio = require('socket.io')
const bodyParser = require('body-parser');
const cors = require('cors')
const app = express()//instancia de express

app.use(cors())

// Certificate
if (ENVIRONMENT=='production') {
	const privateKey = fs.readFileSync('/etc/letsencrypt/live/colombia.programandoweb.net/privkey.pem', 'utf8');
	const certificate = fs.readFileSync('/etc/letsencrypt/live/colombia.programandoweb.net/cert.pem', 'utf8');
	const ca = fs.readFileSync('/etc/letsencrypt/live/colombia.programandoweb.net/chain.pem', 'utf8');
	const credentials = {
		key: privateKey,
		cert: certificate,
		ca: ca
	};
	const server   = https.createServer(credentials, app);
	const io = socketio(server);
	const PORT = process.env.PORT || 6500
	app.use(express.static(__dirname + '/public'))
	app.use(bodyParser.json()); // body en formato json
	app.use(bodyParser.urlencoded({ extended: false })); //body formulario

	app.post('/emit', function (req, res) {
		  io.sockets.emit("actualizar_mensaje_a_todos", req.body);
			console.log(req.body);
			res.json('OK');
	});

	io.on('connection', function(socket){

		io.emit('estatus',"usuario conectado");

		socket.on('enviar_mensaje_a_todos', function (data) {
			io.emit('actualizar_mensaje_a_todos', data);
	  });

		socket.on('PGRW_state', function (data) {
			io.emit('PGRW_state', data);
	  });

	})

	server.listen(PORT, () => {
	  console.log("Server running in port "+ PORT)
	})

}else {
	const server  = http.createServer(app);
	const io = socketio(server);
	const PORT = process.env.PORT || 6500
	app.use(express.static(__dirname + '/public'))
	app.use(bodyParser.json()); // body en formato json
	app.use(bodyParser.urlencoded({ extended: false })); //body formulario

	io.emit('estatus',"usuario conectado");

	app.post('/emit', function (req, res) {
		  io.sockets.emit("actualizar_mensaje_a_todos", req.body);
			console.log(req.body);
			res.json('OK');
	});

	io.on('connection', function(socket){
	  io.emit('estatus',"usuario conectado");

		socket.on('enviar_mensaje_a_todos', function (data) {
			io.emit('actualizar_mensaje_a_todos', data);
		});

		socket.on('PGRW_state', function (data) {
			console.log(data);
			io.emit('PGRW_state', data);
		});

	})
	server.listen(PORT, () => {
	  console.log("Server running in port "+ PORT)
	})
}
