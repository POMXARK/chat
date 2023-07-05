<?php

namespace Domain;

use Ratchet\ConnectionInterface;
use Ratchet\WebSocket\MessageComponentInterface;

class GraphsWebsocket implements MessageComponentInterface
{

    protected $clients;
    protected $db;

    public function __construct() {
        $this->db = new DB();
        $this->clients = new \SplObjectStorage;
    }

    public function onOpen(ConnectionInterface $conn) {
        $socketId = sprintf('%d.%d', random_int(1, 1000000000), random_int(1, 1000000000));
        $conn->socketId = $socketId;
        $conn->app = new \stdClass();
        $conn->app->id = 'my_app';
        $this->clients->attach($conn);
        echo "New connection! ({$conn->resourceId})\n";



//        $sql = 'SELECT ai1, ai2, ai3, ai4,ai5, ai6, ai7, ai8, ai9, ai10
//                FROM obj1_cmn
//                LIMIT 24';
//        $categories = '"categories": ' . json_encode(array_merge(...$this->db::query('SELECT date FROM obj1_cmn LIMIT 24')->fetchAll(\PDO::FETCH_NUM)));
//        $rez = SerializeGraph::getGraphData($this->db::query($sql)->fetchAll(\PDO::FETCH_ASSOC));
//        $conn->send('{"value_1":{ ' . $categories . ', "series": '  . json_encode($rez) . '}}');
    }

    public function onMessage(ConnectionInterface $conn, $msg) {
        $countPoints = preg_replace("/\D+/", "", $msg->getContents());
        $sql = 'SELECT ai1, ai2, ai3, ai4,ai5, ai6, ai7, ai8, ai9, ai10
                FROM obj1_cmn
                LIMIT ' . $countPoints;
        $categories = '"categories": ' . json_encode(array_merge(...$this->db::query('SELECT date FROM obj1_cmn LIMIT ' . $countPoints)->fetchAll(\PDO::FETCH_NUM)));
        $rez = SerializeGraph::getGraphData($this->db::query($sql)->fetchAll(\PDO::FETCH_ASSOC));
        $conn->send('{"value_1":{ ' . $categories . ', "series": '  . json_encode($rez) . '}}');
    }

    public function onClose(ConnectionInterface $conn) {
        // The connection is closed, remove it, as we can no longer send it messages
        $this->clients->detach($conn);

        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}\n";

        $conn->close();
    }
}
