<!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8">
    <title>Enduser Chat</title>
    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
    <link rel="stylesheet" href="{{ secure_asset('assets/css/front-chat.css') }} "/>
    <script type="text/javascript" src="{{ secure_asset('assets/js/jquery-1.11.2.min.js') }} "></script>
    <script type="text/javascript" src="{{ secure_asset('assets/plugins/jquery.scrollbar/jquery.scrollbar.min.js') }} "></script>
  </head>
  <body>
    <div class="container">
      <p>&nbsp;</p>  
      <div class="panel panel-info">
        <div class="panel-heading">Tenposs</div>
        <div class="panel-body">
          <div id="" class="rooms">
              <ul class="messages scrollbar-macosx"></ul>
          </div>
        </div>
        <div class="panel-footer">
          <div class="input-group">
              <input type="text" class="form-control message_input" placeholder="Enter message...">
              <span class="input-group-btn">
                  <button class="btn btn-default send_message" type="button">Send</button>
              </span>
          </div><!-- /input-group -->
        </div>
      </div><!--end panel --> 
      <div class="message_template" style="display:none">
          <li class="message">
              <div class="avatar">
                <img src=""/>
              </div>
              <div class="text_wrapper">
                  <div class="text"></div>
                  <div class="timestamp"></div>
              </div>
          </li>
      </div>
      
      <div class="message_template_system" style="display:none">
          <li class="text-center">
              <p class="text-muted"></p>
          </li>
      </div>
      
      
    </div><!--end container -->
    
<script type="text/javascript">

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
var profile = $.parseJSON('<?php echo ($profile) ?>');
var room_id = '{{ $room_id }}';


function drawMessage(side, profile, message){
     
    var $message;
    var $messages = $('ul.messages');
    var d = new Date();
    $message = $($('.message_template').clone().html());
    $message.addClass(side).find('.text').html(message);
    $message.find('.avatar img').attr('src',profile.pictureUrl+'/small')
    $message.find('.timestamp').text(d.getTime()/1000);
    $messages.append($message);
    setTimeout(function () {
        return $message.addClass('appeared');
    }, 0);
    return $messages.animate({ scrollTop: $messages.prop('scrollHeight') }, 300);
}

function drawSystemMessage(text){
  $message = $($('.message_template_system').clone().html());
  $message.find('p').text(text);
  $('.messages').append($message);
    return setTimeout(function () {
        return $message.addClass('appeared');
    }, 0);
}
 
function getMessageText() {
   return $('.message_input').val();
};


function sendMessage(text) {
    var $messages, message;
    if (text.trim() === '') {
        return;
    }
    $('.message_input').val('');
    $messages = $('ul.messages');
    drawMessage('right',profile, text);
    // Send message to server
    var d = new Date();
    var params = {
        'action': 'message',
        'message': text,
        'timestamp': d.getTime()/1000
    };
    conn.send(JSON.stringify(params));
    console.log('Send message');
    console.log(JSON.stringify(params));
    
    return $messages.animate({ scrollTop: $messages.prop('scrollHeight') }, 300);
};

// Connect to server 
function connectToChat() {
    conn = new WebSocket(ws_url);
    // client connected
    conn.onopen = function() {
        var params = {
            'action': 'connect',
            'roomId': room_id,
            'from' : {
              'user_type' : 'endusers',
              'profile' : profile
            }
        };
        console.log('Send request connect');
        console.log(params);
        console.log('----------------------------------------------------------------------------------------------');
        conn.send(JSON.stringify(params));
    };

    // client handle message from server
    conn.onmessage = function(e) {
        console.log('Client handle message from server');
        console.log(e.data);
        console.log('---------------------------------------------------------');
        var data = JSON.parse(e.data);
        
        if (data.hasOwnProperty('type')) {
          if( data.type == 'user-connected' && data.hasOwnProperty('message_type') && data.hasOwnProperty('message')){
            if( data.message_type == 'system_status' ){
              drawSystemMessage(data.message);
            }
          }
          
          if (data.type = 'message' && data.hasOwnProperty('message') && data.hasOwnProperty('from')) {
            drawMessage('left',data.from, data.message);
          }
          
          if (data.type = 'user-disconnected' && data.hasOwnProperty('message_type') && data.hasOwnProperty('message')) {
            if( data.message_type == 'system_status' ){
              drawSystemMessage(data.message);
            }
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

      
</script>
</body>
</html>