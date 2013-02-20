<?php
class CwSearchPage
{
    protected $session;

    private $_searchSelector = array('id', 'globalSearch');

    private $_searchUrl = 'http://www.climbingweather.com';

    public function __construct($session)
    {
        $this->session = $session;
    }

    public function open()
    {
        $this->session->open($this->_searchUrl);
    }

    public function search($str, $clickResult = true)
    {
        if ($this->session->url() != $this->_searchUrl) {
            $this->open();
        }
        $search = $this->session->element($this->_searchSelector[0], $this->_searchSelector[1]);
        $search->sendKeys($str . PHPWebDriver_WebDriverKeys::ReturnKey());

        if ($clickResult) {
            $search = $this->session->element(PHPWebDriver_WebDriverBy::PARTIAL_LINK_TEXT, $str);
            $search->click();
        }
    }
}
