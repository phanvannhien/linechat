@extends('admin.master')

@section('content')
<div class="topbar-content">
	<div class="wrap-topbar clearfix">
		<div class="left-topbar">
			<h1 class="title">Clients/Apps</h1>
		</div>
	</div>
</div>	
<!-- END -->

<div class="main-content">
	@include('admin.partials.message')
	<div class="wrap-btn-content">
		<a href="{{ route('admin.clients.apps.create',['user_id' => $user_id]) }}" class="btn-me btn-hong">Create App</a>
		<a href="{{ route('admin.clients') }}" class="btn-me btn-xanhduongnhat">Back</a>
	</div>	<!-- end wrap-btn-content-->
	<div class="wrapper-content">

		<form action="" method="get" class="form-inline">
			<label for="">Name</label>
			<input name="name" type="text" class="form-control" value="{{ Input::get('name') }}">
			<button class="btn btn-primary" type="submit">Filter</button>
		</form>
		<p>&nbsp;</p>
		<div class="clearfix">
			<p style="margin-bottom:10px;" class="">Showing {{$apps->firstItem()}}-{{$apps->lastItem()}} of {{$apps->total()}}results</p>
		</div>
		<table class="table table-bordered" >
			<thead>
				<tr>
					<th>Name</a></th>
					<th>App Id</th>
					<th>App Secret</th>
					<th>Status</th>
					<th>Created At</th>
					<th>Action</th>
				</tr>
			</thead>
			<tbody>
				@if (count($apps) > 0)
				@foreach($apps as $app)
				<tr>
					<td>{{ $app->name }}</td>
					<td>{{ $app->app_app_id }}</td>
					<td>{{ $app->app_app_secret }}</td>
					<td>{{ $app->status }}</td>
					<td>{{ $app->created_at }}</td>
					<td>
						<a href="{{route('admin.clients.apps.edit',['user_id'=>$user_id,'app_id' => $app->id])}}">Edit</a>
						<a href="{{route('admin.clients.apps.delete',['user_id'=>$user_id,'app_id' => $app->id])}}">Remove</a>
					</td>
				</tr>
				@endforeach
				@else
				<tr>
					<td></td>
				</tr>
				@endif
			</tbody>

		</table>
		<div class="pagination pull-right">
			<div class="clearfix">
				{{ $apps->render() }}
			</div>
			
		</div>
	</div>	<!-- wrap-content-->
</div>
<!-- END -->
@endsection


@section('footer')
<script src="{{ url('adcp/js/script.js') }}"></script>
@endsection