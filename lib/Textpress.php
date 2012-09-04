<?php
 /**
 * Textpress - PHP Flat file blog engine
 * Textpress is a flat file blog engine, built on top of Slim inspired from Toto. 
 * Now it have only a limited set of features and url options.
 * 
 * @author 		Shameer C <me@shameerc.com>
 * @copyright   2012 - Shameer C
 * @version 	Beta
 * @todo        Add some error and exception handling
 *
 * MIT LICENSE
 *
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the
 * "Software"), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish,
 * distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to
 * the following conditions:
 *
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
 * LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
 * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
 * WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

/**
* Textpress
* @author 		Shameer
* @since 		Alpha 
*/
class Textpress
{
	/**
	* Array of file names
	*
	* @var array
	*/
	public $fileNames = array();
	
	/**
	* Article location
	*
	* @var string 
	*/
	private $_articlePath;

	/**
	* Do we need markdown parser?
	* 
	* @var bool
	*/
	public $markdown;

	/**
	* Articles
	*
	* @var array 
	*/
	public $allArticles = array();
	
	/**
	* View data
	*
	* @var array
	*/
	public $viewData = array();

	/**
	* Enable or disable layout
	*
	* @var bool
	*/
	public $enableLayout = true;

	/**
	* Slim object
	*
	* @var Slim
	*/
	public $slim;

	/**
	* Array of all categories in the blog
	*
	* @var array
	*/
	public $categories = array();

	/**
	* Array of all tags in the blog
	* A tag is an object of class Tag with name and count attributes
	*
	* @var array
	*/
	public $tags = array();

	/**
	* Constructor
	* 
	* @param Slim $slim Object of slim
	*/
	public function __construct(Slim $slim)
	{
		$this->slim = $slim;
		$this->init();
	}

	/**
	* Initialize Textpress
	*/
	public function init()
	{
		
		if(!is_dir($this->slim->config('article.path'))){
			throw new Exception('Article location is invalid');
		}
		$this->markdown 	= $this->slim->config('markdown');
		$this->_articlePath = $this->slim->config('article.path');
		if($this->markdown){
			require_once __DIR__ . '/markdown.php';
		}
		$this->setViewConfig();
		$this->setRoutes();
		$self = $this;
		// work around for not found
		// consider revising it
		try{
			$this->slim->notFound(function() use($self){
				header("HTTP/1.0 404 Not Found");
				$self->setLayout();
				$self->slim->render('404');				
			});
		}
		catch(Exception $e){}
	}

	/**
	* @return array Article file names
	*/
	public function getfileNames()
	{
		if (empty($this->fileNames))
		{
			$iterator = new DirectoryIterator($this->_articlePath);
			$files = new RegexIterator($iterator,'/\\'.$this->slim->config('file.extension').'$/'); 
			foreach($files as $file){
				if($file->isFile()){
					$this->fileNames[] = $file->getFilename();
				}
			}
			rsort($this->fileNames);
		}
		return $this->fileNames;
	}

	/**
	* Loads an article
	*
	* @param string $fileName Name of article file
	* @param bool $isArticle For requests to article it should 
	*						 merge meta data to global data
	* @return array 
	*/
	public function loadArticle($fileName)
	{
		if(!($fullPath = $this->getFullPath($fileName))){
			return false;
		}
		$handle 	= fopen($fullPath, 'r');
		$content 	= stream_get_contents($handle);
		$sections 	= explode("\n\n", $content);
		$meta 		= json_decode(array_shift($sections),true);
		$contents 	= implode("\n\n",$sections);
		if($this->markdown){ 
			$contents = Markdown($contents);
		}
		$slug = (array_key_exists('slug', $meta) && $meta['slug'] !='') 
					? $meta['slug']
					: $this->slugize($meta['title']);
		$article 	= array(
						'meta' => $meta, 
						'content' => $contents,
						'url'=>$this->getArticleUrl($meta['date'],$slug)
						);
		return $this->viewData['article'] = $article;
	}

	/**
	* Sets view data for an article route.
	*
	* @param string $url URL without prefix
	*/
	public function setArticle($url)
	{
		if(! isset( $this->allArticles[$url] )){
			$this->notFound();
		}
		$article = $this->allArticles[$url];
		$this->slim->view()->appendGlobalData($article['meta']); 
		$this->viewData['article'] = $article;
	}

