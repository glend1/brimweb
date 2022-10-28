<?PHP
require_once 'functions.php';
if (!fCanSee($_SESSION['permissions']['group'][$_POST['id']] >= 300)) {
	$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
if ($_POST['id'] != '') {
	$queryDeleteGroup = 'delete from grouptable
	where id = ' . $_POST['id'];
	odbc_exec($conn, $queryDeleteGroup);
	$queryDeleteJunction = 'delete from groupjunction
	where groupid = ' . $_POST['id'];
	odbc_exec($conn, $queryDeleteJunction);
	$queryDeletePermission = 'delete from permissions
	where groupfk = ' . $_POST['id'] . ' or groupadminfk = ' . $_POST['id'];
	odbc_exec($conn, $queryDeletePermission);
	//print($queryDeletePermission);
	$_SESSION['sqlMessage'] = 'Group was sucessfully Deleted!';
	$_SESSION['uiState'] = 'active';
	odbc_close($conn);
} else {
	$_SESSION['sqlMessage'] = 'Group deletion failed!';
	$_SESSION['uiState'] = 'error';
};
fRedirect(); ?>