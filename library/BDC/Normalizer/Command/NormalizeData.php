<?php

namespace BDC\Normalizer\Command;

use BDC\Normalizer\ValueObject\DataElementList;
use Exception;

/**
 * @package Kompro 
 */
class NormalizeData 
{

	/**
	 * @var DataElementList
	 */
	public $data;
	public $join;
	public $select;

	/**
	 * @param DataElementList $data
	 * @param array $join
	 * @param array $select 
	 */
	public function __construct(
	DataElementList $data, $join, $select
	) {
		$this->data = $data;
		$this->join = $join;
		$this->select = $select;
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

		$result = DataElementList::buildFromRequestData($requestData["data"]);
		if (is_object($result)) {
			$voDataElementList = $result;
		} else {
			$validationErrors["data"] = $result;
		}

		$voJoin = $requestData["join"];
		$voSelect = $requestData["select"];

		// final check and return
		if (empty($validationErrors)) {
			return new self(
							$voDataElementList,
							$voJoin,
							$voSelect
			);
		} else {
			return array(
				'validationErrors' => $validationErrors,
			);
		}
	}

}