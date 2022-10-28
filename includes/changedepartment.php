<?PHP
require_once 'functions.php';
if (isset($_SESSION['admin']['departmentadmin']) && isset($_SESSION['admin']['groupadmin'])) {
	$bAdminDept = true;
};
if (isset($_POST['add'])) {
	if (fCanSee(isset($bAdminDept))) {
		if ($valid = fTextDatabase($_POST['dname'], 61)) {
			$queryNewDepartment = 'insert into department (name)
			values (\'' . $valid . '\')';
			odbc_exec($conn, $queryNewDepartment);
			$queryGetId = 'select id from department where name = \'' . $valid . '\'';
			$dataId = odbc_exec($conn, $queryGetId);
			while (odbc_fetch_row($dataId)) {
				$iId = odbc_result($dataId, 1);
			};
			$queryNewPermissions = 'insert into permissions (departmentfk, groupfk, level)
			values (' . $iId . ', ' . $_POST['groupadmin'] . ', 300)';
			odbc_exec($conn, $queryNewPermissions);
			$_SESSION['sqlMessage'] = 'Department added!';
			$_SESSION['uiState'] = 'active';
		} else {
			$_SESSION['sqlMessage'] = 'Department creation failed!';
			$_SESSION['uiState'] = 'error';
		};
	} else {
		$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
		$_SESSION['uiState'] = 'error';
	};
};
if (isset($_POST['update'])) {
	if (fCanSee($_SESSION['permissions']['department'][$_POST['update']] >= 200)) {
		if ($valid = fTextDatabase($_POST['dname'], 61)) {
			$queryUpdateDepartment = 'update department
			set name=\'' . $valid . '\'
			where id =' . $_POST['update'];
			odbc_exec($conn, $queryUpdateDepartment);
			$_SESSION['sqlMessage'] = 'Department updated!';
			$_SESSION['uiState'] = 'active';
		} else {
			$_SESSION['sqlMessage'] = 'Department failed to update!';
			$_SESSION['uiState'] = 'error';
		};
	} else {
		$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
		$_SESSION['uiState'] = 'error';
	};
};
if (isset($_POST['delete'])) {
	if (fCanSee($_SESSION['permissions']['department'][$_POST['delete']] >= 300)) {
		$queryDeleteDepartment = 'delete from department
		where id = ' . $_POST['delete'];
		odbc_exec($conn, $queryDeleteDepartment);
		$queryDeleteAlarmGroup = 'delete from alarmgroup
		where departmentfk = ' . $_POST['delete'];
		odbc_exec($conn, $queryDeleteAlarmGroup);
		$queryDeleteEquip = 'delete from equip
		where departmentfk = ' . $_POST['delete'];
		odbc_exec($conn, $queryDeleteEquip);
		$queryDeleteTrain = 'delete from train
		where departmentfk = ' . $_POST['delete'];
		odbc_exec($conn, $queryDeleteTrain);
		$_SESSION['sqlMessage'] = 'Department deleted!';
		$_SESSION['uiState'] = 'active';
	} else {
		$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
		$_SESSION['uiState'] = 'error';
	};
};
fRedirect(); ?>