<!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8">
    <title>Enduser Chat</title>
    
    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
     <!-- Optional theme -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">
    <link rel="stylesheet" href="{{ secure_asset('assets/css/front-chat.css') }} "/>
   
  </head>
  <body>
    <div class="container">
      <div class="panel panel-info">
        <div class="panel-body">
          @foreach($datas as $d)
            <div class="media">
              <div class="media-left">
                <a href="{{ route('line.verifined.token',['id' => $d->mid ]) }}">
                  <img class="media-object" src="{{ $d->pictureUrl.'/small' }}" alt="{{ $d->displayName }}">
                </a>
              </div>
              <div class="media-body">
                <h4 class="media-heading">{{ $d->displayName }}</h4>
                {{ $d->statusMessage }}
              </div>
            </div>
          @endforeach
        </div>
      </div><!--end panel --> 
    </div><!--end container -->  
  </body>
</html>