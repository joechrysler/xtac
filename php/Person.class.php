<?php
require_once 'config.php';
require_once 'Category.class.php';

class Person
{
	protected $categories = array();
	public $id;

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
					if ($subsubvalue->LdapName !== NULL AND $subsubvalue->LdapName !== 'NULL')
						$subsubvalue->LdapValue = array_key_exists($subsubvalue->LdapName, $inArray)?
							$inArray[$subsubvalue->LdapName]:
							null;

		return $this;
	}
	public function importMysqlData($inArray){
		foreach ($this->categories as $key => $value) 
			//TODO Cleanup for loops
			if ($value)
				foreach ($value as $subkey => $subvalue)
					foreach ($subvalue as $subsubvalue)
					if ($subsubvalue->MysqlName !== NULL AND $subsubvalue->MysqlName !== 'NULL' AND array_key_exists($subsubvalue->MysqlName, $inArray))
						$subsubvalue->MysqlValue = $inArray[$subsubvalue->MysqlName];

		return $this;
	}

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
	public function drawHistory($inArray){
		echo '<dl id="history">', "\n\t",
			'<h2>history</h2>', "\n\t";

		if (is_array($inArray))
			foreach ($inArray as $record)
				echo '<div class="dg">', "\n\t\t",
					'<dt class="timestamp">',@date("F j, Y - g:i a", @strtotime($record['TimeStamp'])),'</dt>', "\n\t\t\t",
					'<dd class="staff">',$record['StaffMember'],'</dd>',"\n\t\t\t",
					'<dd class="comments">',$record['Comments'],'</dd>',"\n\t\t",
					'</div>';

		require_once 'forms/historyForm.html';
		echo '</dl>';

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
	public function setID($inID) {
		$this->id = $inID;

		return $this;
	}
}
