<?PHP
require_once 'functions.php';
if (!fCanSee(@$_SESSION['permissions']['page'][1] >= 300)) {
	$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
if (isset($_POST['add'])) {
		if ($valid = fTextDatabase($_POST['name'], 61)) {
			$queryCheck = 'select id from oeename where name = \'' . $valid . '\'';
			$dataCheck = odbc_exec($conn, $queryCheck);
			if (!odbc_fetch_row($dataCheck)) {
				$queryNewName = 'insert into oeename (name)
				values (\'' . $valid . '\')';
				odbc_exec($conn, $queryNewName);		
				$_SESSION['sqlMessage'] = 'OEE Reason added!';
				$_SESSION['uiState'] = 'active';
			} else {
				$_SESSION['sqlMessage'] = 'Reason already exists!';
				$_SESSION['uiState'] = 'error';
			};
		} else {
			$_SESSION['sqlMessage'] = 'OEE Reason creation failed!';
			$_SESSION['uiState'] = 'error';
		};
};
if (isset($_POST['update'])) {
	if ($valid = fTextDatabase($_POST['name'], 61)) {
		$queryCheck = 'select id from oeename where name = \'' . $valid . '\'';
		$dataCheck = odbc_exec($conn, $queryCheck);
		if (!odbc_fetch_row($dataCheck)) {
			$queryUpdateName = 'update oeename
			set name=\'' . $_POST['name'] . '\'
			where id =' . $_POST['update'];
			odbc_exec($conn, $queryUpdateName);
			$_SESSION['sqlMessage'] = 'OEE Reason updated!';
			$_SESSION['uiState'] = 'active';
		} else {
			$_SESSION['sqlMessage'] = 'Reason already exists!';
			$_SESSION['uiState'] = 'error';
		};
	} else {
		$_SESSION['sqlMessage'] = 'OEE Reason failed to update!';
		$_SESSION['uiState'] = 'error';
	};
};
if (isset($_POST['delete'])) {
	$queryDeleteName = 'delete from oeename
	where id = ' . $_POST['delete'];
	odbc_exec($conn, $queryDeleteName);
	$_SESSION['sqlMessage'] = 'OEE Reason deleted!';
	$_SESSION['uiState'] = 'active';
};
fRedirect(); ?>