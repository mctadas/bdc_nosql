<?php

namespace Bb4w\Service\Facebook;

/**
 * @package Kompro 
 */
class Graph 
{

	/**
	 * Facebook Graph URL
	 */
	public $graphUri = 'https://graph.facebook.com/';
//    const GRAPH_URI = 'https://graph.facebook.com/';

	/**
	 * Facebook APP Id
	 */
	public $appId = '249559641815961';
//    const APP_ID = '249559641815961';

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

		$this->client = new \Zend_Http_Client($this->graphUri, $config);
	}

	/**
	 * Facebook public posts search method.
	 * 
	 * @param array $params
	 * @return array
	 */
	public function search(array $params = array()) 
	{
		$this->client->setUri($this->graphUri . 'search');
		$this->client->setMethod(\Zend_Http_Client::GET);
		$this->client->setParameterGet('type', 'post');

		// buna atveju, kai iconv apsivemia jei locale nenustatyta, tai nustatau siam atvejui
		setlocale(LC_ALL, 'lt_LT');

		foreach ($params as $key => $param) {

			switch ($key) {

				case 'query':

					$this->client->setParameterGet('q', iconv('UTF-8', 'ASCII//TRANSLIT', $param));

					break;

				case 'since':

					$this->client->setParameterGet($key, $param);

					break;
			}
		}

		$response = $this->client->request();

		return \Zend_Json::decode($response->getBody());
	}

	/**
	 * Facebook post a new message method.
	 * 
	 * @param array $params - requires page_id, access_token and a message in order to work.
	 * @return array
	 */
	public function postMessage(array $params = array()) 
	{
		$this->client->setUri($this->graphUri . ( isset($params['page_id']) ? $params['page_id'] : 'me' ) . '/feed');
		$this->client->setMethod(\Zend_Http_Client::POST);

		foreach ($params as $key => $param) {

			switch ($key) {

				case 'access_token':
				case 'message':
				case 'picture':
				case 'link':
				case 'name':
				case 'caption':
				case 'description':

					$this->client->setParameterPost($key, $param);

					break;
			}
		}

		$response = $this->client->request();

		return \Zend_Json::decode($response->getBody());
	}

	/**
	 * Facebook create a new event method.
	 * 
	 * @param array $params - requires page_id, access_token, start_time, end_time and a name in order to work.
	 * @return array
	 */
	public function postEvent(array $params = array()) 
	{
		$this->client->setUri($this->graphUri . ( isset($params['owner']) ? $params['owner'] : 'me' ) . '/events');
		$this->client->setMethod(\Zend_Http_Client::POST);

		foreach ($params as $key => $param) {

			switch ($key) {

				case 'access_token':
				case 'start_time':
				case 'end_time':
				case 'location':
				case 'name':
				case 'privacy_type':
				case 'description':

					$this->client->setParameterPost($key, $param);

					break;

				case 'owner':

					$this->client->setParameterPost($key, $param);
					$this->client->setParameterPost('page_id', $param);

					break;
			}
		}

		$response = $this->client->request();

		return \Zend_Json::decode($response->getBody());
	}

}