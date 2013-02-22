<?php
/**
 * Climbing Weather daily forecast page helper
 */
class CwDailyForecastPage
{
    /**
     * Web driver browser session
     * @var PHPWebDriver_WebDriver
     */
    protected $session;

    /**
     * Day selector
     * @var array
     */
    private $_daySelector = array('xpath', "//table[contains(@class, 'forecast')]//td[contains(@class, 'day_date')]");

    /**
     * Constructor
     * @param PHPWebDriver_WebDriver Web driver session
     */
    public function __construct($session)
    {
        $this->session = $session;
    }

    /**
     * Get count of forecast days
     * @return integer
     */
    public function getDayCount()
    {
        return count($this->session->elements($this->_daySelector[0], $this->_daySelector[1]));
    }
}

