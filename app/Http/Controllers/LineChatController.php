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


class LineChatController extends Controller
{
    //

    private $bot;
    private $user;
    protected $clients;
   
    protected $botService;
    protected $loginService;
   
    public function __construct(){
        $this->clients = new \SplObjectStorage;
    	$this->botService = [
	        'channelId' => '1476076743',
	        'channelSecret' => 'c3b5f65446faefcf1471609353cc943c',
	        'channelMid' => 'uaa357d613605ebf36f6366a7ce896180',
	    ];
	    

    	$this->bot = new LINEBot($this->botService, new GuzzleHTTPClient($this->botService));
    }

    /*
    * Route: /
    * Handle BOT Server callback
    */
    public function index(Request $request){
        $from = json_decode(json_encode($request->all()));
        $from = $from[0]->content['from'];

    }
    
    /*
    * Route: /chat/{mid}
    * View chat enduser
    */
    public function chat($mid){
        $profile = $this->bot->getUserProfile($mid);
        return view('chat',[
            'profile' => json_encode($profile['contacts'][0]),
            'room_id' => $this->botService['channelMid']
        ]);
    }
    
    /*
    * Route: /login
    * Login LINE button
    */
    
    public function login(){
        return view('login');
    }
    
    /*
    * Route: /verifined
    * Callback LINE authentication
    */
    public function verifined(Request $request){
       
        if( $request->has('code') ){
            $curl = new \anlutro\cURL\cURL;
            $this->loginService = array(
                'grant_type' => 'authorization_code', 
                'client_id' => '1477592731',
                'client_secret' => '789ac444af36a5020a5b4c74a9455f5f',
                'code' => $request->input('code'),
                'redirect_uri' => 'https://tenposs-phanvannhien.c9users.io/verifined'
            );
            $response = $curl->post('https://api.line.me/v1/oauth/accessToken', $this->loginService);
        
            if($response->statusCode == 200){
             
                $data = json_decode($response->body);
                //$res = $this->bot->sendText($data->mid, 'Welcome to Tenposs');
                return redirect('chat/'.$data->mid);
            }
            
        }
        // if request LINE login fail
        if( $request->has('errorCode') ){
            return view('login',['errors' => $request->input('errorMessage') ]);
        }
        
    }

   
    /*
    * Route: /admin/chat
    * 
    */
    public function chatAdmin(){
        return view('admin.chat.message');
    }
    
}
