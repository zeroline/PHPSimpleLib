<?php
/* Copyright (C) Frederik Nieß <fred@zeroline.me> - All Rights Reserved */

namespace PHPSimpleLib\Core\Controlling;

use PHPSimpleLib\Core\Controlling\NamespaceExtractor;

class ModuleManager
{
    use \PHPSimpleLib\Core\Data\ConfigReaderTrait;
    use \PHPSimpleLib\Core\ObjectFactory\Singleton;

    const DEFAULT_LIBRARY_NAME = "PHPSimpleLib";
    
    const DEFAULT_FOLDER_MODULES = 'Modules';
    const DEFAULT_FOLDER_CONTROLLER = 'Controller';
    const DEFAULT_CONTROLLER_CLASS_SUFFIX = 'Controller';
    
    const DEFAULT_FOLDER_COMMAND = 'Commands';
    const DEFAULT_COMMAND_CLASS_SUFFIX = 'CommandController';

    const RESERVED_MODULE_NAMES = array(
        'PHPSimpleLib',
        'Core',
        'System'
    );
    
    /**
     * Store for all instanciated controller classes
     *
     * @var array
     */
    private $controllerInstances = array();

    /**
     * Store for all instanciated command controller classes
     *
     * @var array
     */
    private $commandControllerInstances = array();

    /**
     * Stored route mappings
     *
     * @var array
     */
    private $controllerRouteMappings = array();
    
    /**
     * Module store. Multidimensional with instancs & co
     *
     * @var array
     */
    private $modules = array();

    /**
     * Stores the folders for the modules
     *
     * @var array
     */
    private $moduleFolder = array();

    /**
     * Module to controller indexing helper store
     *
     * @var array
     */
    private $moduleController = array();

    /**
     * Module to command controller indexing helper store
     *
     * @var array
     */
    private $moduleCommandController = array();
    
    /**
     * 
     * @param Controller $controller 
     * @return string 
     */
    public function getSimplifiedControllerName(Controller $controller) : string {
        $classNamePartials = explode('\\', get_class($controller));
        $simplifiedControllerClassName = $classNamePartials[count($classNamePartials) - 1];
        $simplifiedControllerClassName2 = str_replace(self::DEFAULT_CONTROLLER_CLASS_SUFFIX, '', ($simplifiedControllerClassName));
        return $simplifiedControllerClassName2;
    }

    /**
     * 
     * @param Controller $controller 
     * @return string 
     */
    public function getSimplifiedCommandControllerName(Controller $controller) : string {
        $classNamePartials = explode('\\', get_class($controller));
        $simplifiedControllerClassName = $classNamePartials[count($classNamePartials) - 1];
        $simplifiedControllerClassName2 = str_replace(self::DEFAULT_COMMAND_CLASS_SUFFIX, '', ($simplifiedControllerClassName));
        return $simplifiedControllerClassName2;
    }

    /**
     * Returns an array with the module name and simplified controller name for
     * the given controller class.
     * Structure is
     * [MODULENAME, SIMPLIFIED_CONTROLLER_NAME] or
     * [null, null]
     *
     * @param string $class
     * @return array
     */
    public function getNamesByControllerClass(string $class) : array
    {
        foreach ($this->modules as $moduleName => $moduleData) {
            foreach ($moduleData as $data) {
                if ($data->controllerClass == $class) {
                    return array($moduleName, $data->simplifiedControllerClassName2);
                }
            }
        }
        return array(null, null);
    }

    /**
     * Checks the given name against the reserved module names.
     * Throws an exception if the module name matches one reserved
     * name.
     *
     * @param string $moduleName
     * @return void
     *
     * @throws \Exception
     */
    private function checkModuleName(string $moduleName) : void
    {
        foreach (self::RESERVED_MODULE_NAMES as $reservedName) {
            if (strtolower($moduleName) == strtolower($reservedName)) {
                throw new \Exception('You must not register a module with a reserved name! Reserved names are ' . implode(',', self::RESERVED_MODULE_NAMES) . '.');
            }
        }
    }
    
    /**
     * Creates a new entry in the module store
     *
     * @param string $moduleName
     * @return void
     */
    private function addModule(string $moduleName) : void
    {
        $this->modules[$moduleName] = array();
        $this->moduleFolder[$moduleName] = null;
        $this->moduleController[strtolower($moduleName)] = array();
        $this->moduleCommandController[strtolower($moduleName)] = array();
    }

