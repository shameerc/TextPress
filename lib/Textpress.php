<?php
 /**
 * Textpress - PHP Flat file blog engine
 * Textpress is a flat file blog engine, built on top of Slim inspired from Toto. 
 * Now it have only a limited set of features and url options.
 * 
 * @author 		Shameer C <me@shameerc.com>
 * @copyright   2012 - Shameer C
 * @version 	Alpha
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
	public $markdown ;

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
		$this->loadFiles();
		$this->setRoutes();
	}

	/**
	* Load article files to an array and sort based on date
	*/
	public function loadFiles()
	{
		$dir = new DirectoryIterator($this->_articlePath);
		foreach($dir as $file){
			if($file->isFile()){
				$this->fileNames[] = $file->getFilename();
			}
		}
		rsort($this->fileNames);
	}

	/**
	* @return array Article file names
	*/
	public function getfileNames()
	{
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
	public function loadArticle($fileName,$isArticle=false)
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
						'url'=>$this->getUrl($meta['date'],$slug)
						);
		if($isArticle){
			$this->slim->view()->appendGlobalData($meta); 
		}
		return $this->viewData['article'] = $article;
	}

	/**
	* Loads all article
	*
	* @return array Articles
	*/
	public function loadArticles()
	{
		$articles = $this->getfileNames();
		foreach($articles as $article){
			$allArticles[] = $this->loadArticle($article);
		}
		return $this->viewData['articles'] =	$this->sortArticles($allArticles);
	}

	/**
	*
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
	*
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
		$articles = $this->loadArticles();
		if(is_null($date)){
			$archives = $articles;
		}
		else{
			foreach($articles as $article){
				if($date == $this->dateFormat($article['meta']['date'],$format))
					$archives[] = $article;
			}
		}
		return $this->viewData['archives'] = $archives;
	}

	/**
	* Helper function for date formatting
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
	* @param $path String File name
	* @return String Path to file or flase if file does not exists
	*/
	public function getFullPath($path)
	{
		if(in_array($path , $this->fileNames)){
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
		foreach ($this->_routes as $key => $value) {
			$self = $this; 
			$prefix = $self->slim->config('prefix');
			$this->slim->map($prefix . $value['route'],function() use($self,$key,$value){
				$args = func_get_args();
				if(isset($value['layout']) && !$value['layout']){
					$self->enableLayout = false;
				}
				else{
					$self->setLayout();
				}

				if($key == '__root__'){
					$self->loadArticles();
				}
				elseif($key== 'article'){
					$ext = $self->slim->config('file.extension');
					$self->loadArticle($self->getPath($args),true);
				}
				elseif($key=='archives'){
					$self->loadArchives($args);
				}
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
		$ext = $this->slim->config('file.extension');
		return implode('-',$params) . $ext;
	}

	/**
	* Creates url from a Date and Title
	* @param $date Date of article
	* @param $title Article title
	* 
	* @todo Extend this function for custom urls
	*/
	public function getUrl($date,$slug)
	{
		$date = new DateTime($date);
		$date = $date->format('Y-m-d');
		$dateSplit = explode('-', $date);
		$prefix = $this->slim->config('prefix');
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
	* @param $string String article title
	* @return String slug
	*/
	public function slugize($string)
	{
		$slug = strtolower(trim($string));
		$find = array(' ', '&', '\r\n', '\n', '+',',');
		$slug = str_replace ($find, '-', $slug);
		return $slug;
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
	* @return array view data
	*/
	public function viewData()
	{
		return $this->viewData;
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
	*/
	public function render($template){
		$this->slim->render($template,$this->viewData());
	}

	/**
	* Run slim
	*/
	public function run(){
		$this->slim->run();
	}
}