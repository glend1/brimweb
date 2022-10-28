<?PHP
require_once 'functions.php';
if (isset($_SESSION['id'])) {
	$_SESSION['sqlMessage'] = 'You are already logged in!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
if (!isset($_POST['email'])) {
	$_SESSION['sqlMessage'] = 'No Email!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
$queryEmail = 'select name, id from users where email = \'' . urldecode($_POST['email']) . '\'';
$dataEmail = odbc_exec($conn, $queryEmail);
while (odbc_fetch_row($dataEmail)) {
	fResetPasswordEmail(urldecode($_POST['email']), odbc_result($dataEmail, 1), odbc_result($dataEmail, 2));
	$_SESSION['sqlMessage'] = 'Password reset Email sent!';
	$_SESSION['uiState'] = 'active';
};
odbc_close($conn);
header('Location:' . $urlPath . 'index.php'); ?>