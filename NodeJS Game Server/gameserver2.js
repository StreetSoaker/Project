var mysql = require('mysql');
var port = 8080;

var online = 0;

var pool  = mysql.createPool({
	host     : '46.21.172.159',
	user     : 'alberjg10_soaker',
	password : 'C2AQtirl',
	database : 'alberjg10_supersoaker'
});

var io = require('socket.io').listen(port);

console.log("Listening on port " + port);

//Update the enemy marker on the map
function updateEnemyMarkers(socket) {
	socket.get('userdata', function(err, userdata) {
		pool.getConnection(function(err, connection) {
			connection.query("SELECT `username`, `latitude`, `longitude`, `time` FROM `location` INNER JOIN `users` ON `users`.`id` = `location`.`userid` WHERE `userid` != "+userdata.userid, function(err, rows, fields) {
				if (err) throw err;
				socket.emit('updateEnemyMarkers', rows);
			});
			connection.end();
		});
	});
}

//Called when a the kill button is pressed
function onKill(data, socket) {
	socket.get('userdata', function(err, userdata) {
		if(data.enemyid && data.killcode) {
			pool.getConnection(function(err, connection) {
				connection.query("SELECT `deathcode`, `username` FROM `users` WHERE `id` = "+data.enemyid, function(err, rows, fields) {
					if (err) throw err;
					if (data.enemyid == userdata.userid) {
						socket.emit('invalidKillCode'); 
					} else {
						if (!rows[0]) {
							socket.emit('invalidKillCode'); 	
						} else {
							if (data.killcode == rows[0].deathcode) {
								//Add 1 kill to person who killed enemy
								connection.query("UPDATE `users` SET `kills` = `kills`+1 WHERE `id` = "+userdata.userid, function(err, rows, fields) {
									if (err) throw err;
								});

								//Add 1 death to enemy who died
								connection.query("UPDATE `users` SET `deaths` = `deaths`+1 WHERE `id` = "+data.enemyid, function(err, rows, fields) {
									if (err) throw err;
								});

								newDeathCode = (""+Math.random()).substring(2,6);

								connection.query("UPDATE `users` SET `deathcode` = "+newDeathCode+" WHERE `id` = "+data.enemyid, function(err, rows, fields) {
									if (err) throw err;
								});

								console.log(data.enemyid + ' has been killed');
								socket.broadcast.emit('getDeathCode', data.enemyid);
								socket.broadcast.emit('getStats', data.enemyid);
								socket.broadcast.emit('info', {message: userdata.username + ' killed ' + rows[0].username});
								getStats(socket);
							} else {
								socket.emit('invalidKillCode'); 
							}
						}
					}
				});
				connection.end();
			});
		} else {
			socket.emit('invalidKillCode'); 
		}
	});
}

function getDeathCode(socket) {
	socket.get('userdata', function(err, userdata) {
		pool.getConnection(function(err, connection) {
			connection.query("SELECT `deathcode` FROM `users` WHERE `id` = "+userdata.userid, function(err, rows, fields) {
				if (err) throw err;
				socket.emit('updateDeathCode', rows[0].deathcode);
			});
			connection.end();
		});
	});
}

//Gets the statistics for the player
function getStats(socket) {
	socket.get('userdata', function(err, userdata) {
		pool.getConnection( function(err, connection) {
			connection.query("SELECT `kills`, `deaths` FROM `users` WHERE `id` = "+userdata.userid, function(err, rows, fields) {
				if (err) throw err;
				socket.emit('stats', rows[0]);
			});
			connection.end();
		});
	});
}

//Update player location in database at 5 min intervals
function updatePlayerLocation(data, socket) {
	if (!data.location) {
		console.log('Location not received retrying!');
		setTimeout((function() {
			socket.emit('updatePlayerLocation');
		}), 10000);
	} else {
		latitude = data.location.coords.latitude;
		longitude = data.location.coords.longitude;
		accuracy = data.location.coords.accuracy;
		timestamp = data.location.timestamp;

		socket.get('userdata', function(err, userdata) {
			pool.getConnection(function(err, connection) {	
				connection.query("SELECT COUNT(`userid`) AS count FROM `location` WHERE `userid` = "+userdata.userid, function(err, rows, fields) {
					if (err) throw err;

					if (rows[0].count == 1) {
						connection.query("UPDATE `location` SET `latitude` = "+latitude+", `longitude` = "+longitude+", `accuracy` = "+accuracy+", `time` = FROM_UNIXTIME("+timestamp+") WHERE `userid` = "+userdata.userid, function(err, rows, fields) {
							if (err) throw err;
						});
					} else {
						connection.query("INSERT INTO `location` VALUES('',"+userdata.userid+","+latitude+","+longitude+","+accuracy+",FROM_UNIXTIME("+timestamp+"))", function(err, rows, fields) {
							if (err) throw err;
						});
					}
				});
				connection.end();
			});
		});
	}
}

//Set socket userdata with username and id
function setUserData(data, socket) {
	socket.set('userdata', data, function() {
		console.log('userdata set:');
		console.log(data);
		getDeathCode(socket);
		getStats(socket);
		updateEnemyMarkers(socket);
		socket.emit('updatePlayerLocation');
	});
}

io.sockets.on('connection', function(socket) {

	socket.on('disconnect', function() {
		socket.get('userdata', function(err, userdata) {
			online--;
			socket.broadcast.emit('usersOnline', online);
			if (!userdata) {
				console.log('ERROR NULL DISCONNECTED');
			} else {
				console.log(userdata.username + ' Disconnected');
				socket.broadcast.emit('info', {message: userdata.username + ' Disconnected'});	
			}
			
		});
	});

	socket.on('connect', function(data) {
		if (!data) {
			setTimeout((function() {
				socket.emit('connect');
			}), 5000);
		} else { 
			setUserData(data, socket);
			online++;
			console.log(data.username + ' Connected');
			socket.broadcast.emit('info', {message: data.username + ' Connected'});
			socket.broadcast.emit('usersOnline', online);
		}
		
	});

	socket.on('location', function(data) {
		updatePlayerLocation(data, socket);
	});
	
	socket.on('locationError', function(data) {
		console.log(data);
	});

	socket.on('kill', function(data) {
		onKill(data, socket);
	});	

	socket.on('getDeathCode', function() {
		getDeathCode(socket);
	});

	socket.on('getStats', function() {
		getStats(socket);
	});
});

