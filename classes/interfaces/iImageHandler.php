<?php
interface iImageHandler{
	
/**
	 * Immagazzina l'immagine nell'identificatore appropriato
	 * @param string $filename Il nome del file da aprire 
	 */
	function setImage($filename);
	
	/**
	 * Restituisce il tipo di immagine (gif, jpeg, png)
	 * @return string
	 */
	function getType();
	
	/**
	 * Restituisce la larghezza dell'immagine
	 * @return string
	 */
	function getWidth();
	
	/**
	 * Restituisce l'altezza dell'immagine
	 * @return string
	 */
	function getHeight();
	
	/**
	 * Ridimensiona l'immagine. Il ridimensionamento è inteso come un rimpicciolimento
	 * dell'immagine. Per forzare l'ingrandimento bisogna impostare il parametro $force su true.
	 * @param int $mx Massima larghezza
	 * @param int $my Massima altezza
	 * @param bool $stretch Se impostato su true non mantiene le proporzioni.
	 * @param bool $force Forza il resize
	 */
	function resize($mx, $my, $stretch = false, $force = false);
	
	/**
	 * Mostra l'immagine
	 */
	function draw();
	
	/**
	 * Salva l'immagine. Eventualmente riconverte l'immagine in base all'estensione del nome del file.
	 * @param $filename Il nome del file con cui si vuole salvare l'immagine
	 */
	function save($filename = '');
	
}

?>