<?php

namespace Pipe;

use Pipe\Util\ProcessorRegistry,
    MetaTemplate\Template,
    MetaTemplate\Util\EngineRegistry,
    CHH\FileUtils\Path,
    CHH\FileUtils\PathInfo,
    CHH\FileUtils\PathStack;

class Environment implements \ArrayAccess
{
    # Stack of Load Paths for Assets.
    public $loadPaths;

    # Map of file extensions to content types.
    public $contentTypes = array(
        '.css' => 'text/css',
        '.js'  => 'application/javascript',
        '.jpeg' => 'image/jpeg',
        '.jpg' => 'image/jpeg',
        '.png' => 'image/png',
        '.gif' => 'image/gif'
    );

    # Engine Registry, stores engines per file extension.
    public $engines;

    # Processors are like engines, but are associated with
    # a mime type.
    public $preProcessors;
    public $postProcessors;
    public $bundleProcessors;

    public $compressors = array(
        "uglify_js" => "\\Pipe\\Compressor\\UglifyJs",
        "yuglify_css" => "\\Pipe\\Compressor\\YuglifyCss",
        "yuglify_js" => "\\Pipe\\Compressor\\YuglifyJs",
    );

    protected $jsCompressor;
    protected $cssCompressor;

    function __construct($root = null)
    {
        $this->root = $root;
        $this->loadPaths = new Pathstack($this->root);

        $this->engines = Template::getEngines();

        # Enable resolving logical paths without extension.
        $this->loadPaths->appendExtensions(array_keys($this->engines->getEngines()));
        $this->loadPaths->appendExtensions(array_keys($this->contentTypes));

        $this->preProcessors    = new ProcessorRegistry;
        $this->postProcessors   = new ProcessorRegistry;
        $this->bundleProcessors = new ProcessorRegistry;

        $this->registerEngine('\\Pipe\\JstProcessor', '.jst');

        # Register default processors
        $this->registerPreProcessor('text/css', '\\Pipe\\ImportProcessor');
        $this->registerPreProcessor('text/css', '\\Pipe\\DirectiveProcessor');
        $this->registerPreProcessor('application/javascript', '\\Pipe\\DirectiveProcessor');
        $this->registerPostProcessor('application/javascript', '\\Pipe\\SafetyColons');
    }

    function registerEngine($engine, $extension)
    {
        $this->loadPaths->appendExtensions((array) $extension);
        $this->engines->register($engine, $extension);
        return $this;
    }

    function registerPreProcessor($contentType, $processor)
    {
        $this->preProcessors->register($contentType, $processor);
        return $this;
    }

    function registerPostProcessor($contentType, $processor)
    {
        $this->postProcessors->register($contentType, $processor);
        return $this;
    }

    function registerBundleProcessor($contentType, $processor)
    {
        $this->bundleProcessors->register($contentType, $processor);
        return $this;
    }

    function prependPath($path)
    {
        $this->loadPaths->prepend($path);
        return $this;
    }

    function appendPath($path)
    {
        $this->loadPaths->push($path);
        return $this;
    }

    function find($logicalPath, $options = array())
    {
        $path = new PathInfo($logicalPath);

        if ($path->isAbsolute()) {
            $realPath = $logicalPath;
        } else {
            $realPath = $this->loadPaths->find($logicalPath);
        }

        if (!is_file($realPath)) {
            return;
        }

        if (null === $realPath) {
            return;
        }

        if (@$options["bundled"]) {
            $asset = new BundledAsset($this, $realPath, $logicalPath);
        } else {
            $asset = new ProcessedAsset($this, $realPath, $logicalPath);
        }

        return $asset;
    }

    function setJsCompressor($compressor)
    {
        if (!isset($this->compressors[$compressor])) {
            throw new \InvalidArgumentException(sprintf('Undefined compressor "%s"', $compressor));
        }

        $js = $this->contentType('.js');

        if ($this->jsCompressor !== null) {
            $this->bundleProcessors->unregister($js, $this->jsCompressor);
        }

        $this->jsCompressor = $compressor;
        $this->bundleProcessors->register($js, $this->compressors[$compressor]);
    }

    function setCssCompressor($compressor)
    {
        if (!isset($this->compressors[$compressor])) {
            throw new \InvalidArgumentException(sprintf('Undefined compressor "%s"', $compressor));
        }

        $css = $this->contentType('.css');

        if ($this->cssCompressor !== null) {
            $this->bundleProcessors->unregister($css, $this->cssCompressor);
        }

        $this->cssCompressor = $compressor;
        $this->bundleProcessors->register($css, $this->compressors[$compressor]);
    }

    function contentType($extension)
    {
        return @$this->contentTypes[Path::normalizeExtension($extension)];
    }

    # Sugar for find().
    #
    # logicalPath - The path relative to the virtual file system.
    #
    # Returns an Asset.
    function offsetGet($logicalPath)
    {
        return $this->find($logicalPath);
    }

    function offsetSet($offset, $value) {}
    function offsetExists($offset) {}
    function offsetUnset($offset) {}
}
