<?php

namespace Domain\Adapter\Soap\ValueObject;

use Bb4w\ValueObject\Uuid;
use Bb4w\ValueObject\Attributes;
use Exception;
use InvalidArgumentException;
use ArrayFunctions;

/**
 * @package Kompro 
 */
class Response 
{

	/**
	 * @var Array
	 */
	public $items = array();

	/**
	 * @param array $items 
	 */
	public function __construct(array $items) 
	{
		foreach ($items as $row) {
			if (!$row instanceof Item) {
				throw new InvalidArgumentException('invalidItemType');
			}

			$rowIdentity = $row->identity->value;

			$this->items[$rowIdentity] = $row;
		}
	}

	/**
	 * @param array $data
	 * @return \Domain\Adapter\Soap\ValueObject\self 
	 */
	static public function buildFromRequestData($data) 
	{
		$validationErrors = array();
		$dynamicErrors = array();
		$items = array();

		if (!empty($data) && is_array($data)) {

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
						$items[] = new Item($voIdentity, $voAttributes);
					} catch (Exception $ex) {
						$dynamicErrors[$key] = "dynamicErrors.row." . $ex->getMessage();
					}
				}
			}
		}

		try {
			$voResponse = new self($items);
		} catch (Exception $ex) {
			$validationErrors["itemsList"] = "validationErrors.itemsList." . $ex->getMessage();
		}

		if (empty($validationErrors) && empty($dynamicErrors)) {
			return $voResponse;
		} else {
			return array(
				"validationErrors" => $validationErrors,
				"dynamicErros" => $dynamicErrors,
			);
		}
	}

}