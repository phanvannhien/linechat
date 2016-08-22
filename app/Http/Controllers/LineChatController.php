<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use \LINE\LINEBot;
use LINE\LINEBot\HTTPClient\GuzzleHTTPClient;
use LINE\LINEBot\Message\MultipleMessages;
use LINE\LINEBot\Message\RichMessage\Markup;
use App\User;
use File;
use Log;
use App;
use App\Message;
use App\LineAccount;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class LineChatController extends Controller implements MessageComponentInterface
{
    //

    private $bot;
    private $user;
    protected $clients;
   
    
    public function __construct(){
        $this->clients = new \SplObjectStorage;
    	$config = [
	        'channelId' => '1476076743',
	        'channelSecret' => 'c3b5f65446faefcf1471609353cc943c',
	        'channelMid' => 'uaa357d613605ebf36f6366a7ce896180',
	    ];
    	$this->bot = new LINEBot($config, new GuzzleHTTPClient($config));
    	
    
    }

    public function index(Request $request){
        
        $from = json_decode(json_encode($request->all()));
        
        $from = $from[0]->content['from'];
        
        
    }
    /*
    * Login line view
    */
    
    public function login(){
        return view('login');
    }
    
    public function verifined(Request $request){
        // if request LINE login success
        //https://developers.line.me/web-login/integrating-web-login#redirect_to_web_site
        if( $request->has('code') ){
            $curl = new \anlutro\cURL\cURL;
            //https://developers.line.me/web-login/integrating-web-login#obtain_access_token
            $response = $curl->post('https://api.line.me/v1/oauth/accessToken', array(
                'grant_type' => 'authorization_code', 
                'client_id' => '1477592731',
                'client_secret' => '789ac444af36a5020a5b4c74a9455f5f',
                'code' => $request->input('code'),
                'redirect_uri' => 'https://tenposs-phanvannhien.c9users.io/verifined'
            ));
            
            if($response->statusCode == 200){
                // Store LINE accounts
                $data = json_decode($response->body);
                $displayName = '';
                $pictureUrl = '';
                $statusMessage = '';
                
                //$res = $this->bot->sendText($data->mid, 'Welcome to Tenposs');
                return redirect('chat/'.$data->mid);
                
            }
            
        }
        // if request LINE login fail
        if( $request->has('errorCode') ){
            return view('login',['errors' => $request->input('errorMessage') ]);
        }
        
    }

    public function chat($mid){
        $profile = $this->bot->getUserProfile($mid);
        return view('chat',['profile' => json_encode($profile['contacts'][0]) ]);
    }
    

    


  public function onOpen(ConnectionInterface $conn) {
    // Store the new connection to send messages to later
    $this->clients->attach($conn);
    echo "New connection! ({$conn->resourceId})\n";
    
    $res = $this->bot->sendText('u9c1af340d8af0d5aa7e63fffa2c2aa28', 'Welcome to Tenposs');
  
    $numRecv = count($this->clients) - 1;
     

    // When a new client connects, greet him.
    $conn->send(sprintf('Welcome connection #%d. You are now %d user(s) in this chat.', $conn->resourceId, $numRecv));

    // And tell the other clients about the new user.
    foreach ($this->clients as $client) {
       
      if ($conn !== $client) {
        $client->send(sprintf('Connection %d has connected to %d other user(s)', $conn->resourceId, $numRecv));
      }
    }
  }

  public function onMessage(ConnectionInterface $from, $msg) {
      
    // The clients are, in this example, are not sending any messages.
      $numRecv = count($this->clients) - 1;
        echo sprintf('Connection %d sending message "%s" to %d other connection%s' . "\n"
            , $from->resourceId, json_decode($msg), $numRecv, $numRecv == 1 ? '' : 's');
        foreach ($this->clients as $client) {
            if ($from !== $client) {
                // The sender is not the receiver, send to each client connected
                $client->send($msg);
            }
        }
  }

  public function onClose(ConnectionInterface $conn) {
    // The connection is closed, remove it, as we can no longer send it
    // messages.
    $this->clients->detach($conn);

    echo "Connection {$conn->resourceId} has disconnected\n";
  }

  public function onError(ConnectionInterface $conn, \Exception $e) {
    echo "An error has occurred: {$e->getMessage()}\n";

    $conn->close();
  }
    
    public function chatAdmin(){
        return view('admin.chat.message');
    }
    
}
