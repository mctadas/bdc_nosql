<?php

namespace Domain\Adapter\FilesCsvTxt\ValueObject;

use Bb4w\ValueObject\Uuid;
use Bb4w\ValueObject\Attributes;
use Exception;
use InvalidArgumentException;
use ArrayFunctions;

/**
 * @package Kompro 
 */
class RowsList 
{

	/**
	 * @var Array
	 */
	public $rows = array();

	/**
	 * @param array $rows 
	 */
	public function __construct(array $rows) 
	{
		foreach ($rows as $row) {
			if (!$row instanceof Row) {
				throw new InvalidArgumentException('invalidRowType');
			}

			$rowIdentity = $row->identity->value;

			$this->rows[$rowIdentity] = $row;
		}
	}

	/**
	 * @param array $data
	 * @return \Domain\Adapter\FilesCsvTxt\ValueObject\self 
	 */
	static public function buildFromRequestData($data) 
	{
		$validationErrors = array();
		$dynamicErrors = array();
		$rows = array();

		if (!empty($data)) {

			foreach ($data as $key => $value) {

				$value = \ArrayFunctions::flatten($value);

				foreach ($value as $k => $v) {
					$value[$k] = mb_convert_encoding($v, "UTF-8");
				}

				try {
					$voIdentity = Uuid::generateNewUuid();
				} catch (Exception $ex) {
					$dynamicErrors[$key] = 'dynamicErrors.rowIndentity.' . $ex->getMessage();
				}

				try {
					$voAttributes = new Attributes($value);
				} catch (Exception $ex) {
					$dynamicErrors[$key] = "dynamicErrors.rowAttributes." . $ex->getMessage();
				}

				if (empty($dynamicErrors[$key])) {
					try {
						$rows[] = new Row($voIdentity, $voAttributes);
					} catch (Exception $ex) {
						$dynamicErrors[$key] = "dynamicErrors.row." . $ex->getMessage();
					}
				}
			}
		}

		try {
			$voRowsList = new self($rows);
		} catch (Exception $ex) {
			$validationErrors["rowsList"] = "validationErrors.rowsList." . $ex->getMessage();
		}

		if (empty($validationErrors) && empty($dynamicErrors)) {
			return $voRowsList;
		} else {
			return array(
				"validationErrors" => $validationErrors,
				"dynamicErros" => $dynamicErrors,
			);
		}
	}

}