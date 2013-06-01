require('jquery');

var express = require("express");
var app = express();
var port = 3700;

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

function newRound() {
    /*
     * Get users location
     * Make custom function for this
     */
    io.sockets.emit('message', { message: 'Get new location' });


    /*
     *
     *
     */

}
setInterval(function() {
	io.sockets.emit('message', { message: 'Get new location' });
}, 10000);

console.log('Booted!');
