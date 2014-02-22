<?php  
header("Content-Type:text/xml");
if($articles) {
    reset($articles);
    $key = key($articles);
    $lastBuildDate = date(DATE_RSS, strtotime($articles[$key]->getDate()));
   // create simplexml object 
    $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8" ?><rss version="2.0" />', LIBXML_NOERROR|LIBXML_ERR_NONE|LIBXML_ERR_FATAL);
    // add channel information 
    $xml->addChild('channel'); 
    $xml->channel->addChild('title', $global['site.name']); 
    $xml->channel->addChild('link', "http://" . $_SERVER['HTTP_HOST']); 
    $xml->channel->addChild('description', $global['site.title']); 
    $xml->channel->addChild('pubDate', $lastBuildDate); 
    foreach($articles as $article) { 
        $item = $xml->channel->addChild('item'); 
        $item->addChild('title', $article->getTitle()); 
        $item->addChild('link', "http://" . $_SERVER['HTTP_HOST'].$article->getUrl()); 
        $guid = $item->addChild('guid', "http://" . $_SERVER['HTTP_HOST'].$article->getUrl()); 
        $guid->addAttribute("isPermaLink",'false');
        $item->description = "<![CDATA[" . substr(strip_tags($article->getContent()), 0,300) . "]]>"; 
        $item->addChild('pubDate', date(DATE_RSS, strtotime($article->getDate()))); 
    } 
    // output xml
    echo $xml->asXML(); 
}