<?php
class TestRunner
{
    const MAX_CONCURRENT_TESTS = 2;

    const TMP_LOG_DIR = 'C:\Users\jonstjohn\Documents';

    /**
     * Pending tests
     * Format is ('path' => $path, 'method' => $method)
     * @var array
     */
    private $_pendingTests = array();

    /**
     * Current running tests
     * @var array
     */
    private $_runningTests = array();

    /**
     * Completed tests
     * @var array
     */
    private $_completedTests = array();

    /**
     * Test directories / paths
     * @var array
     */
    private $_directories = array();

    /**
     * Number of seconds between status reports
     * @var integer
     */
    private $_statusFrequency = 120;


    /**
     * Run test runner
     */
    public function run()
    {
        $this->_queueTests();
        print_r($this->_pendingTests);

        $this->_runTests();
    }

    /**
     * Run tests - main loop
     */
    private function _runTests()
    {
        $this->_startTime = time();
        $lastReportTime = 0;

        // Loop while there are still running or pending tests
        while (count($this->_runningTests) || count($this->_pendingTests)) {
            
            // Run tests while pending tests exist, or running processes are less than max processes
            while (count($this->_pendingTests) && count($this->_runningTests) < self::MAX_CONCURRENT_TESTS) {
                
                $test = array_shift($this->_pendingTests);
                
                $command = $this->_buildTestCommand($test);
                    
                $test['pipes'] = array();
                $this->_log("Starting $command");
                
                $test['out'] = self::TMP_LOG_DIR . '/' . str_replace('xml-', 'out-', $test['log']);
                $test['err'] = self::TMP_LOG_DIR . '/' . str_replace('xml-', 'err-', $test['log']);
                $procOpenDescriptors = array(
                    0 => array('pipe', 'r'),
                    1 => array('file', $test['out'], 'w'),
                    2 => array('file', $test['err'], 'w')
                );
        
                $test['handle'] = proc_open($command, $procOpenDescriptors, $test['pipes']);
                $status = proc_get_status($test['handle']);
                $test['pid'] = $status['pid'];
                
                $this->_log('START - ' . $this->_formatTest($test));
                $test['start_time'] = microtime(true);
                $this->_runningTests[] = $test;
                
            }
            
            // Loop over running tests, check to see if any have finished
            foreach ($this->_runningTests as $index => $test) {
                
                $ph = $test['handle'];
                $newData = false;
                $status = proc_get_status($ph);
                
                // If test is still running, just continue to next test
                if ($status['running']) {
                    continue;
                }
                
                // Echo the time command and run time
                $test['exitCode'] = $status['exitcode'];
                $this->_log(
                    'END - ' . $this->_formatTest($test) . " - " . 'Exit code: ' . $test['exitCode'] . ' - ' .
                    round(microtime(true) - $test['start_time'], 2) . " seconds."
                );
                
                // Exit code other than 0 indicates a failure, error or other issue
                if ($test['exitCode'] !== 0) {
                    $hasFailure = true;
                }
                
                $test['rv'] = proc_close($ph);
                unset($test['handle']);
                $this->_completedTests[] = $test;
                unset($this->_runningTests[$index]);
                
            }
            
            // Give some output for debugging every xx seconds
            if ((time() - $lastReportTime) > $this->_statusFrequency) {
                $this->_log("--- STATUS REPORT (every " . $this->_statusFrequency . " seconds) ---");
                $this->_log($this->_statusReport());
                $this->_log("--- ---");
                $lastReportTime = time();
            }
            
            sleep(10);
        }

    }

    /**
     * Add test directory to test runner
     */
    public function addDirectory($directory)
    {
        $this->_directories[] = $directory;
    }

