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
* Article 
* This class encapsulates article and it's meta data.
*
* @author       Shameer
* @since        1.0 
*/

class Article
{

    /**
    *
    * @var string Article content as Text/HTML
    */
    public $content;

    /**
    *
    * @var array article meta data
    */
    public $meta = array();

    /**
    *
    * Constructor
    * @param array $meta article meta data
    * @param string $content article in text or HTML
    */
    public function __construct($meta, $content)
    {
        $this->setMeta($meta);
        $this->setContent($content);
    }

    /**
    * 
    * Set article content
    * @param string $content article in text or HTML
    */
    public function setContent($content)
    {
        $this->content = $content;
    }

    /**
    *
    * Set article meta data
    * @param array $meta article meta data
    */
    public function setMeta($meta)
    {
        $this->meta = $meta;
    }

    /**
    * 
    * Get article content
    * @return string  article content
    */
    public function getContent()
    {
        return $this->content;
    }

    /**
    *
    * Get article meta data
    * @param string $key 
    * @return string value if $key is given or meta data array
    */
    public function getMeta($key = null)
    {   
        if ($key === null)
            return $this->meta;
        return isset($this->meta[$key])
            ? $this->meta[$key]
            : false ;
    }

    /**
    *
    * Get article title
    * @return string title of article
    */
    public function getTitle()
    {
        return $this->getMeta('title');
    }

    /**
    *
    * Get summary of the article
    * @param int $length length of the summary text
    * @return string summary text
    */
    public function getSummary($length = 250)
    {
        return preg_replace('/\s+?(\S+)?$/', '', substr(strip_tags($this->getContent()), 0, $length));
    }

    /**
    * Get a path to the article
    * 
    * @return string path to article
    */
    public function getPath()
    {
        return $this->getMeta('path');
    }

    /**
    *
    * Get article url
    * @return string article url
    */
    public function getUrl()
    {
        return $this->getMeta('url');
    }

    /**
    *
    * Get published date of article, in the given format
    * @param string $format required date format
    * @return string Date in the given format
    */
    public function getDate($format = false)
    {
        if ($format && $this->getMeta('date')) {
            $date = new \DateTime($this->getMeta('date'));
            return $date->format($format);
        }
        return $this->getMeta('date');
    }

    /**
    *
    * Get name of article author
    * @return string author name
    */
    public function getAuthor()
    {
        return $this->getMeta('author');
    }

    /**
    *
    * Get tags of the article
    * @return array tags
    */
    public function getTags()
    {
        return $this->getMeta('tag');
    }

    /**
    *
    * Get categories
    * @return array categories
    */
    public function getCategories()
    {
        return $this->getMeta('category');
    }

}