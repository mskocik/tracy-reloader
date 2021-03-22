<?php declare(strict_types=1);

namespace Mskocik\TracyReloader\SSE;

use Nette\Utils\Finder;

class Reloader
{
    /** @var array */
    private $config;
    /** @var array */
    private $stats = [];

    public function __construct(array $config = [])
    {
        if (!isset($config['in']) && !isset($config['from']))
            $this->sendError('Option "from" or "in" must be specified');
        if (isset($config['in']) && isset($config['from']))
            $this->sendError('Only ONE of "from" or "in" options can be specified');
        if (is_array($config['from'] ?? false)) {
            foreach ($config['from'] as &$dir) {
                if (strpos($dir, './') === 0) {
                    $dir = getcwd() . substr($dir, 1);
                }
            }
        }
        $this->config = $config;
    }

    private function sendMessage(string $action, string $message): void
    {   
        $message = [
            "action" => $action,
            "message" => $message,
            "conn_status" => !connection_aborted (),
            "timestamp" => microtime()
        ];

        if ($action === 'ping') {
            echo "event: ping\n";
        }

        echo "data: " . json_encode($message) . "\n\n";
        while (ob_get_level() > 0) {
            ob_end_flush();
        }
        flush();
    }


    public function start(): void
    {
        $this->sendHeaders();
        $this->scanFiles();
        $this->watchFiles();
        exit(0);
    }

    private function sendHeaders(): void
    {
        set_time_limit($this->config['timeout']);

        header('Cache-Control: no-cache', true);
        header("Access-Control-Allow-Origin: *", true);
        header('Content-Type: text/event-stream', true);
        header('Access-Control-Allow-Methods: GET', true);
        header('Access-Control-Expose-Headers: X-Events', true);
    }

    public function sendError(string $message): void
    {
        $this->sendHeaders();
        $this->sendMessage('error', $message);
        exit(0);
    }

    private function watchFiles(): void
    {
        $startSent = false;
        $internalCounter = 0;
        $refreshThreshold = $this->config['refreshRate'] ?? 30;
        $watchInterval = $this->config['watchInterval'] ?? 2;
        while (true) {
            echo "\n";

            if( connection_status() != CONNECTION_NORMAL || connection_aborted() ) {
                die;
            }

            clearstatcache();
            foreach($this->stats as $file => &$mtime) {
                if (!file_exists($file)) {
                    unset($this->stats[$file]);
                    continue;
                }
                if ($mtime !== filemtime($file)) {
                    $mtime = filemtime($file); 
                    $this->sendMessage('reload', $file);
                    exit;
                }
            }
            /**
             * To periodically check, that we still have connection
             */
            $this->sendMessage('ping', '');
                
            sleep($watchInterval);
            // NOTE: because script runs only for 30 seconds, no need to scan for files again
            // if ($internalCounter++ === $refreshThreshold) {
            //     $internalCounter = 0;
            //     $this->scanFiles();
            // }

            if (!$startSent) {
                $startSent = true;
                $this->sendMessage('start', (string) count($this->stats));
            }
        }
    }

    /**
     * Full check again, checking for new files
     *
     * @return void
     */
    private function scanFiles(): void
    {
        $finder = Finder::findFiles($this->config['mask']);
        if ($this->config['exclude'] ?? false) $finder = $finder->exclude($this->config['exclude']);
        var_dump($this->config['from']);
        if ($this->config['from'] ?? false) $finder = $finder->from($this->config['from']);
        if ($this->config['in'] ?? false) $finder = $finder->in($this->config['in']);
        if ($this->config['excludeDir'] ?? false) $finder = $finder->exclude($this->config['excludeDir']);
        /** @var SplInfo[] $finder  */
        foreach ($finder as $key => $value) {
            $this->stats[$key] = $value->getMTime();
        }
    }
}