    /**
     * Queue tests
     */
    private function _queueTests()
    {
        // get all test directories
        $dirs = array();
        
        foreach ($this->_directories as $path) {
            
            if (file_exists($path)) {
                if (is_dir($path)) {
                    $dirs = array_merge($dirs, self::_getTestDirs($path));
                } else {
                    $this->_addTestToPending($path);
                }
            }
        }

        // loop over directories
        foreach ($dirs as $dir) {
            
            // Add '/*' to directory, if needed
            if (substr($dir, -2) != '/*') {
                $dir .= "/*";
            }
            
            // Loop over all tests and add to pending tests
            $tests = $dir . "Test.php";
            if (glob($tests) !== false) {
                
                foreach (glob($tests) as $test) {
                    
                    $this->_addTestToPending($test);
                      
                }
            }
        }


    }

    /**
     * Get recursive list of test directories
     * @param string $dir Start directory
     * @return array
     */
    private static function _getTestDirs($dir) 
    {
        $tests = array();
        $pathNames = glob($dir);
        
        if ($pathNames !== false) {
            foreach ($pathNames as $path) {
                if (is_dir($path)) {
                    $tests[] = $path;
                    $tests = array_merge($tests, self::_getTestDirs($path . "/*"));
                } 
            }    
        }
        
        return $tests;
    }

    /**
     * Add test to pending using path
     * @param string $path Absolute path to test
     */
    private function _addTestToPending($path)
    {
        $path = realpath($path);
        
        // examine class in separate process so it doesn't crash or adversely affect this one
        $phpPath = "C:\Program Files (x86)\PHP\php.exe";
        $cmd = '"' . $phpPath . '" ' . realpath(dirname(__FILE__)) . '/ClassInfoJson.php ' . $path;
        $result = `$cmd`;
        
        if (strpos($result, '{') !== 0) {
            echo "** FATAL ERROR examining test file at '$path' - check that the class name matches the path!\n";
            return;
        }
        
        $classData = json_decode($result, true);
        
        if (array_key_exists('error', $classData)) {
            echo "** FATAL ERROR trying to get class info: " . $classData['error'];
        }
        
        // get class name and include class file
        $className = $classData['className'];
        $classNameParts = explode('_', $className);
        $classNameLast = array_pop($classNameParts);
        
        foreach ($classData['methodData'] as $methodData) {
        
            $this->_pendingTests[] = array(
                'path' => $path,
                'method' => $methodData['method'],
                'log' => $className . '-' . $methodData['method'],
            ); 

        }
        
    }

    /**
     * Build test command
     * @param array $test Array of test data
     */
    private function _buildTestCommand($test)
    {
        $command = "phpunit -d memory_limit=512M " .  " --filter " . $test['method'] . ' ' . $test['path']; 
        return $command;
    }
    
    /**
     * Output string
     * @param string $string Log string
     * @param boolean $appendNewline T: append newline to string
     */
    private function _log($string, $appendNewline = true)
    {
        $dateStamp = date('m-d H:i:s');
        $string = "$dateStamp\t| $string";
        
        if ($appendNewline) {
            $string .= "\n";
        }
        
        echo $string;
    }

    /**
     * Format test for output
     * @param array $test Test data array
     */
    private function _formatTest($test)
    {
        return $test['path'] . ' - ' . $test['method'] . ' - PID #' . $test['pid'];
    }

    /**
     * Get status report
     * @return string
     */
    private function _statusReport()
    {
        $str = '';
        $running = array();
        
        foreach ($this->_runningTests as $t) {
            
            $running[] = $this->_formatTest($t);
           
        }
        
        $str .= "Running processes: " . count($this->_runningTests) . "\n" .
            '  -- ' . implode("\n  -- ", $running)  . "\n";
        $str .= "Pending processes: " . count($this->_pendingTests) . "\n";
        $str .= "Completed processes: " . count($this->_completedTests) . "\n";
        
        return $str;
    }



}

// If class is executed directly, start consumer
if (!count(debug_backtrace())) {
    $runner = new TestRunner();
    for ($i = 1; $i < count($argv); $i++) {
        $runner->addDirectory($argv[$i]);
    } 
    $runner->run();
}

