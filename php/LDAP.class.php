<?php
require_once 'XtacData.class.php';

class LDAP extends XtacData {
	// ## Class-variables ######################################
	protected $host;
	protected $database;
	protected $hasConnection;
	protected $connection;
	protected $lastBindDn;
	protected $lastBindPw;


	//## Connection Management #################################
	public function connect($inUserName, $inPassword){
		$this->hasConnection = true;
		$this->connection = ldap_connect($this->host);

		$this->bind($inUserName, $inPassword);

		$this->connection;

		return $this;
	}
	public function disconnect() {
		ldap_unbind($this->connection);

		return $this;
	}


	//## Parent Methods ########################################
	public function getUser($inUsername, $inCol, &$outUser){
		$ldapArray = array();
		$tempResult = array();
		$ldapResults = null;

		$ldapResults = $this->search('o=svsu', "(cn=$inUsername)", $inCol);
		foreach ($ldapResults[0] as $key => $value)
			if (!is_numeric($key)) {
				$ldapArray[$key] = is_array($value)?
					$value[0]:
					$value;
				$ldapArray[$key] = parent::translateValue($ldapArray[$key]);
			}

		$outUser = $ldapArray;

		return $this;
	}

	public function resetPassword($inUsername) {
		$info = null;
		$newPass = '';
		$update = array();
		$userdn = '';
		$FileHandle = null;
		$LogMessage = '';

		$info = $this->search('o=svsu', "(cn=$inUsername)");

		if ($info['count'] === 1) {
			$userdn = $info[0]['dn'];
			$newPass = generatePassword();

			// Prepare updates:
			$update['userPassword'] = $newpass;
			$update['loginGraceLimit'] = '20';
			$update['loginGraceRemaining'] = '5';
			$update['passwordExpirationTime'] = '20080223132322Z';

			//Fail with an error message if the update is unsuccesful
			$this->update($userdn, $update);

			//Log String
			$LogMessage = date('r') . ' - ' . $_SERVER['PHP_AUTH_USER'] . ' reset the password on ' . $userdn . "\n";
			file_put_contents('/var/log/passreset.log', $LogMessage, FILE_APPEND);

			echo $newPass;
		}

		return $this;
	}

	//## Distinct methods ######################################
	public function rebind() {
		$this->bind($this->lastBindDn, $this->lastBindPw);

		return $this;
	}


	//## Private Methods #######################################
	private function bind($inDn, $inPassword) {
		//Set Variables in case we need to reconnect
		$this->lastBindDn = $inDn;
		$this->lastBindPw = $inPassword;

		ldap_bind($this->connection, $inDn, $inPassword);

		return true;
	}
	private function search($inBase, $inFilter, $inArrAttribute = null) {

		$ldapSearch = false;       // Such-Handler
		$arrResult = Array();      // Suchergebnisse

		// Fail if there is no open connection to LDAP
		if (!$this->hasConnection())
			return false;

		$ldapSearch = (is_array($inArrAttribute))? 
			ldap_search($this->connection, $inBase, $inFilter, $inArrAttribute):
			ldap_search($this->connection, $inBase, $inFilter);

		return ldap_get_entries($this->connection, $ldapSearch);
	}

	private function update($inUserName, $inArrUpdate) {
		return (@ldap_modify($this->connection, $inUserName, $inArrUpdate))?
			true:
			false;
	}
	
}
?>
