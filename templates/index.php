<section class="hero-unit">
  <p>Download TextPress from GitHub</p>
  <p class="small">TextPress is now Beta</p>
  <a href="https://github.com/shameerc/TextPress/tarball/master" class="btn btn-large btn-primary">Get TextPress</a>
</section>
<section id="articles">
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
        <span class="date"><?php  echo date($global['date.format'],strtotime($article['meta']['date']));  ?></span>
      </header>

      <section class="content">
        <?php echo substr(strip_tags($article['content']), 0,150); ?>...
      </section>
      <div class="postmeta">
        <div class="tags">
        <?php
        foreach ($article['meta']['tag'] as $key => $tag) {
          echo '<span class="tag"><a href="/tag/' . $tag .'">' . ucfirst($tag) . "</a></span>";
        }
        ?>
        </div>
        <div class="more"><a href="<?php echo $article['url']; ?>">read on &raquo;</a></div>
        <div class="clear"></div>
      </div>
    </article>
<?php
  }
}
?>
</section>
