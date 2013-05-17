<?php

namespace BDC\Zend\Service\Twitter;

use Zend_Json;
use Zend_Feed;

/**
 * @package Kompro 
 */
class Search extends \Zend_Service_Twitter_Search 
{

	/**
	 * Performs a Twitter search query.
	 *
	 * @throws Zend_Http_Client_Exception
	 */
	public function search($query, array $params = array()) 
	{

		$_query = array();

		$_query['q'] = $query;

		foreach ($params as $key => $param) {
			switch ($key) {
				case 'geocode':
				case 'lang':
				case 'max_id':
				case 'since_id':
					$_query[$key] = $param;
					break;
				case 'rpp':
					$_query[$key] = min(intval($param), 100);
					break;
				case 'page':
					$_query[$key] = intval($param);
					break;
				case 'show_user':
					$_query[$key] = 'true';
			}
		}

		$response = $this->restGet('/search.' . $this->_responseType, $_query);

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