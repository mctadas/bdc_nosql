<?php

namespace BDC\Normalizer\Command;

use Exception;

class NormalizeSimpleData 
{
	public $table;
	
	public $adapter;
	
	public function __construct(
		$table,
		$adapter
	) {
		$this->table = $table;
		$this->adapter = $adapter;
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
		if ( empty( $requestData['table'] ) ) {
			
			$validationErrors['table'] = 'missingTable';
		}
		
		if ( empty( $requestData['adapter'] ) ) {
			
			$validationErrors['adapter'] = 'missingAdapter';
		}
		
		if (empty($validationErrors)) {
			
			return new self(
				$requestData['table'],
				$requestData['adapter']
			);
		} else {
			return array(
				'validationErrors' => $validationErrors,
			);
		}
	}

}