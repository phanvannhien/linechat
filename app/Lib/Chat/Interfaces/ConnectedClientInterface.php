<?php
namespace App\Lib\Chat\Interfaces;

use Ratchet\ConnectionInterface;

interface ConnectedClientInterface
{

    /**
     * @return mixed
     */
    public function getResourceId();

    /**
     * @param mixed $resourceId
     */
    public function setResourceId($resourceId);

    /**
     * @return ConnectionInterface
     */
    public function getConnection();

    /**
     * @param ConnectionInterface $connection
     */
    public function setConnection($connection);

    /**
     * @return string
     */
    public function getName();

    /**
     * @param string $name
     */
    public function setName($name);
    
    /**
     * @return string
     */
    public function getMid();

    /**
     * @param string $name
     */
    public function setMid($mid);

    /**
     * @return array
     */
    public function asArray();

}