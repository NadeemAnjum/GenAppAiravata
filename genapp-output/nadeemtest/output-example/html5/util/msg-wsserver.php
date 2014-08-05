<?php

// todo: monitor connections and on close, remove any associated topic keys

$json = json_decode( file_get_contents( "/home/nadeem/nadeemtest/appconfig.json" ) );

require '../vendor/autoload.php';

use Ratchet\ConnectionInterface;
use Ratchet\Wamp\WampServerInterface;

class Pusher implements WampServerInterface {
    /**
     * A lookup of all the topics clients have subscribed to
     */
    protected $subscribedTopics = array();

    public function onOpen(ConnectionInterface $conn) {

    }
    public function onClose(ConnectionInterface $conn) {

    }
    public function onCall(ConnectionInterface $conn, $id, $topic, array $params) {
        // In this application if clients send data it's because the user hacked around in console

        $conn->callError($id, $topic, 'You are not allowed to make calls')->close();
    }
    public function onPublish(ConnectionInterface $conn, $topic, $event, array $exclude, array $eligible) {
        // In this application if clients send data it's because the user hacked around in console

        $conn->close();
    }
    public function onError(ConnectionInterface $conn, \Exception $e) {

    }

// here's where we start

    public function onSubscribe(ConnectionInterface $conn, $topic) {
        // When a visitor subscribes to a topic link the Topic object in a  lookup array


        if ( substr( $topic->getId(), 0, 6 ) == 'unsub:' )
        { 
           $tmp = substr( $topic->getId(), 6 );

           unset( $this->subscribedTopics[ $tmp ] );
        } else {
           if (!array_key_exists($topic->getId(), $this->subscribedTopics)) {
              $this->subscribedTopics[$topic->getId()] = $topic;
           }
        }
    }

    public function onUnSubscribe(ConnectionInterface $conn, $submsg) {

      if (array_key_exists((string) $submsg, $this->subscribedTopics)) {

         unset( $this->subscribedTopics[ (string) $submsg ] );
      } else {

      }


    }

    /**
     * @param string JSON'ified string we'll receive from ZeroMQ
     */
    public function onMsgPost($postmsg) {
        $postData = json_decode($postmsg, true);







        
        if (!array_key_exists( $postData[ '_uuid' ], $this->subscribedTopics ) ) {

            return;
        }


        $topic = $this->subscribedTopics[$postData['_uuid']];

        // re-send the data to all the clients subscribed to that category


        $topic->broadcast($postData);
    }
}


$loop   = React\EventLoop\Factory::create();
$pusher = new Pusher;

// Listen for the web server to make a ZeroMQ push after an ajax request
$context = new React\ZMQ\Context($loop);
$pull = $context->getSocket(ZMQ::SOCKET_PULL);
$pull->bind('tcp://' . $json->messaging->zmqhostip . ':' . $json->messaging->zmqport ); // Binding to 127.0.0.1 means the only client that can connect is itself
$pull->on('message', array($pusher, 'onMsgPost'));

// Set up our WebSocket server for clients wanting real-time updates
$webSock = new React\Socket\Server($loop);
$webSock->listen( $json->messaging->wsport, '0.0.0.0'); // Binding to 0.0.0.0 means remotes can connect
$webServer = new Ratchet\Server\IoServer(
    new Ratchet\Http\HttpServer(
        new Ratchet\WebSocket\WsServer(
            new Ratchet\Wamp\WampServer(
                $pusher
            )
        )
    ),
    $webSock
);

echo "msg_wsserver: listening WS port:" . $json->messaging->wsport . " receiving ZMQ port: " . $json->messaging->zmqport . PHP_EOL;

$loop->run();
