<?php declare(strict_types=1);

namespace Mskocik\TracyReloader;

use Mskocik\TracyReloader\SSE\Reloader;
use Tracy\IBarPanel;

class ReloaderPanel implements IBarPanel
{
    const
        LIVERELOAD = 'LR',
        SERVER_SENT_EVENTS = 'SSE';

    private static $configDefaults = [
        'mode' => 'LR',             // 'LR' is default, but can be 'SSE'
        'LR' => [
            'https' => false,       // when accessing LiveReload server from https host
            'host' => null,         // when NULL, pick visited hostname 
            'port' => 35729,
            'path' => 'livereload',
            // internal
            'excludeHeaders' => [], // additional header definition for AJAX requests exclusion
        ],
        'SSE' => [
            // Finder
            'mask' => '*.*',
            'in' => null,
            'from' => null,
            'exclude' => null,
            'excludeDir' => null,
            // internal
            'excludeHeaders' => [], // additional header definition for AJAX requests exclusion
            'timeout' => 30,        // SSE/Reloader.php max execution time
            'watchInterval' => 2,   // loop sleep interval
            'refreshRate' => 30,    // NOTE: not used
        ]
    ];

    /** @var string */
    private $mode;
    /** @var array */
    private $config = [];
    
    /** @var \Nette\Http\IRequest */
    private $request;

    public function __construct(\Nette\Http\IRequest $request, string $mode = 'LR', array $config = [])
    {
        $this->mode = strtoupper($mode);
        if ($mode === static::LIVERELOAD && !isset($config['host'])) {
            $config['host'] = $request->getUrl()->getHost();
        }
        $modeConfig = array_merge(static::$configDefaults[$this->mode], $config);
        $this->config = $modeConfig;
        $this->request = $request;

        $this->isSSE() && $this->handleRequest();
    }

    public function getTab(): ?string
    {
        if ($this->isInvalidRequest()) return null;
        return \Nette\Utils\Helpers::capture(function () {
            $isSSE = $this->isSSE();
            $isLiveReload = !$isSSE;
            $mode = $this->mode;
            $config = $this->config;
			require __DIR__ . '/templates/ReloadPanel.tab.phtml';
		});
    }
    
    public function getPanel(): ?string
    {
        if ($this->isInvalidRequest()) return null;
        return \Nette\Utils\Helpers::capture(function () {
			$mode = $this->mode;
            $config = $this->config;
			require __DIR__ . '/templates/ReloadPanel.panel.phtml';
		});
    }

    public function isInvalidRequest(): bool
    {
        foreach ($this->config['excludeHeaders'] as $key => $value) {
            if ($this->request->getHeader($key) === $value) return true;
        }
        return $this->request->isAjax();
    }

    public function isSSE(): bool
    {
        return $this->mode === self::SERVER_SENT_EVENTS;
    }

    public function handleRequest(): void
    {
        $flag = $this->request->getQuery('tracy_reloader');
        if ($flag && strtolower($flag) === 'sse') {
            $sseReloder = new Reloader($this->config);
            $sseReloder->start();
        }
    }
}
