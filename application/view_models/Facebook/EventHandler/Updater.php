<?php

namespace ViewModel\Facebook\EventHandler;

use Domain\Adapter\Facebook\Event\MentionFound;
use Domain\Adapter\Facebook\Event\PostCreated;
use Domain\Adapter\Facebook\Event\EventCreated;

// Lib
use BDC\ValueObject\Uuid;
use BDC\Models\BaseWriteModel;
use ReadModel\User as UserReadModel;

// Zend
use \Zend_Db_Adapter_Abstract;
use \Zend_Session_Namespace;

/**
 * @package Kompro 
 */
class Updater extends BaseWriteModel 
{

	const RESULTS_TABLE = 'facebook_adapter';
	const DATA_TABLE = 'facebook_adapter_data';
	const METADATA_TABLE = 'facebook_adapter_meta';

	/**
	 * @param Zend_Db_Adapter_Abstract $db
	 */
	public function __construct(
	Zend_Db_Adapter_Abstract $db
	) {
		parent::__construct($db);
	}

	/**
	 * @param MentionFound $event 
	 */
	public function mentionFound(MentionFound $event) 
	{
		$eventKey = $event->attributes->value->identity;
		$attributes = $event->attributes->value;
		$data = $event->mentionsList->mentions;
		$meta = array();

		// different posts have different columns. selecting all possible.
		foreach ($data as $item => $attr) {

			$item_meta = array_keys((array) $attr->attributes->value);
			$meta = array_merge($meta, $item_meta);
		}

		$meta = array_unique($meta);

		foreach ($meta as $column) {

			switch ($column) {

				case 'updated_time':
				case 'created_time':

					$column_type = 'date';

					break;

				case 'object_id':
				case 'from_id':
				case 'application_id':

					$column_type = 'int';

					break;

				// serialized arrays
				case 'story_tags':
				case 'properties':
				case 'message_tags':
				case 'to':
				case 'likes':

					$column_type = 'text';

					break;

				default:

					$column_type = 'varchar';
			}

			$this->insertMeta(self::METADATA_TABLE, $eventKey, $column, $column_type);
		}

		$this->insertData(self::DATA_TABLE, $eventKey, $data);
		$this->insertResult(self::RESULTS_TABLE, $eventKey, $attributes);
	}

	/**
	 * @param PostCreated $event 
	 */
	public function postCreated(PostCreated $event) 
	{
		$eventKey = $event->attributes->value->identity;
		$attributes = $event->attributes->value;

		$this->insertResult(self::RESULTS_TABLE, $eventKey, $attributes);
	}

	/**
	 * @param EventCreated $event 
	 */
	public function eventCreated(EventCreated $event) 
	{
		$eventKey = $event->attributes->value->identity;
		$attributes = $event->attributes->value;

		$this->insertResult(self::RESULTS_TABLE, $eventKey, $attributes);
	}

}
