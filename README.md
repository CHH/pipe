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

To retrieve a custom config key, use the config object as
array.

```php
<?php

$key = $config["my_custom_key"];
```

### Dumping an Asset to a File

To dump an asset to a file, use the `write` method. The `write` method
has the following signature:

```php
write($directory = '', $digestFile = true)
```

The directory is the prefix of the file. A hash of the asset's contents
is automatically included in the resulting filename. This hash is
written to a file named `.digest` and dumped to the same directory as
the asset itself. You can use the digest from the file to get the 
hash for reconstructing the filename.

### Enabling Compression

You can turn on compression by setting the `js_compressor` and
`css_compressor` config keys.

For now only the `uglify_js` compressor is supported.

### Defining Dependencies

Each file with content type "application/javascript" or "text/css" is
processed by the `DirectiveProcessor`. The `DirectiveProcessor` parses
the head of these files for special comments starting with an equals
sign.

```
/* CSS
 *= require foo.css
 *= depend_on bar.css
 */

# CoffeeScript
#= require foo.coffee

// Javascript:
//= require foo.js
```

The arguments for each directive are split by the Bourne Shell's
rules. This means you have to quote arguments which contain spaces
with either single or double quotes.

```
//= require "some name with spaces.js"
```

### Serving Assets dynamically.

Pipe includes a `Pipe\Server` which is able to serve assets dynamically
via HTTP. The server is designed to be called in `.php` file, served via
`mod_php` or FastCGI.

The Server must be initialized with an Environment instance.

```php
<?php

use Pipe\Server,
    Pipe\Environment;

$env = new Environment;
$env->appendPath("vendor_assets");
$env->appendPath("assets");

$server = new Server($env);
```

To serve an asset use the server's `dispatch` method. It takes an
`Symfony\Component\HttpFoundation\Request` and returns a Response object.

Call `send` on the response object to send the asset to the client.

```php
<?php

use Symfony\Component\HttpFoundation\Request;

$response = $server->dispatch(Request::createFromGlobals());
$response->send();
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

