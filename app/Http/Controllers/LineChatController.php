<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use \LINE\LINEBot;
use LINE\LINEBot\HTTPClient\GuzzleHTTPClient;
use LINE\LINEBot\Message\MultipleMessages;
use LINE\LINEBot\Message\RichMessage\Markup;

use File;
use Log;
use App;
use App\Message;
use App\LineAccount;
use App\User;

class LineChatController extends Controller
{
    //

    protected $bot;
    protected $user;
    protected $curl;
    protected $botService;
    protected $loginService;
   
    public function __construct(App\User $user){
        $this->user = $user->where('email','test@gmail.com')->first();
    	$this->botService = [
	        'channelId' => $this->user->channelId,
	        'channelSecret' => $this->user->channelSecret,
	        'channelMid' => $this->user->channelMid,
	    ];
	    
    	$this->bot = new LINEBot($this->botService, new GuzzleHTTPClient($this->botService));
    	$this->curl = new \anlutro\cURL\cURL;
    	
    }

    /*
    * Route: /
    * Handle BOT Server callback
    */
    public function index(Request $request){
        return 'This is the BOT Server handle';
        $from = json_decode(json_encode($request->all()));
        $from = $from[0]->content['from'];

    }
    
    public function chatScreen($user_id){
         // Check users having line account
        $lineAccounts = LineAccount::where('user_id',$user_id)->get();
    
        if( $lineAccounts->count() <= 0 ){
            return redirect()->route('line.login');
        }
        return view('endusers.linelists',['datas' => $lineAccounts ]);
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
    * Route: /line/verifined/token/{mid}
    * Login LINE button
    */
    public function verifinedToken($mid){
        $lineAccount = LineAccount::where('mid',$mid)->firstOrFail();
        
        $request = $this->curl->newRequest('get','https://api.line.me/v1/oauth/verify')
             ->setHeader('Authorization', $lineAccount->token_type.' '.$lineAccount->access_token);
        $response = $request->send();
        $responseData = json_decode(json_encode($response->body));
        
        if( isset( $responseData->statusCode ) && $responseData->statusCode == 401 ){
            // refress new token
            return view('login');
        }else{
            return redirect('chat/'.$mid);
            if( $this->user->client_id ==  $responseData->channelId )
            {
                
            }
        }
        // check if token and service is true
        
 
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
                'client_id' => $this->user->client_id,
                'client_secret' => $this->user->client_secret,
                'code' => $request->input('code'),
                'redirect_uri' => 'https://tenposs-phanvannhien.c9users.io/verifined'
            );
            // Request to get access token
            $response = $curl->post('https://api.line.me/v1/oauth/accessToken', $this->loginService);
            if($response->statusCode == 200){
             
                $data = json_decode($response->body);
                $getprofile = $this->bot->getUserProfile($data->mid);
                $profile = json_decode(json_encode($getprofile['contacts'][0]));
                
                // Save Line Account
                $exitsLineAccount = LineAccount::where('mid', $data->mid )->firstOrFail();
                if( $exitsLineAccount->count() > 0 ){
                    // Update token
                    $exitsLineAccount->access_token = $data->access_token;
                    $exitsLineAccount->token_type = $data->token_type;
                    $exitsLineAccount->expires_in = $data->expires_in;
                    $exitsLineAccount->refresh_token = $data->refresh_token;
                    $exitsLineAccount->save();
                    $this->user->line()->save($exitsLineAccount);
                
                }else{
                    // Create new
                     $this->user->line()->create([
                        'mid' => $data->mid,
                        'displayName' => $profile->displayName,
                        'pictureUrl' => $profile->pictureUrl,
                        'statusMessage' => $profile->statusMessage,
                        'access_token' => $data->access_token,
                        'token_type' => $data->token_type,
                        'expires_in' => $data->expires_in,
                        'refresh_token' => $data->refresh_token,
                        'scope' => ''
                    ]);
                }
               
               
                
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
