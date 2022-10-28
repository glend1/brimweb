<?PHP
require_once 'functions.php';
if (!isset($_SESSION['id'])) {
	$validUser = fTextDatabase($_POST['user'], 61);
	require_once 'PasswordHash.php';
	$t_hasher = new PasswordHash(8, FALSE);
	$queryUserGet = 'select id, name, password, active from users where name = \'' . $validUser . '\'';
	$queryUsers = odbc_exec($conn, $queryUserGet);
	$resultUser = odbc_fetch_array($queryUsers);
	$valid = $t_hasher->CheckPassword($_POST['pass'], $resultUser['password']);
	$aPermissions = [];
	$i = 0;
	if ($valid == True) {
		if ($resultUser['active'] == false) {
			$_SESSION['sqlMessage'] = 'Account not activated, Login not possible';
			$_SESSION['uiState'] = 'error';
		} else {
			setcookie('id', session_id(), time() + (60 * 60 * 24 * 14), '/');
			$_SESSION['id'] = $resultUser['id'];
			$_SESSION['user'] = $resultUser['name'];
			$_SESSION['sqlMessage'] = 'Welcome, ' . $_POST['user'] . '!';
			$_SESSION['uiState'] = 'active';
		};
	} else {
		$_SESSION['sqlMessage'] = 'Username and/or password were invalid!';
		$_SESSION['uiState'] = 'error';
	};
} else {
	$_SESSION['sqlMessage'] = 'You are already logged in!';
	$_SESSION['uiState'] = 'error';
};
fRedirect(); ?>

