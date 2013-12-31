<?php

namespace Textpress;
use Textpress\Article;

class ArticleTest extends \PHPUnit_Framework_TestCase
{

    public function testArticleCreated()
    {
        $article = new Article(array(), 'Content');
        $this->assertTrue(is_object($article));
    }

    public function testArticleContent()
    {
        $article = new Article(array(), 'Content');
        $this->assertEquals('Content', $article->getContent());
    }

    public function testArticleTitle()
    {
        $article = new Article(array('title' => 'Title'), 'Content');
        $this->assertEquals('Title', $article->getTitle());
    }

    public function testGetMeta()
    {
        $article = new Article(array('title' => 'Title'), 'Content');
        $this->assertEquals(array('title' => 'Title'), $article->getMeta());
        $this->assertEquals('Title', $article->getMeta('title'));
    }

    public function testGetSummary()
    {
        $article = new Article(array(), 'This is an article');
        $this->assertEquals('This is an', $article->getSummary(15));
    }

    public function testGetUrl()
    {
        $article = new Article(array('url' => '/hello-world'), 'Content');
        $this->assertEquals('/hello-world', $article->getUrl());
    }

    public function testGetDate()
    {
        date_default_timezone_set('Asia/Calcutta');
        $article = new Article(array('date' => '12-12-2012'), 'Content');
        $this->assertEquals('12-12-2012', $article->getDate());
        $this->assertEquals('12 Dec, 2012', $article->getDate('d M, Y'));
    }

    public function testGetAuthor()
    {
        $article = new Article(array('author' => 'Shameer'), 'Content');
        $this->assertEquals('Shameer', $article->getAuthor());
    }

    public function testGetTags()
    {
        $tag = new Tag('php');
        $article = new Article(array('tag' => array('tag' => $tag)), 'Content');
        $this->assertSame(array('tag' => $tag), $article->getTags());
    }

    public function testGetCategories()
    {
        $article = new Article(array('category' => array('php' => 'Php')), 'Content');
        $this->assertEquals(array('php' => 'Php'), $article->getCategories());
    }
}