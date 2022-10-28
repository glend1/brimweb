<?PHP
require_once 'functions.php';
if ($_SESSION['id'] == 1) {
	if (isset($_POST['to']) && isset($_POST['name']) && isset($_POST['id'])) {
		fConfirmEmail($_POST['to'], $_POST['name'], $_POST['id']);
		$_SESSION['sqlMessage'] = 'Confirmation Email resent!';
		$_SESSION['uiState'] = 'active';
		odbc_close($conn);
	} else {
		$_SESSION['sqlMessage'] = 'Form incomplete';
		$_SESSION['uiState'] = 'error';
	};
} else {
	$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
	$_SESSION['uiState'] = 'error';
};
fRedirect(); ?>