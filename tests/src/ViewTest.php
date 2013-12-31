<?php
namespace Textpress;

use Textpress\View;

class ViewTest extends \PHPUnit_Framework_TestCase
{

    public function testViewObject()
    {
        $view =  new View();
        $this->assertTrue(is_object($view));
    }

    public function testRender()
    {
        $this->expectOutputString('From layout global. From template data.');
        $view = new View();
        $view->setTemplatesDirectory(dirname(__FILE__) . "/templates");
        $view->setLayout("layout.php");
        $view->appendGlobalData(array("test" => "global"));
        $view->appendData(array("test" => "data"));
        echo $view->render("test");
    }
}