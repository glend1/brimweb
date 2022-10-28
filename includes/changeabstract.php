<?PHP
require_once 'functions.php';
if ($_SESSION['id'] != 1) {
	$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
if (isset($_POST['add'])) {
	if ($valid = fTextDatabase($_POST['aname'], 61)) {
		if (fValidNumber($_POST['id'])) {
			$queryNewAbstract = 'insert into PermissionAbstract (AbstractName, PermissionFK)
			values (\'' . $valid . '\', ' . $_POST['id'] . ')';
			odbc_exec($conn, $queryNewAbstract);
			$_SESSION['sqlMessage'] = 'Abstract Permission added!';
			$_SESSION['uiState'] = 'active';
		} else {
			$_SESSION['sqlMessage'] = 'Abstract permission creation failed!';
			$_SESSION['uiState'] = 'error';
		};
	} else {
		$_SESSION['sqlMessage'] = 'Abstract Permission creation failed!';
		$_SESSION['uiState'] = 'error';
	};
};
if (isset($_POST['update'])) {
	if ($valid = fTextDatabase($_POST['aname'], 61)) {
		if (fValidNumber($_POST['id'])) {
			$queryUpdateAbstract = 'update PermissionAbstract
			set AbstractName=\'' . $valid . '\', permissionfk = ' . $_POST['id'] . '
			where id =' . $_POST['update'];
			odbc_exec($conn, $queryUpdateAbstract);
			$_SESSION['sqlMessage'] = 'Abstract Permission updated!';
			$_SESSION['uiState'] = 'active';
		} else {
			$_SESSION['sqlMessage'] = 'Abstract permission update failed!';
			$_SESSION['uiState'] = 'error';
		};
	} else {
		$_SESSION['sqlMessage'] = 'Abstract permission update failed!';
		$_SESSION['uiState'] = 'error';
	};
};
if (isset($_POST['delete'])) {
		$queryDeleteDepartment = 'delete from PermissionAbstract
		where id = ' . $_POST['delete'];
		odbc_exec($conn, $queryDeleteDepartment);
		$_SESSION['sqlMessage'] = 'Abstract permission deleted!';
		$_SESSION['uiState'] = 'active';
};
fRedirect(); ?>