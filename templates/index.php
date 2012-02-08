<section id="articles">
<?php
	foreach($articles as $article){
?>
	  <article class="post">
      <header>
        <h1><a href="<?php echo $article['url']; ?>"><?php echo $article['meta']['title']; ?></a></h1>
        <span class="date"><?php  echo date($global['date.format'],strtotime($article['meta']['date']));  ?></span>
      </header>

      <section class="content">
        <?php echo substr(strip_tags($article['content']), 0,150); ?>...
      </section>
      <div class="more"><a href="<?php echo $article['url']; ?>">read on &raquo;</a></div>
      <br />
    </article>
  <?php
	}
  ?>
</section>
