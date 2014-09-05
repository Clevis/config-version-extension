clevis/config-version-extension
===============================

Installation
------------

```
composer require clevis/config-version-extension:~1.0
```

`config.neon`:

```yml
extensions:
	version: Clevis\Version\DI\VersionExtension
```

`config.local.sample.neon` and `config.local.neon`:
```yml
version: [1]
```

Usage
-----

Whenever you change `config.local.sample.neon` in a back compatibility breaking way, update version
key in sample config. Hook `onCompile` will check if your local config is up to date. If not, it presents
you the following exception with custom Tracy panel:

<img src="https://raw.githubusercontent.com/Clevis/config-version-extension/master/static/bluecreen.png" height="640">

If you aren't using default paths, set them before Container builds

```php
use Clevis\Version\DI\VersionExtension;
VersionExtension::$samplePath = '%appDir%/config/config.local.example.neon';
```
