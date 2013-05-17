<?php

namespace BDC\Domain;

use Zend_Db_Adapter_Abstract;

/**
 * @package Kompro 
 */
abstract class AdapterQueue 
{

	/**
	 * @var Zend_Db_Adapter_Abstract
	 */
	private $_db;

	/**
	 * @param Zend_Db_Adapter_Abstract $db 
	 */
	public function __construct(Zend_Db_Adapter_Abstract $db) 
	{
		$this->_db = $db;
	}

	/**
	 * Adds command to internal queue
	 * 
	 * @param Command $command
	 * @return boolean 
	 */
	public function enque(Command $command) 
	{
		$this->_db->insert(
				$this->_tableName, array(
			"identity" => $command->identity->value,
			"class_name" => get_class($command),
			"priority" => $command->priority->value,
			"attributes" => serialize($command->attributes->value),
			"time" => microtime(true),
			"user_id" => $command->user_id,
			"title" => isset($command->title) ? $command->title : ''
				)
		);

		return true;
	}

	/**
	 * Takes message from the queue, sets work_in_progress and returns it
	 * 
	 * @return array
	 */
	public function deque() 
	{
		$result = array();

		$this->_db->beginTransaction();
		try {
			$select = $this->_db
					->select()
					->from($this->_tableName)
					->where("work_in_progress = ?", "false")
					->order("failed ASC")
					->order("priority DESC")
					->order("time ASC");

			$result = $this->_db->fetchRow($select);
			if (!empty($result)) {
				$this->_db->update(
						$this->_tableName, array(
					"work_in_progress" => "true",
					"begin_work_at" => microtime(true),
					"pid" => getmypid()
						), $this->_db->quoteInto("identity = ?", $result["identity"])
				);
				$result["attributes"] = unserialize($result["attributes"]);
			}

			$this->_db->commit();
		} catch (\Exception $ex) {
			$this->_db->rollBack();
			throw $ex;
		}

		return $result;
	}

	/**
	 * Removes message from the queue
	 * 
	 * @param string $identity 
	 */
	public function removeFromQueue($identity) 
	{
		$this->_db->beginTransaction();
		try {
			$this->_db->delete($this->_tableName, $this->_db->quoteInto("identity = ?", $identity));
			$this->_db->commit();
		} catch (\Exception $ex) {
			$this->_db->rollBack();
			throw $ex;
		}
	}

	/**
	 * Updates queue item. Sets failed = 1, working = false.
	 * 
	 * @param Uuid $queueIdentity
	 */
	public function updateFailedQueue($identity) 
	{
		$this->_db->beginTransaction();
		try {
			$this->_db->update(
					$this->_tableName, array('failed' => 1, 'work_in_progress' => 'false'), $this->_db->quoteInto("identity = ?", $identity)
			);
			$this->_db->commit();
		} catch (\Exception $ex) {
			$this->db->rollBack();
			throw $ex;
		}
	}

}