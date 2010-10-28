<?php
if(!class_exists('Xml2array')){
	class Xml2array{
		private $buffer = '';
		private $array = array();
		public function fromFile($file){
			$this->buffer = file_get_contents($file);
		}
		
		public function fromString($buffer){
			$this->buffer = $buffer;
		}
		
		public function parse(){
			$contents = $this->buffer;
			
			if(!$contents) return array();
		
		    if(!function_exists('xml_parser_create')) {
		        return array();
		    }
		
		    //Get the XML parser of PHP - PHP must have this module for the parser to work
		    $parser = xml_parser_create('');
		    xml_parser_set_option($parser, XML_OPTION_TARGET_ENCODING, "UTF-8"); # http://minutillo.com/steve/weblog/2004/6/17/php-xml-and-character-encodings-a-tale-of-sadness-rage-and-data-loss
		    xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
		    xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
		    
		    xml_parse_into_struct($parser, trim($contents), $xml_values);
		    xml_parser_free($parser);
		
		    if(!$xml_values) return; 
			$parent = array();
	
			$this->__parse($parent, $xml_values);
		    $this->array = $this->__purge($parent);  
		    return $this->array;
		}
		
		private function __parse(&$__parent, $__data, $__level=1, $__startFrom=0){
	
			for($i=$__startFrom; $i<count($__data); $i++){
				unset($tag);
				unset($level);
				unset($value);
				unset($attributes);
				extract	($__data[$i]);
				
				if($__level == $level ){
					if($type=='open' || $type =='complete'){
						if($__parent==null) $__parent = array();
						if(!isset($attributes)) $attributes = array();
						if(!isset($value)) 		$value = '';
						if(isset($__parent[$tag])){
							
							if(isset($__parent[$tag]['attributes'])) $__parent[$tag] = array($__parent[$tag]);
							$__parent[$tag][] = array('attributes'=>$attributes, 'value'=>$value);
							
							$__currentParent = &$__parent[$tag][count($__parent[$tag])-1];
						}else{
							$__parent[$tag] = array('attributes'=>$attributes, 'value'=>$value);
							$__currentParent = &$__parent[$tag]; 
						}
						$__selfParent = &$__currentParent;
					}
				}else{
					
					if($__level+1 == $level){
							
						$i = $this->__parse($__currentParent, $__data, $__level+1, $i);
						$i -= 1;
						
					}else if($__level > $level){
						if(isset($__selfParent)) $__currentParent = $__selfParent;
						break;
						
					}
				}
			}
			return $i;
		}
		
		private function __purge($xmlArray){
			if(!is_array($xmlArray)) return $xmlArray;
			
			foreach($xmlArray as $key => $value){
				if(is_array($value)){
					$value = $this->__purge($value);
				}
				if(!is_string($value)){
					if(isset($value['attributes'])){
						if(count($value['attributes'])==0) unset($value['attributes']);
						if($value['value'] == '') unset($value['value']);
						if(count($value)==1 && isset($value['value'])) $value = $value['value'];
					}
				}
				$xmlArray[$key] = $value;
			}
			return $xmlArray;
			
		}
		
	}
}
?>