<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\ModuleManager;

use Zend\Loader\AutoloaderFactory;

/**
 * Offer common setUp/tearDown methods for preserve current autoload functions and include paths.
 */
trait ResetAutoloadFunctionsTrait
{
    /**
     * @var callable[]
     */
    private $loaders;

    /**
     * @var string
     */
    private $includePath;

    /**
     * @before
     */
    protected function preserveAutoloadFunctions()
    {
        $this->loaders = spl_autoload_functions();
        if (!is_array($this->loaders)) {
            // spl_autoload_functions does not return empty array when no
            // autoloaders registered...
            $this->loaders = [];
        }
    }

    /**
     * @before
     */
    protected function preserveIncludePath()
    {
        $this->includePath = get_include_path();
    }

    /**
     * @after
     */
    protected function restoreAutoloadFunctions()
    {
        AutoloaderFactory::unregisterAutoloaders();
        $loaders = spl_autoload_functions();
        if (is_array($loaders)) {
            foreach ($loaders as $loader) {
                spl_autoload_unregister($loader);
            }
        }

        foreach ($this->loaders as $loader) {
            spl_autoload_register($loader);
        }
    }

    /**
     * @before
     */
    protected function restoreIncludePath()
    {
        set_include_path($this->includePath);
    }
}
