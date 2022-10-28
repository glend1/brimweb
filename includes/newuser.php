<?PHP
require_once 'functions.php';
if (isset($_SESSION['id'])) {
	$_SESSION['sqlMessage'] = 'You are already logged in!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
require_once 'PasswordHash.php';
$t_hasher = new PasswordHash(8, FALSE);
if ($_POST['username'] != '' && $_POST['password'] != '' && $_POST['email'] != '' && $_POST['cpassword'] == $_POST['password'] && $_POST['cemail'] == $_POST['email'] && fValidEmail($_POST['email'])) {
	$validUser = fTextDatabase($_POST['username'], 61);
	$queryUser = 'select name from users where name = \'' . $validUser . '\'';
	$dataUser = odbc_exec($conn, $queryUser);
	$queryEmail = 'select email from users where email = \'' . $_POST['email'] . '\'';
	$dataEmail = odbc_exec($conn, $queryEmail);
	$mobile = '';
	if ($_POST['mobile'] != '') {
		if (fValidMobile($_POST['mobile'])) {
			$mobile = $_POST['mobile'];
		};
	};
	if (odbc_num_rows($dataUser) == 0 && odbc_num_rows($dataEmail) == 0) {
		$hash = $t_hasher->HashPassword($_POST['password']);
		$queryNewUser = 'insert into users (name, password, email, active, mobile)
		values ( \'' . $validUser . '\',\'' . $hash . '\', \'' . $_POST['email'] . '\', 0, \'' . $mobile . '\')';
		odbc_exec($conn, $queryNewUser);
		$queryId = 'select id from users where name = \'' . $validUser . '\'';
		$dataId = odbc_exec($conn, $queryId);
		$_SESSION['sqlMessage'] = 'User creation failed!';
		$_SESSION['uiState'] = 'error';
		while (odbc_fetch_row($dataId)) {
			fConfirmEmail($_POST['email'], $_POST['username'], odbc_result($dataId, 1));
			$_SESSION['sqlMessage'] = 'User ' . $_POST['username'] . ' was sucessfully Created! Check your E-Mail inbox for Confirmation';
			$_SESSION['uiState'] = 'active';
		};
	} else {
		$_SESSION['sqlMessage'] = 'User or Email already exists in database!';
		$_SESSION['uiState'] = 'error';
	};
} else {
	$_SESSION['sqlMessage'] = 'User creation failed!';
	$_SESSION['uiState'] = 'error';
};
odbc_close($conn);
header('Location:' . $urlPath . 'index.php'); ?>