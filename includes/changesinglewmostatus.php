<?PHP
require_once 'functions.php';
if (!isset($_SESSION['id'])) {
	$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
if (!isset($_POST['status'])) {
	$_SESSION['sqlMessage'] = 'No status action selected!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
if (!isset($_POST['id'])) {
	$_SESSION['sqlMessage'] = 'No WMO selected!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
$noAction = true;
if ($_POST['status'] == 'close') {
	$noAction = false;
	$aStatus['statusfk'] = 2;
	$closeTime = date_create($localTime, timezone_open('UTC'));
	$aStatus['closedatetime'] = date_format($closeTime, 'Y-m-d H:i:s');
} else {
	$aStatus['closedatetime'] = NULL;
};
if ($_POST['status'] == 'wip') {
	$noAction = false;
	$aStatus['statusfk'] = 4;
	if (isset($_POST['sec'])) {
		if (fValidNumber($_POST['sec'])) {
			$aStatus['esttimesec'] = $_POST['sec'];
		} else {
			$_SESSION['sqlMessage'] = 'Estimated Time must be in Seconds!';
			$_SESSION['uiState'] = 'error';
			fRedirect();
		};
	} else {
		$_SESSION['sqlMessage'] = 'Form not complete!';
		$_SESSION['uiState'] = 'error';
		fRedirect();
	};
};
if ($_POST['status'] == 'open') {
	$noAction = false;
	$aStatus['statusfk'] = 3;
};
if ($noAction) {
	$_SESSION['sqlMessage'] = 'Invalid status action selected!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
$sUpdate = '';
$sep = '';
foreach ($aStatus as $key => $value) {
	if ($value === NULL) {
		$newValue = 'NULL';
	} else {
		$newValue = '\'' . $value . '\'';
	};
	$sUpdate .= $sep . $key . ' = ' . $newValue;
	$sep = ', ';
};
$queryUpdate = 'update wmo set ' . $sUpdate . ' where id = ' . $_POST['id'];
odbc_exec($conn, $queryUpdate);
$_SESSION['sqlMessage'] = 'WMO Status Changed!';
$_SESSION['uiState'] = 'active';
odbc_close($conn);
fRedirect();
?>