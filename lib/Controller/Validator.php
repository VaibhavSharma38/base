<?php



namespace xepan\base;

class Controller_Validator extends \Controller_Validator{

    function init(){
        parent::init();
        $this->is_mb=false;
    }

    function rule_required($a)
    {
        if ($a==='' || $a===false || $a===null) {
            return $this->fail('must not be empty');
        }
    }
	
	function rule_unique($a,$field){

        $q = clone $this->owner->dsql();

        $result = $q
                ->where($field, $a)
                ->where($q->getField('id'),'<>', $this->owner->id)
                ->field($field)
                ->getOne();

        if($result !== null) return $this->fail('Value "{{arg1}}" already exists', $a);
    }

    function rule_unique_in_epan($a,$field){
        
        $q = clone $this->owner->dsql();

        $result = $q
                ->where($field, $a)
                ->where($q->getField('id'),'<>', $this->owner->id)
                ->where($q->expr('[0] = [1]',[$this->owner->getElement('epan_id'),$this->app->epan->id]))
                ->field($field)
                ->getOne();

        if($result !== null) return $this->fail('Value "{{arg1}}" already exists', $a);
    }

    function rule_max_in_epan($a,$field){
         $q = clone $this->owner->dsql();

        $result = $q
                ->where($field, $a)
                ->where($q->getField('id'),'<>', $this->owner->id)
                ->where($q->expr('[0] = [1]',[$this->owner->getElement('epan_id'),$this->app->epan->id]))
                ->field($q->expr('MAX([0]',[$field]))
                ->getOne();
        if($a <= $result) $this->fail('Value "{{arg1}}" is not maximum',$a);
    }

    function mb_str_to_lower($a)
    {
        return ($this->is_mb) ? mb_strtolower($a, $this->encoding) : strtolower($a);
    }

    function mb_str_to_upper($a)
    {
        return ($this->is_mb) ? mb_strtoupper($a, $this->encoding) : strtoupper($a);
    }

    function mb_str_to_upper_words($a)
    {
        if ($this->is_mb)
        {
            return mb_convert_case($a, MB_CASE_TITLE, $this->encoding);
        }

        return ucwords(strtolower($a));

    }

    function mb_truncate($a, $len, $append = '...')
    {
        if ($this->is_mb)
        {
            return mb_substr($value, 0, $len, $this->encoding) . $append;
        }

        substr($value, 0, $limit).$end;
    }

    function rule_len($a)
    {
         return mb_strlen($a, $this->encoding);
    }

    function mb_str_len($str){
        return mb_strlen($str);
    }
}