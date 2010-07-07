<?php

class field{
	public $HtmlID;
	public $MysqlName;
	public $LdapName;
	public $LdapValue = null;
	public $MysqlValue = null;
	public $HtmlClass = null;
	public $HtmlParentID = null;
	public $HtmlName = null;
	public $HtmlTitle = null;

	//## Display ######################################################
	public function draw(&$firstItem = false, &$multiItemId = '') {
		$buffer = '';
		$this->ConsistencyCheck();
		$this->ApplyRules();

		if (!$this->IsEmpty())
			$buffer = ($this->MultiValue())?
				$this->drawMultiValue($firstItem, $multiItemId):
				$this->drawSingleValue($firstItem);

		return $buffer;
	}
	private function drawMultiValue(&$firstItem, &$multiItemId) {
		$buffer = '';

		if ($this->HtmlParentID !== $multiItemId) {
			$firstItem = true;
			$multiItemId = $this->HtmlParentID;
		}
		else
			$firstItem = false;

		if ($firstItem) {
			$buffer .= "\t<div class=\"dg\">"
				. "\t <li "
				. $this->ID($this->HtmlParentID)
				. $this->nodeClass('head')
				. $this->Title()
				.  '>'
				. $this->HtmlName . ':'
				. "</li>\n";

			$buffer .= "\t\t\t".'<li '
				. $this->ID($this->HtmlID)
				. $this->nodeClass('first')
				. '>'
				. $this->Value()
				. "</li>\n";
		}
		else {
			$buffer .= "\t\t\t".'<li '
				. $this->ID($this->HtmlID)
				. $this->nodeClass()
				. '>'
				. $this->Value()
				. "</li>\n";
		}
		if ($this->HtmlID === 'LastName' || $this->HtmlID === 'ResidentialZip' || $this->HtmlID === 'MailingZip')
			$buffer .= '</div>';


		return $buffer;
	}
	private function drawSingleValue(&$firstItem) {
		$buffer = '';

		if ($firstItem === true)

			$firstItem = false;
		$buffer .= "\t<div class=\"dg ";
		if ($this->hasError())
			$buffer .= ' error" title="'
				.  $this->HtmlTitle;
		$buffer .= '" ';
		$buffer .= ">\n\t\t<li "
			. $this->ID($this->HtmlID)
			. $this->nodeClass('head')
			. '>'
			. $this->HtmlName . ':' ."</li>\n"
			. "\t\t\t<li>"
			. $this->Value()
			. "</li>\n"
			. '</div>';

		return $buffer;
	}

	private function nodeClass($inClass = '') {
		return (sizeof($this->HtmlClass) > 0)?
			'class="' . implode(' ', $this->HtmlClass) . ' ' . $inClass . ' " ':
			"class=\"$inClass\"";
	}
	private function Title() {
		if ($this->HtmlTitle !== null)
			return "title=\"$this->HtmlTitle\" ";
	}
	private function ID($inID) {
		return "id=\"$inID\" ";
	}
	private function Value() {
		return ($this->MysqlValue !== null)?
			trim($this->MysqlValue):
			trim($this->LdapValue);
	}


	//## Backend Processing ###########################################
	private function ConsistencyCheck(){
		if ($this->MysqlValue === null)
			return true;
		elseif ($this->LdapValue === null)
			return true;

		if ($this->MysqlValue !== null || $this->LdapValue !== null) {
			if (trim($this->LdapValue) === trim($this->MysqlValue))
				return true;
			else {
				// Certain bit-for-bit inconsistencies in mysql and ldap are allowable.
				// We first need to check to see if this is one of those allowable inconsistencies.
				// See definition of allowableInconsistency() for more information.
				if ($this->allowableInconsistency())
					return true;
				$this->HtmlClass[] = 'error';
				$this->HtmlTitle  = 'Ldap: ' . $this->LdapValue . '   Mysql: ' . $this->MysqlValue;
				return false;
			}
		}
		else return true;
	}
	private function ApplyRules(){
		switch ($this->LdapName) {
			case 'logindisabled':
				if ($this->LdapValue === 'Y') {
					$this->HtmlClass[] = 'error';
					$this->HtmlTitle = 'Account Disabled!';
				}
				break;
			
			default:
				// code...
				break;
		}
	}
	private function CleanCssClasses() {
		if ($this->HtmlClass === 'NULL')
			$this->HtmlClass = '';
	}


	//## Simple binary checks #########################################
	private function MultiValue(){
		foreach ($this->HtmlClass as $value) {
			if ($value === 'multi')
				return true;
		}
		return false;
	}
	public function IsEmpty() {
		if ($this->LdapValue === null && $this->MysqlValue === null)
			return true;
		else
			return false;
	}

	private function hasError() {
		foreach ($this->HtmlClass as $value) {
			if ($value === 'error')
				return true;
		}
		return false;
	}

		private function allowableInconsistency() {
			if	($this->LdapName === 'birthdate') {
				$LdapValueWithSlashes = substr($this->LdapValue, 0,2) . '/' . substr($this->LdapValue, 2,2) . '/' . substr($this->LdapValue, 4,2);
				if ($LdapValueWithSlashes === $this->MysqlValue)
					return true;
			}

			return false;
		}

	//## Constructor ##################################################
	public function field($attribute){
		if (is_array($attribute)) {
			$this->HtmlID = $attribute['HtmlID'];
			$this->MysqlName = $attribute['mysql'];
			$this->LdapName = $attribute['ldap'];
			$this->HtmlParentID = $attribute['HtmlParentID'];
			$this->HtmlName = $attribute['HtmlName'];
			$this->HtmlClass = array();
			if (strtolower($attribute['HtmlClass']) !== 'null')
				$this->HtmlClass = explode(" ",$attribute['HtmlClass']);

			if (array_key_exists('MysqlValue', $attribute))
				$this->MysqlValue = $attribute['MysqlValue'];
			if (array_key_exists('LdapValue', $attribute))
				$this->LdapValue = $attribute['LdapValue'];

			return 1;
		}
	}
}
?>
