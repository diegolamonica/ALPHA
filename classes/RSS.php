<?php
class RSS extends Debugger{
	
	public $title = '';
	public $pubDate = '';
	private $generator =  'ALPHA-1.2'; #'ALPHA 1.2 RSS Builder';
	public $language = 'it';
	public $description = '';
	
	private $items = array();
	
	public function RSS(){
		$this->pubDate = date('D, d M Y H:i:s O');
	}
	
	public function addItem(
		$title, $link, $pubDate, 
		$dcCreator, $guid, $description
	){
		
		$this->items[] = array(
			'title' 		=> $title,
			'link' 			=> $link,
			'pubDate' 		=> $pubDate,
			'dc:creator' 	=> $dcCreator,
			'guid'			=> $guid,
			'description'	=> $description
		);
		
	}
	
	public function render(){
		header('Content-type: text/xml; encoding=UTF-8');
		echo('<?xml version="1.0" encoding="UTF-8"?>' ."\n");
		?>
<rss xmlns:content="http://purl.org/rss/1.0/modules/content/" xmlns:dc="http://purl.org/dc/elements/1.1/" version="2.0">
	<channel>
		<title><?php echo($this->title)?></title>
		<link><?php echo($this->link)?></link>
		<pubDate><?php echo($this->pubDate)?></pubDate>
		<generator><?php echo($this->generator)?></generator>
		<language><?php echo($this->language)?></language>
		<description><?php echo($this->description)?></description>
	<?php
	foreach($this->items as $index => $item){
		?>
		<item>
			<title><![CDATA[<?php echo str_replace("\n","",$item['title'])?>]]></title>
			<link><?php echo htmlentities($item['link'])?></link>
			<pubDate><?php echo($item['pubDate'])?></pubDate>
			<dc:creator><?php echo($item['dc:creator'])?></dc:creator>
			<guid><?php echo htmlentities($item['guid'])?></guid>
			<description><![CDATA[<?php echo nl2br($item['description'])?>]]></description>
		</item>
	<?php
	}
	?>
</channel>
</rss>
		<?php 
	}

}
?>