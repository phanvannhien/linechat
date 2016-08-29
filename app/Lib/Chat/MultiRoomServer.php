<?php
namespace App\Lib\Chat;

// Chat Lib
use App\Lib\Chat\Exception\ConnectedClientNotFoundException;
use App\Lib\Chat\Exception\InvalidActionException;
use App\Lib\Chat\Exception\MissingActionException;
use App\Lib\Chat\Interfaces\ConnectedClientInterface;

// Chat server package
use Ratchet\ConnectionInterface;
use Ratchet\Http\HttpServer;
use Ratchet\MessageComponentInterface;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;


//  App package
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Message;
use App\User;
use Log;
use Debugbar;
use ReflectionClass;
use DB;

// BOT Server
use LINE\LINEBot;
use LINE\LINEBot\HTTPClient\GuzzleHTTPClient;
use LINE\LINEBot\Message\MultipleMessages;
use LINE\LINEBot\Message\RichMessage\Markup;



class MultiRoomServer extends Controller implements MessageComponentInterface
{
    
    const ACTION_USER_CONNECTED = 'connect';
    const ACTION_MESSAGE_RECEIVED = 'message';
    const ACTION_LIST_USERS = 'list-users';
    const ACTION_USER_STARTED_TYPING = 'start-typing';
    const ACTION_USER_STOPPED_TYPING = 'stop-typing';

    const PACKET_TYPE_USER_CONNECTED = 'user-connected';
    const PACKET_TYPE_USER_DISCONNECTED = 'user-disconnected';
    const PACKET_TYPE_MESSAGE = 'message';
    const PACKET_TYPE_USER_LIST = 'list-users';
    
    
    const PACKET_TYPE_USER_STARTED_TYPING = 'user-started-typing';
    const PACKET_TYPE_USER_STOPPED_TYPING = 'user-stopped-typing';
    

    protected $bot;
    protected $user; // User logedin
    protected $curl; // Curl to request API
    protected $botService; // BOT service configuration LINE
    protected $loginService; // LINE login service configuration

    ///@var arrays
    public $rooms;

    //@var array|ConnectedClientInterface[]
    public $clients;

    //@var array
    public $validActions;

    public function __construct()
    {
        $this->rooms = array();
        $this->clients = array();
        $refl = new ReflectionClass(get_class());
        $this->validActions = array();
        foreach ($refl->getConstants() AS $key => $value) {
            if (substr($key, 0, 6) === 'ACTION') {
                $this->validActions[$key] = $value;
            }
        }
        
        $this->user = User::where('email','test@gmail.com')->first();
    	$this->botService = [
	        'channelId' => $this->user->channelId,
	        'channelSecret' => $this->user->channelSecret,
	        'channelMid' => $this->user->channelMid,
	    ];
	    
    	$this->bot = new LINEBot($this->botService, new GuzzleHTTPClient($this->botService));
    	$this->curl = new \anlutro\cURL\cURL;
    	
    	
    }
    
    public function index(Request $request){
        
        $data = json_encode( $request->all() ) ;
        $data = json_decode($data);
        $data = $data->result[0];
        Log::info(print_r($data, true));
        Log::info(print_r($this->getRooms(), true));
        Log::info(print_r($this->getClients(), true));
        //“138311609000106303”	Received message (example: text, images)
        //“138311609100106403”	Received operation (example: added as friend)
        if ( $data->eventType == '138311609000106303' ){
            if( $data->toChannel == $this->user->channelId  && $data->to[0] == $this->user->channelMid){
                //1	Text message
                //2	Image message
                //3	Video message
                //4	Audio message
                //7	Location message
                //8	Sticker message
                //10	Contact message
                $roomId = trim( $this->user->channelMid );
                $from = $this->findClientByMid($roomId, $data->content->from);
                
                $to = $this->findClientByMid($roomId, $data->content->to[0]);
                switch ($data->content->contentType) {
                    
                    case 1: // Text Message
                    
                        $this->sendMessage($roomId,$from,$to,$data->content->text,$data->content->createdTime);
                        break;
                    

                    default:
                        // code...
                        break;
                }
                
            }
            
            
            
        }
        
        
        
    }
    
    
    // @return array
    public function getRooms()
    {
        return $this->rooms;
    }

    // @param array $rooms
    public function setRooms($rooms)
    {
        $this->rooms = $rooms;
    }

