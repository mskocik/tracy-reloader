# Tracy Reloader
Tracy extension for automatic page live update (LiveReload) or refresh (LiveReload and SSE).

## Installation
```
composer require --dev mskocik/tracy-reloader
```

## Add to Tracy

```
tracy:
	bar:
		- Common\Tracy\ReloadPanel(<mode>, <options>, <excludeHeaders>)
```


## Config

Extension supports 2 modes: `LiveReload` or `Server Sent Events`

### LiveReload

```php
// minimal setup
Common\Tracy\ReloadPanel('LR', [
    'https' => false,       // when accessing LiveReload server from https host
    'host' => null,         // when NULL, pick visited hostname 
    'port' => 35729,
    'path' => 'livereload',
    // internal
    'excludeHeaders' => [], // additional header definition for AJAX requests exclusion
]);
```

### Server-sent Events

Connects to SSE endpoint (provided in the extension out of the box) and watch specified directories and (or) files. Uses `Nette\Utils\Finder` under the hood, so you can setup Finder according to your needs.

Second section - marked `internal` is related to SSE itself. It's kind of selfexplanatory.

```php
// minimal setup
Common\Tracy\ReloadPanel('SSE', [   
    // Nette\Utils\Finder config
    'mask' => '*.*',
    'in' => null,
    'from' => null,
    'exclude' => null,
    'excludeDir' => null,
    // internal
    'excludeHeaders' => [], // additional header definition for AJAX requests exclusion
    'timeout' => 30,        // SSE/Reloader.php max execution time
    'watchInterval' => 2,   // loop sleep interval
]);
```

### Filter ajax requests

It is not desired to render tracy panel for AJAX requests. Basic AJAX requests are filtered out automatically, but if you send some additional AJAX requests, you can filter them out by specifying headers that are related to these requests.

These headers should be specified in `excludedHeaders` property of `$config` as `$key: $value` pairs.

Example of `local.neon`:
```
tracy:
	bar:
		- Mskocik\TracyReloader\ReloaderPanel('LR', [ 
			https: true,
			excludeHeaders: [
				x-requested-with: swup
			]
		])
```