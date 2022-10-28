<?PHP
require_once 'functions.php';
if (!isset($_SESSION['id'])) {
	$_SESSION['sqlMessage'] = 'You must be logged in to perform this action!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
if (!isset($_POST['id'])) {
	$_SESSION['sqlMessage'] = 'Form Incomplete, no action taken!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
if ($_SESSION['id'] != 1 && $_SESSION['id'] != $_POST['id']) {
	$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
if ($_SESSION['id'] == 1 && $_SESSION['id'] == $_POST['id']) {
	$isAdmin = true;
}
$updateUser = 'update users set ';
$sep = '';
foreach ($_POST as $key => $value) {
	switch ($key) {
		case 'email':
		case 'name':
			if (!isset($isAdmin)) {
				$updateUser .= $sep . $key . ' = \'' . $value . '\'';
				$changeSep = true;
			};
			break;
		case 'mobile':
			$updateUser .= $sep . $key . ' = \'' . $value . '\'';
			$changeSep = true;
			break;
	};
	if (isset($changeSep)) {
		$sep = ', ';
	};
};
$updateUser .= ' where id = ' . $_POST['id'];
if (odbc_exec($conn, $updateUser)) {
	$_SESSION['sqlMessage'] = 'Details updated!';
	$_SESSION['uiState'] = 'active';
};
odbc_close($conn);
header('Location:' . $urlPath . 'index.php'); ?>