<?php

/**
* description: ATK Model_Table is just left to put some global amendments needed in xEpan platform
* 
* @author : Gowrav Vishwakarma
* @email : gowravvishwakarma@gmail.com, info@xavoc.com
* @website : http://xepan.org
* 
*/

namespace xepan\base;

class Model_Table extends \Model_Table{

	function init(){
		parent::init();

		$this->addHook('afterAdd',function($e){			
			if($e->hasElement('epan_id') && isset($e->app->epan->id)) {
				if(!isset($e->epan_condition_set)) // comment to mute
	                $e->addCondition('epan_id',$e->app->epan->id);
	            $e->epan_condition_set = true;  // Comment to mute
            }
		});
	}
}
