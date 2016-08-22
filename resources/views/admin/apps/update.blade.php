@extends('admin.master')

@section('content')
    <div class="topbar-content">
        <div class="wrap-topbar clearfix">
            <div class="left-topbar">
                <h1 class="title">Clients/Apps/Edit</h1>
            </div>
        </div>
    </div>
    <!-- END -->

    <div class="main-content">

        @include('admin.partials.message')
        <form method="post" action="{{ route('admin.clients.apps.update',['user_id' => $user->id, 'app_id' => $app->id]) }}">
            <input type="hidden" value="{{ csrf_token() }}"/>
            <input type="hidden" name="user_id" value="{{ $user->id }}">
            <input type="hidden" name="app_id" value="{{ $app->id }}">
            <input type="hidden" name="mode" value="_update">
            <div class="wrap-btn-content">
                <button class="btn btn-primary" type="submit"><i class=""></i> Save</button>
                <a href="{{ route('admin.clients.apps',['user_id' => $user->id]) }}" class="btn-me btn-xanhduongnhat">Back</a>
            </div>	<!-- end wrap-btn-content-->
            <div class="wrapper-content">
                <div class="form-group">
                    <label for="">App Name</label>
                    <input type="text" class="form-control" name="name" value="{{ $app->name }}">
                </div>
                <div class="form-group">
                    <label for="">App ID</label>
                    <input type="text" class="form-control" name="app_app_id" value="{{ $app->app_app_id }}">
                </div>
                <div class="form-group">
                    <label for="">App Secret</label>
                    <input type="text" class="form-control" name="app_app_secret" value="{{ $app->app_app_secret }}">
                </div>
                <div class="form-group">
                    <label for="">App Description</label>
                    <textarea name="description" id="" cols="30" rows="10" class="form-control">{{ $app->description }}</textarea>
                </div>
            </div>	<!-- wrap-content-->
        </form>
    </div>
    <!-- END -->
@endsection


@section('footer')
    <script src="{{ url('adcp/js/script.js') }}"></script>
@endsection