    /**
     * Stores the folder for a specific module
     *
     * @param string $moduleName
     * @param string $folder
     * @return void
     */
    private function addModuleFolder(string $moduleName, string $folder) : void
    {
        $this->moduleFolder[$moduleName] = $folder;
    }
    
    /**
     * Adds a controller instance to the module store.
     * Additional class information and namings are genarted and
     * stored.
     *
     * @param string $moduleName
     * @param mixed $controller
     * @return void
     */
    private function addControllerToModule(string $moduleName, $controller) : void
    {
        $classNamePartials = explode('\\', get_class($controller));
        $simplifiedControllerClassName = $classNamePartials[count($classNamePartials) - 1];
        $simplifiedControllerClassName2 = str_replace(self::DEFAULT_CONTROLLER_CLASS_SUFFIX, '', ($simplifiedControllerClassName));
        $this->modules[$moduleName][] = (object)array(
            'moduleName' => $moduleName,
            'controllerInstance' => $controller,
            'controllerClass' => get_class($controller),
            'simplifiedControllerClassName' => $simplifiedControllerClassName,
            'simplifiedControllerClassName2' => $simplifiedControllerClassName2,
        );
        $this->moduleController[strtolower($moduleName)][strtolower($simplifiedControllerClassName2)] = $controller;
    }
    
    /**
     * Adds a command controller instance to the command controller and module store.
     *
     * @param string $moduleName
     * @param mixed $controller
     * @return void
     */
    private function addCommandControllerToModule(string $moduleName, $controller) : void
    {
        $classNamePartials = explode('\\', get_class($controller));
        $simplifiedControllerClassName = $classNamePartials[count($classNamePartials) - 1];
        $simplifiedControllerClassName2 = str_replace(self::DEFAULT_COMMAND_CLASS_SUFFIX, '', ($simplifiedControllerClassName));
        $this->moduleCommandController[strtolower($moduleName)][strtolower($simplifiedControllerClassName2)] = $controller;
    }
    
    /**
     * Returns a controller instance if found by module name and class name
     *
     * @param string $moduleName
     * @param string $simplifiedControllerClassName
     * @return mixed|null
     */
    public function getControllerByModuleAndClass(string $moduleName, string $simplifiedControllerClassName)
    {
        if (array_key_exists($moduleName, $this->moduleController)) {
            if (array_key_exists($simplifiedControllerClassName, $this->moduleController[$moduleName])) {
                return $this->moduleController[$moduleName][$simplifiedControllerClassName];
            }
        }
        return null;
    }
    
    /**
     * Returns a command controller instance if found by module name and class name
     *
     * @param string $moduleName
     * @param string $simplifiedControllerClassName
     * @return mixed|null
     */
    public function getCommandControllerByModuleAndClass(string $moduleName, string $simplifiedControllerClassName)
    {
        if (array_key_exists($moduleName, $this->moduleCommandController)) {
            if (array_key_exists($simplifiedControllerClassName, $this->moduleCommandController[$moduleName])) {
                return $this->moduleCommandController[$moduleName][$simplifiedControllerClassName];
            }
        }
        return null;
    }
    
