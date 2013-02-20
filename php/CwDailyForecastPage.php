<?php
class CwDailyForecastPage
{
    protected $session;

    private $_daySelector = array('xpath', "//table[contains(@class, 'forecast')]//td[contains(@class, 'day_date')]");

    public function __construct($session)
    {
        $this->session = $session;
    }

    public function getDayCount()
    {
        return count($this->session->elements($this->_daySelector[0], $this->_daySelector[1]));
    }
}

