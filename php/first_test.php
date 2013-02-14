<?php
require_once '__init__.php';

// This would be the url of the host running the server-standalone.jar
$wd_host = 'http://127.0.0.1:4444/wd/hub'; // this is the default
$web_driver = new WebDriver($wd_host);

// First param to session() is the 'browserName' (default = 'firefox')
// Second param is a JSON object of additional 'desiredCapabilities'

// POST /session
$session = $web_driver->session('firefox');
print('test');
