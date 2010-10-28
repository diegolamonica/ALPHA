<?php
/**
 * @name ImageHandler
 * @version 1.0
 * @package ALPHA
 * @author Federico Volpini
 */

/**
 * @desc Classe che si occupa della gestione delle immagini.
 * @desc Formati supportati: GIF, JPEG, PNG
 * @author Federico Volpini
 *
 */
class ImageHandler{
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
	 * Immagazzina l'immagine nell'identificatore appropriato
	 * @param string $filename Il nome del file da aprire 
	 */
	function setImage($filename){
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
	}
	
	/**
	 * Restituisce il tipo di immagine (gif, jpeg, png)
	 * @return string
	 */
	function getType(){
		return $this->type;
	}
	
	/**
	 * Restituisce la larghezza dell'immagine
	 * @return string
	 */
	function getWidth(){
		return $this->width;
	}
	
	/**
	 * Restituisce l'altezza dell'immagine
	 * @return string
	 */
	function getHeight(){
		return $this->height;
	}
	
	/**
	 * Ridimensiona l'immagine. Il ridimensionamento è inteso come un rimpicciolimento
	 * dell'immagine. Per forzare l'ingrandimento bisogna impostare il parametro $force su true.
	 * @param int $mx Massima larghezza
	 * @param int $my Massima altezza
	 * @param bool $stretch Se impostato su true non mantiene le proporzioni.
	 * @param bool $force Forza il resize
	 */
	function resize($mx, $my, $stretch, $force){
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
	}
	
	/**
	 * Mostra l'immagine
	 */
	function draw(){
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
	}
	
	/**
	 * Salva l'immagine. Eventualmente riconverte l'immagine in base all'estensione del nome del file.
	 * @param $filename Il nome del file con cui si vuole salvare l'immagine
	 */
	function save($filename){
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
	}
	
	/**
	 * Memorizza i dati dell'immagine quali larghezza, altezza, tipo e mime-type.
	 */
	private function setImageInfo($filename){
		$info = getimagesize($filename);
		$this->width = $info[0];
		$this->height = $info[1];
		$this->headerContents = 'Content-type: '.$info['mime'];
		$this->type = substr($info['mime'], strrpos($info['mime'], '/') + 1);
	}
	
}
?>