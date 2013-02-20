<?php
/**
 * Echo JSON object:
 * array(
 *     'className' => '',
 *     'methodData' => array(
 *         'name' => '',
 *         'groups' => ''
 *      )
 * )
 */
if (count($argv) !== 2) {
    echo "Script takes a single argument - class path\n";
    exit;
}

$script = new script_ClassInfoJson();
$script->run($argv[1]);

/**
 * Handles getting class info in json format
 */
class script_ClassInfoJson
{
    /**
     * Run the command - echoes json
     * @param string $path path to class file
     */
    public function run($path)
    {
        try {
            
            // Get class name
            $className = $this->_getClassNameFromPath($path);
            
        } catch (Exception $e) {
            
            echo json_encode(array('error' => $e->getMessage()));
            return;
            
        }
        
        // Include test file
        $this->_includeTestFile($path);
        
        // Create reflection class and get group names
        $reflection = new ReflectionClass($className);
        $classGroups = $this->_getGroupsFromDocComment($reflection->getDocComment());
        
        // Add test methods to pending tests
        $methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);
        
        $methodData = array();
        foreach ($methods as $method) {
            
            if (strpos($method->name, 'test') === 0) {
                
                $groups = array_unique(
                    array_merge($classGroups, $this->_getGroupsFromDocComment($method->getDocComment()))
                );
                
                $methodName = $method->name;
                $methodData[] = array(
                    'method' => $methodName,
                    'groups' => $groups,
                );

            }
        }
        
        echo json_encode(array('className' => $className, 'methodData' => $methodData));
    }
    
    /**
     * Get class name from path
     * @param string $path Full path to test file
     */
    private function _getClassNameFromPath($path)
    {
        // Strip everything up to base directory
        $path = preg_replace('/.*selenium-setup-to-scale.php./', '', $path);
        return str_replace(array('\\', '.php'), array('_', ''), $path);
    }
    
    /**
     * Get groups from doc comment
     * @param string $comment Doc comment
     */
    private function _getGroupsFromDocComment($comment)
    {
        $groups = array();
        $commentLines = explode("\n", $comment);
        
        foreach ($commentLines as $cLine) {
            if (preg_match('/@group (.*)$/', $cLine, $cMatches)) {
                $groups[] = trim($cMatches[1]);
            }
        }
        return $groups;
    }
    
    /**
     * Include test file - do in separate method to prevent variable conflicts
     * @param string $path Path to test file
     */
    private function _includeTestFile($path)
    {
        require_once $path;
    }
    
}
