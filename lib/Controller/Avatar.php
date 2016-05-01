<?php

/**
* description: Adds Avatar in template at {avatar} spot
* 
* @author : Gowrav Vishwakarma
* @email : gowravvishwakarma@gmail.com, info@xavoc.com
* @website : http://xepan.org
* 
*/

namespace xepan\base;

class Controller_Avatar extends \AbstractController{

	public $name_field = 'name';
	public $default_value = '';
	public $image_field = 'image';

	public $_options = [
			"border"=> [
				'color'=> '#ddd',
				'width'=> 3
			],
			'colors'=> ['#a3a948', '#edb92e', '#f85931', '#ce1836', '#009989'],
			'text'=> '#fff',
			'size'=> 60,
			'margin'=> 5,
			'middlename'=> false,
			'uppercase'=> true
		];
	public $extra_classes='';
	public $options=[];
	function init(){
		parent::init();
		

		$this->_options = $this->options + $this->_options;

		$this->style = $style = "
				color: ".$this->_options['text'].";
				border: ".$this->_options['border']['width'] ."px solid ". $this->_options['border']['color'].";
				display: inline-block;
				font-family: Arial,Helvetica Neue, Helvetica, sans-serif;
				font-size: ". $this->_options['size'] * 0.35 ."px;
				border-radius: ".$this->_options['size']."px;
				width: ".$this->_options['size']."px;
				max-width: ".$this->_options['size']."px;
				height: ".$this->_options['size']."px;
				line-height: ".$this->_options['size']."px;
				margin: ".$this->_options['margin']."px;
				text-align: center;
				text-transform : ".($this->_options['uppercase'] ? "uppercase" : "").";";
		
		$this->style = $style= preg_replace("/[\n\t]/", "", $style);

		if($this->owner instanceof \Lister){
			$obj = $this->owner;
			$this->manageLister($obj);
		}elseif($this->owner instanceof \CRUD){
			$obj= $this->owner->grid;
			$this->manageLister($obj);
		}else{
			$obj = $this->owner;			
			$this->manageView($obj);
		}
	}

	function manageLister($obj){
		$style= $this->style;
		$obj->addHook('formatRow',function($g)use($style){			
			if(!$g->model[$this->image_field]){
				$initials= trim($g->model[$this->name_field]);
				preg_match_all("/[A-Z]/", ucwords(strtolower($initials)), $initials);
				if(!$this->_options['middlename'] && count($initials[0])>2)
					$initials=[[$initials[0][0],$initials[0][count($initials[0])-1]]];
				$initials = implode("", $initials[0]);
				$stringhash = intval(substr(md5($initials), 0, 9), 16)%5;
				if(strlen($initials)>0)
					$style .= "background-color: ".$this->_options['colors'][$stringhash].";";
				else{
					$style .= "background-color: lightgray;";
					$initials = $this->default_value;
				}
				$g->current_row_html['avatar']= "<div class='namebadge  $this->extra_classes' style=\"position:relative; float:left; ".$style."\">".$initials."</div>";
			}else{
				$g->current_row_html['avatar']= "<img src='".$g->model[$this->image_field]."' alt='' />";
			}
		});

	}

	function manageView($obj){		
		$style= $this->style;
		if(!$obj->model['image']){
			$initials= trim($obj->model[$this->name_field]);
			preg_match_all("/[A-Z]/", ucwords(strtolower($initials)), $initials);
			if(!$this->_options['middlename'] && count($initials[0])>2)
				$initials=[[$initials[0][0],$initials[0][count($initials[0])-1]]];
			$initials = implode("", $initials[0]);
			$stringhash = intval(substr(md5($initials), 0, 9), 16)%5;
			if(strlen($initials)>0)
				$style .= "background-color: ".$this->_options['colors'][rand(0,count($this->_options['colors'])-1)].";";
			else{
				$style .= "background-color: lightgray;";
				$initials = $this->default_value;
			}
			$obj->template->trySetHTML('avatar',"<div class='namebadge $this->extra_classes' style=\"position:relative; float:left; ".$style."\">".$initials."</div>");
		} 
	}
}
