<?php
return array(
	'date.format' => 'd M, Y',   // Date format to be used in article page (not for routes)   
	'author.name' => 'Author name', // Global author name 
	'site.name'  => 'TextPress',   // Site name (Global)
	'site.title' => 'PHP Flat-file blog engine',  // Site default title (Global)
	'article.path'=> './articles',      // Path to articles
	'templates.path' => './templates',  // Path to templates
	'layout.file' => 'layout',    // Site layout file
	'file.extension' => '.txt',   // file extension of articles
	'disqus.username' => '',   // Your disqus username or false (Global)
	'markdown'		=> true, //Enable of disable markdown parsing. 
	'base.directory'  => '',
	'prefix' => '',   // prefix to be added with all URLs. eg : '/blog'
	'google.analytics' => false, // Google analytics code. set false to disable
	// Define routes
	'routes' => array(
			// Site root
			'__root__'=> array(
					'route' => '/',
					'template'=>'index',
					'layout' => true
				),
			'article' => array(
					'route' => '/:year/:month/:date/:article',
					'template'=>'article',
					'conditions' => array(
										 'year' => '(19|20)\d\d'
										,'month'=>'([1-9]|[01][0-9])'
										,'date'=>'([1-9]|[0-3][0-9])'
										)
				),
			'archives' => array(
					'route' => '/archives(/:year(/:month(/:date)))',
					'template' => 'archives',
					'conditions' => array(
										'year' => '(19|20)\d\d',
										'month'=>'([1-9]|[01][0-2])'
										)
				),
			'about' => array(
					'route' => '/about',
					'template' => 'about',
					'layout' => false
				),
			'rss' => array(
					'route' => '/feed/rss(.xml)',
					'template' => 'rss',
					'layout' => false,
				),
			'atom' => array(	
					'route' => '/feed(/atom(.xml))',
					'template' => 'atom',
					'layout' => false,
				)
		),
);
