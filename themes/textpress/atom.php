<?php
header("Content-Type:text/xml");
if($articles) {
    reset($articles);
    $key = key($articles);
    $lastBuildDate = date('c', strtotime($articles[$key]['meta']['date']));
   // create simplexml object
    $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8" ?><feed xmlns="http://www.w3.org/2005/Atom" />', LIBXML_NOERROR|LIBXML_ERR_NONE|LIBXML_ERR_FATAL);
    // add channel information
    
    $xml->addChild('title', $global['site.name']);
    
    $link = $xml->addChild('link');
    $link->addAttribute("href", "http://" . $_SERVER['HTTP_HOST']);
    
    $link = $xml->addChild('link');
    $link->addAttribute("href", "http://" . $_SERVER['HTTP_HOST'] . "/feed/atom.xml");
    $link->addAttribute("rel","self");

    $xml->addChild('subtitle', $global['site.title']);
    $xml->addChild('updated', $lastBuildDate);
    $xml->addChild('id', "http://" . $_SERVER['HTTP_HOST'] . "/feed/atom");
    $author = $xml->addChild("author");
    $author->addChild("name","John Doe");
    $author->addChild("email","johndoe@example.com");
    foreach($articles as $article) {
        $entry = $xml->addChild('entry');
        $entry->addChild('title', $article['meta']['title']);
        $link = $entry->addChild('link');
        $link->addAttribute("href", "http://" . $_SERVER['HTTP_HOST'].$article['url']);
        $entry->addChild('id', "http://" . $_SERVER['HTTP_HOST'].$article['url']);
        $entry->addChild("summary");
        $entry->summary = "<![CDATA[" . substr(strip_tags($article['content']), 0,300) . "]]>";
        $entry->summary->addAttribute("type","html");
        $entry->addChild('updated', date('c', strtotime($article['meta']['date'])));
    }
    // output xml
    echo $xml->asXML();
}