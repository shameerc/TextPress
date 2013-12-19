<h1>Archives</h1>
<div class="archives">
<p>
<ul>
<?php
  if($archives){
  	$years = array();
    foreach($archives as $archive){
    ?>
      <li>
       <span class="archives-date"><?php echo date($global['date.format'],strtotime($archive['meta']['date'])); ?></span> <a href="<?php echo $archive['url']; ?>"><?php echo $archive['meta']['title']; ?></a>
      </li>
  <?php 
  	}
  } ?>
</ul>
</p>
</div>
