<?php
require_once 'PHPWebDriver/__init__.php';

// This would be the url of the host running the server-standalone.jar
$wd_host = 'http://127.0.0.1:4444/wd/hub'; // this is the default
$web_driver = new PHPWebDriver_WebDriver($wd_host);

// First param to session() is the 'browserName' (default = 'firefox')
// Second param is a JSON object of additional 'desiredCapabilities'

// POST /session
$session = $web_driver->session('firefox');

// Set implicit wait
$session->implicitlyWait(10);

// Open ClimbingWeather.com
$session->open('http://www.climbingweather.com');

// Find search element and submit search
$search = $session->element('id', 'globalSearch');
$search->sendKeys('Little Cottonwood Canyon' . PHPWebDriver_WebDriverKeys::ReturnKey());

// Try to find link
$search = $session->element(PHPWebDriver_WebDriverBy::PARTIAL_LINK_TEXT, 'Little Cottonwood Canyon');
$search->click();

// Check for number of forecast days, should be 7
$days = $session->elements('xpath', "//table[contains(@class, 'forecast')]//td[contains(@class, 'day_date')]");
print count($days);

// Close session
$session->close();
