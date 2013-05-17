<?php

namespace Domain\Adapter\MySQL\Command;

use Bb4w\ValueObject\Uuid;
use Bb4w\ValueObject\Priority;
use Bb4w\ValueObject\Adapter;
use Bb4w\ValueObject\Attributes;
use Bb4w\Domain\Command;
use Exception;

/**
 * @package Kompro 
 */
class Select extends Command 
{
	
	/**
	 * @param Uuid $Identity
	 * @param Priority $priority
	 * @param Adapter $adapter
	 * @param Attributes $attributes
	 * @param int $user_id
	 * @param string $title 
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
		$validColumns = array('int', 'decimal', 'text', 'varchar', 'date');

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

		if (empty($requestData['user_id']) || !is_numeric($requestData['user_id'])) {

			$validationErrors['user_id'] = 'validationErrors.user_id.invalidUserId';
		}

		if (empty($requestData['attributes']['columns']) || !is_array($requestData['attributes']['columns'])) {

			$validationErrors['columns'] = 'validationErrors.columns.emptyColumns';
		} else {

			foreach ($requestData['attributes']['columns'] as $column) {

				if (!in_array($column, $validColumns)) {

					$validationErrors['attributes']['columns'][$column] = 'validationErrors.attributes.columns.' . $column . '.invalidColumnType';
				}
			}
		}

		if (empty($requestData['attributes']['host'])) {

			$validationErrors['attributes']['host'] = 'validationErrors.attributes.host.missingHostname';
		}

		if (empty($requestData['attributes']['username'])) {

			$validationErrors['attributes']['username'] = 'validationErrors.attributes.username.missingUsername';
		}

		if (empty($requestData['attributes']['database'])) {

			$validationErrors['attributes']['database'] = 'validationErrors.attributes.database.missingDatabase';
		}

		if (empty($requestData['attributes']['password'])) {

			$validationErrors['attributes']['password'] = 'validationErrors.attributes.password.missingPassword';
		}

		if (empty($requestData['attributes']['table'])) {

			$validationErrors['attributes']['table'] = 'validationErrors.attributes.table.missingTable';
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