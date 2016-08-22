$(document).ready(function(){
	$('.trigger').on('click', function(){
		$('.sidebar, .content').toggleClass('offset-sidebar-active');
	})
})