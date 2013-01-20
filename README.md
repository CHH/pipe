# Pipe

_Put your assets into the pipe and smoke them._

Pipe is an asset pipeline in the spirit of [Sprockets][]. It's meant
as the practical way for managing assets. It aims to provide a useful
out of the box setup for managing assets and support for common
preprocessor languages found in the web environment, like CoffeeScript or
LESS.

What Pipe provides for you:

 - Out of the box support for [Less](http://lesscss.org) and [CoffeeScript][]
 - Integrated **Dependency Managment**.
 - Support for multiple **asset load paths**, which allows you to untie
   your _application's libraries_ from your _vendor libraries_.
 - Tries to take the pain out of asset deployment, by being designed for
   dealing with **cache busting** and **compression**.

[coffeescript]: http://coffeescript.org/
[sprockets]: https://github.com/sstephenson/sprockets

[![Build Status](https://secure.travis-ci.org/CHH/pipe.png)](http://travis-ci.org/CHH/pipe)

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

Use the config's `createEnvironment` method to convert a config to an
environment instance.

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

To retrieve a custom config key, access the "camelCased" name as
property:

```php
<?php

$key = $config->myCustomKey;
```

### Dumping an Asset to a File

To dump an asset to a file, use the `write` method. 

The `write` method takes an array of options:

 * `dir (string)`: The directory is the prefix of the file. A hash of the asset's contents
   is automatically included in the resulting filename.
 * `include_digest (bool)`: Should the SHA1 hash of the asset's contents
   be included in the filename?
 * `compress (bool)`: Compresses the contents with GZIP and writes it
   with an `.gz` extension.

### Enabling Compression

You can turn on compression by setting the `js_compressor` and
`css_compressor` config keys, or by calling `setJsCompressor` or
`setCssCompressor` on an Environment instance.

Supported Javascript Compressors:

* `uglify_js`, uses the popular Uglify JS compressor built for NodeJS.
  Install with `npm -g install uglify-js`.
* `yuglify_js`, Compressor built upon Uglify JS, and behaves like YUI
  compressor. Install with `npm -g install yuglify`.

Supported CSS Compressors:

* `yuglify_css`, uses the Yuglify compressor's ability to compress CSS
  using [CSSmin](https://github.com/yui/ycssmin). Requires the `yuglify`
  NPM package.

### Directives

Each file with content type `application/javascript` or `text/css` is
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

// Javascript
//= require foo.js
```

The arguments for each directive are split by the Bourne Shell's
rules. This means you have to quote arguments which contain spaces
with either single or double quotes.

```
//= require "some name with spaces.js"
```

#### require

Usage:

    require <path>

The require directive takes an asset path as argument, processes the
asset and puts the dependency's contents before the asset's contents.

The path can also start with `./`, which skips the load path for the
path resolution and looks up the file in the same path as the current
asset.

#### depend\_on

Usage:

    depend_on <path>

Defines that the `path` is a dependency of the current asset, but does
not process anything. Assets defined this way get considered when the
last modified time is calculated, but the contents get not prepended.

#### require\_tree

Usage:

    require_tree <path>

Requires all files found in the directory specified by `path`.

For example, if you have a directory for all individual widgets and a
widget base prototype, then you could `require_tree` the `widgets/`
directory. This way every developer can just drop a file into the
`widgets/` directory without having to maintain a massive list of
individual assets.

    // index.js
    //= require ./widget_base
    //= require_tree ./widgets

### Engines

Engines are used to process assets before they're dumped. Each engine is
mapped to one or more file extension (e.g. CoffeeScript to `.coffee`). 
Each asset can be processed by one or more engines. Which engines are
used on the asset and their order is determined by the asset's file
extensions.

For example, to process an asset first by the PHP engine and then
by the LESS compiler, give the asset the `.less.php` suffix.

Here's a list of the engines provided by default and their mapping to
file extensions:

<table>
    <tr>
        <th>Engine</th>
        <th>Extensions</th>
        <th>Requirements</th>
    </tr>
    <tr>
        <td><a href="http://coffeescript.org">CoffeeScript</a></td>
        <td>.coffee</td>
        <td><code>coffee</code> &mdash; install with <code>npm install -g coffee-script</code></td>
    </tr>
    <tr>
        <td><a href="http://lesscss.org">LESS</a></td>
        <td>.less</td>
        <td><code>lessc</code> &mdash; install with <code>npm install -g less</code></td>
    </tr>
    <tr>
        <td>PHP</td>
        <td>.php, .phtml</td>
    </tr>
    <tr>
        <td>
            Mustache
        </td>
        <td>.mustache</td>
        <td>Add <code>phly/mustache</code> package</td>
    </tr>
    <tr>
        <td>Markdown</td>
        <td>.markdown, .md</td>
        <td>Add <code>dflydev/markdown</code> package</td>
    </tr>
    <tr>
        <td>
            <a href="http://twig.sensiolabs.com">Twig</a>
        </td>
        <td>
            .twig
        </td>
        <td>Add <code>twig/twig</code> package</td>
    </tr>
    <tr>
        <td><a href="http://www.typescriptlang.org/">TypeScript</a></td>
        <td>.ts</td>
        <td><code>tsc</code> &mdash; install with <code>npm install -g typescript</code></td>
    </tr>
</table>

Under the hood, Pipe Engines are [meta-template][] templates. Look at
its `README` for more information on building your own engines.

To add an engine class to Pipe, use the environment's `registerEngine`
method, which takes the engine's class name as first argument and an
array of extensions as second argument.

[meta-template]: https://github.com/CHH/meta-template

### Serving Assets dynamically.

Pipe includes a `Pipe\Server` which is able to serve assets dynamically
via HTTP. The server is designed to be called in `.php` file, served via
`mod_php` or FastCGI.

To use the dynamic asset server, you've to additionally require
`symfony/http-foundation`. The `require` section of your `composer.json`
should look like this:

    {
        "require": {
            "chh/pipe": "*@dev",
            "symfony/http-foundation": "*"
        }
    }

The server's constructor expects an environment as only argument. You
can either construct the environment from scratch or use the `Config`
class.

Put this in a file named `assets.php`:

```php
<?php

use Pipe\Server,
    Pipe\Environment,
    Symfony\Component\HttpFoundation\Request;

$env = new Environment;
$env->appendPath("vendor_assets");
$env->appendPath("assets");

$server = new Server($env);
$server->dispatch(Request::createFromGlobals())
       ->send();
```

The server resolves all request URIs relative to the environment's load
path. So to render the Javascript file `js/index.js` you would request
the URI `/assets.php/js/index.js`.

The server also applies some conditional caching via `Last-Modified` and
`If-Not-Modified-Since` HTTP headers. Should a change to a dependency
not be instantly visible, try to make a hard refresh in your browser or
clear your browser's cache.

### Preparing Assets for Production Deployment

It's a good idea to compile assets in a way that they don't need the
runtime support of Pipe. The `Pipe\Manifest` class is responsible for
just that.

The Manifest is used to compile assets and writes a JSON encoded file
which maps the logical paths (which the app knows anyway) to the paths
including the digest (which the app can't know in advance).

To add a file to the manifest, call the manifest's `compile` method:

    <?php
    
    $env = new \Pipe\Environment;
    $env->appendPath('assets/javascripts');

    $manifest = new \Pipe\Manifest($env, 'build/assets/manifest.json');
    $manifest->compile('index.js');

This creates the `index-<SHA1 digest>.js` file, and a `manifest.json`
both in the `build/assets` directory.

This file looks a bit like this:

    {
        "assets": {
            "index.js": "index-0beec7b5ea3f0fdbc95d0dd47f3c5bc275da8a33.js"
        }
    }

Then deploy everything located in `build/assets`, e.g. to some CDN or
static file server.

An app running in a production environment could use the manifest like
this, to create links to the assets:

    <?php
    
    # Better cache this, but omitted for brevity
    $manifest = json_decode(file_get_contents('/path/to/manifest.json'), true);

    # Path where the contents of "build/assets" are deployed.
    # Could be a path to a CDN.
    $prefix = "/assets";

    printf('<script type="text/javascript" src="%s/%s"></script>', $prefix, $manifest['assets']['index.js']);

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

