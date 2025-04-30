<?php
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

require 'vendor/autoload.php';

class FileServer implements MessageComponentInterface {
    protected $clients;

    public function __construct() {
        $this->clients = new \SplObjectStorage;
    }

    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        $data = json_decode($msg, true);

        if (isset($data['action'])) {
            if ($data['action'] === "get_files") {
                // Get list of uploaded files
                $files = glob('uploads/*');
                $fileNames = array_map('basename', $files);
                $from->send(json_encode(["status" => "success", "files" => $fileNames]));
            } elseif ($data['action'] === "download_file" && isset($data['filename'])) {
                $filePath = 'uploads/' . basename($data['filename']);
                if (file_exists($filePath)) {
                    $fileContents = base64_encode(file_get_contents($filePath));
                    $from->send(json_encode([
                        "status" => "success",
                        "filename" => $data['filename'],
                        "filedata" => $fileContents
                    ]));
                } else {
                    $from->send(json_encode(["status" => "error", "message" => "File not found."]));
                }
            }
        } elseif (isset($data['filename']) && isset($data['filedata'])) {
            // Save uploaded file
            $filePath = 'uploads/' . basename($data['filename']);
            file_put_contents($filePath, base64_decode($data['filedata']));
            $from->send(json_encode(["status" => "success", "message" => "File saved!"]));
        } else {
            $from->send(json_encode(["status" => "error", "message" => "Invalid request."]));
        }
    }

    public function onClose(ConnectionInterface $conn) {
        $this->clients->detach($conn);
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "Error: " . $e->getMessage();
        $conn->close();
    }
}

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;

$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new FileServer()
        )
    ),
    8080
);

echo "WebSocket server started on port 8080...\n";
$server->run();
?>

