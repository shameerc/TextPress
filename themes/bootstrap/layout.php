<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>
      <?php 
        $title= (isset($global['title'])) ? $global['title'] : $global['site.title'];
        echo $global['site.name'] .' | '. $title;
      ?>
    </title>
    <meta name="description" content="">
    <meta content='Authur Name' name='<?php echo $global['author.name']; ?>'/> 

    <!-- Le HTML5 shim, for IE6-8 support of HTML elements -->
    <!--[if lt IE 9]>
      <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->

    <!-- Le styles -->
    <link href="<?php echo $global['theme.base'];?>/assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?php echo $global['theme.base'];?>/assets/css/main.css" rel="stylesheet">

    <!-- Le fav and touch icons -->
    <link rel="shortcut icon" href="images/favicon.ico">
    <link rel="apple-touch-icon" href="images/apple-touch-icon.png">
    <link rel="apple-touch-icon" sizes="72x72" href="images/apple-touch-icon-72x72.png">
    <link rel="apple-touch-icon" sizes="114x114" href="images/apple-touch-icon-114x114.png">
    <script src="<?php echo $global['theme.base'];?>/assets/js/jquery.js"></script>
    <script src="<?php echo $global['theme.base'];?>/assets/js/bootstrap.min.js"></script>
    <?php if($global['google.analytics']){?>
    <script type="text/javascript">
      var _gaq = _gaq || [];
      _gaq.push(['_setAccount', '<?php echo $global['google.analytics']; ?>']);
      _gaq.push(['_trackPageview']);

      (function() {
        var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
        ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
        var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
      })();
      $(function(){
        $('.dropdown-toggle').dropdown()  
      })
  </script>
  <?php }?>
  </head>
  <body>
    <div class="navbar navbar-fixed-top">
      <div class="navbar-inner">
        <div class="container">
          <h1><a class="brand" href="<?php echo $global['prefix'];?>/"><?php echo $global['site.name'];?></a></h1>
          <ul class="nav">
            <li class="dropdown <?php if($global["route"] == "category") echo "active";?>">
              <a class="dropdown-toggle" id="drop5" role="button" data-toggle="dropdown" href="#">Categories <b class="caret"></b></b></a>
              <ul id="menu3" class="dropdown-menu" role="menu" aria-labelledby="drop5">
                <?php
                  foreach ($global['categories'] as $slug => $category) {
                    echo '<li><a tabindex="-1" href="/category/'.$slug.'">'. $category .'</a></li>';
                  }
                ?>
              </ul>
            </li>
            <li class="<?php if($global["route"] == "archives") echo "active";?>"><a href="<?php echo $global['prefix'];?>/archives">Archives</a></li>
            <li><a href="https://github.com/shameerc/TextPress" target="_blank">Source</a>
            <li class="<?php if($global["route"] == "about") echo "active";?>"><a href="<?php echo $global['prefix'];?>/about">About</a></li>
          </ul>
        </div>
      </div>
    </div>
    <div class="container">
      <div class="content">
        <div class="row">
          <div class="span13">
            <?php echo $content;?>
          </div>
        </div>
      </div>

      <footer>
        <p>Powered by TextPress &copy; <a href="http://shameerc.com" target="_blank">Shameer C </a>2012</p>
      </footer>
    </div> <!-- /container -->
    
  </body>
</html>
