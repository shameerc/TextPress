<?php

class Article
{
	public function __construct($meta, $content)
	{
		
	}

	public function getContent()
	{
		return $this->content;
	}

	public function getMeta($key = null)
	{	
		if(!is_null($key))
			return $this->meta;
		return 
	}

	public function setContent($content)
	{
		$this->content = $content;
	}

	public function setMeta($meta)
	{
		$this->meta = $meta;
	}


}