    /**
     * Checks folders for modules and controllers.
     * Stores them for future use.
     *
     * @return void
     */
    public function prepareControllerInstances() : void
    {
        $moduleFolderName = $this->getConfig('moduleFolderName', self::DEFAULT_FOLDER_MODULES);
        $moduleFolder = $this->getConfig('cwd', getcwd()) . DIRECTORY_SEPARATOR . $moduleFolderName;
        $controllerFolderName = $this->getConfig('controllerFolderName', self::DEFAULT_FOLDER_CONTROLLER);
        
        $commandControllerFolderName = $this->getConfig('commandControllerFolderName', self::DEFAULT_FOLDER_COMMAND);

        // Load modules and controller from application environment
        if (is_dir($moduleFolder)) {
            foreach (new \DirectoryIterator($moduleFolder) as $moduleFile) {
                if ($moduleFile->isDir() && !$moduleFile->isDot()) {
                    $currentModuleFolder = $moduleFile->getFilename();
                    
                    $this->checkModuleName($currentModuleFolder);
                    $this->addModule($currentModuleFolder);
                    $this->addModuleFolder($currentModuleFolder, $moduleFile->getPath() . DIRECTORY_SEPARATOR . $currentModuleFolder);
                    
                    // Controller
                    $controllerDir = $moduleFolderName . DIRECTORY_SEPARATOR . $currentModuleFolder . DIRECTORY_SEPARATOR . $controllerFolderName . DIRECTORY_SEPARATOR;
                    $controllerFiles = glob($controllerDir . '*.php');
                    foreach ($controllerFiles as $filename) {
                        $controllerClass = '\\' . str_replace(array('/','.php'), array('\\',''), $filename);
                        try {
                            $this->controllerInstances[$controllerClass] = $controllerClass::getInstance();
                            $this->controllerRouteMappings = array_merge($controllerClass::getInstance()->getRouteMappings(), $this->controllerRouteMappings);
                            $this->addControllerToModule($currentModuleFolder, $controllerClass::getInstance());
                        } catch (\Exception $ex) {
                            throw $ex;
                        }
                    }
                    
                    // CommandController
                    $controllerDir = $moduleFolderName . DIRECTORY_SEPARATOR . $currentModuleFolder . DIRECTORY_SEPARATOR . $commandControllerFolderName . DIRECTORY_SEPARATOR;
                    $controllerFiles = glob($controllerDir . '*.php');
                    foreach ($controllerFiles as $filename) {
                        $controllerClass = '\\' . str_replace(array('/','.php'), array('\\',''), $filename);
                        try {
                            $this->commandControllerInstances[$controllerClass] = $controllerClass::getInstance();
                            $this->addCommandControllerToModule($currentModuleFolder, $controllerClass::getInstance());
                        } catch (\Exception $ex) {
                            throw $ex;
                        }
                    }
                }
            }
        }

        // Load modules and controller from core

        $coreModuleFolder = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . $moduleFolderName;

        if (is_dir($coreModuleFolder)) {
            foreach (new \DirectoryIterator($coreModuleFolder) as $moduleFile) {
                if ($moduleFile->isDir() && !$moduleFile->isDot()) {
                    $currentModuleFolder = $moduleFile->getFilename();
                    
                    $this->addModule($currentModuleFolder);
                    $this->addModuleFolder($currentModuleFolder, $moduleFile->getPath() . DIRECTORY_SEPARATOR . $currentModuleFolder);

                    // CommandController
                    $controllerDir = $coreModuleFolder . DIRECTORY_SEPARATOR . $currentModuleFolder . DIRECTORY_SEPARATOR . $commandControllerFolderName . DIRECTORY_SEPARATOR;
                    
                    $controllerFiles = glob($controllerDir . '*.php');
                    foreach ($controllerFiles as $filename) {
                        $controllerClass = NamespaceExtractor::byRegExp(file_get_contents($filename)) . '\\' . str_replace('.php', '', basename($filename));//'\\'.str_replace(array('/','.php'), array('\\',''), $filename);
                        try {
                            $this->commandControllerInstances[$controllerClass] = $controllerClass::getInstance();
                            $this->addCommandControllerToModule($currentModuleFolder, $controllerClass::getInstance());
                        } catch (\Exception $ex) {
                            throw $ex;
                        }
                    }
                }
            }
        }
    }
    
    /**
     * Returns the route mappings array
     *
     * @return array
     */
    public function getAllRouteMappings() : array
    {
        return $this->controllerRouteMappings;
    }
    
    /**
     * Returns the controller intances
     *
     * @return array
     */
    public function getControllerInstances() : array
    {
        return $this->controllerInstances;
    }

    /**
     * Returns the command controller instances
     *
     * @return array
     */
    public function getCommandControllerInstances() : array
    {
        return $this->commandControllerInstances;
    }

    /**
     * Returns all registered module names
     *
     * @return array
     */
    public function getModuleNames() : array
    {
        return array_keys($this->modules);
    }

    /**
     * Returns the path of one module
     *
     * @param string $moduleName
     * @return string
     */
    public function getModulePath(string $moduleName) : string
    {
        return $this->moduleFolder[$moduleName];
    }
    
    /**
     * Returns the big modules array
     *
     * @return array
     */
    public function getModules() : array
    {
        return $this->modules;
    }

    /**
     * Returns all modules / module controller instances
     * @param null|string $moduleName 
     * @return array 
     */
    public function getCommandControllerModules(?string $moduleName = null) : array {
        return $moduleName ? array_filter($this->moduleCommandController, function($key) use ($moduleName) { return $key == strtolower($moduleName); } ,ARRAY_FILTER_USE_KEY ) : $this->moduleCommandController;
    }
}
