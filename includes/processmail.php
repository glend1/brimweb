<?PHP 
require_once 'functions.php';
if (!fCanSee(isset($_SESSION['id']))) {
	$_SESSION['sqlMessage'] = 'You must be logged in to use this page!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
$strippedBody = trim(strip_tags($_POST['body']));
if (empty($_POST['subject']) || empty($strippedBody)) {
	$_SESSION['sqlMessage'] = 'Form incomplete!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
if (!($subject = fTextDatabase($_POST['subject'], 225))) {
	$_SESSION['sqlMessage'] = 'subject Invalid!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
if (!($body = fTextDatabase($_POST['body'], 'na', true))) {
	$_SESSION['sqlMessage'] = 'Body Invalid!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
if (isset($_POST['recipient'])) {
	$aTemp = explode(',', $_POST['recipient']);
	$sep = ' where ';
	$recipients = '';
	foreach ($aTemp as $users) {
		$temp = trim($users);
		if ($temp) {
			$recipients .= $sep . 'name = \'' . $temp . '\'';
			$sep = ' or ';
		};
	};
	$queryRecipient = 'select id from users' . $recipients;
	print($queryRecipient);	
	$dataRecipient = odbc_exec($conn, $queryRecipient);
	while(odbc_fetch_row($dataRecipient)) {
		$recipientsId[] = odbc_result($dataRecipient, 1);
	};
} else {
	$_SESSION['sqlMessage'] = 'Recipients Invalid!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
if (isset($recipientsId)) {
	$timeUTC = date_create($localTime);
	date_timezone_set($timeUTC, timezone_open('UTC'));
	$querySep = '';
	$queryInsert = 'insert into message (timestamp, fromid, toid, subject, message, readbit)
	values ';
	foreach ($recipientsId as $to) {
		$queryInsert .= $querySep . '( \'' . date_format($timeUTC, 'Y-m-d H:i:s') . '\', ' . $_SESSION['id'] . ', ' . $to . ', \'' . $subject . '\', \'' . $body . '\', 0)';
		$querySep = ', ';
	};
	print($queryInsert);
	odbc_exec($conn, $queryInsert);
	$_SESSION['sqlMessage'] = 'Mail sent!';
	$_SESSION['uiState'] = 'active';
} else {
	$_SESSION['sqlMessage'] = 'No valid recipients!';
	$_SESSION['uiState'] = 'error';
};
fRedirect();
?>