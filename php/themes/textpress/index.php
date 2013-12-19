<div class="news-head">/News</div>
<?php
if( count($articles) < 1 ){
  echo "<h3>No articles found!</h3>";
}
else{
  foreach($articles as $article){
?>
<article class="post">
  <header>
    <h1><a href="<?php echo $article['url']; ?>"><?php echo $article['meta']['title']; ?></a></h1>
    <div class="postmeta">
      <span class="date"><?php  echo date($global['date.format'],strtotime($article['meta']['date']));  ?></span> / 
      <span class="author-by"> By </span>
      <span class="author"><?php  echo isset($article['meta']['author'])
                        ? $article['meta']['author']
                        : $global['author.name'] ;  ?></span>
      <div class="clear"></div>
    </div>
  </header>

  <section class="content">
    <?php echo preg_replace('/\s+?(\S+)?$/', '', substr(strip_tags($article['content']), 0, 250)); ?>...
  </section>
  <div class="more">
    <a href="<?php echo $article['url']; ?>">Read on &raquo;</a>
  </div>
</article>
<?php
  }
}
?>