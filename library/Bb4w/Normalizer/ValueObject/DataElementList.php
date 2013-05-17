<?php

namespace Bb4w\Normalizer\ValueObject;

use Exception;
use InvalidArgumentException;

/**
 * @package Kompro 
 */
class DataElementList 
{

	/**
	 * @var Array
	 */
	public $elements = array();

	/**
	 * @param array $dataElements 
	 */
	public function __construct(array $dataElements) 
	{
		foreach ($dataElements as $dataElement) {
			if (!$dataElement instanceof DataElement) {
				throw new InvalidArgumentException('invalidElementType');
			}

			$this->elements[] = $dataElement;
		}
	}

	/**
	 * @param array $data
	 * @return \Bb4w\Normalizer\ValueObject\self 
	 */
	static public function buildFromRequestData($data) 
	{
		$validationErrors = array();
		$dynamicErrors = array();
		$dataElements = array();

		if (!empty($data)) {
			foreach ($data as $key => $value) {
				$result = DataElement::buildFromRequestData(array(
							"key" => $value["key"],
							"data_key_column" => $value["data_key_column"],
							"data_table_name" => $value["data_table_name"],
							"data_column" => $value["data_column"],
							"meta_key_column" => $value["meta_key_column"],
							"meta_table_name" => $value["meta_table_name"],
							"meta_data_column_name" => $value["meta_data_column_name"],
							"meta_data_column_type" => $value["meta_data_column_type"],
						));

				if (!is_array($result)) {
					$dataElements[] = $result;
				} else {
					$dynamicErrors[$key] = "dynamicErrors.dataElement.bad_element_array";
				}
			}
		}

		try {
			$voDataElementsList = new self($dataElements);
		} catch (Exception $ex) {
			$validationErrors["dataElementsList"] = "validationErrors.dataElementsList." . $ex->getMessage();
		}

		if (empty($validationErrors) && empty($dynamicErrors)) {
			return $voDataElementsList;
		} else {
			return array(
				"validationErrors" => $validationErrors,
				"dynamicErros" => $dynamicErrors,
			);
		}
	}

}