<?php
return array(
	'routes' => array(
			// Root path
			'__root__'=> array(
					'route' => '/',
					'template'=>'index',
				),
			'article' => array(
					'route' => '/:year/:month/:date/:article(/)',
					'template'=>'article',
					'conditions' => array('year' => '(19|20)\d\d','month'=>'([1-9]|[01][0-9])','date'=>'([1-9]|[0-3][0-9])')
				),
			'archives' => array(
					'route' => '/archives(/:year(/:month(/:date)))',
					'template' => 'archives',
					'conditions' => array('year' => '(19|20)\d\d','month'=>'([1-9]|[01][0-2])')
				),
			'about' => array(
					'route' => '/about',
					'template' => 'about',
					'layout' => false
				)
		),
	'date.format' => 'd M, Y',
	'author.name' => 'Author name',
	'site.title' => 'TextPress - PHP Flat-file blog engine',
	'article.path'=> './articles',
	'templates.path' => './templates',
	'layout.file' => 'layout',
	'file.extension' => '.txt',
	'disqus.username' => false
);