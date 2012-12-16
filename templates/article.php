<article class="post">
  <header>
    <h1><?php echo $article['meta']['title']; ?></h1>
    <span class="date"><?php  echo date($global['date.format'],strtotime($article['meta']['date']));  ?></span>
  </header>

  <section class="content">
    <?php echo $article['content']; ?>
  </section>
  <section class="comments">
  
    <?php if($global['facebook.appid']){?>
      <div class="fb-comments" data-href="<?php echo $article['url']?>" data-width="470" data-num-posts="<?php echo $global['facebook.postcount']?>"></div>
    <?php }?>
  
    <?php if($global['disqus.username']){?>
      <div id="disqus_thread"></div>
      <script type="text/javascript" src="http://disqus.com/forums/<?php echo $global['disqus.username']; ?>/embed.js"> </script>
      <noscript><a href="http://<?php echo $global['disqus.username']; ?>.disqus.com/?url=ref">View the discussion thread.</a></noscript>
      <a href="http://disqus.com" class="dsq-brlink">blog comments powered by <span class="logo-disqus">Disqus</span></a>
    <?php }?>
  </section>
</article>

