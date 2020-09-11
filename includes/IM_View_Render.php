<?php
namespace Hellodev\InventoryManager;

class IM_View_Render
{

    private $directory;

    private $renderList;
    
    // Singleton design pattern
    protected static $instance = NULL;
    
    // Method to return the singleton instance
    public static function get_instance()
    {
        if (null == self::$instance) {
            self::$instance = new self();
        }
        
        return self::$instance;
    }

    public function __construct()
    {
        $this->directory = untrailingslashit(plugin_dir_path(IM_PLUGIN_FILE)) . '/views/';
        // Read files
        $files = array_filter(scandir($this->directory), function ($item) {
            return ! is_dir($this->directory . $item);
        });
        
        $files = array_combine(array_map('basename', $files), $files);
        
        $this->renderList = $files;
    }

    /*
     * Method to render provided templates and values
     */
    public function render($fileInput, $values = NULL)
    {
        foreach ($this->renderList as $file) {
            $withExtensionFileInput = $fileInput . ".php";
            if ($file == $withExtensionFileInput) {
                include ($this->directory . $withExtensionFileInput);
            }
        }
    }
}
