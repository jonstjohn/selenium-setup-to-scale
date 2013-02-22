<?php
require_once 'PHPWebDriver/__init__.php';
require_once 'PHPUnit/Autoload.php';

/**
 * Climbing Weather daily forecast test
 */
class CwForecastTest extends PHPUnit_Framework_TestCase
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
        // Open ClimbingWeather.com
        $this->session->open('http://www.climbingweather.com');

        // Find search element and submit search
        $search = $this->session->element('id', 'globalSearch');
        $search->sendKeys('Little Cottonwood Canyon' . PHPWebDriver_WebDriverKeys::ReturnKey());

        // Try to find link
        $search = $this->session->element(PHPWebDriver_WebDriverBy::PARTIAL_LINK_TEXT, 'Little Cottonwood Canyon');
        $search->click();

        // Check for number of forecast days, should be 7
        $days = $this->session->elements('xpath', "//table[contains(@class, 'forecast')]//td[contains(@class, 'day_date')]");
        $this->assertEquals(count($days), 7);
    }

    /**
     * Test tear down
     */
    protected function tearDown()
    {
        // Close session
        $this->session->close();
    }
}
