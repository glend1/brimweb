<?PHP
require_once 'functions.php';
if (!fCanSee($_SESSION['permissions']['group'][$_POST['id']] >= 300)) {
	$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};

if ($_POST['id'] != '' && $valid = fTextDatabase($_POST['name'], 61)) {
	$queryDeleteGroup = 'update grouptable
	set name = \'' . $valid . '\'
	where id = ' . $_POST['id'];
	odbc_exec($conn, $queryDeleteGroup);
	$_SESSION['sqlMessage'] = 'Group was sucessfully updated!';
	$_SESSION['uiState'] = 'active';
	odbc_close($conn);
} else {
	$_SESSION['sqlMessage'] = 'Group update failed!';
	$_SESSION['uiState'] = 'error';
};
fRedirect(); ?>