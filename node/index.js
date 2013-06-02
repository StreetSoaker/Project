var mysql = require('mysql');
var express = require("express");
var app = express();
var port = 3700;



var mysql      = require('mysql');
var connection = mysql.createConnection({
  host     : '46.21.172.159',
  user     : 'alberjg10_soaker',
  password : 'C2AQtirl',
  database : 'alberjg10_supersoaker'
});

connection.connect(function(err) {
  // connected! (unless `err` is set)
  if(err) {
    console.log(err);
    console.log('please make sure connection is working!');
  } else {
    console.log('Succes connection');

    connection.query('SELECT * FROM users', function(err, rows) {
        if(!err) {
            console.log(rows);
        } else {
            console.log('ERROR:' + err);
        }
    });



    app.use(express.static(__dirname + '/public'));

    app.set('views', __dirname + '/tpl');
    app.set('view engine', "jade");
    app.engine('jade', require('jade').__express);
    app.get("/", function(req, res){
        res.render("page");
    });

    var io = require('socket.io').listen(app.listen(port));
    console.log("Listening on port " + port);

    io.sockets.on('connection', function (socket) {

        socket.emit('message', { message: 'welcome to the chat' });
        socket.broadcast.emit('message', { message: 'User Connected' });
        console.log(io.sockets.manager.server._connections);

        socket.on('disconnect', function(){
            socket.broadcast.emit('message', { message: 'User disconnected' });
        });

        socket.on('send', function (data) {
            io.sockets.emit('message', data);
        });
    });

    function newRound(userid, latitude, longitude, accuracy, time) {
        /*
         * Get users location
         * Make custom function for this to call Koens function
         */
        io.sockets.emit('message', { message: 'Get new location' });


        /*
         * Add location to the db
         * Maybe check for valid location
         */
        connection.query('INSERT INTO location VALUES(userid, latitude, longitude, accuracy, time) ?, ?, ?, ?, ?',
            [userid, latitude, longitude, accuracy, time],
            function(err, rows) {
            if(!err) {
                console.log(rows);
            } else {
                console.log('ERROR:' + err);
            }
        });
    }
    setInterval(function() {
        io.sockets.emit('message', { message: 'Get new location' });
    }, 60000);

  }
});
console.log('Booted!');
