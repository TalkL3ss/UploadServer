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

    private function fetchWebContent($url) {
        // Fetch content from the URL
        $context = stream_context_create([
            'http' => [
                'timeout' => 10,
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'
            ]
        ]);
        
        $content = @file_get_contents($url, false, $context);
        if ($content === false) {
            return false;
        }
        
        // Strip HTML tags and extract text
        $text = strip_tags($content);
        $text = preg_replace('/\s+/', ' ', $text); // Normalize whitespace
        return trim($text);
    }

    private function summarizeText($text, $maxSentences = 3) {
        if (empty($text)) {
            return "No content to summarize.";
        }
        
        // Split into sentences
        $sentences = preg_split('/[.!?]+/', $text);
        $sentences = array_filter(array_map('trim', $sentences));
        
        if (count($sentences) <= $maxSentences) {
            return implode('. ', $sentences) . '.';
        }
        
        // Simple scoring: prefer sentences with common words and good length
        $scored = [];
        $words = str_word_count(strtolower($text), 1);
        $wordFreq = array_count_values($words);
        
        foreach ($sentences as $sentence) {
            $sentenceWords = str_word_count(strtolower($sentence), 1);
            $score = 0;
            $wordCount = count($sentenceWords);
            
            // Skip very short or very long sentences
            if ($wordCount < 5 || $wordCount > 40) {
                continue;
            }
            
            // Score based on word frequency
            foreach ($sentenceWords as $word) {
                if (isset($wordFreq[$word])) {
                    $score += $wordFreq[$word];
                }
            }
            
            // Normalize by sentence length
            $score = $score / $wordCount;
            $scored[] = ['sentence' => $sentence, 'score' => $score];
        }
        
        // Sort by score and take top sentences
        usort($scored, function($a, $b) {
            return $b['score'] <=> $a['score'];
        });
        
        $topSentences = array_slice($scored, 0, $maxSentences);
        
        // Maintain original order
        $result = [];
        foreach ($sentences as $sentence) {
            foreach ($topSentences as $top) {
                if ($top['sentence'] === $sentence) {
                    $result[] = $sentence;
                    break;
                }
            }
        }
        
        return implode('. ', $result) . '.';
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
            } elseif ($data['action'] === "summarize" && isset($data['url'])) {
                // Summarize content from URL
                $url = $data['url'];
                
                // Validate URL
                if (!filter_var($url, FILTER_VALIDATE_URL)) {
                    $from->send(json_encode(["status" => "error", "message" => "Invalid URL provided."]));
                    return;
                }
                
                echo "Fetching content from: $url\n";
                
                // For demo purposes, if the URL is example.com, use sample content
                if (strpos($url, 'example.com') !== false) {
                    $content = "Example Domain is a reserved domain name for use in illustrative examples in documents. You may use this domain in literature without prior coordination or asking for permission. More information about Example.com can be found in RFC 2606. This domain is for use in illustrative examples in documents. You can use this domain freely in documentation and testing. The domain name example.com is specifically reserved for use in examples. It helps developers and writers create documentation without worrying about domain conflicts. This makes it a reliable choice for educational and demonstration purposes.";
                } else {
                    $content = $this->fetchWebContent($url);
                    if ($content === false) {
                        $from->send(json_encode(["status" => "error", "message" => "Failed to fetch content from the URL. The URL might be unreachable or blocked."]));
                        return;
                    }
                }
                
                $summary = $this->summarizeText($content);
                $from->send(json_encode([
                    "status" => "success",
                    "url" => $url,
                    "summary" => $summary
                ]));
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
