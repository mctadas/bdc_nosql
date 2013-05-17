<?php

namespace Bb4w\Zend\Service\Twitter;

use Zend_Json;
use Zend_Feed;

/**
 * @package Kompro 
 */
class Trends extends \Zend_Service_Twitter_Search 
{

	/**
	 * Constructor
	 *
	 * @param  string $returnType
	 * @return void
	 */
	public function __construct($responseType = 'json') 
	{
		$this->setResponseType($responseType);
		$this->setUri("http://api.twitter.com");

		$this->setHeaders('Accept-Charset', 'ISO-8859-1,utf-8');
	}

	/**
	 * Performs a Twitter trends query.
	 */
	public function search(array $params = array()) 
	{
		$_query = array();

		foreach ($params as $key => $param) {
			switch ($key) {
				case 'date':
					if (preg_match("/^(\d{4})-(\d{2})-(\d{2})$/", $param, $parts)) {
						// a date of valid format may not exist. ex.: 2007-02-29 (2007 feb has only 28 days)
						if (checkdate($parts[2], $parts[3], $parts[1])) {
							$_query[$key] = $param;
						}
					}
					break;
			}
		}


		$response = $this->restGet('/1/trends/weekly.' . $this->_responseType, $_query);

		switch ($this->_responseType) {
			case 'json':
				return Zend_Json::decode($response->getBody());
				break;
			case 'atom':
				return Zend_Feed::importString($response->getBody());
				break;
		}

		return;
	}

}