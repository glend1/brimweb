<?PHP
require_once 'functions.php';
if ($_SESSION['id'] != 1) {
	$queryUserCanChange = 'select distinct Users.id 
	from Users, GroupJunction';
	$aQueryGroupPermissions[] = 'GroupJunction.UserID = Users.ID';
	fOrThem($_SESSION['permissions']['group'], 300, 'GroupJunction.GroupID', $aQueryGroupPermissions);
	fGenerateWhere($queryUserCanChange, $aQueryGroupPermissions);
	$dataUserCanChange = odbc_exec($conn, $queryUserCanChange);;
	while (odbc_fetch_row($dataUserCanChange)) {
		$aUserCanChange[] = odbc_result($dataUserCanChange, 1);
	};
	if (!fCanSee(in_array($_POST['id'] , $aUserCanChange))) {
		$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
		$_SESSION['uiState'] = 'error';
		fRedirect();
	};
};
require_once 'PasswordHash.php';
$t_hasher = new PasswordHash(8, FALSE);
	if (isset($_POST['newpass']) && isset($_POST['confirmnewpass']) && isset($_POST['currentpass']) && isset($_SESSION['user']) && isset($_POST['id'])) {
		if ($_POST['confirmnewpass'] == $_POST['newpass']) {
			fPassTest ($_POST['newpass']);
			fPassTest ($_POST['confirmnewpass']);
			/*if (isset($_POST['id'])) {
				$newHash = $t_hasher->HashPassword($_POST['newpass']);
				$queryNewPass = 'UPDATE users
				SET password = \'' . $newHash . '\'
				WHERE id = \'' . $_POST['id'] . '\'';
				odbc_exec($conn, $queryNewPass);
				$_SESSION['sqlMessage'] = 'Password was sucessfully changed!';
				$_SESSION['uiState'] = 'active';
			} else*/if (isset($_SESSION['id'])) { 
				fPassTest ($_POST['currentpass']);
				$queryPasswordTest = 'select password from users where id = \'' . $_SESSION['id'] . '\'';
				$queryPassword = odbc_exec($conn, $queryPasswordTest);
				$resultPassword = odbc_fetch_array($queryPassword);
				$valid = $t_hasher->CheckPassword($_POST['currentpass'], $resultPassword['password']);
				if ($valid == 1) {
					$newHash = $t_hasher->HashPassword($_POST['newpass']);
					$queryNewPass = 'UPDATE users
					SET password = \'' . $newHash . '\'
					WHERE id = \'' . $_SESSION['id'] . '\'';
					odbc_exec($conn, $queryNewPass);
					$_SESSION['sqlMessage'] = 'Password was sucessfully changed!';
					$_SESSION['uiState'] = 'active';
				} else {
					$_SESSION['sqlMessage'] = 'Password failed to change!';
					$_SESSION['uiState'] = 'error';
				};
			} else {
				$_SESSION['sqlMessage'] = 'No user selected!';
				$_SESSION['uiState'] = 'error';
			};
		} else {
			$_SESSION['sqlMessage'] = 'Passwords do not match!';
			$_SESSION['uiState'] = 'error';
		};
	} elseif (isset($_POST['newpass']) && isset($_POST['id'])) {
		fPassTest ($_POST['newpass']);
		$newHash = $t_hasher->HashPassword($_POST['newpass']);
		$queryNewPass = 'UPDATE users
		SET password = \'' . $newHash . '\'
		WHERE id = \'' . $_POST['id'] . '\'';
		odbc_exec($conn, $queryNewPass);
		$_SESSION['sqlMessage'] = 'Password was sucessfully changed!';
		$_SESSION['uiState'] = 'active';
	} else {
		$_SESSION['sqlMessage'] = 'Password failed to change!';
		$_SESSION['uiState'] = 'error';
	};
odbc_close($conn);
fRedirect(); ?>