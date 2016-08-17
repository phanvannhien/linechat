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

class LineChatController extends Controller
{
    //

    private $bot;
    public function __construct(){
    	$config = [
	        'channelId' => '1476076743',
	        'channelSecret' => 'c3b5f65446faefcf1471609353cc943c',
	        'channelMid' => 'uaa357d613605ebf36f6366a7ce896180',
	    ];
    	$this->bot = new LINEBot($config, new GuzzleHTTPClient($config));
    }

    public function index(Request $request){
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
                $data = json_decode($response->body);
                $res = $this->bot->sendText($data->mid, 'Welcome to Tenposs');
                
            }
            
        }
        // if request LINE login fail
        if( $request->has('errorCode') ){
            return view('login',['errors' => $request->input('errorMessage') ]);
        }
        
        
         dd($request->all());
    }


    
}
