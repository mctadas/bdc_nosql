<?php

namespace Domain\Adapter\GooglePlus\ValueObject;

use Bb4w\ValueObject\Uuid;
use Bb4w\ValueObject\Attributes;
use Exception;
use ArrayFunctions;

/**
 * @package Kompro 
 */
class UsersList 
{

	/**
	 * @var Array
	 */
	public $users = array();

	/**
	 * @param array $users 
	 */
	public function __construct(array $users) 
	{
		foreach ($users as $user) {

			if (!$user instanceof User) {

				throw new InvalidArgumentException('invalidUsersType');
			}

			$userIdentity = $user->identity->value;

			$this->users[$userIdentity] = $user;
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
		$users = array();

		if (!empty($data)) {

			foreach ($data as $key => $value) {

				$value = ArrayFunctions::flatten($value);

				foreach ($value as $k => $v) {

					$value[$k] = mb_convert_encoding($v, "UTF-8");
				}

				try {
					$voIdentity = Uuid::generateNewUuid();
				} catch (Exception $ex) {
					$dynamicErrors[$key] = 'dynamicErrors.userIndentity.' . $ex->getMessage();
				}

				try {
					$voAttributes = new Attributes($value);
				} catch (Exception $ex) {
					$dynamicErrors[$key] = "dynamicErrors.userAttributes." . $ex->getMessage();
				}

				if (empty($dynamicErrors[$key])) {
					try {
						$users[] = new User($voIdentity, $voAttributes);
					} catch (Exception $ex) {
						$dynamicErrors[$key] = "dynamicErrors.user." . $ex->getMessage();
					}
				}
			}
		}

		try {
			$voUsersList = new self($users);
		} catch (Exception $ex) {
			$validationErrors["usersList"] = "validationErrors.usersList." . $ex->getMessage();
		}

		if (empty($validationErrors) && empty($dynamicErrors)) {
			return $voUsersList;
		} else {
			return array(
				"validationErrors" => $validationErrors,
				"dynamicErros" => $dynamicErrors,
			);
		}
	}

}