<?php
require_once 'PHPWebDriver/__init__.php';

class ForecastTest extends PHPUnit_Framework_TestCase
{
    protected $session;

    protected function setUp()
    {   
        $wd_host = 'http://127.0.0.1:4444/wd/hub'; // this is the default
        $web_driver = new PHPWebDriver_WebDriver($wd_host);

        $this->session = $web_driver->session('firefox');
        $this->session->implicitlyWait(10);
    }

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

    protected function tearDown()
    {
        // Close session
        $this->session->close();
    }
}
