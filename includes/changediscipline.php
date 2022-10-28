<?PHP
require_once 'functions.php';
if (isset($_SESSION['admin']['disciplineadmin']) && isset($_SESSION['admin']['groupadmin'])) {
	$bAdminDisc = true;
};
if (isset($_POST['add'])) {
	if (fCanSee(isset($bAdminDisc))) {
		if ($valid = fTextDatabase($_POST['dname'], 61)) {
			$queryNewDiscipline = 'insert into discipline (name, areafk)
			values (\'' . $valid . '\', ' . $_POST['area'] . ' )';
			odbc_exec($conn, $queryNewDiscipline);
			$queryGetId = 'select id from discipline where name = \'' . $valid . '\'';
			$dataId = odbc_exec($conn, $queryGetId);
			while (odbc_fetch_row($dataId)) {
				$iId = odbc_result($dataId, 1);
			};
			$queryNewPermissions = 'insert into permissions (disciplinefk, groupfk, level)
			values (' . $iId . ', ' . $_POST['groupadmin'] . ', 300)';
			odbc_exec($conn, $queryNewPermissions);			
			$_SESSION['sqlMessage'] = 'Discipline added!';
			$_SESSION['uiState'] = 'active';
		} else {
			$_SESSION['sqlMessage'] = 'Discipline creation failed!';
			$_SESSION['uiState'] = 'error';
		};
	} else {
		$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
		$_SESSION['uiState'] = 'error';
	};
};
if (isset($_POST['update'])) {
	if (fCanSee($_SESSION['permissions']['discipline'][$_POST['update']] >= 200)) {
		if ($valid = fTextDatabase($_POST['dname'], 61)) {
			$queryUpdateDiscipline = 'update discipline
			set name=\'' . $_POST['dname'] . '\', areafk = ' . $_POST['area'] . ' 
			where id =' . $_POST['update'];
			odbc_exec($conn, $queryUpdateDiscipline);
			$_SESSION['sqlMessage'] = 'Discipline updated!';
			$_SESSION['uiState'] = 'active';
		} else {
			$_SESSION['sqlMessage'] = 'Discipline failed to update!';
			$_SESSION['uiState'] = 'error';
		};
	} else {
		$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
		$_SESSION['uiState'] = 'error';
	};
};
if (isset($_POST['delete'])) {
	if (fCanSee($_SESSION['permissions']['discipline'][$_POST['delete']] >= 300)) {
		$queryDeleteDiscipline = 'delete from discipline
		where id = ' . $_POST['delete'];
		odbc_exec($conn, $queryDeleteDiscipline);
		$_SESSION['sqlMessage'] = 'Discipline deleted!';
		$_SESSION['uiState'] = 'active';
	} else {
		$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
		$_SESSION['uiState'] = 'error';
	};
};
fRedirect(); ?>