<?php

namespace BDC\Normalizer\ValueObject;

use Exception;
use InvalidArgumentException;

/**
 * @package Kompro 
 */
class DataElement 
{

	public $key;
	public $dataKeyColumn;
	public $dataTableName;
	public $dataColumn;
	public $metaKeyColumn;
	public $metaTableName;
	public $metaDataColumnName;
	public $metaDataColumnType;

	/**
	 * @param string $key
	 * @param string $dataKeyColumn
	 * @param string $dataTableName
	 * @param string $dataColumn
	 * @param string $metaKeyColumn
	 * @param string $metaTableName
	 * @param string $metaDataColumnName
	 * @param string $metaDataColumnType 
	 */
	public function __construct(
	$key, $dataKeyColumn, $dataTableName, $dataColumn, $metaKeyColumn, $metaTableName, $metaDataColumnName, $metaDataColumnType
	) {
		$this->key = $key;
		$this->dataKeyColumn = $dataKeyColumn;
		$this->dataTableName = $dataTableName;
		$this->dataColumn = $dataColumn;
		$this->metaKeyColumn = $metaKeyColumn;
		$this->metaTableName = $metaTableName;
		$this->metaDataColumnName = $metaDataColumnName;
		$this->metaDataColumnType = $metaDataColumnType;
	}

	/**
	 * @param array $data
	 * @return \Bb4w\Normalizer\ValueObject\self 
	 */
	static public function buildFromRequestData($data) 
	{
		$validationErrors = array();

		if (!isset($data["key"]) || empty($data["key"])) {
			$validationErrors["key"] = "validationErrors.key.cannot_be_empty";
		} else {
			$key = $data['key'];
		}

		if (!isset($data["data_key_column"]) || empty($data["data_key_column"])) {
			$validationErrors["data_key_column"] = "validationErrors.data_key_column.cannot_be_empty";
		} else {
			$dataKeyColumn = $data['data_key_column'];
		}

		if (!isset($data["data_table_name"]) || empty($data["data_table_name"])) {
			$validationErrors['data_table_name'] = "validationErrors.data_table_name.cannot_be_empty";
		} else {
			$dataTableName = $data["data_table_name"];
		}

		if (!isset($data["data_column"]) || empty($data["data_column"])) {
			$validationErrors['data_column'] = "validationErrors.data_column.cannot_be_empty";
		} else {
			$dataColumn = $data["data_column"];
		}

		if (!isset($data["meta_key_column"]) || empty($data["meta_key_column"])) {
			$validationErrors['meta_key_column'] = "validationErrors.meta_key_column.cannot_be_empty";
		} else {
			$metaKeyColumn = $data["meta_key_column"];
		}

		if (!isset($data["meta_table_name"]) || empty($data["meta_table_name"])) {
			$validationErrors['meta_table_name'] = "validationErrors.meta_table_name.cannot_be_empty";
		} else {
			$metaTableName = $data["meta_table_name"];
		}

		if (!isset($data["meta_data_column_name"]) || empty($data["meta_data_column_name"])) {
			$validationErrors['meta_data_column_name'] = "validationErrors.meta_data_column_name.cannot_be_empty";
		} else {
			$metaDataColumnName = $data["meta_data_column_name"];
		}

		if (!isset($data["meta_data_column_type"]) || empty($data["meta_data_column_type"])) {
			$validationErrors['meta_data_column_type'] = "validationErrors.meta_data_column_type.cannot_be_empty";
		} else {
			$metaDataColumnType = $data["meta_data_column_type"];
		}

		if (empty($validationErrors)) {
			return new self(
							$key,
							$dataKeyColumn,
							$dataTableName,
							$dataColumn,
							$metaKeyColumn,
							$metaTableName,
							$metaDataColumnName,
							$metaDataColumnType
			);
		} else {
			return array(
				"validationErrors" => $validationErrors,
			);
		}
	}

}