<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width">
    <title>Login - Tenposs</title>
    <link rel="stylesheet" href="{{ url('adcp/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ url('adcp/css/style.css') }}">
    @yield('header')
</head>
<body>
<div class="page">
  <div class="container">
      <div class="col-lg-4 col-lg-offset-4">
          <p>&nbsp;</p>
          <p>&nbsp;</p>
          <div class="panel panel-info">
              <div class="panel-body">
                  <div class="panel-title">
                      Administration
                      <p>&nbsp;</p>
                      @include('admin.partials.message')
                  </div>
                  <form class="form" action="" method="post">
                        <div class="form-group">
                            <label for="">Email</label>
                            <input type="email" class="form-control" name="email" value="{{ old('email') }}">
                        </div>
                        <div class="form-group">
                          <label for="">Password</label>
                          <input type="text" class="form-control" name="password"  value="{{ old('password') }}">
                        </div>
                        <button class="btn btn-primary" type="submit">Login</button>
                        <p>&nbsp;</p>
                        <p>
                            Email: client@tenposs.com <br>
                            Pass: 123456
                        </p>
                  </form>
              </div>
          </div>
      </div>
  </div>
</div>

@yield('footer')
</body>
</html>