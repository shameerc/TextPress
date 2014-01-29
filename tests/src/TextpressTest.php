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
            // 'SCRIPT_NAME' => '/foo', //<-- Physical
            'PATH_INFO' => '/bar', //<-- Virtual
            'QUERY_STRING' => 'one=foo&two=bar',
            'SERVER_NAME' => 'slimframework.com',
        ));
        date_default_timezone_set('Asia/Dubai');
    }

    public static function config()
    {
        return array(
            'markdown' => true,
            'date.format' => 'd M, Y',
            'article.path' => __DIR__ . '/articles',
            'themes.path' => __DIR__ . '/templates',
            'layout.file' => 'layout',
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
                    'route' => '/:year/:month/:date/:article',
                    'template'=>'test'
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
        $this->assertTrue(in_array("2012-02-16-test-article-2.txt", $fileNames));
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

    public function testFilterArticles()
    {
        $slim = new \Slim\Slim(array('view' => new \Textpress\View()));
        $textpress =  new Textpress($slim, self::config());
        $textpress->init();
        $articles = $textpress->loadArticles();
        $this->assertEquals(2, count($articles));
        $articles = $textpress->filterArticles('tag', 'test');
        $this->assertEquals(1, count($articles));
    }

    public function testSetArticle()
    {
        $slim = new \Slim\Slim(array('view' => new \Textpress\View()));
        $textpress =  new Textpress($slim, self::config());
        $textpress->init();
        $articles = $textpress->loadArticles();
        $textpress->setArticle("/2012/02/06/test-article");
        $article = $textpress->getViewData('article');
        $this->assertEquals("Test article", $article->getTitle());
    }

    public function testSetArchives()
    {
        $slim = new \Slim\Slim(array('view' => new \Textpress\View()));
        $textpress =  new Textpress($slim, self::config());
        $textpress->init();
        $articles = $textpress->loadArticles();
        $archives1 = $textpress->setArchives('2012-02', 'Y-m');
        $this->assertEquals(2, count($archives1));
        $archives2 = $textpress->setArchives('2012-02-06', 'Y-m-d');
        $this->assertEquals(1, count($archives2));
    }

    public function testGetArticlePath()
    {
        $slim = new \Slim\Slim(array('view' => new \Textpress\View()));
        $textpress =  new Textpress($slim, self::config());
        $textpress->init();
        $articles = $textpress->loadArticles();
        $filename = '2012-02-06-test-article.txt';
        $path = $textpress->getArticlePath($filename);
        $this->assertEquals( __DIR__ . '/articles/' . $filename, $path);
        $this->assertFalse($textpress->getArticlePath('no-such-files.txt'));
    }

    public function testGetPath()
    {
        $slim = new \Slim\Slim(array('view' => new \Textpress\View()));
        $textpress =  new Textpress($slim, self::config());
        $textpress->init();
        $articles = $textpress->loadArticles();
        $path = $textpress->getPath(array('2012','02','16','article-slug'));
        $this->assertEquals('/2012/02/16/article-slug', $path);
    }

    public function testGetArticleUrl()
    {
        $slim = new \Slim\Slim(array('view' => new \Textpress\View()));
        $textpress =  new Textpress($slim, self::config());
        $textpress->init();
        $articles = $textpress->loadArticles();
        $url = $textpress->getArticleUrl('16-02-2012','article-slug');
        $this->assertEquals('/2012/02/16/article-slug', $url);
    }

    public function testSlugize()
    {
        $slim = new \Slim\Slim(array('view' => new \Textpress\View()));
        $t =  new Textpress($slim, self::config());
        $this->assertEquals('simple-slug', $t->slugize('Simple slug'));
        $this->assertEquals('slug-with-spaces', $t->slugize('  slug  with spaces '));
    }

    public function testCollectCategories()
    {
        $slim = new \Slim\Slim(array('view' => new \Textpress\View()));
        $t =  new Textpress($slim, self::config());
        $meta = array('title'=>'Test');
        $c1 = $t->collectCategories($meta);
        $this->assertTrue(empty($c1));
        $meta = array(
                'title' => 'Test title',
                'category' => ', TextPress, Slim,'
            );
        $c2 = $t->collectCategories($meta);
        $this->assertEquals(2, count($c2));
        $this->assertEquals('TextPress', array_shift($c2));
    }

    public function testCollectTags()
    {
        $slim = new \Slim\Slim(array('view' => new \Textpress\View()));
        $t =  new Textpress($slim, self::config());
        $meta = array(
                'title' => 'Test title',
                'tag' => ', TextPress, Slim,'
            );
        $tags1 = $t->collectTags($meta);
        $this->assertEquals(2, count($tags1));
    }
}