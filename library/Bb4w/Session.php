<?php

namespace Bb4w;

use \Zend_Session_Namespace;

/**
 * @package Kompro 
 */
class Session extends Zend_Session_Namespace 
{
	/**
	 * @param string $name
	 * @return string 
	 */
	function pop($name) 
	{
		$ret_val = $this->$name;
		unset($this->$name);

		return $ret_val;
	}

}