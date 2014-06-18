<?php

// todo: monitor connections and on close, remove any associated topic keys

$json = json_decode( file_get_contents( "__appconfig__" ) );

require '../vendor/autoload.php';

use Ratchet\ConnectionInterface;
use Ratchet\Wamp\WampServerInterface;

class Pusher implements WampServerInterface {
    /**
     * A lookup of all the topics clients have subscribed to
     */
    protected $subscribedTopics = array();

    public function onOpen(ConnectionInterface $conn) {
__~debugws{       echo "msg-wsserver.php onOpen\n";}
    }
    public function onClose(ConnectionInterface $conn) {
__~debugws{       echo "msg-wsserver.php onClose\n";}
    }
    public function onCall(ConnectionInterface $conn, $id, $topic, array $params) {
        // In this application if clients send data it's because the user hacked around in console
__~debugws{        echo "msg-wsserver.php onCall\n";}
        $conn->callError($id, $topic, 'You are not allowed to make calls')->close();
    }
    public function onPublish(ConnectionInterface $conn, $topic, $event, array $exclude, array $eligible) {
        // In this application if clients send data it's because the user hacked around in console
__~debugws{        echo "msg-wsserver.php onPublish\n";}
        $conn->close();
    }
    public function onError(ConnectionInterface $conn, \Exception $e) {
__~debugws{        echo "msg-wsserver.php onError\n";}
    }

// here's where we start

    public function onSubscribe(ConnectionInterface $conn, $topic) {
        // When a visitor subscribes to a topic link the Topic object in a  lookup array
__~debugws{        echo "msg-wsserver.php received topic id:" . $topic->getId() . "\n";}
__~debugws{        echo "onSubscribe: topic getId " . $topic->getId() . "\n";}
        if ( substr( $topic->getId(), 0, 6 ) == 'unsub:' )
        { 
           $tmp = substr( $topic->getId(), 6 );
__~debugws{           echo "as unsub $tmp\n";}
           unset( $this->subscribedTopics[ $tmp ] );
        } else {
           if (!array_key_exists($topic->getId(), $this->subscribedTopics)) {
              $this->subscribedTopics[$topic->getId()] = $topic;
           }
        }
    }

    public function onUnSubscribe(ConnectionInterface $conn, $submsg) {
__~debugws{       echo "msg-wsserver.php onUnSubscribe $submsg\n";}
      if (array_key_exists((string) $submsg, $this->subscribedTopics)) {
__~debugws{         echo "unsub: array key exists\n";}
         unset( $this->subscribedTopics[ (string) $submsg ] );
      } else {
__~debugws{         echo "unsub: array key does NOT exists\n";}
      }
__~debugws{        print_r( array_keys( $this->subscribedTopics ) );}
__~debugws{        echo "\n---\n";}
    }

    /**
     * @param string JSON'ified string we'll receive from ZeroMQ
     */
    public function onMsgPost($postmsg) {
        $postData = json_decode($postmsg, true);

__~debugws{        echo "msg-wsserver.php received postmsg, postData\n";}
__~debugws{        print_r( $postmsg );}
__~debugws{        echo "\n---\n";}
__~debugws{        print_r( $postData );}
__~debugws{        echo "\n---\n";}
__~debugws{        echo "postData[_uuid] = " . $postData[ '_uuid' ] . "\n";}
        
        if (!array_key_exists( $postData[ '_uuid' ], $this->subscribedTopics ) ) {
__~debugws{            echo "no array key exists\n";}
            return;
        }

__~debugws{        echo "array key exists\n";}
        $topic = $this->subscribedTopics[$postData['_uuid']];

        // re-send the data to all the clients subscribed to that category
__~debugws{        echo "onMsgPost broadcast()\n";}

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
