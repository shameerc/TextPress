<h1>Archives</h1>
<div class="archives">
<p>
<?php
  if($archives){
    echo  '<ul>';
  	$years = array();
    foreach($archives as $archive){
    ?>
      <li>
       <span class="archives-date"><?php echo date($global['date.format'],strtotime($archive['meta']['date'])); ?></span> <a href="<?php echo $archive['url']; ?>"><?php echo $archive['meta']['title']; ?></a>
      </li>
  <?php 
  	}
    echo '</ul>';
  } else{ ?>
    No archives found.
  <?php 
  }
  ?>

</p>
</div>