    // @return array|ConnectedClientInterface[]
    public function getClients()
    {
        return $this->clients;
    }

    // @param array|ConnectedClientInterface[] $clients
    public function setClients($clients)
    {
        $this->clients = $clients;
    }

    // @param ConnectionInterface $conn
    public function onOpen(ConnectionInterface $conn)
    {

    }

    public function onMessage(ConnectionInterface $conn, $msg)
    {
        echo '--------------------------------------------------------------------------------------------------------'.PHP_EOL;
        $msg = json_decode($msg);
        echo "Packet received: ".json_encode($msg).PHP_EOL;
       
        if (!isset($msg->action)) {
            throw new MissingActionException('No action specified');
        }
        
        $this->checkActionExists($msg->action);
        
        if ( $msg->action != self::ACTION_USER_CONNECTED) {
            $client = $this->findClient($conn);
            $roomId = $this->findClientRoom($client);
        }
        
        
        switch ( $msg->action ) {
            case self::ACTION_USER_CONNECTED:
                
                $fromClients = true;
                $roomId = $this->makeRoom( $msg->roomId );
                if( $msg->from->user_type == 'endusers' )
                    $fromClients = false;
                // Create new client    
                $client = $this->createClient($conn, 
                    $msg->from->profile->displayName,
                    $msg->from->profile->mid,
                    $msg->from->profile,
                    $fromClients);
        
                // Conntect client to room
                $this->connectUserToRoom($client, $roomId);
                //$this->sendUserMessageHistory($roomId,$client);
                break;
            case self::ACTION_LIST_USERS :
                //$this->sendListUsersMessage($client, $roomId);
                break;
            case self::ACTION_MESSAGE_RECEIVED:
               
                $msg->timestamp = isset($msg->timestamp) ? $msg->timestamp : time();
                // if Clients send message to endusers
                if( $client->getisClients() && isset($msg->to) ){
                    $toClient = $this->findClientByMid($roomId,$msg->to);
                    $to_mid = $msg->to;
                }else{
                    $toClient = $this->findClientByMid($roomId,$roomId);
                    $to_mid = $roomId;
                }
                if( $toClient )
                    $this->sendMessage($roomId, $client, $toClient, $msg->message, $msg->timestamp);
                $this->saveMessageReceived( $roomId, $client->getMid(), $to_mid, $msg->message, $msg->timestamp );
                break;
                
            case self::ACTION_USER_STARTED_TYPING:
                $this->sendUserStartedTypingMessage($client, $roomId);
                break;
                
            case self::ACTION_USER_STOPPED_TYPING:
                $this->sendUserStoppedTypingMessage($client, $roomId);
                break;
            default: throw new InvalidActionException('Invalid action: '.$msg['action']);
        }
    }

