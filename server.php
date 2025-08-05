<?php
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

require 'vendor/autoload.php';

class FileServer implements MessageComponentInterface {
    private $password = "securepassword"; // Default password
    protected $clients;

    public function __construct() {
        $this->clients = new \SplObjectStorage;
    }

    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);
        echo "New connection ({$conn->resourceId}) opened.\n";
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        $data = json_decode($msg, true);

        // Validate password
        if (!isset($data['password']) || $data['password'] !== $this->password) {
            $from->send(json_encode(["status" => "error", "message" => "Invalid password."]));
            return;
        }

        if (isset($data['action'])) {
            if ($data['action'] === "get_files") {
                // Get list of uploaded files with details
                $files = glob('uploads/*');
                $fileDetails = [];
                foreach ($files as $file) {
                    if (is_file($file)) {
                        $fileDetails[] = [
                            'name' => basename($file),
                            'size' => filesize($file),
                            'modified' => filemtime($file)
                        ];
                    }
                }
                $from->send(json_encode(["status" => "success", "files" => $fileDetails]));
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
        echo "Connection {$conn->resourceId} has disconnected.\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
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
