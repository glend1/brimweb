<?php
function getSoap($functionName, $args) {
	$client = new SoapClient('http://brimweb.brimcontrols.local:8080/BrimJava/WebServiceImplService?WSDL');
	$response = $client->__soapCall($functionName, array($args));
	if (isset($response->return)) {
		return $response->return;
	};
};
function soapDescription($desc, $array) {
	GLOBAL $args;
	$str = '<div class="hinttext">' . $desc . ', it takes ' . count($array) . ' argument(s), in order;
		<ul>';
	foreach ($array as $value) {
		$str .= '<li>';
		if (isset($args[$value])) {
			$str .= $args[$value];
		} else {
			$str .= 'Argument unknown.';
		}
		$str .= '</li>';
	};
	$str .= '</ul></div>';
	return $str;
};
function createSelect($array, $arg) {
	$select = '<select name="soap[arg' . $arg . ']">';
	if (!empty($array)) {
		foreach ($array as $key => $value) {
			$select .= '<option value="' . $key . '">' . $value . '</option>';
		};
	};
	$select .= '</select>';
	return $select;
};
function getData($table, $value, $distinct = false) {
	GLOBAL $conn;
	if (isset($value['key'])) {
		$valueCol = '';
		$sep = '';
		foreach ($value as $col) {
			$valueCol .= $sep . $col;
			$sep = ', ';
		};
		$query = 'select ';
		if ($distinct) {
			$query .= 'distinct ';
		};
		$query .= $valueCol . ' from ' . $table . ' order by ';
		if (isset($value['sort'])) {
			$query .= $value['sort'];
		} else {
			$query .= $value['key'];
		};
		$data = odbc_exec($conn, $query);
		while(odbc_fetch_row($data)) {
			$array[odbc_result($data,$value['key'])] = '';
			$sep = '';
			foreach ($value as $key => $col) {
				if ($key != 'key') {
					$array[odbc_result($data, $value['key'])] .= $sep . odbc_result($data, $col);
					$sep .= ', ';
				};
			};
			if ($array[odbc_result($data, $value['key'])] == '') {
				$array[odbc_result($data, $value['key'])] = odbc_result($data, $value['key']);
			};
		};
		if (isset($array)) {
			return $array;
		};
	};
};
if (isset($_GET['soap']) && isset($_GET['method'])) {
	if ($_GET['soap'] != null && $_GET['method'] != null) {
		if (fCanSee(isset($_SESSION['id']))) {
			if ($_SESSION['id'] != 1) {
				$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
				$_SESSION['uiState'] = 'error';
				fRedirect();
			} else {
				$stdOut .= getSoap($_GET['method'], $_GET['soap']);
			};
		};
	};
};
if (fCanSee(isset($_SESSION['id']))) {
	if ($_SESSION['id'] == 1) {
		$disabled = '';
	} else {
		$disabled = ' disabled ';
	};
} else {
	$disabled = ' disabled ';
};
$args = [
	"device" => 'The device as it appears in TopServer. If the device does not appear in the list BrimJava must be restarted.',
	"address" => 'The address as you would use in InTouch.',
	"io value" => 'The value you wish to change the address to.',
	"phone number" => 'The Number you wish to send a SMS to. Arrays Supported.',
	"sms message" => 'The Message you wish to send over SMS. You should use less than 160 characters.',
	"email" => 'The E-Mail you wish to send a message to. Arrays Supported.',
	"subject" => 'The E-Mail subject you wish to use.',
	"email message" => 'The E-Mail message in HTML to send.',
	"table" => 'The SQL Database to be used for the query.',
	"query" => 'The SQL query.',
	"path" => 'The File path on BrimWeb.',
	"filename" => 'The Filename to be used when saving the query.',
	"duration" => 'A "Key-to-Value" array, using both the duration type (date part) and value',
	"server" => 'The name of the collection of servers.',
	"host" => 'The Hostname of the server.',
	"condition" => 'The value used when compared against I/O.',
	"operator" => 'The type of comparison to be used.',
	"data" => 'A pre-configured stored I/O address.',
	"structure event" => 'A pre-configured stored condition/operator pair.',
	"schedule name" => 'The name used to identify the Event.',
	"start time" => 'The start time for the Event as Long in milliseconds.',
	"interval" => 'The Interval between Events as Long in milliseconds.',
	"event" => 'A unique identifier to specify a generic event. <a href="soap.eventlist.php">Lookup table</a>',
	"file sql" => 'A unique identifier to specify a SQL query event. <a href="soap.filelookup.php">Lookup table</a>',
	"duration type" => 'The date part to be subtracted from the current time.',
	"duration value" => 'The value to be subtracted from the current time.',
	"address group" => 'A unique value for identifying groups of users/groups. <a href="soap.users.php">Lookup table</a>',
	"user" => 'The BrimWeb username.',
	"group" => 'The BrimWeb group, unlike BrimWebs where permissions are inherited from child groups this is group users exclusively.'
];
?>