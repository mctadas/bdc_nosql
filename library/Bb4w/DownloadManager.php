<?php

namespace Bb4w;

use Bb4w\ValueObject\Uuid;
use Bb4w\ValueObject\Attributes;

/**
 * @package Kompro 
 */
class DownloadManager 
{

	const METATABLE = 'exported_files';

	protected $filesDir;
	protected $db;

	/**
	 * @param string $db
	 * @param string $filesDir 
	 */
	public function __construct($db, $filesDir) 
	{
		$this->filesDir = $filesDir;
		$this->db = $db;
	}

	/**
	 * @return string
	 */
	public function getFilesDir() 
	{
		return $this->filesDir;
	}

	/**
	 * Register a created file.
	 * 
	 * @param string $tempFile
	 * @param string $filename
	 * @param int    $userId
	 * @return Bb4w\ValueObject\Attributes
	 * @throws \InvalidArgumentException
	 * @throws \Exception 
	 */
	public function registerFile($tempFile, $filename, $userId) 
	{
		if (!file_exists($tempFile)) {

			throw new \InvalidArgumentException('fileNotFound');
		}

		if (!is_numeric($userId)) {

			throw new \InvalidArgumentException('invalidUserId');
		}

		$fileId = Uuid::generateNewUuid();

		$temp = @fopen($tempFile, 'r');
		$new = @fopen($this->filesDir . DIRECTORY_SEPARATOR . $fileId, 'x');

		if (!$new) {

			throw new \Exception('unable to write ' . $this->filesDir);
		}

		if (!$temp) {

			throw new \Exception('unable to read ' . $tempFile);
		}

		while (!feof($temp)) {

			fwrite($new, fread($temp, 8192));
		}

		fclose($temp);
		fclose($new);
		@unlink($tempFile);

		switch (end(explode('.', $filename))) {

			case 'csv':

				$mime = 'text/csv';
				break;

			case 'txt';

				$mime = 'text/plain';
				break;

			case 'pdf':

				$mime = 'application/pdf';
				break;

			case 'docx':

				$mime = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
				break;

			case 'xlsx':

				$mime = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
				break;

			default:

				$mime = 'text/plain';
		}

		$data = array(
			'user_id' => $userId,
			'filename' => $filename,
			'identity' => $fileId,
			'mime' => $mime,
			'size' => filesize($this->filesDir . DIRECTORY_SEPARATOR . $fileId),
			'generated' => date('Y-m-d H:i:s')
		);

		$this->db->insert(self::METATABLE, $data);

		return new Attributes($data);
	}

	/**
	 * Fetches file.
	 * 
	 * @param Uuid $identity
	 * @return Bb4w\ValueObject\Attributes
	 * @throws \InvalidArgumentException 
	 */
	public function fetchFile(Uuid $identity) 
	{
		$stmt = $this->db->select()->from(self::METATABLE)->where('`identity` = ?', $identity);

		$file = reset($this->db->fetchAll($stmt));

		if (empty($file) || !is_array($file)) {

			throw new \InvalidArgumentException('fileNotFound');
		}

		return new Attributes($file);
	}

	/**
	 * @param Uuid $identity
	 * @param int $user_id
	 * @return boolean 
	 */
	public function deleteFile(Uuid $identity, $user_id) 
	{

		$di = \Zend_Registry::get('di');

		try {

			$file = $this->fetchFile($identity)->value;
		} catch (\InvalidArgumentException $ex) {

			return false;
		}

		if ($file->user_id != $user_id) {

			return false;
		}

		$this->db->delete(self::METATABLE, $this->db->quoteInto("identity = ?", $identity));

		@unlink($this->filesDir . DIRECTORY_SEPARATOR . $identity);

		return true;
	}

}
