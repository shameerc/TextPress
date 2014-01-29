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
* View class for Textpress 
*
* @author       Shameer
* @since        1.0 
*/

class View extends \Slim\View
{
	/**
	* @var  String layout file
	*/
	public $layout = false;

	/**
	* @var Array Global array
	*/
	public $global = array();

	/**
	* Set layout file
	* @var String layout file
	*/
	public function setLayout($layout)
	{
		$this->layout = $layout;
	}

	/**
	* Append values to existing global values
	*@var array data
	*/
	public function appendGlobalData(Array $data)
	{
		$this->global = array_merge($this->global,$data);
	}


	/**
	* Render template
	* @var string $template Template to be rendered
	*/
	public function render($template = '', $data = null)
	{ 
		$template = is_string($template) ? $template . '.php' : null;
		if($template){
			$this->appendData(array('global' => $this->global));
			$content =  parent::render($template);
		}
		else{
			$content = '';
		}
		// make sure buffers flushed
		ob_end_flush(); 
		if(ob_get_length() !== false)
	    	ob_flush();
		ob_start();
		extract(array('content' => $content, 'global' => $this->global));
		if($this->layout){
			$layoutPath = $this->getTemplatesDirectory() . DIRECTORY_SEPARATOR . ltrim($this->layout, '/');
			if ( !is_readable($layoutPath) ) {
            	throw new \RuntimeException('View cannot render layout `' . $layoutPath );
        	}
	        require $layoutPath;
		}
		else{
			echo $content;
		} 
        return ob_get_clean();
	}	

}