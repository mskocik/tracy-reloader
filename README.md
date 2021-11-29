# Tracy Reloader
Tracy extension for automatic page live update or refresh through LiveReload

## Installation
```
composer require --dev mskocik/tracy-reloader
```

## Add to Tracy

```
tracy:
	bar:
		- Mskocik\TracyReloader\ReloaderPanel(<mode>, <options>, <excludeHeaders>)
```


## Config

Extension supports 2 modes: `LiveReload` or `Server Sent Events`

### LiveReload

```php
// minimal setup
Mskocik\TracyReloader\ReloaderPanel('LR', [
    'https' => false,       // when accessing LiveReload server from https host
    'host' => null,         // when NULL, pick visited hostname 
    'port' => 35729,
    'path' => 'livereload',
    // internal
    'excludeHeaders' => [], // additional header definition for AJAX requests exclusion
]);
```

### Filter ajax requests

It is not desired to render Reloader panel for AJAX requests. Because you want only ONE active instance, which should 
happen only on full page load/reload. Basic AJAX requests are filtered out automatically, but if you send some additional AJAX requests, you can filter them out by specifying headers that are related to these requests.

These headers should be specified in `excludedHeaders` property of `$config` as `$key: $value` pairs.

Example of `local.neon`, when app is using [swup.js](https://swup.js.org/) for app navigation:
```
tracy:
	bar:
		- Mskocik\TracyReloader\ReloaderPanel(mode: 'LR', config: [ 
			https: true,
			excludeHeaders: [
				x-requested-with: swup
			]
		])
```
