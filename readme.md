#Setup Invenroiment

**Setup nodejs server and redis server Ubuntu

*curl -sL https://deb.nodesource.com/setup_4.x | sudo -E bash -
*sudo apt-get install -y nodejs
*sudo apt-get install redis-server


**Install node module

*npm install express ioredis socket.io --save --no-bin-links

**Add laravel predis package
*"predis/predis": "~1.1@dev"
*composer update

**Setup laravel load service Facades
*'Redis' => Illuminate\Support\Facades\Redis::class,
*'L5Redis' => Illuminate\Support\Facades\Redis::class,
