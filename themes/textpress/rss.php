<?php  
header("Content-Type:text/xml");
echo '<?xml version="1.0" encoding="UTF-8" ?>';
$lastBuildDate = "";
if($articles) {
        reset($articles);
        $key = key($articles);
        $lastBuildDate = date(DATE_RSS, strtotime($articles[$key]['meta']['date']));
}
?>
<rss version="2.0">
<channel>
        <title><?php echo htmlspecialchars($global['site.name']); ?></title>
        <description><?php echo htmlspecialchars($global['site.title']); ?></description>
        <link>http://<?php echo htmlspecialchars($_SERVER['HTTP_HOST']); ?></link>
        <lastBuildDate><?php echo $lastBuildDate; ?></lastBuildDate>
        <pubDate><?php echo $lastBuildDate; ?></pubDate>
        <ttl>1800</ttl>
<?php if($articles): ?>
<?php foreach($articles as $article): ?>
        <item>
                <title><?php echo htmlspecialchars($article['meta']['title']); ?></title>
                <description><?php echo htmlspecialchars(substr(strip_tags($article['content']), 0,300)); ?>...</description>
                <link>http://<?php echo htmlspecialchars($_SERVER['HTTP_HOST'].$article['url']); ?></link>
                <guid isPermaLink="true">http://<?php echo htmlspecialchars($_SERVER['HTTP_HOST'].$article['url']); ?></guid>
                <pubDate><?php echo date(DATE_RSS, strtotime($article['meta']['date']));?></pubDate>
        </item>
<?php endforeach ?>
<?php endif ?> 
</channel>
</rss>
