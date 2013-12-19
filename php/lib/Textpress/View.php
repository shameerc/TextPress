<?php
/**
* View class for Textpress
*/

namespace Textpress;

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
	* @var string Template to be rendered (optional)
	*/
	public function render($template = '')
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
			$layoutPath = $this->getTemplatesDirectory() . '/' . ltrim($this->layout, '/');
			if ( !is_readable($layoutPath) ) {
            	throw new RuntimeException('View cannot render layout `' . $layoutPath );
        	}
	        require $layoutPath;
		}
		else{
			echo $content;
		} 
        return ob_get_clean();
	}	

}