	/**
	* Loads all article
	*
	* @return array Articles
	*/
	public function loadArticles($numbers = -1)
	{
		$filenames = $this->getfileNames();
		$i = 0;
		$allArticles = array();
		foreach($filenames as $filename){
			if ($numbers > -1 && $i == $numbers) {
				break;
			}
			$article = $this->loadArticle($filename);
			$slug = isset($article['meta']['slug']) 
						? $article['meta']['slug']
						: $this->slugize($article['meta']['title']);
			$prefix = $this->slim->config('prefix');
			$url  	= $this->getArticleUrl($article['meta']['date'],$slug);
			$allArticles[$url] = $article;
			$this->collectCategories($article['meta']);
			$this->collectTags($article['meta']);
			$i++;
		}
		$this->allArticles = $allArticles;
		$this->slim->view()->appendGlobalData(
				array(
					"categories" => $this->categories,
					"tags" => $this->tags
					)
			);
		return $this->viewData['articles'] = $this->sortArticles($allArticles);
	}

	/**
	* Sort articles based on date
	*
	* @param array $articles Array of articles
	*/
	public function sortArticles($articles)
	{
		$results	= array();
		foreach($articles as $article){
			$date = $this->dateFormat($article['meta']['date'],'Y-m-d');
			$results[$date] = $article;
		}
		krsort($results);
		return $results;
	}

	/**
	* Load archives based on current route
	*
	* @param array $route Route params
	*/
	public function loadArchives($route)
	{
		switch(count($route)){
			case 0 :
				$this->setArchives();
				break;
			case 1 :
				$this->setArchives(implode('-',$route),'Y');
				break;
			case 2 :
				$this->setArchives(implode('-',$route),'Y-m');
				break;
			case 3 :
				$this->setArchives(implode('-',$route),'Y-m-d');
				break;
		}
		return $this->viewData['archives'];
	}

	/**
	* Sets archives to be shown to viewData array.
	*
	* @param  Date $date from arguments passed via rout
	* @param  String $format Date format
	* @return array archives 
	*/
	public function setArchives($date=null,$format='')
	{
		$this->viewData['archives']  = array();
		$archives = array();
		if(is_null($date)){
			$archives = $this->allArticles;
		}
		else{
			foreach($this->allArticles as $article){
				if($date == $this->dateFormat($article['meta']['date'],$format))
					$archives[] = $article;
			}
		}
		return $this->viewData['archives'] = $archives;
	}

	/**
	* Custom 404 handler
	* Function can be called for handling 404 errors
	*/
	public function notFound()
	{
		$self = $this;
		$this->slim->notFound(function() use($self){
			$self->slim->render('404');
		});
	}

	/**
	* Helper function for date formatting
	*
	* @param $date Input date
	* @param $format Date format
	*/
	public function dateFormat($date,$format=null)
	{
		$format = is_null($format) ? $this->slim->config('date.format') : $format;
		$date  = new DateTime($date);
		return $date->format($format);	
	}

	/**
	* Function to get full path of article file from its filename
	*
	* @param $path String File name
	* @return String Path to file or false if file does not exists
	*/
	public function getFullPath($path)
	{
		if(in_array($path , $this->getFileNames())){
			return $this->_articlePath . '/' . $path ;
		}
		return false;
	}

	/**
	* Set Application routes based on the routes specified in config
	* Also sets layout file if it's enabled for that specific route
	*/
	public function setRoutes()
	{
		$this->_routes = $this->slim->config('routes');
		$self = $this; 
		$prefix = $self->slim->config('prefix');
		foreach ($this->_routes as $key => $value) {
			$this->slim->map($prefix . $value['route'],function() use($self,$key,$value){
				$args = func_get_args();
				if(isset($value['layout']) && !$value['layout']){
					$self->enableLayout = false;
				}
				else{
					$self->setLayout();
				}
				
				// load all articles
				// This isn't necessary for route to an article though
				// will help to generate tag cloud/ category listing
				$self->loadArticles();

				//set view data for article  and archives routes
				if($key == '__root__' || $key == 'rss' || $key == 'atom'){
					$self->allArticles = array_slice($self->allArticles, 0, 10);
				}
				elseif($key== 'article'){
					$self->setArticle($self->getPath($args));
				}
				elseif($key =='archives'){
					$self->loadArchives($args);
				}
				// render the template file
				$self->render($value['template']);
			})->via('GET')
			  ->name($key)
			  ->conditions(
				isset($value['conditions']) ? $value['conditions']: array()
			);
		}
	}

