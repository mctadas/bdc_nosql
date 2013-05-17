<?php

namespace Domain\Adapter\Twitter\ValueObject;

use Bb4w\ValueObject\Uuid;
use Bb4w\ValueObject\Attributes;
use Exception;
use InvalidArgumentException;
use ArrayFunctions;

/**
 * @package Kompro 
 */
class TrendsList 
{

	/**
	 * @var Array
	 */
	public $trends = array();

	/**
	 * @param array $trends
	 */
	public function __construct(array $trends) 
	{
		foreach ($trends as $trend) {
			if (!$trend instanceof Trend) {
				throw new InvalidArgumentException('invalidTrendType');
			}

			$trendIdentity = $trend->identity->value;

			$this->trends[$trendIdentity] = $trend;
		}
	}

	/**
	 * @param array $data
	 * @return \Domain\Adapter\Twitter\ValueObject\self 
	 */
	static public function buildFromRequestData($data) 
	{
		$validationErrors = array();
		$dynamicErrors = array();
		$trends = array();

		if (!empty($data)) {
			foreach ($data as $date => $items) {
				foreach ($items as $key => $value) {

					$value['date'] = $date;
					$value = \ArrayFunctions::flatten($value);

					foreach ($value as $k => $v) {
						$value[$k] = mb_convert_encoding($v, "UTF-8");
					}
					try {
						$voIdentity = Uuid::generateNewUuid();
					} catch (Exception $ex) {
						$dynamicErrors[$key] = 'dynamicErrors.trendIndentity.' . $ex->getMessage();
					}

					try {
						$voAttributes = new Attributes($value);
					} catch (Exception $ex) {
						$dynamicErrors[$key] = "dynamicErrors.trendAttributes." . $ex->getMessage();
					}

					if (empty($dynamicErrors[$key])) {
						try {
							$trends[] = new Trend($voIdentity, $voAttributes);
						} catch (Exception $ex) {
							$dynamicErrors[$key] = "dynamicErrors.trend." . $ex->getMessage();
						}
					}
				}
			}
		}

		try {
			$voTrendsList = new self($trends);
		} catch (Exception $ex) {
			$validationErrors["trendsList"] = "validationErrors.trendsList." . $ex->getMessage();
		}

		if (empty($validationErrors) && empty($dynamicErrors)) {
			return $voTrendsList;
		} else {
			return array(
				"validationErrors" => $validationErrors,
				"dynamicErros" => $dynamicErrors,
			);
		}
	}

}