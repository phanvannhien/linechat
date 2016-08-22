@extends('admin.master')

@section('content')
<div class="topbar-content">
	<div class="wrap-topbar clearfix">
		<div class="left-topbar">
			<h1 class="title">Clients</h1>
		</div>
	</div>
</div>	
<!-- END -->

<div class="main-content news">
	<div class="wrap-btn-content">
		<a href="#" class="btn-me btn-hong">スタの新着情報</a>
		<a href="#" class="btn-me btn-xanhduongnhat">スタの新着情報 2</a>
	</div>	<!-- end wrap-btn-content-->
	<div class="wrapper-content">
		<div class="clearfix">
            <p style="margin-bottom:10px;" class="">Showing {{$users->firstItem()}}/{{$users->lastItem()}} of {{$users->total()}}results</p>
		</div>
		<table class="table table-bordered" >
			<thead>
				<tr>
					<th>Name</th>
					<th>Email</th>
					<th>Fullname</th>
					<th>Sex</th>
					<th>birthday</th>
					<th>locale</th>
					<th>status</th>
					<th>company</th>
					<th>address</th>
					<th>tel</th>
					<th>apps</th>
					<th>Group</th>
				</tr>
			</thead>
			<tbody>
				@if (count($users) > 0)
				@foreach($users as $user)
				<tr>
					<td>{{ $user->name }}</td>
					<td>{{ $user->email }}</td>
					<td>{{ $user->fullname }}</td>
					<td>{{ $user->sex }}</td>
					<td>{{ $user->birthday }}</td>
					<td>{{ $user->locale }}</td>
					<td>{{ $user->status }}</td>
					<td>{{ $user->company }}</td>
					<td>{{ $user->address }}</td>
					<td>{{ $user->tel }}</td>
					<td>
					<a href="{{ route('admin.clients.apps',['id' => $user->id]) }}">View apps ({{ $user->apps()->count() }})</a>

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
				{{ $users->render() }}
			</div>
			
		</div>
	</div>	<!-- wrap-content-->
</div>
<!-- END -->
@endsection


@section('footer')
<script src="{{ url('adcp/js/script.js') }}"></script>
@endsection