    public function onClose(ConnectionInterface $conn)
    {
        $this->closeClientConnection($conn);
    }
    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        $this->closeClientConnection($conn);
        $conn->close();
    }

    protected function findClient(ConnectionInterface $conn)
    {
        if (isset($this->clients[$conn->resourceId])) {
            return $this->clients[$conn->resourceId];
        }
        throw new ConnectedClientNotFoundException($conn->resourceId);
    }
    
    protected function findClientByMid($roomId, $mid)
    {
        $clients = $this->findRoomClients( $roomId );
        foreach( $clients as $client ){
            if( $client->getMid() == $mid ){
                return $client;
                break;
            }
        }
        return false;
    }
    
    protected function checkActionExists($action)
    {
        if (!in_array($action, $this->validActions)) {
            throw new InvalidActionException('Invalid action: '.$action);
        }
    }

    protected function connectUserToRoom(ConnectedClientInterface $client, $roomId)
    {
        $this->rooms[$roomId][$client->getResourceId()] = $client;
        $this->clients[$client->getResourceId()] = $client;
    }
    
    // return ConnectedClientInterface $client
    protected function findClientsisAdmin( $roomid ){
        $clients = $this->findRoomClients( $roomid );
        foreach( $clients as $client ){
            if( $client->getisClients() ){
                return $client;
                break;
            }
        }
        return false;
    }
    
    protected function findRoomClients($roomId)
    {
        return $this->rooms[$roomId];
    }

    /**
     * @return int|string
     */
    protected function findClientRoom(ConnectedClientInterface $client)
    {
        foreach ($this->rooms AS $roomId=>$roomClients) {
            if (isset($roomClients[$client->getResourceId()])) {
                return $roomId;
            }
        }

        throw new ConnectedClientNotFoundException($client->getResourceId());
    }

   
    /**
     * @param array|ConnectedClientInterface[] $clients
     * @param array $packet
     */
    protected function sendDataToClients(array $clients, array $packet)
    {
        foreach ($clients AS $client) {
            $this->sendData($client, $packet);
        }
    }

    /**
    * @param ConnectedClientInterface $client
    * @param array $packet
    */
    protected function sendData(ConnectedClientInterface $client, array $packet)
    {
        $client->getConnection()->send(json_encode($packet));
    }

    /**
     * @param $roomId
     * @return mixed
     */
    protected function makeRoom($roomId)
    {
        if (!isset($this->rooms[$roomId])) {
            $this->rooms[$roomId] = array();
        }
        return $roomId;
    }
    
    protected function sendMessage($roomid, ConnectedClientInterface $from,ConnectedClientInterface $to, $message, $timestamp)
    {
        $dataPacket = array(
            'type'=> self::PACKET_TYPE_MESSAGE,
            'roomid' => $roomid,
            'from'=> $from->getProfile(),
            'timestamp'=>$timestamp,
            'message'=> $message,
        );
        $this->sendData($to, $dataPacket);
    }


    protected function sendUserMessageHistory($roomId, ConnectedClientInterface $client)
    {
        // is client 
        if( $client->getisClients() ){
            
            
            $arrUserOnline = array();
            $clientsAreOnline = $this->findRoomClients($roomId);
            unset($clientsAreOnline[$client->getResourceId()]);
            if( count($clientsAreOnline) > 0 ){
                foreach( $clientsAreOnline as $useronline ){
                    array_push( $arrUserOnline, $useronline->getMid() );
                }
                
                $strUserOnline = implode(',',$arrUserOnline);
                echo 'User online'.json_encode($strUserOnline).PHP_EOL;
                $topEnduserHistory = DB::select("SELECT from_mid, to_mid FROM messages 
                WHERE from_mid IN ('{  $strUserOnline }') or to_mid IN ('{$strUserOnline}')");
                
                echo json_encode($topEnduserHistory); die();
                
                
            }
            
            $arrU = array();
            foreach( $topEnduserHistory as $user){
                $temp = array();
                $temp['mid'] = $user->to_id;
                $temp['displayName'] = $user->displayName;
                $temp['pictureUrl'] = $user->pictureUrl;
                $historyData = DB::table('messages')
                    ->where('room_id',$room_id)
                    ->where('from_mid',$client->getMid())
                    ->where('to_mid',$user->to_mid)
                    ->orderBy('created_at','DESC')
                    ->take(20)
                    ->toSql();
                    //->get();
                    
                echo 'History'.json_encode($historyData);
                
                die();
                $temp['history'] = $historyData;
                array_push( $arrU, $temp );
            }
            
            
            
            $dataPacket = array(
                'type' => self::PACKET_TYPE_USER_CONNECTED,
                'timestamp' => time(),
                'clients'=> $arrU
            );
        
            $this->sendData($client, $dataPacket);
            // Alert all enduser clients has connected
            $this->sendMessageSystemClientsConnected($roomId, $client);            

        }
        
        
    }
    
    protected function sendMessageSystemClientsConnected( $roomId,ConnectedClientInterface $client ){
        $dataPacket = array(
            'type' => self::PACKET_TYPE_USER_CONNECTED,
            'timestamp' => time(),
            'message_type'=> 'system_status',
            'message' => $client->getName().' has connected! '
        );
        
        $clients = $this->findRoomClients($roomId);
        unset($clients[$client->getResourceId()]);
        $this->sendDataToClients($clients, $dataPacket);
    }
    
  
    /**
     * @param ConnectedClientInterface $client
     * @param $roomId
     */
    protected function sendUserConnectedMessage(ConnectedClientInterface $client, $roomId)
    {
        $dataPacket = array(
            'type' => self::PACKET_TYPE_USER_CONNECTED,
            'timestamp' => time(),
            'data' => array(
                'message' => $this->makeUserConnectedMessage($client, time()),
                'profile' =>$client->getProfile() // client connected
            )
        );
        // if is clients manager, send to all enduser
        if( $client->getisClients() ){
            $clients = $this->findRoomClients($roomId);
            $this->sendDataToClients($clients, $dataPacket);
        }else{
            // is endusers connected agains, send message to client manager
            $clientManager = $this->findClientsisAdmin( $roomId );
            if( $clientManager ){
                $this->sendData( $clientManager, $dataPacket );
            }
        }
        
    }

    /**
     * @param ConnectedClientInterface $client
     * @param $roomId
     */
    protected function sendUserDisconnectedMessage(ConnectedClientInterface $client, $roomId)
    {
        $dataPacket = array(
            'type'=>self::PACKET_TYPE_USER_DISCONNECTED,
            'timestamp'=>time(),
            'mid' => $client->getMid(),
            'message_type' => 'system_status',
            'message' => $this->makeUserDisconnectedMessage($client, time())
        );
        
        // if is clients manager
        if( $client->getisClients() ){
            $clients = $this->findRoomClients($roomId);
            $this->sendDataToClients($clients, $dataPacket);
        }else{
            // is endusers disconnected, send message to client manager
            $clientManager = $this->findClientsisAdmin( $roomId );
            if( $clientManager ){
                $this->sendData( $clientManager, $dataPacket );
            }
        }
    
    }

    /**
     * @param ConnectedClientInterface $client
     * @param $roomId
     */
    protected function sendUserStartedTypingMessage(ConnectedClientInterface $client, $roomId)
    {
        $dataPacket = array(
            'type' => self::PACKET_TYPE_USER_STARTED_TYPING,
            'roomid' => $roomId,
            'from' => $client->getMid(),
            'message' => $client->getName().' is typing...',
            'message_type' => 'system_status',
            'timestamp'=>time()
        );
        $clients = $this->findRoomClients($roomId);
        unset($clients[$client->getResourceId()]);
        $this->sendDataToClients($clients, $dataPacket);
    }

    /**
     * @param ConnectedClientInterface $client
     * @param $roomId
     */
    protected function sendUserStoppedTypingMessage(ConnectedClientInterface $client, $roomId)
    {
        $dataPacket = array(
            'type'=>self::PACKET_TYPE_USER_STOPPED_TYPING,
            'from'=>$client->asArray(),
            'timestamp'=>time(),
        );

        $clients = $this->findRoomClients($roomId);
        unset($clients[$client->getResourceId()]);
        $this->sendDataToClients($clients, $dataPacket);
    }



    /**
     * @param ConnectionInterface $conn
     * @throws ConnectedClientNotFoundException
     */
    protected function closeClientConnection(ConnectionInterface $conn)
    {
        $client = $this->findClient($conn);
        unset($this->clients[$client->getResourceId()]);
        foreach ($this->rooms AS $roomId => $connectedClients) {
            if (isset($connectedClients[$client->getResourceId()])) {
                $clientRoomId = $roomId;
                unset($this->rooms[$roomId][$client->getResourceId()]);
            }
        }

        if (isset($clientRoomId)) {
            $this->sendUserDisconnectedMessage($client, $clientRoomId);
        }
    }

    protected function createClient(ConnectionInterface $conn, $name,$mid,$profile,$isClients )
    {
        
        $client = new ConnectedClient();
        $client->setResourceId($conn->resourceId);
        $client->setConnection($conn);
        $client->setName($name);
        $client->setMid($mid);
        $client->setProfile($profile);
        $client->setisClients($isClients);
        return $client;
    }
    protected function saveMessageReceived($room_id,$from_mid,$to_mid, $message, $timestamp)
    {
        $created = Message::create([
            'room_id' => $room_id,
            'from_mid' => $from_mid,
            'to_mid' => $to_mid,
            'message' => $message,
            'created_at' => $timestamp
        ]);
        return $created;
    }
    protected function makeUserWelcomeMessage(ConnectedClientInterface $client, $timestamp)
    {
        return vsprintf('Welcome %s!', array($client->getName()));
    }

    protected function makeUserConnectedMessage(ConnectedClientInterface $client, $timestamp)
    {
        return vsprintf('%s has connected', array($client->getName()));
    }

    protected function makeUserDisconnectedMessage(ConnectedClientInterface $client, $timestamp)
    {
        return vsprintf('%s has left', array($client->getName()));
    }

}