<?php
namespace xepan\base;

class View_User_AlreadyLoggedin extends \View{
	public $options = [];

	function init(){
		parent::init();

	}
	
	function defaultTemplate(){
		return [$this->options['already_loggedin_layout']];
	}
}	