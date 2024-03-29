<?php

namespace Domain\Adapter\Facebook\Command;

use Bb4w\ValueObject\Uuid;
use Bb4w\ValueObject\Priority;
use Bb4w\ValueObject\Adapter;
use Bb4w\ValueObject\Attributes;
use Bb4w\Domain\Command;
use Exception;

/**
 * @package Kompro 
 */
class CreatePost extends Command 
{
	
	/**
	 * @param Uuid $Identity
	 * @param Priority $priority
	 * @param Adapter $adapter
	 * @param Attributes $attributes
	 * @param type $user_id
	 * @param type $title 
	 */
	public function __construct(
	Uuid $Identity, Priority $priority, Adapter $adapter, Attributes $attributes, $user_id, $title = null
	) {
		$this->identity = $Identity;
		$this->priority = $priority;
		$this->adapter = $adapter;
		$this->attributes = $attributes;
		$this->user_id = $user_id;
		$this->title = $title;
	}

	/**
	 * Build command from requestData
	 * 
	 * @param array $requestData
	 * @throws \Exception 
	 * @return Object of itself || Array validationErrors
	 */
	static public function buildFromRequestData(array $requestData) 
	{
		$validationErrors = array();

		try {
			$voIdentity = Uuid::generateNewUuid();
		} catch (Exception $ex) {
			$validationErrors['identity'] = 'validationErrors.identity.' . $ex->getMessage();
		}

		try {
			$voPriority = new Priority($requestData["priority"]);
		} catch (Exception $ex) {
			$validationErrors['priority'] = 'validationErrors.priority.' . $ex->getMessage();
		}

		try {
			$voAdapter = new Adapter($requestData['adapter']);
		} catch (Exception $ex) {
			$validationErrors['adapter'] = 'validationErrors.adapter.' . $ex->getMessage();
		}

		try {
			$voAttributes = new Attributes($requestData["attributes"]);
		} catch (Exception $ex) {
			$validationErrors['attributes'] = 'validationErrors.attributes.' . $ex->getMessage();
		}

		if (!is_numeric($requestData['user_id'])) {

			$validationErrors['user_id'] = 'validationErrors.user_id.invalidUserId';
		}

		if (empty($requestData['attributes']['access_token'])) {

			$validationErrors['access_token'] = 'validationErrors.attributes.access_token.invalidAccessToken';
		}

		if (!is_numeric($requestData['attributes']['page_id'])) {

			$validationErrors['page_id'] = 'validationErrors.attributes.page_id.invalidPageId';
		}

		// final check and return
		if (empty($validationErrors)) {
			return new self(
							$voIdentity,
							$voPriority,
							$voAdapter,
							$voAttributes,
							$requestData['user_id'],
							isset($requestData['title']) ? $requestData['title'] : ''
			);
		} else {
			return array(
				'validationErrors' => $validationErrors,
			);
		}
	}

}