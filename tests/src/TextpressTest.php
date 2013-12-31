<?php
namespace Textpress;

class TextpressTest extends \PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        //Remove environment mode if set
        unset($_ENV['SLIM_MODE']);

        //Reset session
        $_SESSION = array();

        //Prepare default environment variables
        \Slim\Environment::mock(array(
            'SCRIPT_NAME' => '/foo', //<-- Physical
            'PATH_INFO' => '/bar', //<-- Virtual
            'QUERY_STRING' => 'one=foo&two=bar',
            'SERVER_NAME' => 'slimframework.com',
        ));
        date_default_timezone_set('Asia/Calcutta');
    }

    public static function config()
    {
        return array(
            'markdown' => true,
            'article.path' => __DIR__ . '/articles',
            'themes.path' => __DIR__ . '/templates',
            'file.extension' => '.txt',
            'active.theme' => "",
            'prefix' => '', 
            'routes' => array(
                '__root__'=> array(
                    'route' => '/home',
                    'template'=>'test',
                    'layout' => 'layout'
                ),
                'article' => array(
                    'route' => '/:year/:month/:date/:article'
                )
            )
        );
    }

    public static function mockArticles()
    {
        $meta1 = array(
                'title' => 'Test title 1',
                'date' => '06-02-2012',
                'slug' => 'test-article'
            );
        $content1 = 'Article content 1';
        $meta2 = array(
                'title' => 'Test title 2',
                'date' => '16-02-2012'
            );
        $content2 = 'Article content 2';
        return array(
                new Article($meta1, $content1),
                new Article($meta2, $content2)
            );
    }

    public function testTextpressInstanceProperties()
    {
        $slim = new \Slim\Slim(array('view' => new \Textpress\View()));
        $textpress =  new Textpress($slim, self::config());
        $this->assertTrue(is_object($textpress));
        $this->assertInstanceOf('\Slim\Slim', $textpress->slim);
        $this->assertInstanceOf('\Textpress\View', $textpress->slim->view());
    }

    public function testConfigThatExists()
    {
        $slim = new \Slim\Slim();
        $textpress =  new Textpress($slim, self::config());
        $this->assertEquals(__DIR__ . '/templates', $textpress->getConfig('themes.path'));
    }

    public function testConfigThatDoesnotExists()
    {
        $slim = new \Slim\Slim();
        $textpress =  new Textpress($slim, self::config());
        $this->assertNull($textpress->getConfig('wrong.config'));
    }

    public function testGetFileNames()
    {
        $slim = new \Slim\Slim();
        $textpress =  new Textpress($slim, self::config());
        $fileNames = $textpress->getFileNames();
        $this->assertEquals("2012-02-16-test-article-2.txt", $fileNames[0]);
    }

    public function testLoadArticles()
    {
        $slim = new \Slim\Slim(array('view' => new \Textpress\View()));
        $textpress =  new Textpress($slim, self::config());
        $textpress->init();
        $articles = $textpress->loadArticles();
        $this->assertEquals(2, count($articles));
    }

    public function testLoadArticle()
    {
        $slim = new \Slim\Slim(array('view' => new \Textpress\View()));
        $textpress =  new Textpress($slim, self::config());
        $textpress->init();
        $article = $textpress->loadArticle('2012-02-06-test-article.txt');
        $this->assertInstanceOf('\Textpress\Article', $article);
        $this->assertEquals("<p>Test article content</p>\n", $article->getContent());
    }

    public function testSortArticles()
    {
        $articles = self::mockArticles();
        $slim = new \Slim\Slim(array('view' => new \Textpress\View()));
        $textpress =  new Textpress($slim, self::config());
        $sorted = $textpress->sortArticles($articles);
        $firstArticle = array_shift($sorted);
        $this->assertEquals('Test title 2', $firstArticle->getTitle());
    }
}