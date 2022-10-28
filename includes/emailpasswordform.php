<?PHP
require_once 'functions.php';
if (isset($_SESSION['id'])) {
	$_SESSION['sqlMessage'] = 'You are already logged in!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
if (!(isset($_POST['id']) && isset($_POST['code']) && isset($_POST['password']) && isset($_POST['cpassword']))) {
	$_SESSION['sqlMessage'] = 'Form incomplete';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
if (fPassTest($_POST['password']) && $_POST['password'] == $_POST['cpassword']) {
	if(fCheckRandom(2, $_POST['id'], $_POST['code'], 60 * 60)) {
		require_once 'PasswordHash.php';
		$t_hasher = new PasswordHash(8, FALSE);
		$newHash = $t_hasher->HashPassword($_POST['password']);
		$queryNewPass = 'UPDATE users
		SET password = \'' . $newHash . '\'
		WHERE id = \'' . $_POST['id'] . '\'';
		odbc_exec($conn, $queryNewPass);
		fDeleteRandom(2, $_POST['id']);
		$_SESSION['sqlMessage'] = 'Password was sucessfully changed!';
		$_SESSION['uiState'] = 'active';
	} else {
		$_SESSION['sqlMessage'] = 'Code expired or invalid!';
		$_SESSION['uiState'] = 'error';
		header('Location:' . $urlPath . 'index.php');
	};
} else {
	$_SESSION['sqlMessage'] = 'Password not valid or passwords don\'t match!';
	$_SESSION['uiState'] = 'error';
};
odbc_close($conn);
header('Location:' . $urlPath . 'index.php'); ?>