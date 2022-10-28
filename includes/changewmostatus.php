<?PHP
require_once 'functions.php';
if ($_SESSION['id'] != 1) {
	$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
if (isset($_POST['add'])) {
	if ($valid = fTextDatabase($_POST['name'], 61)) {
		$queryNewWmoStatus = 'insert into WMOStatusCode (Description)
		values (\'' . $valid . '\')';
		odbc_exec($conn, $queryNewWmoStatus);
		$_SESSION['sqlMessage'] = 'WMO Status Code added!';
		$_SESSION['uiState'] = 'active';
	} else {
		$_SESSION['sqlMessage'] = 'WMO Status Code creation failed!';
		$_SESSION['uiState'] = 'error';
	};
};
if (isset($_POST['update'])) {
	if ($valid = fTextDatabase($_POST['name'], 61)) {
		$queryUpdateWmoStatus = 'update WMOStatusCode
		set Description=\'' . $_POST['name'] . '\'
		where id =' . $_POST['update'];
		odbc_exec($conn, $queryUpdateWmoStatus);
		$_SESSION['sqlMessage'] = 'WMO Status Code updated!';
		$_SESSION['uiState'] = 'active';
	} else {
		$_SESSION['sqlMessage'] = 'WMO Status updated failed';
		$_SESSION['uiState'] = 'error';
	};
};
if (isset($_POST['delete'])) {
		$queryDeleteWmoStatus = 'delete from WMOStatusCode
		where id = ' . $_POST['delete'];
		odbc_exec($conn, $queryDeleteWmoStatus);
		$_SESSION['sqlMessage'] = 'WMO Status Code deleted!';
		$_SESSION['uiState'] = 'active';
};
fRedirect(); ?>