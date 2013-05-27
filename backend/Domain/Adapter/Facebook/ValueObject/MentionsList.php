<?php

namespace Domain\Adapter\Facebook\ValueObject;

use Bb4w\ValueObject\Uuid;
use Bb4w\ValueObject\Attributes;
use Exception;
use InvalidArgumentException;
use ArrayFunctions;

/**
 * @package Kompro 
 */
class MentionsList 
{

	/**
	 * @var Array
	 */
	public $mentions = array();

	/**
	 * @param array $mentions 
	 */
	public function __construct(array $mentions) 
	{
		foreach ($mentions as $mention) {
			if (!$mention instanceof Mention) {
				throw new InvalidArgumentException('invalidMentionType');
			}

			$mentionIdentity = $mention->identity->value;

			$this->mentions[$mentionIdentity] = $mention;
		}
	}

	/**
	 * @param array $data
	 * @return \Domain\Adapter\Facebook\ValueObject\self 
	 */
	static public function buildFromRequestData($data) 
	{
		$validationErrors = array();
		$dynamicErrors = array();
		$mentions = array();

		if (!empty($data)) {

			foreach ($data as $key => $value) {

				$value = ArrayFunctions::flatten($value, array('story_tags', 'properties', 'message_tags', 'to', 'likes'));

				foreach ($value as $k => $v) {

					$value[$k] = @mb_convert_encoding($v, "UTF-8");
				}

				try {
					$voIdentity = Uuid::generateNewUuid();
				} catch (Exception $ex) {
					$dynamicErrors[$key] = 'dynamicErrors.mentionIndentity.' . $ex->getMessage();
				}

				try {
					$voAttributes = new Attributes($value);
				} catch (Exception $ex) {
					$dynamicErrors[$key] = "dynamicErrors.mentionAttributes." . $ex->getMessage();
				}

				if (empty($dynamicErrors[$key])) {
					try {
						$mentions[] = new Mention($voIdentity, $voAttributes);
					} catch (Exception $ex) {
						$dynamicErrors[$key] = "dynamicErrors.mention." . $ex->getMessage();
					}
				}
			}
		}

		try {
			$voMentionsList = new self($mentions);
		} catch (Exception $ex) {
			$validationErrors["mentionsList"] = "validationErrors.mentionsList." . $ex->getMessage();
		}

		if (empty($validationErrors) && empty($dynamicErrors)) {
			return $voMentionsList;
		} else {
			return array(
				"validationErrors" => $validationErrors,
				"dynamicErros" => $dynamicErrors,
			);
		}
	}

}