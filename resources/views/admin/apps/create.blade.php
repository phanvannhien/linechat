@extends('admin.master')

@section('content')
    <div class="topbar-content">
        <div class="wrap-topbar clearfix">
            <div class="left-topbar">
                <h1 class="title">Clients/Apps/Create</h1>
            </div>
        </div>
    </div>
    <!-- END -->

    <div class="main-content">

        @include('admin.partials.message')
        <form method="post" action="{{ route('admin.clients.apps.store',['user_id' => $user_id]) }}">
            <input type="hidden" value="{{ csrf_token() }}"/>
            <input type="hidden" name="user_id" value="{{ $user_id }}">
            <input type="hidden" name="mode" value="_create_new">
            <div class="wrap-btn-content">
                <button class="btn btn-primary" type="submit"><i class=""></i> Save</button>
                <a href="{{ route('admin.clients.apps',['user_id' => $user_id]) }}" class="btn-me btn-xanhduongnhat">Back</a>
            </div>	<!-- end wrap-btn-content-->
            <div class="wrapper-content">
                <div class="form-group">
                    <label for="">App Name</label>
                    <input type="text" class="form-control" name="name" value="{{ old('name') }}">
                </div>
                <div class="form-group">
                    <label for="">App ID</label>
                    <input type="text" class="form-control" name="app_app_id" value="{{ old('app_app_id') }}">
                </div>
                <div class="form-group">
                    <label for="">App Secret</label>
                    <input type="text" class="form-control" name="app_app_secret" value="{{ old('app_app_secret') }}">
                </div>
                <div class="form-group">
                    <label for="">App Description</label>
                    <textarea name="description" id="" cols="30" rows="10" class="form-control">{{ old('description') }}</textarea>
                </div>
            </div>	<!-- wrap-content-->
        </form>
    </div>
    <!-- END -->
@endsection


@section('footer')
    <script src="{{ url('adcp/js/script.js') }}"></script>
@endsection