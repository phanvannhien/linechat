<?php
namespace App\Lib\Chat;

use App\Lib\Chat\Interfaces\ConnectedClientInterface;
use Ratchet\ConnectionInterface;

class ConnectedClient implements ConnectedClientInterface
{

    /**
     * @var mixed
     */
    protected $resourceId;

    /**
     * @var ConnectionInterface
     */
    protected $connection;

    /**
     * @var string
     */
    protected $name;

     /**
     * @var string
     */
    protected $mid;

     /**
     * @var array
     */
    protected $profile;
    
    /**
     * @var bool
     */
    protected $isClients;


    /**
     * @return mixed
     */
    public function getResourceId()
    {
        return $this->resourceId;
    }

    /**
     * @param mixed $resourceId
     */
    public function setResourceId($resourceId)
    {
        $this->resourceId = $resourceId;
    }

    /**
     * @return ConnectionInterface
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * @param ConnectionInterface $connection
     */
    public function setConnection($connection)
    {
        $this->connection = $connection;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }
    
    /**
    * @param string $name
    */
    public function setMid($mid)
    {
        $this->mid = $mid;
    }
    
    /**
     * @return string
     */
    public function getMid()
    {
        return $this->mid;
    }

    /**
     * @param array $profile
     */
    public function setProfile($profile)
    {
        $this->profile = $profile;
    }

     /**
     * @return array
     */
    public function getProfile()
    {
        return $this->profile;
    }

     /**
     * @param bool
     */
    public function setisClients($bool)
    {
        $this->isClients = $bool;
    }

     /**
     * @return bool
     */
    public function getisClients()
    {
        return $this->isClients;
    }

    /**
     * @return array
     */
    public function asArray()
    {
        return array(
            'name' => $this->name,
            'isClients' => $this->getisClients(),
            'mid' => $this->mid,
            'profile' => $this->profile 
        );
    }

}