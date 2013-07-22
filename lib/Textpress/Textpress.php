<?php
 /**
 * Textpress - PHP Flat file blog engine
 * Textpress is a flat file blog engine, built on top of Slim inspired from Toto. 
 * Now it have only a limited set of features and url options.
 * 
 * @author      Shameer C <me@shameerc.com>
 * @copyright   2012 - Shameer C
 * @version     1.0
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
 
namespace Textpress;
/**
* Textpress
* @author       Shameer
* @since        1.0 
*/
class Textpress
{
    
    /**
    * TextPress configuration
    *
    * @var array
    */
    public $config = array();

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
    * Base directory of current active theme
    *
    * @var string
    */
    public $themeBase;

    /**
    * Constructor
    * 
    * @param Slim $slim Object of slim
    */
    public function __construct(\Slim\Slim $slim, $config)
    {
        $this->slim = $slim;
        $this->setConfig($config);
        $this->init();
    }

    /**
    * Set TextPress configurations
    *
    * @param array $config Configuration array
    */
    public function setConfig($config)
    {
        $this->config = $config;
        $this->slim->config($config);
    }

    /**
    * Getter function to get config variable
    *
    * @var string $configVar Config variable
    * @return Configuration value
    */
    public function config($configVar)
    {
        return $this->config[$configVar];
    }

    /**
    * Initialize Textpress
    */
    public function init()
    {
        if(!is_dir($this->config('article.path'))){
            throw new Exception('Article location is invalid');
        }
        $this->markdown     = $this->config('markdown');
        $this->_articlePath = $this->config('article.path');
        if($this->markdown){
            require_once __DIR__ . '/../markdown.php';
        }
        $this->themeBase = $this->config('themes.path') . "/" . $this->config("active.theme");
        $this->slim->view()->setTemplatesDirectory($this->themeBase);
        $this->setViewConfig();
        $this->setRoutes();
        $self = $this;
    }

