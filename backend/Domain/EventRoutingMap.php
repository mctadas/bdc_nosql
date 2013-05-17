<?php

namespace Domain;

use Bb4w\DiContainer;

/**
 * @package Kompro 
 */
class EventRoutingMap 
{

	/**
	 * @var DiContainer;
	 */
	private $_diContainer;

	/**
	 * @param DiContainer $diContainer 
	 */
	public function __construct(DiContainer $diContainer) 
	{
		$this->_diContainer = $diContainer;
	}

	public function getReportingDbUpdaters() 
	{
		$c = $this->_diContainer;

		return array(
			// Twitter
			'Domain\Adapter\Twitter\Event\MentionFound' => array(
				function ($e) use ($c) {
					$c->twitterUpdater->mentionFound($e);
				},
			),
			'Domain\Adapter\Twitter\Event\TrendsReceived' => array(
				function ($e) use ($c) {
					$c->twitterUpdater->trendsReceived($e);
				},
			),
			'Domain\Adapter\Twitter\Event\TweetCreated' => array(
				function ($e) use ($c) {
					$c->twitterUpdater->tweetCreated($e);
				},
			),
			// Facebook
			'Domain\Adapter\Facebook\Event\MentionFound' => array(
				function ($e) use ($c) {
					$c->facebookUpdater->mentionFound($e);
				},
			),
			'Domain\Adapter\Facebook\Event\PostCreated' => array(
				function ($e) use ($c) {
					$c->facebookUpdater->postCreated($e);
				},
			),
			'Domain\Adapter\Facebook\Event\EventCreated' => array(
				function ($e) use ($c) {
					$c->facebookUpdater->eventCreated($e);
				},
			),
			// GooglePlus
			'Domain\Adapter\GooglePlus\Event\MentionFound' => array(
				function ($e) use ($c) {
					$c->googlePlusUpdater->mentionFound($e);
				},
			),
			'Domain\Adapter\GooglePlus\Event\CommentsRetrieved' => array(
				function ($e) use ($c) {
					$c->googlePlusUpdater->commentsRetrieved($e);
				},
			),
			'Domain\Adapter\GooglePlus\Event\UsersFound' => array(
				function ($e) use ($c) {
					$c->googlePlusUpdater->usersFound($e);
				},
			),
			// MySQL
			'Domain\Adapter\MySQL\Event\Selected' => array(
				function ($e) use ($c) {
					$c->mysqlUpdater->selected($e);
				},
			),
			'Domain\Adapter\MySQL\Event\Exported' => array(
				function ($e) use ($c) {
					$c->mysqlUpdater->exported($e);
				},
			),
			// Oracle
			'Domain\Adapter\Oracle\Event\Selected' => array(
				function ($e) use ($c) {
					$c->oracleUpdater->selected($e);
				},
			),
			'Domain\Adapter\Oracle\Event\Exported' => array(
				function ($e) use ($c) {
					$c->oracleUpdater->exported($e);
				},
			),
			// PostgreSQL
			'Domain\Adapter\PostgreSQL\Event\Selected' => array(
				function ($e) use ($c) {
					$c->postgresqlUpdater->selected($e);
				},
			),
			'Domain\Adapter\PostgreSQL\Event\Exported' => array(
				function ($e) use ($c) {
					$c->postgresqlUpdater->exported($e);
				},
			),
			// FilesCsvTxt
			'Domain\Adapter\FilesCsvTxt\Event\FileParsed' => array(
				function ($e) use ($c) {
					$c->filescsvtxtUpdater->fileParsed($e);
				},
			),
			'Domain\Adapter\FilesCsvTxt\Event\Exported' => array(
				function ($e) use ($c) {
					$c->filescsvtxtUpdater->exported($e);
				},
			),
			// FilesXlsx
			'Domain\Adapter\FilesXlsx\Event\FileParsed' => array(
				function ($e) use ($c) {
					$c->filesxlsxUpdater->fileParsed($e);
				},
			),
			'Domain\Adapter\FilesXlsx\Event\Exported' => array(
				function ($e) use ($c) {
					$c->filesxlsxUpdater->exported($e);
				},
			),
			// PDF
			'Domain\Adapter\PDF\Event\Exported' => array(
				function ($e) use ($c) {
					$c->pdfUpdater->exported($e);
				},
			),
			// Soap
			'Domain\Adapter\Soap\Event\Queried' => array(
				function ($e) use ($c) {
					$c->soapUpdater->queried($e);
				},
			),
			'Domain\Adapter\Soap\Event\Exported' => array(
				function ($e) use ($c) {
					$c->soapUpdater->exported($e);
				},
			),
			// Normalized
			'Domain\Adapter\Normalized\Event\Saved' => array(
				function ($e) use ($c) {
					$c->normalizedUpdater->saved($e);
				},
			),
		);
	}

}