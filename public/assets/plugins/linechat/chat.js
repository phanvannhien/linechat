// Change localhost to the name or ip address of the host running the chat server
var ws_host = 'tenposs-phanvannhien.c9users.io';
var ws_port = '80';
var ws_folder = '';
var ws_path = '/websocket';

// We are using wss:// as the protocol because Cloud9 is using
// HTTPS. In case you try to run this, using HTTP, make sure
// to change this to ws:// .
var ws_url = 'wss://' + ws_host;
if (ws_port != '80' && ws_port.length > 0) {
    ws_url += ':' + ws_port;
}
ws_url += ws_folder + ws_path;


function displayChatMessage(from, message) {
    var node = document.createElement("LI");

    if (from) {
        var nameNode = document.createElement("STRONG");
        var nameTextNode = document.createTextNode(from);
        nameNode.appendChild(nameTextNode);
        node.appendChild(nameNode);
    }

    var messageTextNode = document.createTextNode(message);
    node.appendChild(messageTextNode);

    document.getElementById("messageList").appendChild(node);
}

function displayUserTypingMessage(from) {
    var nodeId = 'userTyping'+from.name.replace(' ','');
    var node = document.getElementById(nodeId);
    if (!node) {
        node = document.createElement("LI");
        node.id = nodeId;

        var messageTextNode = document.createTextNode(from.name + ' is typing...');
        node.appendChild(messageTextNode);

        document.getElementById("messageList").appendChild(node);
    }
}

function removeUserTypingMessage(from) {
    var nodeId = 'userTyping' + from.name.replace(' ', '');
    var node = document.getElementById(nodeId);
    if (node) {
        node.parentNode.removeChild(node);
    }
}

var conn;

function connectToChat() {
    conn = new WebSocket(ws_url);

    conn.onopen = function() {
        //document.getElementById('connectFormDialog').style.display = 'none';
       // document.getElementById('messageDialog').style.display = 'block';

        var params = {
            'roomId':'room1',
            'userName': 'nhien',
            'action': 'connect'
        };
        console.log(params);
        conn.send(JSON.stringify(params));
    };

    conn.onmessage = function(e) {
        console.log(e);
        var data = JSON.parse(e.data);

        if (data.hasOwnProperty('message') && data.hasOwnProperty('from')) {
            displayChatMessage(data.from.name, data.message);
        }
        else if (data.hasOwnProperty('message')) {
            displayChatMessage(null, data.message);
        }
        else if (data.hasOwnProperty('type')) {
            if (data.type == 'list-users' && data.hasOwnProperty('clients')) {
                displayChatMessage(null, 'There are ' + data.clients.length + ' users connected');
            }
            else if (data.type == 'user-started-typing') {
                displayUserTypingMessage(data.from)
            }
            else if (data.type == 'user-stopped-typing') {
                removeUserTypingMessage(data.from);
            }
        }
    };

    conn.onerror = function(e) {
        console.log(e);
    };

    return false;
}

function sendChatMessage() {
    var d = new Date();
    var params = {
        'message': document.getElementsByName("message")[0].value,
        'action': 'message',
        'timestamp': d.getTime()/1000
    };
    conn.send(JSON.stringify(params));

    document.getElementsByName("message")[0].value = '';
    return false;
}

function updateChatTyping() {
    var params = {};

    if (document.getElementsByName("message")[0].value.length > 0) {
        params = {'action': 'start-typing'};
        conn.send(JSON.stringify(params));
    }
    else if (document.getElementsByName("message")[0].value.length == 1) {
        params = {'action': 'stop-typing'};
        conn.send(JSON.stringify(params));
    }
}