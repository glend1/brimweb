<?PHP
require_once 'includes/functions.php';
if (isset($_SESSION['id'])) {
	$_SESSION['sqlMessage'] = 'You are already logged in!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
if (!isset($_GET['code'])) {
	$_SESSION['sqlMessage'] = 'No code!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
if (!isset($_GET['user'])) {
	$_SESSION['sqlMessage'] = 'No user!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
if (fCheckRandom(1, $_GET['user'], $_GET['code'])) {
	fDeleteRandom(1, $_GET['user']);
	$queryUpdate = 'update users set active = 1 where id = \'' . $_GET['user'] . '\'';
	odbc_exec($conn, $queryUpdate);
	$_SESSION['sqlMessage'] = 'Activation complete. You are now able to Login';
	$_SESSION['uiState'] = 'active';
} else {
	$_SESSION['sqlMessage'] = 'Failed to activate!';
	$_SESSION['uiState'] = 'error';
};
odbc_close($conn);
header('Location:' . $urlPath . 'index.php'); ?>