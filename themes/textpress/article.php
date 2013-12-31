<article class="post">
  <header>
    <h1><?php echo $article->getTitle(); ?></h1>
    <div class="postmeta">
    <span class="date"><?php  echo $article->getDate($global['date.format']);  ?></span> /
    <span class="author-by"> By </span>
    <span class="author"><?php  echo $article->getAuthor()
                        ? $article->getAuthor()
                        : $global['author.name'] ;  ?></span>
    </div>
  </header>

  <section class="content">
    <?php echo $article->getContent(); ?>
    <div class="tags">
      Tags : 
      <?php
        foreach ($article->getTags() as $slug => $tag) {
          echo '<span class="tag"><a href="/tag/' . $slug .'">' . $tag->name . "</a></span>";
        }
        ?>
    </div>
  </section>
  <section class="comments">
    <?php if($global['disqus.username']){?>
      <div id="disqus_thread"></div>
      <script type="text/javascript" src="http://disqus.com/forums/<?php echo $global['disqus.username']; ?>/embed.js"> </script>
      <noscript><a href="http://<?php echo $global['disqus.username']; ?>.disqus.com/?url=ref">View the discussion thread.</a></noscript>
      <a href="http://disqus.com" class="dsq-brlink">blog comments powered by <span class="logo-disqus">Disqus</span></a>
    <?php }?>
  </section>
</article>

