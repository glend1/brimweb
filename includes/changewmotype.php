<?PHP
require_once 'functions.php';
if ($_SESSION['id'] != 1) {
	$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
if (isset($_POST['add'])) {
	if ($valid = fTextDatabase($_POST['name'], 61)) {
		if (fValidNumber($_POST['order'])) {
			$queryNewWmoType = 'insert into WMOType (Description, typeorder)
			values (\'' . $valid . '\', ' . $_POST['order'] . ')';
			odbc_exec($conn, $queryNewWmoType);
			$_SESSION['sqlMessage'] = 'WMO Type added!';
			$_SESSION['uiState'] = 'active';
		} else {
			$_SESSION['sqlMessage'] = 'WMO Type creation failed!';
			$_SESSION['uiState'] = 'error';
		};
	} else {
		$_SESSION['sqlMessage'] = 'WMO Type creation failed!';
		$_SESSION['uiState'] = 'error';
	};
};
if (isset($_POST['update'])) {
	if ($valid = fTextDatabase($_POST['name'], 61)) {
		if (fValidNumber($_POST['order'])) {
			$queryUpdateWmoType = 'update WMOType
			set Description=\'' . $_POST['name'] . '\', typeorder = ' . $_POST['order'] . '
			where id =' . $_POST['update'];
			print($queryUpdateWmoType);
			odbc_exec($conn, $queryUpdateWmoType);
			$_SESSION['sqlMessage'] = 'WMO Type updated!';
			$_SESSION['uiState'] = 'active';
		} else {
			$_SESSION['sqlMessage'] = 'WMO Type update failed!';
			$_SESSION['uiState'] = 'error';
		};
	} else {
		$_SESSION['sqlMessage'] = 'WMO Type update failed!';
		$_SESSION['uiState'] = 'error';
	};
};
if (isset($_POST['delete'])) {
		$queryDeleteWmoType = 'delete from WMOType
		where id = ' . $_POST['delete'];
		odbc_exec($conn, $queryDeleteWmoType);
		$_SESSION['sqlMessage'] = 'WMO Type deleted!';
		$_SESSION['uiState'] = 'active';
};
fRedirect(); ?>