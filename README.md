# Tracy Re;pader
Tracy extension for automatic page update. Support for Server-sent Events and LiveReload.

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

Extension supports 2 modes: `LiveReload` or `Server-sent Events`

### LiveReload

```php
// minimal setup
Common\Tracy\ReloadPanel('LR', [   
    // these are default values
    'https' => false,
    'host' => null, // if null, will be set automatically by current domain
    'port' => 35729,
    'path' => 'livereload'
]);
```

### Server-sent Events

Connects to SSE endpoint (provided in the extension) and watch specified directories and (or) files. Uses `Nette\Utils\Finder` under the hood, so you can setup Finder according to your needs.

Second section - marked `internal` is related to SSE itself. It's kind of selfexplanatory.

```php
// minimal setup
Common\Tracy\ReloadPanel('SSE', [   
    // Finder
    'mask' => '*.*',
    'in' => null,
    'from' => null,
    'exclude' => null,
    'excludeDir' => null,
    // internal
    'timeout' => 30,                // every script runs only for 30 seconds
    'refreshRate' => 30,            
    'watchInterval' => 2,
]);
```

### ExcludeHeaders

Additional check for request for which extension should return nothing. Basically extension should not render for AJAX requests. Because on every render it connects to LiveReload/SSE endpoint and that's not wanted on ajax requests.

Provide any `key => value` pair for additional headers, which signal AJAX request.