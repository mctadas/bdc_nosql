<?php

namespace Domain\Adapter\GooglePlus\ValueObject;

use Bb4w\ValueObject\Uuid;
use Bb4w\ValueObject\Attributes;
use Exception;
use InvalidArgumentException;
use ArrayFunctions;

/**
 * @package Kompro 
 */
class CommentsList 
{

	/**
	 * @var Array
	 */
	public $comments = array();

	/**
	 * @param array $comments
	 */
	public function __construct(array $comments) 
	{
		foreach ($comments as $comment) {

			if (!$comment instanceof Comment) {

				throw new InvalidArgumentException('invalidCommentType');
			}

			$commentIdentity = $comment->identity->value;

			$this->comments[$commentIdentity] = $comment;
		}
	}

	/**
	 * @param array $data
	 * @return \Domain\Adapter\GooglePlus\ValueObject\self 
	 */
	static public function buildFromRequestData($data) 
	{
		$validationErrors = array();
		$dynamicErrors = array();
		$comments = array();

		if (!empty($data)) {

			foreach ($data as $key => $value) {
				$value = \ArrayFunctions::flatten($value);

				foreach ($value as $k => $v) {
					$value[$k] = mb_convert_encoding($v, "UTF-8");
				}

				foreach ($value as $k => $v) {

					$value[$k] = mb_convert_encoding($v, "UTF-8");
				}

				try {
					$voIdentity = Uuid::generateNewUuid();
				} catch (Exception $ex) {
					$dynamicErrors[$key] = 'dynamicErrors.commentIndentity.' . $ex->getMessage();
				}

				try {
					$voAttributes = new Attributes($value);
				} catch (Exception $ex) {
					$dynamicErrors[$key] = "dynamicErrors.commentAttributes." . $ex->getMessage();
				}

				if (empty($dynamicErrors[$key])) {
					try {
						$comments[] = new Comment($voIdentity, $voAttributes);
					} catch (Exception $ex) {
						$dynamicErrors[$key] = "dynamicErrors.comment." . $ex->getMessage();
					}
				}
			}
		}

		try {
			$voCommentsList = new self($comments);
		} catch (Exception $ex) {
			$validationErrors["commentsList"] = "validationErrors.commentsList." . $ex->getMessage();
		}

		if (empty($validationErrors) && empty($dynamicErrors)) {
			return $voCommentsList;
		} else {
			return array(
				"validationErrors" => $validationErrors,
				"dynamicErros" => $dynamicErrors,
			);
		}
	}

}