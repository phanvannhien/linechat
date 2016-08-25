
var ws_host = 'tenposs-phanvannhien.c9users.io';
var ws_port = '80';
var ws_folder = '';
var ws_path = '/websocket';
var ws_url = 'wss://' + ws_host;
if (ws_port != '80' && ws_port.length > 0) {
    ws_url += ':' + ws_port;
}
ws_url += ws_folder + ws_path;

var conn;
var Message;
var getMessageText, message_side, sendMessage;
message_side = 'right';

Message = function (arg) {
    this.text = arg.text, this.message_side = arg.message_side;
    this.draw = function (_this) {
        return function () {
            var $message;
            $message = $($('.message_template').clone().html());
            $message.addClass(_this.message_side).find('.text').html(_this.text);
            $('.messages').append($message);
            return setTimeout(function () {
                return $message.addClass('appeared');
            }, 0);
        };
    }(this);
    
    return this;
};
    
// Get message text enduser typing   
getMessageText = function () {
    var $message_input;
    $message_input = $('.message_input');
    return $message_input.val();
};

// Send message text enduser typing   
sendMessage = function (text) {
    // Draw message client
    var $messages, message;
    if (text.trim() === '') {
        return;
    }
    $('.message_input').val('');
    $messages = $('.messages');
    message = new Message({
        text: text,
        message_side: 'right'
    });
    message.draw();
    
    // Send message to server
    var d = new Date();
    var params = {
        'message': text,
        'action': 'message',
        'from' : 'enduser',
        'timestamp': d.getTime()/1000
    };
    conn.send(JSON.stringify(params));
    
    return $messages.animate({ scrollTop: $messages.prop('scrollHeight') }, 300);
};

// Connect to server 
function connectToChat() {
    conn = new WebSocket(ws_url);
    
    // client connected
    conn.onopen = function() {
        var params = {
            'roomId': room_id,
            'mid': profile.mid,
            'userName': profile.displayName,
            'from': 'endusers',
            'profile' : profile,
            'action': 'connect'
        };
        conn.send(JSON.stringify(params));
        console.log('User request connect:');
         console.log(params);
        console.log('----------------------------------------------------------------------------------------------');
        
    };

    // client get message from server
    conn.onmessage = function(e) {
        console.log('User get message from server:');
        console.log(e.data);
        console.log('---------------------------------------------------------');
        var data = JSON.parse(e.data);

        if (data.hasOwnProperty('message') && data.hasOwnProperty('from')) {
            message = new Message({
                text: data.message,
                message_side: 'left'
            });
            message.draw();
            
            //displayChatMessage(data.from.name, data.message);
        }
        else if (data.hasOwnProperty('message')) {
            message = new Message({
                text: data.message,
                message_side: 'left'
            });
            message.draw();
            
        }
        else if (data.hasOwnProperty('type')) {
            if (data.type == 'list-users' && data.hasOwnProperty('clients')) {
                //displayChatMessage(null, 'There are ' + data.clients.length + ' users connected');
            }
            else if (data.type == 'user-started-typing') {
               // displayUserTypingMessage(data.from)
            }
            else if (data.type == 'user-stopped-typing') {
               // removeUserTypingMessage(data.from);
            }
        }
    };

    conn.onerror = function(e) {
        console.log(e);
    };
    
    conn.onclose =function(e){
        console.log('Connection closed');
    };


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






$(document).ready(function(){
    jQuery('.scrollbar-macosx').scrollbar();
    connectToChat();
    $('.send_message').click(function (e) {
        console.log('Action click send message');
        return sendMessage(getMessageText());
    });
    $('.message_input').keyup(function (e) {
        if (e.which === 13) {
            console.log('Action enter send message');
            return sendMessage(getMessageText());
        }
    });
    
});
