<?php

namespace BDC\Zend\Service\Twitter;

use Zend_Json;

/**
 * @package Kompro 
 */
class Tweet extends \Zend_Service_Twitter 
{

	/**
	 * Post a tweet
	 * 
	 * @param string $consumerKey
	 * @param string $consumerSecret
	 * @param array $params
	 * @return array 
	 */
	public static function post($consumerKey, $consumerSecret, $params) 
	{
		$token = new \Zend_Oauth_Token_Access;
		$token->setParams(array(
			'oauth_token' => $params['access_token'],
			'oauth_token_secret' => $params['access_token_secret']
		));

		$twitter = new self(array(
					'consumerKey' => $consumerKey,
					'consumerSecret' => $consumerSecret,
					'accessToken' => $token
				));

		$result = true;

		try {
			$response = $twitter->statusUpdate($params['status']);
		} catch (\Zend_Service_Twitter_Exception $ex) {
			return array('error' => $ex->getMessage());
		}

		return Zend_Json::decode($response->getBody());
	}

	/**
	 * Update user's current status
	 *
	 * @param  string $status
	 * @param  int $in_reply_to_status_id
	 * @return Zend_Rest_Client_Result
	 * @throws Zend_Http_Client_Exception if HTTP request fails or times out
	 * @throws Zend_Service_Twitter_Exception if message is too short or too long
	 */
	public function statusUpdate($status, $inReplyToStatusId = null) 
	{
		$this->_init();
		$path = '/1/statuses/update.json';
		$len = iconv_strlen(htmlspecialchars($status, ENT_QUOTES, 'UTF-8'), 'UTF-8');
		if ($len > self::STATUS_MAX_CHARACTERS) {
			include_once 'Zend/Service/Twitter/Exception.php';
			throw new Zend_Service_Twitter_Exception(
					'Status must be no more than '
					. self::STATUS_MAX_CHARACTERS
					. ' characters in length'
			);
		} elseif (0 == $len) {
			include_once 'Zend/Service/Twitter/Exception.php';
			throw new Zend_Service_Twitter_Exception(
					'Status must contain at least one character'
			);
		}
		$data = array('status' => $status);
		if (is_numeric($inReplyToStatusId) && !empty($inReplyToStatusId)) {
			$data['in_reply_to_status_id'] = $inReplyToStatusId;
		}
		return $this->_post($path, $data);
	}

}