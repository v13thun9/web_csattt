<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
date_default_timezone_set('Asia/Ho_Chi_Minh');
echo "[WS] Server started\n";

require 'vendor/autoload.php';

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use React\EventLoop\Factory;

class MonitoringServer implements \Ratchet\MessageComponentInterface {
    protected $clients;
    protected $requestCount = 0;
    protected $lastMinuteRequests = [];
    protected $ipStats = [];
    protected $methodStats = [];

    public function __construct() {
        $this->clients = new \SplObjectStorage;
    }

    public function onOpen(\Ratchet\ConnectionInterface $conn) {
        $this->clients->attach($conn);
        echo "[WS] New connection! ({$conn->resourceId})\n";
    }

    public function onMessage(\Ratchet\ConnectionInterface $from, $msg) {
        echo "[WS] Message from client: $msg\n";
    }

    public function onClose(\Ratchet\ConnectionInterface $conn) {
        $this->clients->detach($conn);
        echo "[WS] Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(\Ratchet\ConnectionInterface $conn, \Exception $e) {
        echo "[WS] An error has occurred: {$e->getMessage()}\n";
        $conn->close();
    }

    public function processLog($logLine) {
        echo "[WS] Processing log: $logLine";
        $this->requestCount++;
        $currentTime = time();
        
        // Cập nhật số request trong 10 giây gần nhất
        $this->lastMinuteRequests[] = $currentTime;
        $this->lastMinuteRequests = array_filter($this->lastMinuteRequests, function($time) use ($currentTime) {
            return $time > ($currentTime - 10);
        });

        // Phân tích log để lấy thông tin chi tiết
        if (preg_match('/^(\S+) - - \[(.*?)\] "(.*?)" (\d+) (\d+) "(.*?)" "(.*?)"/', $logLine, $matches)) {
            $ip = $matches[1];
            $timestamp = $matches[2];
            $request = $matches[3];
            $status = $matches[4];
            $referer = $matches[6];
            $userAgent = $matches[7];

            // Tách method từ request
            $method = 'UNKNOWN';
            if (preg_match('/^(\S+) /', $request, $m)) {
                $method = strtoupper($m[1]);
            }
            // Đếm method
            if (!isset($this->methodStats[$method])) {
                $this->methodStats[$method] = 0;
            }
            $this->methodStats[$method]++;

            // Tạo unique identifier cho request
            $requestId = md5($ip . $timestamp . $request . $userAgent);

            // Cập nhật thống kê IP
            if (!isset($this->ipStats[$ip])) {
                $this->ipStats[$ip] = [
                    'count' => 0,
                    'lastRequest' => 0,
                    'suspicious' => false,
                    'requests' => [],
                    'userAgents' => []
                ];
            }

            // Thêm thông tin request mới
            $this->ipStats[$ip]['requests'][] = [
                'time' => $timestamp,
                'url' => $request,
                'status' => $status,
                'userAgent' => $userAgent,
                'id' => $requestId
            ];

            // Giới hạn số lượng request lưu trữ
            if (count($this->ipStats[$ip]['requests']) > 100) {
                array_shift($this->ipStats[$ip]['requests']);
            }

            // Cập nhật thống kê User-Agent
            if (!isset($this->ipStats[$ip]['userAgents'][$userAgent])) {
                $this->ipStats[$ip]['userAgents'][$userAgent] = 0;
            }
            $this->ipStats[$ip]['userAgents'][$userAgent]++;

            $this->ipStats[$ip]['count']++;
            $this->ipStats[$ip]['lastRequest'] = $currentTime;

            // Kiểm tra IP đáng ngờ với điều kiện mới
            $uniqueRequests = count(array_unique(array_column($this->ipStats[$ip]['requests'], 'id')));
            if ($uniqueRequests > 50 && ($currentTime - $this->ipStats[$ip]['lastRequest']) < 60) {
                $this->ipStats[$ip]['suspicious'] = true;
            }
        }

        // Gửi dữ liệu đến tất cả client
        $this->broadcast([
            'type' => 'log',
            'content' => $logLine
        ]);

        // Gửi thống kê request (không bao gồm CPU/RAM)
        $requestsPerSecond = round(count($this->lastMinuteRequests) / 10, 2);

        $this->broadcast([
            'type' => 'stats',
            'content' => [
                'totalRequests' => $this->requestCount,
                'requestsPerSecond' => $requestsPerSecond
            ]
        ]);

        $this->broadcast([
            'type' => 'chart',
            'content' => [
                'time' => date('H:i:s'),
                'value' => $requestsPerSecond
            ]
        ]);

        // Gửi thống kê method
        $this->broadcast([
            'type' => 'methodStats',
            'content' => $this->methodStats
        ]);

        // Gửi danh sách IP đáng ngờ với thông tin chi tiết hơn
        $suspiciousIPs = array_filter($this->ipStats, function($stats) {
            return $stats['suspicious'];
        });

        $this->broadcast([
            'type' => 'suspicious',
            'content' => array_map(function($ip, $stats) {
                return [
                    'address' => $ip,
                    'requests' => $stats['count'],
                    'uniqueRequests' => count(array_unique(array_column($stats['requests'], 'id'))),
                    'userAgents' => $stats['userAgents'],
                    'lastRequests' => array_slice($stats['requests'], -5), // Lấy 5 request gần nhất
                    'reason' => 'Nhiều request độc lập trong thời gian ngắn'
                ];
            }, array_keys($suspiciousIPs), $suspiciousIPs)
        ]);
    }

    protected function broadcast($data) {
        echo "[WS] Broadcast: " . json_encode($data) . "\n";
        foreach ($this->clients as $client) {
            $client->send(json_encode($data));
        }
    }
}

// Khởi tạo server và lưu lại đối tượng MonitoringServer
$monitoringApp = new MonitoringServer();
$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            $monitoringApp
        )
    ),
    9000
);

echo "[WS] Server is running and listening on port 9000\n";

// Đọc log file định kỳ, luôn mở lại file để tránh lỗi buffer hoặc rotate
$logFile = '/var/log/apache2/access.log';
$lastPos = 0;
$loop = $server->loop;
$loop->addPeriodicTimer(0.1, function() use (&$lastPos, $logFile, $monitoringApp) {
    if (!file_exists($logFile)) return;
    $fp = fopen($logFile, 'r');
    if (!$fp) return;
    fseek($fp, $lastPos);
    while ($line = fgets($fp)) {
        echo "[WS] New log line: $line";
        $monitoringApp->processLog($line);
    }
    $lastPos = ftell($fp);
    fclose($fp);
});

$server->run(); 