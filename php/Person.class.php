<?php
require_once 'config.php';
require_once 'Category.class.php';
require_once 'Field.class.php';

class Person
{
	protected $categories = array();
	public    $id;
	private   $CheckedUnix = false;
	
	// WARNING: Fluent Interface Ahead!
	//   This class tries to conform to the fluent-interface paradigm for public
	//   function returns.  See http://devzone.zend.com/article/1362 for an in 
	//   depth explanation.  The gist is that if every public    function returns 
	//   $this, then writing driver code that uses this class is ridiculously easy.
	//   
	//   The only gotcha is that if you need to send output somewhere, it has to 
	//   be through a pass-by-reference parameter.
	//
	//   private and protected functions can return values as normal, since they
	//   aren't called explicitely by any driver code.  A purely fluent app would
	//   force these to return $this too, but as far as I can see, there's no
	//   real good reason for it.
	//
	//   Any new public    functions must return $this, otherwise the whole idea of
	//   clean driver code goes to pieces.  If you write a public    function that
	//   doesn't return $this you force the end-coder to keep track of which
	//   public    functions return what...it's a mess.  Don't do it.

	// Import Data
	public    function importCategories($inArray){
		foreach ($inArray as $key => $value) {
			$categoryName = strtolower($value['Category']);
			if (!isset($this->categories[$categoryName]))
				$this->categories[$categoryName] = new category($categoryName);

			$this->categories[$categoryName]->attributeList[$value['HtmlID']] = new field($value);
		}


		return $this;
	}
	public    function importLdapData($inArray){
		foreach ($this->categories as $Category => $AttributeList)
			if ($AttributeList)
				foreach ($AttributeList as $FieldName => $FieldObject)
					foreach ($FieldObject as $FieldAttribute)
					if ($FieldAttribute->LdapName !== NULL && $FieldAttribute->LdapName !== 'NULL') {
						if ($FieldAttribute->LdapName === 'logindisabled' && @$inArray[$FieldAttribute->LdapName] === null)
							$FieldAttribute->LdapValue = 'N';
						else
							$FieldAttribute->LdapValue = array_key_exists($FieldAttribute->LdapName, $inArray)?
								$inArray[$FieldAttribute->LdapName]:
								null;
					}

		return $this;
	}
	public    function importMysqlData($inArray){
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



	// Display Data
	public    function draw(){
		$categories = array();
		$categories[0] = $this->categories['identity'];
		$categories[1] = $this->categories['location'];
		$categories[2] = $this->categories['contact'];
		$categories[3] = $this->categories['employee'];
		$categories[4] = $this->categories['catchall'];


		$this->validateUnixStatus();

		foreach ($this->categories as $category)
			if (!in_array($category, $categories))
				$categories[] = $category;

		foreach ($categories as $output)
			$output->draw();

		return $this;
	}
	public    function drawHistory($inArray, $HistoryItemsShown = 0){
		echo '<ul class="dictionary" id="history">', "\n\t";
		echo (count($inArray) > $HistoryItemsShown)?
			'<h2 class="moreAvailable">':
			'<h2>';
		echo	'history</h2>', "\n\t";

		if (is_array($inArray)){
			echo '<div id="scrollable">';
			$HistoryTail = array_slice($inArray, 0 - $HistoryItemsShown);
			if (is_array($HistoryTail))
				foreach ($HistoryTail as $record)
					$this->drawHistoryItem($record);

			echo '</div>';
		}

		require_once 'forms/historyForm.html';
		echo '</ul>';

		return $this;
	}

	public    function drawHistoryItems($inArray, $HistoryItemsShown){
		$History = array_slice($inArray, 0, count($inArray) - $HistoryItemsShown);
		if (is_array($History))
			foreach ($History as $record)
				$this->drawHistoryItem($record, 'newItem');

		return $this;
	}

	private   function drawHistoryItem($inRecord, $addClass = ''){
		echo '<div class="dg ',$addClass,'">', "\n\t\t",
			'<li class="head timestamp">',@date("F j, Y - g:i a", @strtotime($inRecord['TimeStamp'])),'</li>', "\n\t\t\t",
			'<li class="staff">',$inRecord['StaffMember'],'</li>',"\n\t\t\t",
			'<li class="comments">',$inRecord['Comments'],'</li>',"\n\t\t",
			'</div>';

		return $this;
	}
	public    function drawPasswordReset($inUsername){
		echo '<dl id="passwordReset">', "\n\t";
			//'<h2>password</h2>', "\n\t";

		// Draw the form for reseting a password
		echo '<form id="resetPassword" autocomplete="off">', "\n\t",
			'<input type="hidden" name="username" id="username" value="',$inUsername,'" />',"\n\t",
			'<input type="submit" class="button" id="cmdResetPassword" name="cmdResetPassword" value="Reset Password" />', "\n",
			'</form>';

		echo '</dl>';

		return $this;
	}
	public    function drawAddGraceLogins($inUsername) {
		echo '<dl id="GraceLoginsForm">', "\n\t";

		echo '<form id="addGraceLogins" autocomplete="off">', "\n\t",
			'<input type="hidden" name="username" id="username" value="',$inUsername,'" />',"\n\t",
			'<input type="submit" class="button" id="cmdAddGraceLogins" name="cmdAddGraceLogins" value="Add Grace Logins" />', "\n",
			'</form>';

		echo '</dl>';
	}

	public    function DisplayMSSoftwareEligibility($inEligible) {
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

	public    function locked(){
		return true;
	}
	public    function setID($inID){
		$this->id = $inID;

		return $this;
	}
	public    function isFullUser(){
		if (array_key_exists('Username',$this->categories['identity']->attributeList))
			return ($this->categories['identity']->attributeList['Username']->LdapValue === null)?
				false:
				true;

	}

	public    function validateUnixStatus() {
		if (array_key_exists('unixUidNum', $this->categories['status']->attributeList) &&
			array_key_exists('unixGidNum', $this->categories['status']->attributeList)) {
			$tempVar = array('HtmlID' => 'unixUidNum',
				'mysql' => NULL,
				'ldap' => 'uidnumber',
				'LdapValue' => 'F',
				'MysqlValue' => NULL,
				'HtmlClass' => array('error'),
				'HtmlParentID' => NULL,
				'HtmlName' => 'Unix Profile',
				'HtmlTitle' => NULL);
			$tempField = new field($tempVar);

			print_nice($this->categories['status']->attributeList['unixUidNum']);

			$this->categories['status']->attributeList['unixUidNum'] = NULL;
			$this->categories['status']->attributeList['unixUidNum'] = $tempField;
			print_nice($this->categories['status']->attributeList['unixUidNum']);



			$this->categories['status']->attributeList['unixUidNum']->LdapValue = 'T';
			$this->categories['status']->attributeList['unixGidNum']->LdapValue = 'T';
			$this->categories['status']->attributeList['unixUidNum']->HtmlClass = array();
			$this->categories['status']->attributeList['unixUidNum']->HtmlClass[] = 'error';
			$this->categories['status']->attributeList['unixUidNum']->HtmlClass[] = 'head';
			print_nice($this->categories['status']->attributeList['unixUidNum']->HtmlClass);
			print_nice($this->categories['status']->attributeList['unixGidNum']->HtmlClass);
			}
		else {
			//$this->categories['status']->attributeList['unixUidNum'] = array('HtmlID' => 'unixUidNum',
				//'MysqlName' => NULL,
				//'LdapName' => 'uidnumber',
				//'LdapValue' => 'F',
				//'MysqlValue' => NULL,
				//'HtmlClass' => array('error'),
				//'HtmlParentID' => NULL,
				//'HtmlName' => 'Unix Profile',
				//'HtmlTitle' => NULL);
			}
		return $this;
	}
	public    function hasGraceLogins(){
		if (array_key_exists('GraceLoginsRemaining',$this->categories['catchall']->attributeList))
			return ($this->categories['catchall']->attributeList['GraceLoginsRemaining']->LdapValue === '0'
			    ||  $this->categories['catchall']->attributeList['GraceLoginsRemaining']->LdapValue === null)?
				false:
				true;
	}
	
// *************** Testing Methods*******************
	public    function printLdapData($inArray){
		return $this;
	}
}
