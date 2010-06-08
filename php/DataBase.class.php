<?php
require_once 'XtacData.class.php';
require_once 'print_nice.php';

class DataBase extends XtacData {

	// ## Class-variables ##############################################
	protected $host;
	protected $database;
	protected $hasConnection;
	protected $connection;
	protected $arrData = Array();
	protected $arrFieldname = Array();


	//## Connection Management #################################
	public function connect($inUsername, $inPassword) {
		$mysqlHandler = new mysqli($this->host, $inUsername, $inPassword, $this->database);

		$this->hasConnection = true;
		$this->connection = $mysqlHandler;

		return $this;
	}
	public function disconnect() {
		$this->connection->close();

		return $this;
	}


	//## Parent Methods ########################################
	public function getUser($inID, $inCol, &$outUser){
		$tempArray = array();

		$tempArray = $this->query('xtac', "PersonID like '%$inID'", '', $inCol);

		foreach ($tempArray[0] as $key => $value)
			$outUser[$key] = parent::translateValue($value);

		return $this;
	}


	//## Distinct methods ######################################
	public function getColumns($inTable, &$outColumns){

		$queryString = '';         // Abfragestring, der an die Datenbank gesendet wird
		$mysqlQuery = false;       // Das Ergebnis der Abfrage
		$mysqlHandler = false;     // Verbindungs-Handler
		$result = Array();         // Die Rueckgabe


		// Verbindung mit der Datenbank aufbauen
		if($this->hasConnection) {

			$queryString = 'show columns from ' . $this->database . '.' . $inTable . ';';

			$mysqlQuery = $this->connection->query($queryString);

			while($mysqlResult = $mysqlQuery->fetch_assoc())
				array_push(&$result, $mysqlResult);
		}
		else
			trigger_error('Not connected to database', E_USER_ERROR);

		$outColumns = $result;

		return $this;
	}
	public function getAttributes(&$outAttributes) {
		$outAttributes = $this->query('fields');

		return $this;
	}

	public function getRole($inLogin, &$outRole) {
		$result = Array();
		$result = $this->query('access_control', "login='$inLogin'");

		$outRole = (array_key_exists(0, $result))?
			$result[0]['role']:
			'library';

		return $this;
	}
	public function getAuthorizedFields($inLogin, &$outMysql, &$outLdap) {
		//## Initialize Variables #############################
		$result = Array();
		$role = '';

		$this->getRole($inLogin, $role);

		//## Perform Query ####################################
		$result = $this->query('roles', "role='$role'");

		//## Process Results ##################################
		$outMysql = $result[0]['MySQLFields'];
		$ldapString = $result[0]['LDAPFields'];
		$outLdap = explode(',',$ldapString);

		return $this;
	}
	public function canResetPassword($inLogin, &$result) {
		$query  = 'access_control.login = \'';
		$query .= $_SERVER['PHP_AUTH_USER'];
		$query .= '\' and access_control.role = roles.role';
		$tmpResults = array();

		$tmpResults = $this->query('roles, access_control',$query,'','roles.AllowPasswordReset');

		$result = $tmpResults[0]['AllowPasswordReset'];

		return $this;
	}
	public function checkMSEligibility($inID, &$outResult) {
		$result = array();

		$result = $this->query('xtac', "PersonID like '%$inID'", '', 'RegEmployee');
		$outResult = (@$result[0]['RegEmployee'] === 'Y')?
			true:
			false;

		return $this;
	}


	public function getUsername($inID, &$outUsername) {
		$result = Array();

		$result = $this->query('xtac', "PersonID like '%$inID'", '', 'Login');

		//## Fail silently if no-one has that userID
		if (!@$result[0])
			$outUsername = false;
		else
			$outUsername = $result[0]['Login'];

		return $this;
	}
	public function getHistory($inID, &$outHistory){
		$result = Array();
		$result = $this->query('history', "PersonID like '%$inID'", 'TimeStamp,StaffMember');

		if (empty($result[0]))
			$outHistory = null;
		else
			$outHistory = $result;

		return $this;
	}
	public function addComment($inID, $inUser, $inComment){
		$dataToInsert = array($inID, $inUser, 'NOW()', $this->connection->real_escape_string($inComment));
		$this->insert('history', $dataToInsert);

		echo '<div class="dg newComment">',
			'<dt class="timestamp">just now</dt>',
			'<dd class="staff">',$inUser,'</dd>',
			'<dd class="comments">',$inComment,'</dd>',
			'</div>';

		return $this;
	}
	public function searchUsers($inCriteria, &$outResults) {
		$searchTerms = array();

		// User is searching in "last,first" or "last, first" format
		if (strpos($inCriteria, ',')) {
			$searchTerms = explode(',', $inCriteria);
			$lastName = trim($searchTerms[0]);
			$firstName = trim($searchTerms[1]);

			$Filter = "Lastname like '%$lastName%' and ".
					"NickName like '%$firstName%'";
		}

		// User is searching for in "first last" format
		elseif (strpos($inCriteria, ' ')) {
			$searchTerms = explode(' ', $inCriteria);

			$Filter = "NickName like '%$searchTerms[0]%' and ".
					"LastName like '$searchTerms[1]%'";
		}

		else {
			$Filter = "Login like '$inCriteria%' or ".
					"LastName like '$inCriteria%' or ".
					"NickName like '$inCriteria%' or ".
					"PersonID like '%$inCriteria%'";
		}


		$SortOrder = 'LastName, NickName';
		$SelectedColumns = 'LastName, NickName, PersonID, Login';

		$outResults = $this->query('xtac', $Filter, $SortOrder, $SelectedColumns);

		return $this;
	}


	//## Generic Query ###################################################
	private function query($inTable, $inFilter = '', $inOrder = '', $inCol = '*'){

		$queryString = '';         // Abfragestring, der an die Datenbank gesendet wird
		$mysqlQuery = false;       // Das Ergebnis der Abfrage
		$mysqlHandler = false;     // Verbindungs-Handler
		$result = Array();         // Rueckgabe


		// Verbindung mit der Datenbank aufbauen
		if($this->hasConnection){
			// Abfrage starten
			$queryString = 'select ' . $inCol
				. ' from ' . $inTable;
			if($inFilter != '')
				$queryString .= ' where ' . $inFilter;
			if($inOrder != '')
				$queryString .= ' order by ' . $inOrder;
			$queryString .= ';';

			if (!$mysqlQuery = $this->connection->query($queryString))
				trigger_error("Query: " . $queryString . "\n", E_USER_NOTICE);

			$result = Array();
			while($mysqlResult = $mysqlQuery->fetch_assoc())
				array_push(&$result, $mysqlResult);
		}
		else {
			trigger_error('Not connected to database', E_USER_ERROR);
			$result = false;
		}

		return $result;
	}

	private function insert($inTable, $inData){
		$queryString = '';
		$firstItem = true;

		if($this->hasConnection){
			$queryString = 'insert into ' . $inTable . ' values(';
					foreach ($inData as $value) {
					if ($firstItem === false)
					$queryString .= ', ';
					else 
					$firstItem = false;

					$queryString .= (substr($value, -2) === '()')?
					$value:
					"'$value'";
					}

					$queryString .= ');';

			return $this->connection->query($queryString);
		}
	}
}
?>
