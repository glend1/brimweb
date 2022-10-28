<?PHP
require_once 'functions.php';
if (isset($_SESSION['admin']['areaadmin']) && isset($_SESSION['admin']['groupadmin'])) {
	$bAdminArea = true;
};
if (isset($_POST['add'])) {
	if (fCanSee(isset($bAdminArea))) {
		if ($valid = fTextDatabase($_POST['aname'], 61)) {
			$queryNewArea = 'insert into area (name)
			values (\'' . $valid . '\')';
			odbc_exec($conn, $queryNewArea);
			$queryGetId = 'select id from area where name = \'' . $valid . '\'';
			$dataId = odbc_exec($conn, $queryGetId);
			while (odbc_fetch_row($dataId)) {
				$iId = odbc_result($dataId, 1);
			};
			$queryNewPermissions = 'insert into permissions (areafk, groupfk, level)
			values (' . $iId . ', ' . $_POST['groupadmin'] . ', 300)';
			odbc_exec($conn, $queryNewPermissions);			
			$_SESSION['sqlMessage'] = 'Area added!';
			$_SESSION['uiState'] = 'active';
		} else {
			$_SESSION['sqlMessage'] = 'Area creation failed!';
			$_SESSION['uiState'] = 'error';
		};
	} else {
		$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
		$_SESSION['uiState'] = 'error';
	};
};
if (isset($_POST['update'])) {
	if (fCanSee($_SESSION['permissions']['area'][$_POST['update']] >= 200)) {
		if ($valid = fTextDatabase($_POST['aname'], 61)) {
			$queryUpdateArea = 'update area
			set name=\'' . $_POST['aname'] . '\'
			where id =' . $_POST['update'];
			odbc_exec($conn, $queryUpdateArea);
			$_SESSION['sqlMessage'] = 'Area updated!';
			$_SESSION['uiState'] = 'active';
		} else {
			$_SESSION['sqlMessage'] = 'Area failed to update!';
			$_SESSION['uiState'] = 'error';
		};
	} else {
		$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
		$_SESSION['uiState'] = 'error';
	};
};
if (isset($_POST['delete'])) {
	if (fCanSee($_SESSION['permissions']['area'][$_POST['delete']] >= 300)) {
		$queryDeleteArea = 'delete from area
		where id = ' . $_POST['delete'];
		odbc_exec($conn, $queryDeleteArea);
		$_SESSION['sqlMessage'] = 'Area deleted!';
		$_SESSION['uiState'] = 'active';
	} else {
		$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
		$_SESSION['uiState'] = 'error';
	};
};
fRedirect(); ?>