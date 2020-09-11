<?php
namespace Hellodev\InventoryManager;

if (! defined('ABSPATH')) {
    exit();
}

class IM_Stock_Import_Controller{

    private $viewRender;

    public function __construct(){
    }

    public function renderView($values = null){
        $values = array();
        $this->viewRender = IM_View_Render::get_instance();
        $this->viewRender->render("stock-import", $values);
    }
}
