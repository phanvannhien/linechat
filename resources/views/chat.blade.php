<!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8">
    <title>Websocket test site</title>
    <script type="text/javascript" src="https://tenposs-phanvannhien.c9users.io/assets/js/jquery-1.11.2.min.js"></script>
    <script type="text/javascript">
      // Edit these variables to match your environent.
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
      var conn = new WebSocket(ws_url);
      conn.onopen = function(e) {
        // Spit this out in the console so we can tell if the
        // connection was successfull.
        var u = $('<p/>').text('Welcome! --||{{ $mid }}||-- Can i help you.?');
        $('#chat-container').append(u);
      };
      conn.onmessage = function(e) {
        // When ever a message is recieved, from the server, append
        // the message to the existing text in the chat area.
        $('#chat-container').append(e.data);
      };
      conn.onclose =function(e){
          console.log('Connection closed');
      };
      
      $(document).ready(function(){
         $('#send-message').on('click',function(e){
            e.preventDefault();
             console.log('Send'+$('#text-message').val());
            $('#chat-container').append($('#text-message').val());
            conn.send( {"mid": "<?php echo $mid ?>" , "data": $('#text-message').val() } );
          });
      });
     
      
      
    </script>
  </head>
  <body>
    <h1>Websocket test site</h1>
    <div id="chat-container">
      
    </div>
    <div id="chat">
      <input type="text" name="text_message" id="text-message"/>
      <input type="button" value="SEND" id="send-message">
    </div>
  </body>
</html>