    /**
    * @return array Article file names
    */
    public function getfileNames()
    {
        if (empty($this->fileNames))
        {
            $iterator = new \DirectoryIterator($this->_articlePath);
            $files = new \RegexIterator($iterator,'/\\'.$this->config('file.extension').'$/'); 
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
    *                        merge meta data to global data
    * @return array 
    */
    public function loadArticle($fileName)
    {
        if(!($fullPath = $this->getFullPath($fileName))){
            return false;
        }
        $handle     = fopen($fullPath, 'r');
        $content    = stream_get_contents($handle);
        // hack for cross platform newline char issue. (by http://darklaunch.com/)
        $content    = str_replace("\r\n", "\n", $content);
        $content    = str_replace("\r", "\n", $content);
        // Don't allow out-of-control blank lines
        $content    = preg_replace("/\n{2,}/", "\n\n", $content);
        $sections   = explode("\n\n", $content);
        $meta       = json_decode(array_shift($sections),true);
        $contents   = implode("\n\n",$sections);
        if($this->markdown){ 
            $contents = Markdown($contents);
        }
        $slug = (array_key_exists('slug', $meta) && $meta['slug'] !='') 
                    ? $meta['slug']
                    : $this->slugize($meta['title']);
        $meta['category'] = $this->collectCategories($meta);
        $meta['tag'] = $this->collectTags($meta);
        $article    = array(
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
        return $this->viewData['article'] = $article;
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
            $prefix = $this->config('prefix');
            $url    = $this->getArticleUrl($article['meta']['date'],$slug);
            $allArticles[$url] = $article;
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
        $results    = array();
        foreach($articles as $article){
            $date = new \DateTime($article['meta']['date']);
            $timestamp = $date->getTimestamp();
            $timestamp = array_key_exists($timestamp, $results) ? $timestamp + 1 : $timestamp;
            $results[$timestamp] = $article;
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
    *
    * Filter list of articles based on the meta key-value
    * Mainly used in categories and tags, but you can extend for other custom 
    * meta keys also. Just add the routes and update routing function to include those routes
    * 
    * @param String $filter meta key to be searched in articles
    * @param string $value value to be mached with
    * @return array list of article matching the criteria
    */
    public function filterArticles($filter,$value){
        $articles = array();
        foreach ($this->allArticles as $article) {
            if(array_key_exists($filter, $article['meta']) && array_key_exists($value, $article['meta'][$filter]))
                $articles[] = $article;
        }
        return $this->viewData['articles'] = $articles;
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
        $format = is_null($format) ? $this->config('date.format') : $format;
        $date  = new \DateTime($date);
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
        $this->_routes = $this->config('routes');
        $self = $this; 
        $prefix = $self->slim->config('prefix');
        foreach ($this->_routes as $key => $value) {
            $this->slim->map($prefix . $value['route'],function() use($self,$key,$value){
                $args = func_get_args();
                $layout = isset($value['layout']) ? $value['layout'] : true;
                if(!$layout){
                    $self->enableLayout = false;
                }
                else{
                    $self->setLayout($layout);
                }

                $self->slim->view()->appendGlobalData(array("route" => $key));
                $template = $value['template'];
                //set view data for article  and archives routes
                switch ($key) {
                    case '__root__' :
                    case 'rss'      :
                    case 'atom'     :
                        $self->allArticles = array_slice($self->allArticles, 0, 10);
                        break;
                    case 'article'  :
                        $article = $self->setArticle($self->getPath($args));
                        $template = (isset($article['meta']['template']) && $article['meta']['template'] !="")
                                        ? $article['meta']['template']
                                        : $template;
                        break;
                    case 'archives' :
                        $self->loadArchives($args);
                        break;
                    case 'category' :
                    case 'tag'      :
                        $self->filterArticles($key,$args[0]);
                        break;
                }
                // render the template file
                $self->render($template);
            })->via('GET')
              ->name($key)
              ->conditions(
                isset($value['conditions']) ? $value['conditions']: array()
            );
        }
        // load all articles
        // This isn't necessary for route to an article though
        // will help to generate tag cloud/ category listing
        $self->loadArticles();
        // Register not found handler
        $this->slim->notFound(function () use ($self) {
            $self->slim->render('404');
        });
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
        $date = new \DateTime($date);
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
        $themeDir   = ltrim($this->themeBase, "./");
        $themeBase = $this->config('base.directory') . "/" . $themeDir;
        $data = array(
                'date.format' => $this->config('date.format'),
                'author.name' => $this->config('author.name'),
                'site.name' => $this->config('site.name'),
                'site.title' => $this->config('site.title'),
                'disqus.username' => $this->config('disqus.username'),
                'base.directory' => $this->config('base.directory'),
                'google.analytics' => $this->config('google.analytics'),
                'prefix' => $this->config('prefix'),
                'theme.base' => $themeBase
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
        $temp = array();
        if(array_key_exists('category', $meta) && $meta['category']){
            $categories = explode(',', $meta['category']);
            foreach ($categories as  $category) {
                $slug = $this->slugize($category);
                $temp[$slug] = trim($category);
            }
            $this->categories = array_merge($this->categories,$temp);
        }
        return $temp;
    }

    /**
    * Collect tags from all articles to build tag cloud
    * Each tag will be an object of Tag with name and count
    * Use $tag->name and $tag->count to get the name and number of occurances of each tag
    *
    * @param string $meta Article meta data
    * @return collection of Tag objects
    */
    public function collectTags($meta)
    {
        $temp = array();
        if(array_key_exists('tag', $meta) && $meta['tag']){
            $tags = explode(',', $meta['tag']);
            foreach ($tags as $tag) {
                $slug = $this->slugize($tag);
                if(isset($this->tags[$slug])){
                    $temp[$slug] = $this->tags[$slug];
                    $temp[$slug]->count++;
                }
                else{
                    $temp[$slug] = new Tag(trim($tag));
                }
            }
            $this->tags = array_merge($this->tags,$temp);
        }
        return $temp;
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
    public function setLayout($layout)
    {
        $layoutFile = is_bool($layout) ? $this->slim->config('layout.file') . '.php'
                                       : $layout . ".php";
        $this->slim->view()->setLayout($layoutFile);
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
class Tag
{
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

    /**
    * Constructor 
    * 
    * @param string $name  Tag name
    * @param int $count  Number of occurances of a tag
    */
    public function __construct($name,$count=1)
    {
        $this->name = $name;
        $this->count = $count;
    }
}