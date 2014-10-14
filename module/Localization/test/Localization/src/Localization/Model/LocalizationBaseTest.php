<?php
namespace Localization\Test\Model;

use Localization\Test\LocalizationBootstrap;
use PHPUnit_Framework_TestCase;

class LocalizationBaseTest extends PHPUnit_Framework_TestCase
{
    /**
     * Localization
     * @var object
     */
    protected $localization;

    /**
     * Service locator
     * @var object
     */
    protected $serviceLocator;

    /**
     * Config
     * @var array
     */
    protected $config;

    /**
     * Setup
     */
    protected function setUp()
    {
        // get service manager
        $this->serviceLocator = LocalizationBootstrap::getServiceLocator();

        // get config
        $this->config = $this->serviceLocator->get('Config');

        // get localization instance
        $this->localization = $this->serviceLocator
            ->get('Application\Model\ModelManager')
            ->getInstance('Localization\Model\LocalizationBase');
    }

    /**
     * Test localization list
     */
    public function testLocalizationList()
    {
        $this->assertNotEmpty($this->localization->getAllLocalizations());
    }
}
