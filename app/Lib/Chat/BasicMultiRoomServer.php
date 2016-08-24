<?php
namespace App\Lib\Chat;

use App\Lib\Chat\Interfaces\ConnectedClientInterface;
use Ratchet\ConnectionInterface;
use App\Message;


class BasicMultiRoomServer extends AbstractMultiRoomServer
{

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
        Message::create([
            ''
        ]);
    }

    protected function createClient(ConnectionInterface $conn, $name,$mid,$profile)
    {
        $client = new ConnectedClient;
        $client->setResourceId($conn->resourceId);
        $client->setConnection($conn);
        $client->setName($name);
        $client->setMid($mid);
        $client->setProfile($profile);
        return $client;
    }

}