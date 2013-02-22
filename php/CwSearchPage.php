<?php
/**
 * Climbing weather search page helper
 */
class CwSearchPage
{
    /**
     * Web driver browser session
     * @var PHPWebDriver_WebDriver
     */
    protected $session;

    /**
     * Search selector
     * @var array
     */
    private $_searchSelector = array('id', 'globalSearch');

    /**
     * Search URL
     * @var string
     */
    private $_searchUrl = 'http://www.climbingweather.com';

    /**
     * Constructor
     * @param PHPWebDriver_WebDriver
     */
    public function __construct($session)
    {
        $this->session = $session;
    }

    /**
     * Open search page
     */
    public function open()
    {
        $this->session->open($this->_searchUrl);
    }

    /**
     * Perform search
     * @string Search string
     * @boolean $clickResult T: Click link in result
     */
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
