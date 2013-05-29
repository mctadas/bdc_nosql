<?php

namespace ViewModel\Event;

use \MongoId;
use BDC\Models\BaseWriteModel;

class EventHandler extends BaseWriteModel
{
    public function handle($event)
    {
    	switch ($event->type){
    	case 'order':
    		$this->_getDiContainer()->serviceViewModel->getServices('internet');
	    	echo $event->data->user_id;
	    	echo $event->data->service;
	    	break;
    	}             
    }
        
}
