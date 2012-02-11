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
	* @var array of File names
	*/
	public $fileNames = array();
	
	/**
	* @var string Article location
	*/
	private $_articlePath;

	/**
	* @var bool if need a markdown parser or not
	*/
	public $markdown ;

	/**
	* @var Array  Articles
	*/
	public $allArticles = array();
	
	/**
	* @var Array View data
	*/
	public $viewData = array();

	/**
	* @var bool Enable or disable layout
	*/
	public $enableLayout = true;

	/**
	* @var Slim
	*/
	public $slim;

	/**
	* Constructor
	* 
	* @param $slim Object of slim
	* @param $markdown bool 
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
	* @param $fileName Name of article file
	* @param $isArticle bool For requests to article it should 
	*						 merge meta data to global data
	* @return Article 
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
		$article 	= array(
						'meta' => $meta, 
						'content' => $contents,
						'url'=>$this->getUrl($meta['date'],$meta['title'])
						);
		if($isArticle){
			$this->slim->view()->appendGlobalData($meta); 
		}
		return $this->viewData['article'] = $article;
	}

	/**
	* Loads all article
	* @return array Articles
	*/
	public function loadArticles()
	{
		$articles = $this->getfileNames();
		foreach($articles as $article){
			$allArticles[] = $this->loadArticle($article);
		}
		return $this->viewData['articles'] =	$allArticles;
	}

	/**
	* Load archives based on current route
	* @param @route array Route params
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
	* @param  $date Date from arguments passed via rout
	* @param  $format String Date format
	* @return Array archives 
	*/
	function setArchives($date=null,$format='')
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
	function dateFormat($date,$format=null){
		$format = is_null($format) ? $this->slim->config('date.format') : $format;
		$date  = new DateTime($date);
		return $date->format($format);	
	}

	/**
	* Function to get full path of article file from its filename
	* @param $path String File name
	* @return String Path to file or flase if file does not exists
	*/
	function getFullPath($path){
		if(in_array($path , $this->fileNames)){
			return $this->_articlePath . '/' . $path ;
		}
		return false;
	}

	/**
	* Set Application routes based on the routes specified in config
	* Also sets layout file if it's enabled for that specific route
	*/
	function setRoutes()
	{
		$this->_routes = $this->slim->config('routes');
		foreach ($this->_routes as $key => $value) {
			$self = $this; 
			$this->slim->map($value['route'],function() use($self,$key,$value){
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
	function getPath($params)
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
	function getUrl($date,$title)
	{
		$slug = strtolower(trim($title));
		$find = array(' ', '&', '\r\n', '\n', '+',',');
		$slug = str_replace ($find, '-', $slug);
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
	* Set config values to View
	* @todo make it comfortable
	*/
	function setViewConfig()
	{
		$data = array(
				'date.format' => $this->slim->config('date.format'),
				'author.name' => $this->slim->config('author.name'),
				'site.name' => $this->slim->config('site.name'),
				'site.title' => $this->slim->config('site.title'),
				'disqus.username' => $this->slim->config('disqus.username'),
				'base.directory' => $this->slim->config('base.directory')
			);
		$this->slim->view()->appendGlobalData($data);
	}

	/**
	* Returns array of view data
	*/
	public function viewData()
	{
		return $this->viewData;
	}

	/**
	* Set layout file
	*/
	function setLayout()
	{
		$this->slim->view()->setLayout($this->slim->config('layout.file') . '.php');
	}

	/**
	* Render template
	*/
	function render($template){
		$this->slim->render($template,$this->viewData());
	}

	/**
	* Run slim
	*/
	public function run(){
		$this->slim->run();
	}
}