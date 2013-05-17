<?php

namespace Domain;

use BDC\DiContainer;

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
		);
	}

}