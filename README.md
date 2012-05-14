# Pipe

_Put your assets into the pipe and smoke them._

Pipe is an asset pipeline in the spirit of [Sprockets][]. Its focus
is on ease of use.

[sprockets]: https://github.com/sstephenson/sprockets

## Install

Get composer (if you haven't):

    wget http://getcomposer.org/composer.phar

Then add this to a `composer.json` in your project's root:

    {
        "require": {
            "chh/pipe": "*"
        }
    }

Now install:

    php composer.phar install

## Getting Started

### Environment

First you need an instance of `Pipe\Environment`. The environment holds
your load paths, is used to retrieve assets and maps processors/engines
to your assets.

The environment consists of multiple load paths. When retrieving an
asset, it's looked up in every directory of the load paths. This way you
can split your assets up in multiple directories, for example
`vendor_assets` and `assets`.

To add some load paths, use the `appendPath` method. Paths can be
prepended by the `prependPath` method. But use this carefully, because
you can override assets this way.

```php
<?php
use Pipe\Environment;

$env = new Environment;
$env->appendPath("assets");
$env->appendPath("vendor_assets");
```

Assets are retrieved by accessing an index of the environment instance,
or by calling the `find` method.

The `find` method returns either `null` when no asset was found or an
asset instance.

```php
<?php

$asset = $env["js/application.js"];
# equal to:
$asset = $env->find("js/application.js");
```

To get the asset's processed body, use the `getBody` method.

```php
<?php

echo $asset->getBody();
```

You can get the asset's last modified timestamp with the
`getLastModified` method.

### Creating an Environment from a YAML File

Pipe environments can also be created from YAML config files. First you
need an instance of `Pipe\Config`. Use the `fromYaml` static method to
parse an YAML file into a config instance.

To create an Environment, use the config instance's `createEnvironment`.

```yaml
# pipe_config.yml
load_paths:
    - assets
    - vendor_assets

my_custom_key: "foo bar"
```

```php
<?php

use Pipe\Config;

$config = Config::fromYaml("pipe_config.yml");
$env = $config->createEnvironment();
```

To retrieve the value of a custom config key, use the config's `get`
method.

```php
<?php

$key = $config->get("my_custom_key");
```

## License

The MIT License

Copyright (c) 2012 Christoph Hochstrasser

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.

