<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width">
	<title>Tenposs</title>
	<link rel="stylesheet" href="{{ secure_asset('adcp/css/bootstrap.min.css') }}">
	<link rel="stylesheet" href="{{ secure_asset('adcp/css/style.css') }}">
	<link rel="stylesheet" href="{{ secure_asset('assets/css/front-chat.css') }}">
	
	<style>
	    .panel{
	        max-width: 300px;
	        margin: 5px;
	        float:left;
	    }
	</style>
</head>
<body>
	<div class="page">
		@include('admin.partials.top')
		<!-- end header -->
		<div class="main common">
			<div class="sidebar">
				@include('admin.partials.sidebar')
			</div>
			<!-- end sidebar -->
			<div class="content">
				<div class="rows">
					<div class="col-lg-3 col-md-3">
						<div id="" style="margin-top:5px;">
							<div id ="enduser-chat-list" class="list-group"></div>
						</div>
					</div>
					<div id="message-wrapper" class="col-lg-9 col-md-9"></div>
				</div>
			    
			    <div id="room-template" class="hide">
			    	<div id="" class="rooms">
				        <div class="panel panel-default">
				            <div class="panel-heading"></div>
				            <div class="panel-body">
				                <ul class="messages scrollbar-macosx"></ul>
				            </div>
				            <div class="panel-footer">
				                <div class="input-group">
	                                <input type="text" class="form-control message_input" placeholder="Enter message...">
	                                <span class="input-group-btn">
	                                    <button class="btn btn-default send_message" type="button">Send</button>
	                                </span>
	                            </div><!-- /input-group -->
				            </div>
				        </div>
				    </div>
			    </div>
			    
			    <div id="members-template" class="hide">
				    <div class="list-group-item">
						<div class="media">
							<div class="media-left">
								<a href="#">
								  <img class="media-object" src="" alt="">
								</a>
							</div>
							<div class="media-body">
								<h4 class="media-heading"></h4>
								<p></p>
							</div>
						</div>
					</div>
			    </div>
			    
			    <div class="message_template" style="display:none">
			          <li class="message">
			              <div class="avatar">
			                  <img src="">
			              </div>
			              <div class="text_wrapper">
			                  <div class="text"></div>
			                  <div class="timestamp"></div>
			              </div>
			          </li>
			      </div>
			</div>	<!-- end main-content-->
			<div class="clearfix"></div>
		</div>	<!--end main -->
	</div>
	<script type="text/javascript" src="{{ secure_asset('adcp/js/jquery-1.11.2.min.js') }}"></script>
	<script type="text/javascript" src="{{ secure_asset('adcp/js/bootstrap.min.js') }}"></script>
	<script type="text/javascript" src="{{ secure_asset('assets/plugins/jquery.scrollbar/jquery.scrollbar.min.js') }} "></script>
	<script>
      var profile = jQuery.parseJSON('{"displayName":"Tenposs1","mid":"uaa357d613605ebf36f6366a7ce896180","pictureUrl":"http:\/\/dl.profile.line-cdn.net\/0m01e82b837251d975a21b9588414fa3f563a9f19abd5a","statusMessage":"hi im strong"}');
    </script>
	
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
var Message;


function drawMessage(side, profile, message){
	var $message;
    var d = new Date();
    
    $messages = $('div#box-'+profile.mid+' ul.messages');
    
    $message = $($('.message_template').clone().html());
    $message.addClass(side).find('.text').html(message);
    $message.find('.avatar img').attr('src',profile.pictureUrl+'/small')
    $message.find('.timestamp').text(d.getTime()/1000);
    
    console.log('Kiem tra ton tai');
    if( checkExistBoxItems(profile) ){
        renderChatLists(profile);
        renderChatBox(profile);
        $messages.append($message);
    }else{
        $messages.append($message);
    }
    
    setTimeout(function () {
        return $message.addClass('appeared');
    }, 0);
    
    return $messages.animate({ scrollTop: $messages.prop('scrollHeight') }, 300);
  
};



// Send message text enduser typing   
function sendMessage(target) {
    // Draw message client
    console.log(target);
    var closest = $(target).closest('.panel');
    var $messages, message;
    if ($(target).val().trim() === '') {
        return;
    }
    
    $messages = $(closest).find('ul.messages');
    

    var d = new Date();
    $message = $($('.message_template').clone().html());
    $message.addClass('right').find('.text').html( $(target).val() );
    $message.find('.avatar img').attr('src',profile.pictureUrl+'/small')
    $message.find('.timestamp').text(d.getTime()/1000);
    $messages.append($message);
    setTimeout(function () {
        return $message.addClass('appeared');
    }, 0);
    // Send message to server
    var d = new Date();
    var params = {
        'message': $(target).val(),
        'to': $(closest).attr('data-id'),
        'action': 'message',
        'timestamp': d.getTime()/1000
    };
    conn.send(JSON.stringify(params));
    $(target).val('');
    return $messages.animate({ scrollTop: $messages.prop('scrollHeight') }, 300);
};

// Connect to server 
function connectToChat() {
    conn = new WebSocket(ws_url);
    conn.onopen = function() {
    	var params = {
            'action': 'connect',
            'roomId': 'uaa357d613605ebf36f6366a7ce896180',
            'from' : {
              'user_type' : 'clients',
              'profile' : profile
            }
        };
        
        console.log('Client request connect:');
        console.log(params);
        conn.send(JSON.stringify(params));
    };

    conn.onmessage = function(e) {
        console.log('Get message from enduser:');
        console.log(JSON.parse(e.data));
        var data = JSON.parse(e.data);
        
        
  
        if (data.hasOwnProperty('type')) {
           
            if (data.type == 'list-users' && data.hasOwnProperty('clients')) {
                displayListEndUsers(data.clients);
            }
            
            else if (data.type == 'message' && data.hasOwnProperty('message')) {
                drawMessage( 'left', data.from, data.message );
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
    if( $('#enduser-chat-list #'+enduser.mid).length > 0 ){
        return false;
    }else{
        return true;
    }

    
}


function renderChatLists(enduser){
    var $template;
    $template = $($('#members-template').clone().html());
    $template.attr('id',enduser.mid).addClass('rendered');
    $template.find('.media-object').attr('src',enduser.pictureUrl+'/small');
    $template.find('.media-heading').html(enduser.displayName);
    $template.find('.media-body p').text(enduser.statusMessage);
      
    $('#enduser-chat-list').append($template);
    return setTimeout(function () {
        return $template.addClass('appeared');
    }, 0);
}


function renderChatBox(enduser){
    
    var $template;
    $template = $($('#room-template .rooms').clone().html());
    $template.attr('id','box-'+enduser.mid).attr('data-id',enduser.mid)
        .find('.panel-heading').html(enduser.displayName);
    $('#message-wrapper').append($template);
    return setTimeout(function () {
        return $template.addClass('appeared');
    }, 0);
}


$(document).ready(function(){
    $('.scrollbar-macosx').scrollbar();
    connectToChat();
    
    $('#message-wrapper').on('keyup','.message_input',function (e) {
        if (e.which === 13) {
            return sendMessage(this);
        }
    });
    
    $('#message-wrapper').on('click','.send_message',function(e){
        
    	var target = $(this).parent().next('input');
    	$(target).trigger('keyup');
    	
    })
});

		
	</script>
</body>
</html>



