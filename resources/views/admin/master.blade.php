<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width">
	<title>Tenposs</title>
	<link rel="stylesheet" href="{{ url('adcp/css/bootstrap.min.css') }}">
	<link rel="stylesheet" href="{{ url('adcp/css/style.css') }}">
	<link rel="stylesheet" href="{{ url('adcp/js/switch/lc_switch.css') }}">
	@yield('header')
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
				@yield('content')
			</div>	<!-- end main-content-->
		</div>	<!--end main -->
	</div>
	<script type="text/javascript" src="{{ url('adcp/js/jquery-1.11.2.min.js') }}"></script>
	<script type="text/javascript" src="{{ url('adcp/js/bootstrap.min.js') }}"></script>
	@yield('footer')
</body>
</html>