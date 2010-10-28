<?php
/**
 * @name GenericImageHandler
 * @version 1.0
 * @package ALPHA
 * @author Federico Volpini
 */

require_once 'interfaces/iImageHandler.php';
require_once 'Debug.php';

/**
 * @desc Classe che si occupa della gestione delle immagini.
 * @desc Formati supportati: GIF, JPEG, PNG
 * @author Federico Volpini
 *
 */
class GenericImageHandler extends Debugger implements iImageHandler{
	/**
	 * Identificatore di immagine
	 * @var resource
	 */
	private $image;
	
	/**
	 * Tipo di immagine
	 * @var string
	 */
	private $type;
	
	/**
	 * Larghezza dell'immagine
	 * @var int
	 */
	private $width;
	
	/**
	 * Altezza dell'immagine
	 * @var int
	 */
	private $height;
	
	/**
	 * Mime-Type dell'immagine
	 * @var string
	 */
	private $headerContents;

	/**
	 * Nome del file immagine
	 * @var string
	 */
	private $fileName;
	
	function setImage($filename){
		
		$dbg = ClassFactory::get('Debug');
		$dbg->setGroup(__CLASS__);
		$dbg->write('Entering ' . __FUNCTION__, DEBUG_REPORT_FUNCTION_ENTER);
		$dbg->writeFunctionArguments(func_get_args());
		
		$this->setImageInfo($filename);
		
		switch ($this->type){
			case 'gif':
				$this->image = imagecreatefromgif($filename);
				break;
			
			case 'jpeg':
				$this->image = imagecreatefromjpeg($filename);
				break;
			
			case 'png':
				$this->image = imagecreatefrompng($filename);
				break;
		}
		//imageantialias($this->image, true);
		
		$dbg->write('Exiting ' . __FUNCTION__, DEBUG_REPORT_FUNCTION_EXIT);
		
	}
	
	function getType(){
		
		$dbg = ClassFactory::get('Debug');
		$dbg->setGroup(__CLASS__);
		$dbg->write('Entering ' . __FUNCTION__, DEBUG_REPORT_FUNCTION_ENTER);
		$dbg->writeFunctionArguments(func_get_args());
		
		$dbg->write('Exiting ' . __FUNCTION__, DEBUG_REPORT_FUNCTION_EXIT);
		
		return $this->type;
	}
	
	function getWidth(){
		
		$dbg = ClassFactory::get('Debug');
		$dbg->setGroup(__CLASS__);
		$dbg->write('Entering ' . __FUNCTION__, DEBUG_REPORT_FUNCTION_ENTER);
		$dbg->writeFunctionArguments(func_get_args());
		
		$dbg->write('Exiting ' . __FUNCTION__, DEBUG_REPORT_FUNCTION_EXIT);
		
		return $this->width;
	}
	
	function getHeight(){
		
		$dbg = ClassFactory::get('Debug');
		$dbg->setGroup(__CLASS__);
		$dbg->write('Entering ' . __FUNCTION__, DEBUG_REPORT_FUNCTION_ENTER);
		$dbg->writeFunctionArguments(func_get_args());
		
		$dbg->write('Exiting ' . __FUNCTION__, DEBUG_REPORT_FUNCTION_EXIT);
		
		return $this->height;
	}
	
	function resize($mx, $my, $stretch = false, $force = false){
		
		$dbg = ClassFactory::get('Debug');
		$dbg->setGroup(__CLASS__);
		$dbg->write('Entering ' . __FUNCTION__, DEBUG_REPORT_FUNCTION_ENTER);
		$dbg->writeFunctionArguments(func_get_args());
		
		if($mx<$this->width || $my<$this->height || $stretch || $force){
			$s = min($mx/$this->width, $my/$this->height);
			if($stretch){
				$newWidth = $mx;
				$newHeight = $my;
			}
			else{
				$newWidth = floor($s*$this->width);
				$newHeight = floor($s*$this->height);
			}
			$tmpImg = imagecreatetruecolor($newWidth, $newHeight);
			imagecopyresized($tmpImg, $this->image,0,0,0,0,
			$newWidth, $newHeight, $this->width, $this->height);
			imagedestroy($this->image);
			$this->image = $tmpImg;
		}
		
		$dbg->write('Exiting ' . __FUNCTION__, DEBUG_REPORT_FUNCTION_EXIT);
		
	}
	
	function draw(){
		
		$dbg = ClassFactory::get('Debug');
		$dbg->setGroup(__CLASS__);
		$dbg->write('Entering ' . __FUNCTION__, DEBUG_REPORT_FUNCTION_ENTER);
		$dbg->writeFunctionArguments(func_get_args());
		
		header($this->headerContents);
		switch($this->type)
		{
			case 'gif':
				imagegif($this->image);
				break;
			
			case 'jpeg':
				imagejpeg($this->image);
				break;
			
			case 'png':
				imagepng($this->image);
				break;
		}
		
		$dbg->write('Exiting ' . __FUNCTION__, DEBUG_REPORT_FUNCTION_EXIT);
		
	}
	
	function save($filename = ''){
		
		$dbg = ClassFactory::get('Debug');
		$dbg->setGroup(__CLASS__);
		$dbg->write('Entering ' . __FUNCTION__, DEBUG_REPORT_FUNCTION_ENTER);
		$dbg->writeFunctionArguments(func_get_args());
		
		if($filename=='') $filename=$this->fileName;
		$ext = substr($filename, strrpos($filename, '.') + 1);
		switch($ext)
		{
			case 'gif':
				imagegif($this->image,$filename);
				break;
			
			case 'jpeg':
				imagejpeg($this->image,$filename);
				break;
			
			case 'png':
				imagepng($this->image,$filename);
				break;
		}
		
		$dbg->write('Exiting ' . __FUNCTION__, DEBUG_REPORT_FUNCTION_EXIT);
		
	}
	/**
	 * Funzione privata. Memorizza i dati dell'immagine quali larghezza, altezza, tipo e mime-type.
	 * @param $filename Il nome del file 
	 */
	private function setImageInfo($filename){
		
		$dbg = ClassFactory::get('Debug');
		$dbg->setGroup(__CLASS__);
		$dbg->write('Entering ' . __FUNCTION__, DEBUG_REPORT_FUNCTION_ENTER);
		$dbg->writeFunctionArguments(func_get_args());
		
		$info = getimagesize($filename);
		$this->width = $info[0];
		$this->height = $info[1];
		$this->headerContents = 'Content-type: '.$info['mime'];
		$this->type = substr($info['mime'], strrpos($info['mime'], '/') + 1);
		$this->fileName = $filename; 
		$dbg->write('Exiting ' . __FUNCTION__, DEBUG_REPORT_FUNCTION_EXIT);
		
	}
	
}

?>