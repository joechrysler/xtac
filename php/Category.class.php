<?php

require_once('Field.class.php');

class category{
	public $attributeList = array();
	protected $HtmlID = NULL;
	protected $label = NULL;

	public function draw(){
		$firstItem = true;
		$multiItemId = '';
		$stream = '';


		// Child Nodes
		foreach ($this->attributeList as $attribute)
			$stream .= $attribute->draw($firstItem, $multiItemId);
		if ($stream !== '') {
			echo "<dl id=\"$this->HtmlID\">\n";
			echo "<h2>$this->label</h2>\n";
			echo $stream;

			// Category footer
			echo '</dl>',"\n\n";
		}
	}



	public function __construct($newlbl){
		$this->label = $newlbl;
		$this->HtmlID = $newlbl;
	}
}
?>
