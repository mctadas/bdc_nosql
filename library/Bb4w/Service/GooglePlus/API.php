<?php

namespace Bb4w\Service\GooglePlus;

/**
 * @package Kompro 
 */
class API 
{

	/**
	 * API URL
	 */
	public $apiUrl = 'https://www.googleapis.com/plus/v1/';

	/**
	 * API key to pass with every request. Can be generated in GooglePlus->APP->API Access section.
	 */
	public $apiKey = 'AIzaSyAOy0oCclw0KrWMIC9UcDs5FAdqWSxS8lU';

	/**
	 * @var Zend_Http_Client
	 */
	protected $client;

	/**
	 * Constructor.
	 */
	public function __construct() 
	{
		$config = array(
			'adapter' => 'Zend_Http_Client_Adapter_Curl',
			'curloptions' => array(
				CURLOPT_SSL_VERIFYHOST => 0,
				CURLOPT_SSL_VERIFYPEER => 0,
				CURLOPT_FOLLOWLOCATION => true
			),
		);

		$this->client = new \Zend_Http_Client($this->apiUrl, $config);
	}

	/**
	 * GooglePlus public activities search method.
	 * 
	 * @param array $params
	 * @return array
	 */
	public function activities(array $params = array()) 
	{
		$this->client->setUri($this->apiUrl . 'activities');
		$this->client->setMethod(\Zend_Http_Client::GET);
		$this->client->setParameterGet('key', $this->apiKey);

		foreach ($params as $key => $param) {

			switch ($key) {

				case 'max_results':

					$this->client->setParameterGet('maxResults', $param);

					break;

				case 'language':
				case 'query':

					$this->client->setParameterGet($key, $param);

					break;
			}
		}

		try {

			$response = $this->client->request();
		} catch (\Zend_Http_Client_Adapter_Exception $ex) {
			return array('error' => $ex->getMessage());
		} catch (\Zend_Http_Client_Exception $ex) {
			return array('error' => $ex->getMessage());
		}

		try {

			return \Zend_Json::decode($response->getBody());
		} catch (\Zend_Json_Exception $ex) {
			return array('error' => $ex->getMessage());
		}
	}

	/**
	 * GooglePlus people search method.
	 * 
	 * @param array $params
	 * @return array
	 */
	public function people(array $params = array()) 
	{
		$this->client->setUri($this->apiUrl . 'people');
		$this->client->setMethod(\Zend_Http_Client::GET);
		$this->client->setParameterGet('key', $this->apiKey);

		foreach ($params as $key => $param) {

			switch ($key) {

				case 'max_results':

					$this->client->setParameterGet('maxResults', $param);

					break;

				case 'language':
				case 'query':

					$this->client->setParameterGet($key, $param);

					break;
			}
		}

		try {

			$response = $this->client->request();
		} catch (\Zend_Http_Client_Adapter_Exception $ex) {
			return array('error' => $ex->getMessage());
		} catch (\Zend_Http_Client_Exception $ex) {
			return array('error' => $ex->getMessage());
		}

		try {

			return \Zend_Json::decode($response->getBody());
		} catch (\Zend_Json_Exception $ex) {
			return array('error' => $ex->getMessage());
		}
	}

	/**
	 * GooglePlus activity comments retrievation method.
	 * 
	 * @param array $params
	 * @throws \InvalidArgumentException
	 * @return array
	 */
	public function comments(array $params = array()) 
	{
		if (!isset($params['activity_id'])) {

			throw new \InvalidArgumentException('missing activity_id');
		}

		try {

			$this->client->setUri($this->apiUrl . 'activities/' . $params['activity_id'] . '/comments');
		} catch (\Zend_Uri_exception $ex) {

			return array('error' => $ex->getMessage());
		}

		$this->client->setMethod(\Zend_Http_Client::GET);
		$this->client->setParameterGet('key', $this->apiKey);

		foreach ($params as $key => $param) {

			switch ($key) {

				case 'max_results':

					$this->client->setParameterGet('maxResults', $param);

					break;

				case 'order_by':

					$this->client->setParameterGet('orderBy', $param);

					break;
			}
		}

		try {

			$response = $this->client->request();
		} catch (\Zend_Http_Client_Adapter_Exception $ex) {
			return array('error' => $ex->getMessage());
		} catch (\Zend_Http_Client_Exception $ex) {
			return array('error' => $ex->getMessage());
		}

		try {

			return \Zend_Json::decode($response->getBody());
		} catch (\Zend_Json_Exception $ex) {
			return array('error' => $ex->getMessage());
		}
	}

}
