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
        $view = new View();
        $view->setTemplatesDirectory(dirname(__FILE__) . "/templates");
        $view->setLayout("layout.php");
        $view->appendGlobalData(array("test" => "global"));
        $view->appendData(array("test" => "data"));
        $viewData = $view->render("test");
        $this->assertEquals('From layout global. From template data.', $viewData);
    }

    public function testRenderWithoutLayout()
    {
        $view = new View();
        $view->setTemplatesDirectory(dirname(__FILE__) . "/templates");
        $view->appendGlobalData(array("test" => "global"));
        $view->appendData(array("test" => "data"));
        $viewData = $view->render("test");
        $this->assertEquals('From template data.', $viewData);
    }
}