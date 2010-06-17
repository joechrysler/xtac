<?php
require_once 'config.php';
require_once 'Category.class.php';

class Person
{
	protected $categories = array();
	public $id;

	// Import Data
	public function importCategories($inArray){
		foreach ($inArray as $key => $value) {
			$categoryName = strtolower($value['Category']);
			if (!isset($this->categories[$categoryName]))
				$this->categories[$categoryName] = new category($categoryName);

			$this->categories[$categoryName]->attributeList[$value['HtmlID']] = new field($value);
		}


		return $this;
	}
	public function importLdapData($inArray){
		foreach ($this->categories as $key => $value)
			if ($value )
				foreach ($value as $subkey => $subvalue)
					foreach ($subvalue as $subsubvalue)
					if ($subsubvalue->LdapName !== NULL AND $subsubvalue->LdapName !== 'NULL') {
						if ($subsubvalue->LdapName === 'logindisabled' && $subsubvalue->LdapValue === null)
							$subsubvalue->LdapValue = 'N';
						else
							$subsubvalue->LdapValue = array_key_exists($subsubvalue->LdapName, $inArray)?
								$inArray[$subsubvalue->LdapName]:
								null;
					}

		return $this;
	}
	public function importMysqlData($inArray){
		foreach ($this->categories as $key => $value) 
			//TODO Cleanup for loops
			if ($value)
				foreach ($value as $subkey => $Category)
					foreach ($Category as $Attribute)
						if (
							$Attribute->MysqlName !== NULL &&
							$Attribute->MysqlName !== 'NULL' &&
							array_key_exists($Attribute->MysqlName, $inArray)
						)
						$Attribute->MysqlValue = $inArray[$Attribute->MysqlName];

		return $this;
	}



	// Drawing Functions
	public function draw(){
		$categories = array();
		$categories[0] = $this->categories['identity'];
		$categories[1] = $this->categories['location'];
		$categories[2] = $this->categories['contact'];
		$categories[3] = $this->categories['employee'];
		$categories[4] = $this->categories['catchall'];

		foreach ($this->categories as $category)
			if (!in_array($category, $categories))
				$categories[] = $category;

		foreach ($categories as $output)
			$output->draw();

		return $this;
	}
	public function drawHistory($inArray, $HistoryItemsShown = 0){
		echo '<dl id="history">', "\n\t";
		echo (count($inArray) > $HistoryItemsShown)?
			'<h2 class="moreAvailable">':
			'<h2>';
		echo	'history</h2>', "\n\t";

		if (is_array($inArray)){
			echo '<div class="scrollable">';
			$HistoryTail = array_slice($inArray, 0 - $HistoryItemsShown);
			if (is_array($HistoryTail))
				foreach ($HistoryTail as $record)
					$this->drawHistoryItem($record);

			echo '</div>';
		}

		require_once 'forms/historyForm.html';
		echo '</dl>';

		return $this;
	}

	public function drawHistoryItems($inArray, $HistoryItemsShown){
		$History = array_slice($inArray, 0, count($inArray) - $HistoryItemsShown);
		if (is_array($History))
			foreach ($History as $record)
				$this->drawHistoryItem($record, 'newItem');

		return $this;
	}

	private function drawHistoryItem($inRecord, $addClass = ''){
		echo '<div class="dg ',$addClass,'">', "\n\t\t",
			'<dt class="timestamp">',@date("F j, Y - g:i a", @strtotime($inRecord['TimeStamp'])),'</dt>', "\n\t\t\t",
			'<dd class="staff">',$inRecord['StaffMember'],'</dd>',"\n\t\t\t",
			'<dd class="comments">',$inRecord['Comments'],'</dd>',"\n\t\t",
			'</div>';

		return $this;
	}
	public function drawPasswordReset($inUsername){
		echo '<dl id="passwordReset">', "\n\t";
			//'<h2>password</h2>', "\n\t";

		// Draw the form for reseting a password
		echo '<form id="resetPassword" autocomplete="off">', "\n\t",
			'<input type="hidden" name="username" id="username" value="',$inUsername,'" />',"\n\t",
			'<input type="submit" id="cmdResetPassword" name="cmdResetPassword" value="Reset Password" />', "\n",
			'</form>';

		return $this;
	}

	public function drawPasswordResetForm() {
		echo '<dl id="passwordReset">', "\n\t",
			'<h2>reset password</h2>', "\n\t";

		require_once 'forms/passwordReset.html';

		return $this;
	}
	public function DisplayMSSoftwareEligibility($inEligible) {
		echo '<dl id="mscheckout">', "\n\t"
			, '<h2>microsoft software</h2>', "\n\t"
			, '<div class="dg">', "\n\t\t"
			, '<dt>Eligible</dt>', "\n\t\t"
			, '<dd>';

		echo ($inEligible === true)?
			'Yes':
			'No';

		echo '</dd>', "\n\t"
			, '</div>', "\n"
			, '</dl>';

	}

	public function locked(){
		return true;
	}
	public function setID($inID){
		$this->id = $inID;

		return $this;
	}
	public function isFullUser(){
		if (array_key_exists('Username',$this->categories['identity']->attributeList))
			return ($this->categories['identity']->attributeList['Username']->LdapValue === null)?
				false:
				true;

	}
}
