<?php

namespace Domain\Adapter\FilesCsvTxt\Command;

use Bb4w\ValueObject\Uuid;
use Bb4w\ValueObject\Priority;
use Bb4w\ValueObject\Adapter;
use Bb4w\ValueObject\Attributes;
use Bb4w\Domain\Command;
use Exception;

/**
 * @package Kompro 
 */
class ParseFile extends Command 
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
		$validDelimeters = array(';', ',');

		if (empty($requestData['attributes']['path']) || (!preg_match('/htt(p|ps):\/\//', $requestData['attributes']['path']) )) {

			$validationErrors['attributes'] = 'validationErrors.attributes.invalidFilePath';
		}

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

		if (empty($requestData['attributes']['columns']) || !is_array($requestData['attributes']['columns'])) {

			$validationErrors['columns'] = 'validationErrors.attributes.columns.emptyColumns';
		} else {

			foreach ($requestData['attributes']['columns'] as $column) {

				if (!in_array($column, $validColumns)) {

					$validationErrors['attributes']['columns'][$column] = 'validationErrors.attributes.columns.invalidColumnType';
				}
			}
		}

		if (!in_array($requestData['attributes']['delimiter'], $validDelimeters)) {

			$validationErrors['attributes']['delimiter'] = 'validationErrors.attributes.delimiter.invalidDelimiter';
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