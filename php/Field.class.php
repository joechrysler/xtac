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
		// In order to speed up output, draw() sends its output to a $buffer
		// variable instead of directly echoing it.  The mutable nature of the
		// buffer as opposed to the output stream also makes the consistency
		// checking and rules application easier to write.
		//
		// ConsistencyCheck just makes sure that data from ldap matches data
		// in the xtac database.
		//
		// ApplyRules is just a handy place to store any field-specific logic
		// that doesn't neatly fit anywhere else in the code.  It's written
		// as a select-case statement on the ldapname field so adding rules
		// like "ldap field cn needs to be marked as an error if it's not 7
		// characters long" (totally fictitious) is simple.
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
		// This function takes a field that is part of a multi-value set
		// and puts it, along with the proper html tags in the display buffer 
		//
		// What's a MultiValue field?
		//   A few of the fields in xtac only make sense visually when they're
		//   paired with another or a couple other fields.  The most obvious
		//   one is street addresses.  Having a separate line-item for 
		//   address, city, state and zip didn't make sense, so they're stored
		//   in the fields database with a multiValue flag.  That flag tells
		//   this little bit of code to draw the group of fields as a single
		//   visual unit.  The other obvious example is first name, last name,
		//   middle initial.  They make way more sense if viewed together.
		$buffer = '';

		if ($this->HtmlParentID !== $multiItemId) {
			$firstItem = true;
			$multiItemId = $this->HtmlParentID;
		}
		else
			$firstItem = false;

		// This tag *should* be a <dg>, but when I wrote this bit, <dg> wasn't
		// properly supported by firefox and IE.  Chrome rendered it fine, but
		// Chrome has virtually no market share.
		// When the widely used versions of FF and IE support <dg><dt><dd></dg>
		// lists properly, feel free to change the div to a <dg>, the heading
		// <li> to a <dt> and the data <li> to a <dd>
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
		// Fields don't make much sense if they aren't every shown on screen.
		// This function adds the current node and some html to the buffer.

		$buffer = '';

		// This code is a bit of a leak from the drawMultiValue function.
		// That function needs to know whether the field it's currently
		// drawing is the first in a series, or not.  The $firstItem flag
		// takes care of that bit and is ordinarily set by the multi-value
		// function, but if a single-value field follows the last element
		// of a multi-value field, the $firstItem flag doesn't ever reset.
		// This fixes that bug.
		// Yes, it's leaky.  Yes, multi-value should take care of itself.
		// But I'm just an intern and multi-value is a terribly irresponsible
		// function.  Feel free to refactor.
		if ($firstItem === true)
			$firstItem = false;

		// This tag *should* be a <dg>, but when I wrote this bit, <dg> wasn't
		// properly supported by firefox and IE.  Chrome rendered it fine, but
		// Chrome has virtually no market share.
		// When the widely used versions of FF and IE support <dg><dt><dd></dg>
		// lists properly, feel free to change the div to a <dg>, the heading
		// <li> to a <dt> and the data <li> to a <dd>
		$buffer .= "\t<div class=\"dg ";

		// Anything with class="error" is visually distinct from squeaky-clean
		// output.
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
		// special html classes for fields are stored in the fields database
		// and come back to this code as an array.  This function takes that
		// array, converts it into a properly formated string and returns the
		// result
		return (sizeof($this->HtmlClass) > 0)?
			'class="' . implode(' ', $this->HtmlClass) . ' ' . $inClass . ' " ':
			"class=\"$inClass\"";
	}
	private function Title() {
		// In this app, titles are used mostly on error-prone data.
		if ($this->HtmlTitle !== null)
			return "title=\"$this->HtmlTitle\" ";
	}
	private function ID($inID) {
		return "id=\"$inID\" ";
	}
	private function Value() {
		// There are a few fields which only show up in Ldap and there could
		// theoretically be some which only show up in xtac.  This function
		// checks for those kinds of fields and displays the proper value.
		return ($this->MysqlValue !== null)?
			trim($this->MysqlValue):
			trim($this->LdapValue);
	}


	//## Backend Processing ###########################################
	private function ConsistencyCheck(){
		// If the field only appears in one of the databases, then checking it
		// against the other doesn't make sense.  Assume single-database fields
		// are valid.
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

				// Make sure both values are visible in the title of this field.
				$this->HtmlTitle  = 'Ldap: ' . $this->LdapValue . '   Mysql: ' . $this->MysqlValue;
				return false;
			}
		}
		else return true;
	}
	private function ApplyRules(){
		// This is a relatively convenient place to put any business rules
		// which don't cleanly fit elsewhere in the code.  For instance, if
		// the ldap field "accountdisabled" has a value equivalent to true (Y)
		// then we should flag the field as an error so that it's very obvious
		// to tech support personel that the account is disabled.  Stuff like that
		// belongs here.
		switch ($this->LdapName) {
			case 'logindisabled':
				if ($this->LdapValue === 'Y') {
					$this->HtmlClass[] = 'error';
					$this->HtmlTitle = 'Account Disabled!';
				}
				break;
			case 'svsuactivated':
				if ($this->LdapValue === null)
					$this->LdapValue = 'F';
				break;
			case 'pwmresponseset':
				if ($this->LdapValue === null)
					$this->LdapValue = 'F';
				else
					$this->LdapValue = 'T';
				break;
		}
	}
	private function CleanCssClasses() {
		// I haven't quite figured out when to expect mysql to actually
		// put a literal null into a field vs. the string "NULL"
		//
		// This function just checks for the string "NULL" and sets the
		// value to an empty string (closer to literal null).
		if ($this->HtmlClass === 'NULL')
			$this->HtmlClass = '';
	}


	//## Simple binary checks #########################################
	private function MultiValue(){
		// fields which only make sense when combined with other fields
		// (first, last, middle; city, state, zip; etc.) have multi as
		// a class.  This function checks for the presence of that class.
		foreach ($this->HtmlClass as $value) {
			if ($value === 'multi')
				return true;
		}
		return false;
	}
	public function IsEmpty() {
		// This one's pretty self-explanatory
		if ($this->LdapValue === null && $this->MysqlValue === null)
			return true;
		else
			return false;
	}

	private function hasError() {
		// Fields with an error will have the html class "error"
		// This function checks for its presence
		foreach ($this->HtmlClass as $value) {
			if ($value === 'error')
				return true;
		}
		return false;
	}

		private function allowableInconsistency() {
			// There are a few fields which are stored differently in ldap
			// and xtac, but shouldn't be considered invalid because of that.
			// xtac uses slashes in the birthdate field, ldap doesn't, but that
			// doesn't mean that their actual values don't match.
			//
			// This function checks for those kinds of allowable inconsistencies.
			
			if	($this->LdapName === 'birthdate') {
				$LdapValueWithSlashes = substr($this->LdapValue, 0,2) . '/' . substr($this->LdapValue, 2,2) . '/' . substr($this->LdapValue, 4,2);
				if ($LdapValueWithSlashes === $this->MysqlValue)
					return true;
			}

			return false;
		}

	//## Constructor ##################################################
	public function field($attribute){
		// This function takes an associative array as a parameter and sorts it out into the proper class variables.
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
