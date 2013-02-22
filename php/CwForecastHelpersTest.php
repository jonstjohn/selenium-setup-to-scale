<?php
require_once 'PHPWebDriver/__init__.php';
require_once 'CwSearchPage.php';
require_once 'CwDailyForecastPage.php';
require_once 'PHPUnit/Autoload.php';

/**
 * Climbing Weather daily forecast test, using page helpers
 */
class CwForecastHelpersTest extends PHPUnit_Framework_TestCase
{
    /**
     * Web driver browser session
     * @var PHPWebDriver_WebDriver
     */
    protected $session;

    /**
     * Test setup
     */
    protected function setUp()
    {   
        $wd_host = 'http://127.0.0.1:4444/wd/hub'; // this is the default
        $web_driver = new PHPWebDriver_WebDriver($wd_host);

        $this->session = $web_driver->session('firefox');
        $this->session->implicitlyWait(10);
    }

    /**
     * Test 7 day forecast
     */
    public function testForecast7Day()
    {
        // Perform search
        $searchHelper = new CwSearchPage($this->session);
        $searchHelper->search('Little Cottonwood Canyon');

        // Check for number of forecast days, should be 7
        $dailyHelper = new CwDailyForecastPage($this->session);
        $this->assertEquals($dailyHelper->getDayCount(), 7);
    }

    /**
     * Tests tear down
     */
    protected function tearDown()
    {
        // Close session
        $this->session->close();
    }
}

