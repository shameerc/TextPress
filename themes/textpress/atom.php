<?php  
header("Content-Type:text/xml");  
echo '<?xml version="1.0" encoding="UTF-8" ?>';
$lastBuildDate = "";
if($articles) {
	$lastBuildDate = date('c', strtotime($articles[0]['meta']['date']));
}
?>
<feed xmlns="http://www.w3.org/2005/Atom">
 
        <title><?php echo $global['site.name']; ?></title>
        <subtitle><?php echo $global['site.title']; ?></subtitle>
        <link href="http://<?php echo $_SERVER['HTTP_HOST']; ?>/feed/atom" rel="self" />
        <link href="http://<?php echo $_SERVER['HTTP_HOST']; ?>" />
        <id>http://<?php echo $_SERVER['HTTP_HOST']; ?>/feed/atom</id>
        <updated><?php echo $lastBuildDate; ?></updated>
        <author>
                <name>John Doe</name>
                <email>johndoe@example.com</email>
        </author>
<?php if($articles): ?>
<?php foreach($articles as $article): ?>
        <entry>
                <title><?php echo $article['meta']['title']; ?></title>
                <link href="http://<?php echo $_SERVER['HTTP_HOST'].$article['url']; ?>" />
                <id>http://<?php echo $_SERVER['HTTP_HOST'].$article['url']; ?></id>
                <updated><?php echo date('c', strtotime($article['meta']['date']));?></updated>
                <summary><?php echo substr(strip_tags($article['content']), 0,300); ?>...</summary>
        </entry>
<?php endforeach ?>
<?php endif ?> 
</feed>
