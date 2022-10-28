<?PHP
require_once 'functions.php';
if (!fCanSee(@$_SESSION['permissions']['page'][10] >= 100)) {
	$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
if (isset($_GET['add'])) {
	if (!(isset($_GET['group']) xor isset($_GET['users']))) {
		$_SESSION['sqlMessage'] = 'Invalid selection!';
		$_SESSION['uiState'] = 'error';
		fRedirect();
	};
	$id = $_GET['add'];
};
if (isset($_GET['delete'])) {
	if (!isset($_GET['key'])) {
		$_SESSION['sqlMessage'] = 'Invalid selection!';
		$_SESSION['uiState'] = 'error';
		fRedirect();
	};
	$id = $_GET['delete'];
};
$sharePermission = false;
if ($_SESSION['id'] == 1) {
	$sharePermission = true;
};
$queryCheckPermission = 'select userfk, name from processtrend where id = ' . $id;
$dataCheckPermission = odbc_exec($conn, $queryCheckPermission);
if (odbc_fetch_row($dataCheckPermission)) {
	if ($_SESSION['id'] == odbc_result($dataCheckPermission, 1)) {
		$sharePermission = true;
	};
} else {
	$_SESSION['sqlMessage'] = 'Trend not found!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
if (!$sharePermission) {
	$_SESSION['sqlMessage'] = 'You do not have permission to share this trend!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
if (isset($_GET['add'])) {
	if (isset($_GET['group'])) {
		$testWhere = 'groupfk = ' . $_GET['group'];
		$insertColName = 'groupfk';
		$insertCol = $_GET['group'];
	};
	if (isset($_GET['users'])) {
		$testWhere = 'userfk = ' . $_GET['users'];
		$insertColName = 'userfk';
		$insertCol = $_GET['users'];
	};
	$queryUnique = 'select id from processtrendshare where processtrendfk = ' . $_GET['add'] . ' and ' . $testWhere;
	$dataUnique = odbc_exec($conn, $queryUnique);
	if (odbc_fetch_row($dataUnique)) {
			$_SESSION['sqlMessage'] = 'This is already shared!';
			$_SESSION['uiState'] = 'error';
			fRedirect();
	} else {
		$queryInsert = 'insert into processtrendshare (processtrendfk, ' . $insertColName . ')
		Values (' . $id . ', ' . $insertCol . ')';
		odbc_exec($conn, $queryInsert);
		$_SESSION['sqlMessage'] = 'Trend shared!';
		$_SESSION['uiState'] = 'active';
	};
};
if (isset($_GET['delete'])) {
	$queryDelete = 'delete from processtrendshare where id = ' . $_GET['key'];
	odbc_exec($conn, $queryDelete);
	$_SESSION['sqlMessage'] = 'Trend share removed!';
	$_SESSION['uiState'] = 'active';
};
fRedirect();
?>