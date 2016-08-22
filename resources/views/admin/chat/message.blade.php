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
	    .rooms{
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
			    <div id="room1" class="rooms">
			        <div class="panel panel-default">
			            <div class="panel-heading">Nhien</div>
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
			</div>	<!-- end main-content-->
			<div class="clearfix"></div>
		</div>	<!--end main -->
	</div>
	<script type="text/javascript" src="{{ secure_asset('adcp/js/jquery-1.11.2.min.js') }}"></script>
	<script type="text/javascript" src="{{ secure_asset('adcp/js/bootstrap.min.js') }}"></script>
	<script type="text/javascript" src="{{ secure_asset('assets/plugins/jquery.scrollbar/jquery.scrollbar.min.js') }} "></script>
	<script>
      var profile = jQuery.parseJSON('{"displayName":"Tenposs1","mid":"u9c1af340d8af0d5aa7e63fffa2c2aa28","pictureUrl":"http:\/\/dl.profile.line-cdn.net\/0m01e82b837251d975a21b9588414fa3f563a9f19abd5a","statusMessage":"hi im strong"}');
    </script>
	<script type="text/javascript" src="{{ secure_asset('assets/js/back-chat.js') }}"></script>
</body>
</html>



