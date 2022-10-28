<?PHP
require_once 'functions.php';
$sqlPrep = fTextDatabase($_POST['group'], 61);
if (isset($_POST['groupadmin'])) {
	$groupAdmin = $_POST['groupadmin'];
} else {
	$groupAdmin = 'none';
};
if (!fCanSee($_SESSION['permissions']['group'][$groupadmin] >= 300)) {
	$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
if ($sqlPrep != '') {
	$queryNewUser = 'insert into grouptable (name)
	values ( \'' . $sqlPrep . '\')';
	odbc_exec($conn, $queryNewUser);
	$queryGetId = 'select id from grouptable where name = \'' . $_POST['group'] . '\'';
	$dataGroupId = odbc_exec($conn, $queryGetId);
	while (odbc_fetch_row($dataGroupId)) {
		$iGroupId = odbc_result($dataGroupId, 1);
	};
	if ($_POST['groupadmin'] == 'none' && $_SESSION['id'] == 1) {
		$queryNewSelfPermissions = 'insert into permissions (groupadminfk, groupfk, level)
		values (' . $iGroupId . ', ' . $iGroupId . ', 300)';
	} else if ($_POST['groupadmin'] == 'none' && $_SESSION['id'] != 1) {
		$_SESSION['sqlMessage'] = 'Group creation failed!';
		$_SESSION['uiState'] = 'error';
		fRedirect();
	} else {
		$queryNewPermissions = 'insert into permissions (groupadminfk, groupfk, level)
		values (' . $iGroupId . ', ' . $_POST['groupadmin'] . ', 300)';
		odbc_exec($conn, $queryNewPermissions);
		$queryNewSelfPermissions = 'insert into permissions (groupadminfk, groupfk, level)
		values (' . $iGroupId . ', ' . $iGroupId . ', 100)';
	};
	odbc_exec($conn, $queryNewSelfPermissions);
	$_SESSION['sqlMessage'] = 'Group ' . $_POST['group'] . ' was sucessfully Created!';
	$_SESSION['uiState'] = 'active';
	odbc_close($conn);
} else {
	$_SESSION['sqlMessage'] = 'Group creation failed!';
	$_SESSION['uiState'] = 'error';
};
fRedirect(); ?>