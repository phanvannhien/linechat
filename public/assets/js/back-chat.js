
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
    this.text = arg.text;
    this.message_side = arg.message_side;
    this.roomid = arg.roomid;
    this.avatar = $('<img/>').attr('src',arg.avatar);
    
    this.draw = function (_this) {
        console.log('render message');
        return function () {
            var $message;
            $message = $($('.message_template').clone().html());
            $message.addClass(_this.message_side).find('.text').html(_this.text);
            $message.find('.avatar').html(_this.avatar);
          
            $('div#box-'+_this.roomid+' .messages').append($message);
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
        'timestamp': d.getTime()/1000
    };
    conn.send(JSON.stringify(params));
    
    return $messages.animate({ scrollTop: $messages.prop('scrollHeight') }, 300);
};

// Connect to server 
function connectToChat() {
    conn = new WebSocket(ws_url);
    conn.onopen = function() {
        var params = {
            'roomId': 'uaa357d613605ebf36f6366a7ce896180',
            'mid': 'uaa357d613605ebf36f6366a7ce896180',
            'userName': profile.displayName,
            'profile': profile,
            'from': 'clients',
            'action': 'connect'
        };
        console.log('User connected:');
        conn.send(JSON.stringify(params));
    };

    conn.onmessage = function(e) {
        console.log('User get message from client:');
        console.log(JSON.parse(e.data));
        var data = JSON.parse(e.data);

       if (data.hasOwnProperty('type')) {
           
           
            if (data.type == 'list-users' && data.hasOwnProperty('clients')) {
                displayListEndUsers(data.clients);
            }
             else if (data.type == 'message' && data.hasOwnProperty('message')) {
                 
                displayClientsMessage(data)
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




function displayListEndUsers(endUsers){
    $(endUsers).each(function(index,item){
        if( item.mid !== item.roomid ){
            if( checkExistBoxItems(item) )
                renderChatLists(item);
                renderChatBox(item);
        }
    });
}

function checkExistBoxItems(enduser){
    $('#members-template .list-group-item').each(function(index,item){
       if( enduser.roomid == $(item).attr('id') ){
           return false;
       } 
    });
    return true;
}


function renderChatLists(enduser){
    var $template;
    $template = $($('#members-template').clone().html());
    $template.attr('id','list-'+enduser.roomid);
    $template.find('.media-object').attr('src',enduser.profile.pictureUrl+'/small');
    $template.find('.media-heading').html(enduser.name);
    $template.find('.media-body p').text(enduser.profile.statusMessage);
      
    $('#enduser-chat-list').append($template);
    return setTimeout(function () {
        return $template.addClass('appeared');
    }, 0);
}


function renderChatBox(enduser){
    
    var $template;
    $template = $($('#room-template .rooms').clone().html());
    $template.attr('id','box-'+enduser.roomid)
        .find('.panel-heading').html(enduser.name);
    $('#message-wrapper').append($template);
    return setTimeout(function () {
        return $template.addClass('appeared');
    }, 0);
}

function displayClientsMessage(data){
    var message = new Message({
        text: data.message,
        roomid: data.roomid,
        message_side: 'right',
        avatar: data.from.profile.pictureUrl
    });
    message.draw();
}


$(document).ready(function(){
    jQuery('.scrollbar-macosx').scrollbar();
    connectToChat();
    $('.send_message').click(function (e) {
        return sendMessage(getMessageText());
    });
    $('.message_input').keyup(function (e) {
        if (e.which === 13) {
            return sendMessage(getMessageText());
        }
    });
    
});
