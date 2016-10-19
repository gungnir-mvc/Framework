<?php
namespace Gungnir\Framework;

/**
 * @package gungnir-mvc\framework
 * @author Conny Karlsson <connykarlsson9@gmail.com>
 */
class View
{
    /** @var array $globalVariables Array of variables that will be available in all views */
    public static $globalVariables = array();

    /** @var string */
    private static $basePath = '';

    /** @var String $file Name of view file */
    private $file = null;

    /** @var string */
    private $fileExtension = '.php';

    /**
     * The complete path to a fallback directory to check for view file if
     * it was not present in the application scope. This will be prepended to
     * the registered file name when checking for fallback
     *
     * @var String
     */
    private $directoryFallback = null;

    /** @var array $data Array of variables that will only be available in this view */
    private $data = array();

    /**
     * @param String $file            Path to view file
     * @param String $fallbackPath    an Absolute path to a fallback folder to load from if primary is not found
     */
    public function __construct(String $file, String $fallbackPath = null)
    {
        if (strpos($file, '/') !== false) {
            $slashPos = strrpos($file, '/');
            $basePath = substr($file, 0, $slashPos+1);
            $this->setBasePath($basePath);
            $file = substr($file, $slashPos+1);
        }
        $this->setFile($file);
        if ($fallbackPath !== null) {
            $this->setFallbackPath($fallbackPath);
        }
    }

    /**
     * Get's a text representation of this View
     *
     * @return string The rendered view
     */
    public function __toString() : String
    {
        return $this->render();
    }

    public function __get(String $name)
    {
        return $this->getData($name);
    }

    public function __set(String $name, $data)
    {
        $this->setData($name, $data);
    }

    /**
     * Get the current base path for this view
     *
     * @return string
     */
    public function getBasePath() : String
    {
        return $this->basePath;
    }

    /**
     * Set the base path that view files get loaded from
     *
     * @param string $basePath
     *
     * @return View
     */
    public function setBasePath(String $basePath)
    {
        $this->basePath = $basePath;
    }

    /**
     * Get all globally registered view variables
     *
     * @return Array
     */
    public static function globals()
    {
        return static::$globalVariables;
    }

    /**
     * Checks if a given global view variable is registered
     *
     * @param  String  $name Name of global view variable
     * @return boolean
     */
    public static function hasGlobal(String $name)
    {
        return isset(static::$globalVariables[$name]);
    }

    /**
     * Retrieve a global view variable by name
     *
     * @param  String $name Name of global view variable
     * @return Mixed
     */
    public static function getGlobal(String $name)
    {
        return static::hasGlobal($name) ? static::$globalVariables[$name] :  false;
    }

    /**
     * Adds global view variable with given name
     * if it isnt already registered.
     *
     * @param String $name  Name of global view variable
     * @param Mixed $value Content of global view variable
     */
    public static function addGlobal(String $name, $value)
    {
        if (static::hasGlobal($name)) {
            throw new \Exception("Cant add global view variable since it already exists.");
        }

        static::setGlobal($name, $value);
    }

    /**
     * Adds global view variable with given name
     * and overwrites if it already exist.
     *
     * @param String $name  Name of global view variable
     * @param Mixed $value Content of global view variable
     */
    public static function setGlobal(String $name, $value)
    {
        static::$globalVariables[$name] = $value;
    }

    /**
     * Removes global view variable by name
     *
     * @param  String $name Name of global view variable to remove
     * @return void
     */
    public static function deleteGlobal(String $name)
    {
        if (static::hasGlobal($name)) {
            unset(static::$globalVariables[$name]);
        }
    }

    /**
     * Get local view variable by name
     *
     * @param  String $name Name of local view variable
     * @return Mixed Because.. Who knows?
     */
    public function getData(String $name)
    {
        return $this->data[$name] ?? false;
    }

    /**
     * Set local view variable with given name and content data
     *
     * @param String $name Name of local view variable
     * @param Mixed $data
     * @return View
     */
    public function setData(String $name, $data) : View
    {
        $this->data[$name] = $data;
        return $this;
    }

    /**
     * Set view file path for View
     *
     * @param String $file File path
     * @return View
     */
    public function setFile(String $file) : View
    {
        $this->file = $file;
        return $this;
    }

    /**
     * Get the current file path registered for View
     *
     * @return String
     */
    public function getFile() : String
    {
        return $this->file;
    }

    /**
     * Set view fallback directory for View
     *
     * @param String $path fallback directory
     * @return View
     */
    public function setFallbackPath(String $path) : View
    {
        $this->directoryFallback = $path;
        return $this;
    }

    /**
     * Get the current fallback directory registered for View
     *
     * @return String
     */
    public function getFallbackPath()
    {
        return $this->directoryFallback;
    }

    /**
     * Tries to include file by first checking for file in application
     * scope and if not found checks in fallback directory if one is defined.
     *
     * @throws ViewException
     * @return void
     */
    public function getFilePath()
    {
        $file = $this->getFile() . $this->fileExtension;
        $appPath = $this->getBasePath() . $file;
        $fallbackPath = $this->getFallbackPath() . $file;

        if (file_exists($appPath)) {
            return $appPath;
        } elseif ($this->getFallbackPath() && is_file($fallbackPath)) {
            return $fallbackPath;
        } else {
            throw new ViewException('View file ' . $this->getFile() . $this->fileExtension . ' not found! ');
        }
    }

    /**
     * Render the view into a string
     *
     * @return String
     */
    public function render() : String
    {
        $globals = View::globals();
        ob_start();
        extract($globals);
        extract($this->data);
        require $this->getFilePath();
        $result = ob_get_contents();
        ob_end_clean();
        return $result;
    }


}

class ViewException extends \Exception {}
