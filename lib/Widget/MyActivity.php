<?php

namespace xepan\base;

class Widget_MyActivity extends \xepan\base\Widget{
	function init(){
		parent::init();

		$this->report->enableFilterEntity('date_range');
		$this->grid = $this->add('Grid');
	}

	function recursiveRender(){
		$activity = $this->add('xepan\base\Model_Activity');
		$activity->addCondition('contact_id',$this->app->employee->id);

		if(isset($this->report->start_date))
			$activity->addCondition('created_at','>',$this->report->start_date);
		if(isset($this->report->end_date))
			$activity->addCondition('created_at','<',$this->app->nextDate($this->report->end_date));

		$this->grid->setModel($activity,['activity','created_at']);
		$this->grid->addPaginator(10);
		
		return parent::recursiveRender();
	}
}