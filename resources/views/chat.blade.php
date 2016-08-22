<!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8">
    <title>Enduser Chat</title>
    <!-- Optional theme -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">
    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
    <link rel="stylesheet" href="{{ secure_asset('assets/css/front-chat.css') }} "/>
    <script type="text/javascript" src="{{ secure_asset('assets/js/jquery-1.11.2.min.js') }} "></script>
    <script>
      var profile = jQuery.parseJSON('<?php echo ($profile) ?>');
    </script>
    <script type="text/javascript" src="{{ secure_asset('assets/plugins/jquery.scrollbar/jquery.scrollbar.min.js') }} "></script>
    <script type="text/javascript" src="{{ secure_asset('assets/js/front-chat.js') }} "></script>
   
  </head>
  <body>
    <div class="container">
      <p>&nbsp;</p>  
      <div class="panel panel-info">
        <div class="panel-heading">Tenposs</div>
        <div class="panel-body">
          <div id="room1" class="rooms">
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
              <div class="avatar"></div>
              <div class="text_wrapper">
                  <div class="text"></div>
              </div>
          </li>
      </div>
      
    </div><!--end container -->  
  </body>
</html>