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
    protected $rooms;

    //@var array|ConnectedClientInterface[]
    protected $clients;

    //@var array
    protected $validActions;

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
        return 'This is BOT server handle';
        Debugbar::info($request->input('result'));
      
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

    /**
     * @param ConnectionInterface $conn
     * @param string $msg
     * @throws ConnectedClientNotFoundException
     * @throws InvalidActionException
     * @throws MissingActionException
     */
    public function onMessage(ConnectionInterface $conn, $msg)
    {
        echo '--------------------------------------------------------------------------------------------------------'.PHP_EOL;
        echo "Packet received: ".$msg.PHP_EOL;
        $msg = json_decode($msg, true);
        echo '--------------------------------------------------------------------------------------------------------'.PHP_EOL;
        
        
        if (!isset($msg['action'])) {
            throw new MissingActionException('No action specified');
        }
        $this->checkActionExists($msg['action']);

        if ($msg['action'] != self::ACTION_USER_CONNECTED) {
            $client = $this->findClient($conn);
            $roomId = $this->findClientRoom($client);
        }

        switch ($msg['action']) {
            case self::ACTION_USER_CONNECTED:
                
                $fromClients = false;
                $roomId = $this->makeRoom($msg['roomId']);

                if( $msg['from'] == 'clients' )
                    $fromClients = true;
                    
                $client = $this->createClient($conn, $msg['userName'],$msg['mid'],$msg['profile'],$fromClients);
                $this->connectUserToRoom($client, $roomId);
                
                echo 'This rooms:'.PHP_EOL;
                echo json_encode($this->getRooms()).PHP_EOL;
                echo '--------------------------------------------------------------------------------------------------------'.PHP_EOL;

                // Send update list clients to clients
                //$this->sendListUsersMessage($client, $roomId);
                
                
                // is Endusers
                if( ! $fromClients ){
                   
                    // send message history to enduser connected
                    $this->sendUserMessageHistory($roomId,$client);
                    // If has clients in rooms, send enduser to clients
                    if( $findisClients = $this->findClientsisAdmin($roomId) ){
                        $this->sendUserMessageHistory($roomId,$client);
                    }
            
                }
                
                // is clients
                if( $fromClients ){
                   
                    // load history message include clients online and offline
                    $this->sendUserMessageHistory($roomId,$client);
                   
            
                }
                
                
                
                break;
            case self::ACTION_LIST_USERS :
                //$this->sendListUsersMessage($client, $roomId);
                break;
            case self::ACTION_MESSAGE_RECEIVED:
                
                
                $msg['timestamp'] = isset($msg['timestamp']) ? $msg['timestamp'] : time();
                $this->logMessageReceived($client, $roomId, $msg['message'], $msg['timestamp']);
                $this->sendMessage($client, $roomId, $msg['message'], $msg['timestamp']);
                $this->sendUserStoppedTypingMessage($client, $roomId);
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

    

    /**
     * @param ConnectionInterface $conn
     * @return ConnectedClientInterface
     * @throws ConnectedClientNotFoundException
     */
    protected function findClient(ConnectionInterface $conn)
    {
        if (isset($this->clients[$conn->resourceId])) {
            return $this->clients[$conn->resourceId];
        }

        throw new ConnectedClientNotFoundException($conn->resourceId);
    }
    
        /**
     * @param $action
     * @throws InvalidActionException
     */
    protected function checkActionExists($action)
    {
        if (!in_array($action, $this->validActions)) {
            throw new InvalidActionException('Invalid action: '.$action);
        }
    }

    /**
     * @param ConnectedClientInterface $client
     * @param $roomId
     */
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
    
    // return ConnectedClientInterface $client
    protected function findClientInRoom( $roomId, $mid ){
        $clients = $this->findRoomClients( $roomid );
        foreach( $clients as $client ){
            if( $clients->getMid() == $mid ){
                return $client;
                break;
            }
        }
        return false;
    }
    

    /**
     * @param $roomId
     * @return array|ConnectedClientInterface[]
     */
    protected function findRoomClients($roomId)
    {
        return $this->rooms[$roomId];
    }

    /**
     * @param ConnectedClientInterface $client
     * @return int|string
     * @throws ConnectedClientNotFoundException
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

   
    protected function getMessageHistory($room_id, $from){
        $messages = Message::where('room_id',$room_id)
            ->where('from_mid',$from->getMid())
            ->orderBy('created_at','DESC')
            ->paginate(10);
        if( $messages->count() > 0 )
            return $messages;    
        return false;
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
    
    

    /**
     * @param ConnectedClientInterface $client
     * @param $roomId
     * @param $message
     * @param $timestamp
     */
    protected function sendMessage(ConnectedClientInterface $client, $roomId, $message, $timestamp)
    {
        $dataPacket = array(
            'type'=>self::PACKET_TYPE_MESSAGE,
            'roomid' => $roomId,
            'from'=>$client->asArray(),
            'timestamp'=>$timestamp,
            'message'=>$this->makeMessageReceivedMessage($client, $message, $timestamp),
        );

        $clients = $this->findRoomClients($roomId);
        unset($clients[$client->getResourceId()]);
        $this->sendDataToClients($clients, $dataPacket);
    }

    

   

    protected function sendUserMessageHistory($roomId,ConnectedClientInterface $client)
    {
        $message = null;
        
        if( $checkMessage = $this->getMessageHistory($roomId,$client) ){
            $message = $checkMessage;
        }else{
            $message = $this->makeUserWelcomeMessage($client, time());
        }
        
       
        $dataPacket = array(
            'type' => self::PACKET_TYPE_USER_CONNECTED,
            'timestamp' => time(),
            'message'=> $message,
        );

        $this->sendData($client, $dataPacket);
        
    }

     /**
     * @param ConnectedClientInterface $client
     * @param $roomId
     */
    protected function sendUserWelcomeMessage(ConnectedClientInterface $client, $roomId)
    {
        $dataPacket = array(
            'type'=>self::PACKET_TYPE_USER_CONNECTED,
            'timestamp'=>time(),
            'data' => array(
                'message' => $this->makeUserWelcomeMessage($client, time()),
            ) 
            
        );
        $this->sendData($client, $dataPacket);
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
            'roomid' => $roomId,
            'data' => array(
                'message'=> $this->makeUserDisconnectedMessage($client, time()),
                'message_type' => 'system_status',
                'client' => $client->getProfile() // client disconnected
            ),
            
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
            'type'=>self::PACKET_TYPE_USER_STARTED_TYPING,
            'timestamp'=>time(),
            'roomid' => $roomId,
            'data' => array(
                'message'=> $client->getName().' is typing...',
                'message_type' => 'system_status',
                'profile' => $client->getProfile()
            )
            
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
     * @param ConnectedClientInterface $client
     * @param $roomId
     */
    protected function sendListUsersMessage(ConnectedClientInterface $client, $roomId)
    {
        $clients = array();
        $cliensInRooms = $this->findRoomClients($roomId);
        foreach ($cliensInRooms AS $roomClient) {
    
                $clients[] = array(
                    'roomid' => $roomId,
                    'name'=> $roomClient->getName(),
                    'mid' => $roomClient->getMid(),
                    'profile' => $roomClient->getProfile(),
                    'isClients' => $roomClient->getisClients()
                );
        }

        $dataPacket = array(
            'type'=>self::PACKET_TYPE_USER_LIST,
            'timestamp'=>time(),
            'clients'=>$clients,
            
        );
        
        if( $client->getisClients() ){
            $this->sendData($client, $dataPacket);
        }else{
            foreach ($cliensInRooms as $client){
                if( $client->getisClients() ){
                    $this->sendData($client, $dataPacket);
                    break;
                }
            }
        }
        
        
    }


    /**
     * @param ConnectionInterface $conn
     */
    public function onClose(ConnectionInterface $conn)
    {
        $this->closeClientConnection($conn);
    }

    /**
     * @param ConnectionInterface $conn
     * @param \Exception $e
     */
    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        $this->closeClientConnection($conn);
        $conn->close();
    }

    /**
     * @param ConnectionInterface $conn
     * @throws ConnectedClientNotFoundException
     */
    protected function closeClientConnection(ConnectionInterface $conn)
    {
        $client = $this->findClient($conn);

        unset($this->clients[$client->getResourceId()]);
        foreach ($this->rooms AS $roomId=>$connectedClients) {
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
        
        $client = new ConnectedClient;
        $client->setResourceId($conn->resourceId);
        $client->setConnection($conn);
        $client->setName($name);
        $client->setMid($mid);
        $client->setProfile($profile);
        $client->setisClients($isClients);
  
        return $client;
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

    protected function makeMessageReceivedMessage(ConnectedClientInterface $from, $message, $timestamp)
    {
        return $message;
    }

    protected function logMessageReceived(ConnectedClientInterface $from, $message, $timestamp)
    {
        /** save messages to a database, etc... */
        $created = Message::create([
            'room_id' => ''
        ]);
    }

}