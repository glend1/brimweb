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
			if (isset($_POST['type'])) {
				$queryNewPriority = 'insert into WMOPriorityCode (Description, PriorityOrder, typefk)
				values (\'' . $valid . '\', ' . $_POST['order'] . ', ' . $_POST['type'] . ')';
				odbc_exec($conn, $queryNewPriority);
				$_SESSION['sqlMessage'] = 'WMO Priority added!';
				$_SESSION['uiState'] = 'active';
			} else {
				$_SESSION['sqlMessage'] = 'WMO Priority creation failed!';
				$_SESSION['uiState'] = 'error';
			};
		} else {
			$_SESSION['sqlMessage'] = 'WMO Priority creation failed!';
			$_SESSION['uiState'] = 'error';
		};
	} else {
		$_SESSION['sqlMessage'] = 'WMO Priority creation failed!';
		$_SESSION['uiState'] = 'error';
	};
};
if (isset($_POST['update'])) {
	if ($valid = fTextDatabase($_POST['name'], 61)) {
		if (fValidNumber($_POST['order'])) {
			if (isset($_POST['type'])) {
				$queryUpdatePriority = 'update WMOPriorityCode
				set Description=\'' . $valid . '\', PriorityOrder = ' . $_POST['order'] . ', typefk = ' . $_POST['type'] . '
				where id =' . $_POST['update'];
				odbc_exec($conn, $queryUpdatePriority);
				print($queryUpdatePriority);
				$_SESSION['sqlMessage'] = 'WMO Priority updated!';
				$_SESSION['uiState'] = 'active';
			} else {
				$_SESSION['sqlMessage'] = 'WMO Priority creation failed!';
				$_SESSION['uiState'] = 'error';
			};
		} else {
			$_SESSION['sqlMessage'] = 'WMO Priority update failed!';
			$_SESSION['uiState'] = 'error';
		};
	} else {
		$_SESSION['sqlMessage'] = 'WMO Priority update failed!';
		$_SESSION['uiState'] = 'error';
	};
};
if (isset($_POST['delete'])) {
		$queryDeletePriority = 'delete from WMOPriorityCode
		where id = ' . $_POST['delete'];
		odbc_exec($conn, $queryDeletePriority);
		$_SESSION['sqlMessage'] = 'WMO Priority deleted!';
		$_SESSION['uiState'] = 'active';
};
fRedirect(); ?>