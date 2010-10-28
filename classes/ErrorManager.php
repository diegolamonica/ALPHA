<?php

class ErrorManager extends Debugger {
	private $text = null;
	private $success = false;
	
	function setText($text, $success = false){
		$this->text = $text;
		$this->success = $success;
	}
	
	function getHTMLError(){
		if($this->success) return $this->getHTMLSuccess();
		if($this->text==null) return null;
		
		if($this->text=='') return '';
		return '<' . ERRORMANAGER_TAG_ELEMENT .' class=\'' . ERRORMANAGER_CLASS_NAME . '\'>'. $this->text . '</' . ERRORMANAGER_TAG_ELEMENT . '>';
	}
	function getHTMLSuccess(){
		
		if($this->text==null) return null;
		
		if($this->text=='') return '';
		return '<' . ERRORMANAGER_TAG_ELEMENT .' class=\'' . ERRORMANAGER_SUCCESS_CLASS_NAME . '\'>'. $this->text . '</' . ERRORMANAGER_TAG_ELEMENT . '>';
	}
	
	function getText(){
		return $this->text;
	}
}
?>