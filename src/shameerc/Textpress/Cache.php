<?php
 /**
 * Textpress - PHP Flat file blog engine
 * Textpress is a flat file blog engine, built on top of Slim inspired from Toto. 
 * Now it have only a limited set of features and url options.
 * 
 * @author      Shameer C <me@shameerc.com>
 * @copyright   2012 - Shameer C
 * @version     2.0.0
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
class Cache extends \Slim\Middleware
{

    /**
    * Location to save cached web pages
    *
    * @var string $path
    */
    public $path = './cache';

    /**
    * Cache lifetime
    *
    * @var int $duration duration in hours
    */
    public $duration = null;

    /**
    * Constructor
    *
    * @param array $config Config array
    * Eg:- $config = array('path' => './tmp', 'duration' => 1);
    */
    public function __construct( $config=array() )
    {
        if( isset($config['path']) ) {
            $this->path = $config['path'];
        }
        // Throw exception if cache directory is not writable/does not exist.
        if (!is_writable($this->path)) {
            throw new CacheException("Cache directory is not writable", 1);
        }
        if(isset($config['duration'])){
            $this->duration = $config['duration'];
        }
    }

    /**
    * SLim middleware call()
    *
    */
    public function call()
    {
        $app = $this->app;
        $path =  $app->request->getPath();
        $key = $this->clean(md5($path));
        if ($this->isCached( $key )) {
            // Cache hit
            $app->response->setBody($this->get( $key ));
            return;
        }
        else {
            // Cache miss, proceed to next middleware
            $this->next->call();
            $body = $app->response->getBody();
            $this->set($key, $body);
        }
        return;
    }

    /**
    * Cache web page using the given key
    *
    * @param string $key Cache file name
    * @param string $value Content
    */
    public function set($key, $value)
    {
        return file_put_contents($this->path .'/' . $key, $value);
    }

    /**
    * Get cached page if exists, or return false
    *
    * @param string $key Cache key
    */
    public function get($key)
    {
        if (!$this->isCached($key)) {
            return false;
        }
        return file_get_contents($this->path . '/' . $key);
    }

    /**
    * Check if the given key is cached
    *
    * @param string $key Key to be checked
    */
    public function isCached($key)
    {
        if (!is_readable($this->path . '/' . $key)) {
            return false;
        }
        if(!is_null($this->duration)) {
            $now = time();
            $expires = $this->duration * 60 * 60;
            $fileTime = filemtime($this->path . '/' . $key);
            if (($now - $expires) < $fileTime) {
                return false;
            }
        }
        return true;
    }

    /**
    * Clean string to make safe file names
    *
    * @param string $filename File name to be cleaned
    */
    function clean($filename) 
    {
        return preg_replace('/[^0-9a-z\.\_\-]/i','', strtolower($filename));
    }
}

class CacheException extends \Exception{}