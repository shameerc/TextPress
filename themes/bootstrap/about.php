<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>
     About | Example of a static page ;)
    </title>
    <meta name="description" content="">
    <meta content='Authur Name' name='Author'/> 

    <!-- Le HTML5 shim, for IE6-8 support of HTML elements -->
    <!--[if lt IE 9]>
      <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->

    <!-- Le styles -->
    <link href="/themes/bootstrap/assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="/themes/bootstrap/assets/css/main.css" rel="stylesheet">
    <!-- Le fav and touch icons -->
    <link rel="shortcut icon" href="images/favicon.ico">
    <link rel="apple-touch-icon" href="images/apple-touch-icon.png">
    <link rel="apple-touch-icon" sizes="72x72" href="images/apple-touch-icon-72x72.png">
    <link rel="apple-touch-icon" sizes="114x114" href="images/apple-touch-icon-114x114.png">
  </head>

  <body>

    <div class="navbar navbar-fixed-top">
      <div class="navbar-inner">
        <div class="container">
          <h1><a class="brand" href="<?Php echo $global['prefix'];?>/">Me and <?Php echo $global['site.name'];?></a></h1>
        </div>
      </div>
    </div>

    <div class="container">
      <div class="content">
        <div class="row">
          <div class="span13 post">
            <p>Hi There</p>
            <p>I am Shameer, a developer from God's own country (Kerala), India. I am mainly working on web development in LAMP stack. 
              Apart from that I do enjoy coding in Ruby (infact, I love it), system administration, database administration, etc. 
              Occasionally I write about PHP stuffs in <a href="http://phpmaster.com">Phpmaster.com</a> and about Cloud in <a href="http://acloudyplace.com">A Cloudy Place</a>.</p>
            <h3>Philosophy</h3>
            <p>Though there are many flat-file blog engines, like Toto, Jekyll, etc. I was unable to find a decent one for PHP lovers. 
              As there are some good PHP Cloud platform services like PHP Fog and Pagodabox which offers free hosting in cloud, 
              having a flat-file blog engine written in PHP makes perfect sense. An important reason is it's performance. 
              Flat file blog engine will load much faster compared to database driven CMS websites. 
              Also it is highly secure and agile. So I decided not to wait for someone else to do it and here's mine - TexPress. My goal is to provide almost
              all the necessary features for a blogging engine with TextPress.
              Its not a big deal to write something like this. So if you are not comfortable with it, feel happy to fork it, 
              add features and let me know what you have done. I will always try to improve it and add more features.</p>
            <h3>Contact</h3>
            <p>You can contact me in twitter <a href="http://twitter.com/#!/shameerc">@shameerc</a>. Visit my personal blog <a href="http://shameerc.com">Shameerc.com</a></p>
          </div>
        </div>
      </div>

      <footer>
        <p>Powered by TextPress © <a href="http://shameerc.com" target="_blank">Shameer C </a>2012</p>
      </footer>
    </div> <!-- /container -->
  </body>
</html>