	/**
	* Constructs file name from route params
	* @param $params Array route parameters
	* @return String file name 
	*/
	public function getPath($params)
	{
		$slug = array_pop($params);
		$date = implode('-', $params);
		return $this->getArticleUrl($date,$slug);
	}

	/**
	* Creates url from a Date and Title
	*
	* @param string $date Date of article
	* @param string $slug Article title
	*/
	public function getArticleUrl($date,$slug)
	{
		$date = new DateTime($date);
		$date = $date->format('Y-m-d');
		$dateSplit = explode('-', $date);
		return $this->slim->urlFor(
					 			'article',
								array(
									'year'=>$dateSplit[0],
									'month'=>$dateSplit[1],
									'date' => $dateSplit[2],
									'article'=>$slug
								)
							) ;
	}

	/**
	* Slugize an article title
	* @param string  $string  article title
	* @return string URL slug corresponding to the string
	*/
	public function slugize($str)
	{
		$str = strtolower(trim($str));
 		
 		$chars = array("ä", "ö", "ü", "ß");
   		$replacements = array("ae", "oe", "ue", "ss");
		$str = str_replace($chars, $replacements, $str);

		$pattern = array("/(é|è|ë|ê)/", "/(ó|ò|ö|ô)/", "/(ú|ù|ü|û)/");
   		$replacements = array("e", "o", "u");
		$str = preg_replace($pattern, $replacements, $str);

		$pattern = array(":", "!", "?", ".", "/", "'");
		$str = str_replace($pattern, "", $str);
		
		$pattern = array("/[^a-z0-9-]/", "/-+/");
		$str = preg_replace($pattern, "-", $str);
		
		return $str;
    }

	/**
	* Set config values to View
	* @todo make it comfortable
	*/
	public function setViewConfig()
	{
		$data = array(
				'date.format' => $this->slim->config('date.format'),
				'author.name' => $this->slim->config('author.name'),
				'site.name' => $this->slim->config('site.name'),
				'site.title' => $this->slim->config('site.title'),
				'disqus.username' => $this->slim->config('disqus.username'),
				'base.directory' => $this->slim->config('base.directory'),
				'google.analytics' => $this->slim->config('google.analytics'),
				'prefix' => $this->slim->config('prefix'),
			);
		$this->slim->view()->appendGlobalData($data);
	}

	/**
	* Collects categories from all articles
	* 
	* @param string $meta Article meta data
	* @return array of distinct categories
	*/
	public function collectCategories($meta)
	{
		if(array_key_exists('category', $meta) && $meta['category']){
			$categories = explode(',', trim($meta['category'],','));
			$this->categories = array_unique(array_merge($this->categories,$categories));
		}
		return $this->categories;
	}

	/**
	* Collect tags from all articles to build tag cloud
	* Each tag will be an object of Tag with name and count
	* Use $tag->name and $tag->count to get the name and number of occurances of each tag
	*
	* @param string $meta Article meta data
	* @return collection of Tag objects
	*/
	public function collectTags($meta){
		if(array_key_exists('tag', $meta) && $meta['tag']){
			$tags = explode(',', trim($meta['tag'],','));
			foreach ($tags as $tag) {
				if(isset($this->tags[$tag])){
					$this->tags[$tag]->count++;
				}
				else{
					$this->tags[$tag] = new Tag($tag);
				}
			}
		}
		return $this->tags;
	}

	/**
	* @return array view data
	*/
	public function getViewData()
	{
		return isset($this->viewData)
					? $this->viewData
					: array();
	}

	/**
	* Set layout file
	*/
	public function setLayout()
	{
		$this->slim->view()->setLayout($this->slim->config('layout.file') . '.php');
	}

	/**
	* Render template
	*
	* @param string $template template file to be rendered
	*/
	public function render($template)
	{
		$this->slim->render($template,$this->getViewData());
	}

	/**
	* Run slim
	*/
	public function run()
	{
		$this->slim->run();
	}
}

/**
* Represents a Tag with name and count 
*/
class Tag{
	/**
	* tag name 
	*
	* @var string
	*/
	public $name;

	/**
	* number of occurances of a tag 
	*
	* @var int
	*/
	public $count;
	public function __construct($name,$count=1){
		$this->name = $name;
		$this->count = $count;
	}
}