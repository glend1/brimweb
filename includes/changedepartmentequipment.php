<?PHP
require_once 'functions.php';
print_r($_POST);
if (isset($_POST['add'])) {
	if (isset($_POST['departmentid'])) {
		if (fCanSee(@$_SESSION['permissions']['department'][$_POST['departmentid']] >= 200)) {
			if ($valid = fTextDatabase($_POST['dename'], 61)) {
				$queryNewDepartment = 'insert into departmentequipment (name, departmentfk)
				values (\'' . $valid . '\', ' . $_POST['departmentid'] . ')';
				odbc_exec($conn, $queryNewDepartment);
				$_SESSION['sqlMessage'] = 'Department Equipment added!';
				$_SESSION['uiState'] = 'active';
			} else {
				$_SESSION['sqlMessage'] = 'Department Equipment creation failed!';
				$_SESSION['uiState'] = 'error';
			};
		} else {
			$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
			$_SESSION['uiState'] = 'error';
		};
	} else {
		$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
		$_SESSION['uiState'] = 'error';
	};
};
if (isset($_POST['update'])) {
	if (isset($_POST['departmentid'])) {
		if (fCanSee(@$_SESSION['permissions']['department'][$_POST['departmentid']] >= 200)) {
			if ($valid = fTextDatabase($_POST['dename'], 61)) {
				$queryUpdateDepartment = 'update departmentequipment
				set name=\'' . $valid . '\', departmentfk = ' . $_POST['departmentid'] . '
				where id =' . $_POST['update'];
				odbc_exec($conn, $queryUpdateDepartment);
				$_SESSION['sqlMessage'] = 'Department Equipment updated!';
				$_SESSION['uiState'] = 'active';
			} else {
				$_SESSION['sqlMessage'] = 'Department Equipment failed to update!';
				$_SESSION['uiState'] = 'error';
			};
		} else {
			$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
			$_SESSION['uiState'] = 'error';
		};
	} else {
		$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
		$_SESSION['uiState'] = 'error';
	};
};
if (isset($_POST['delete'])) {
	if (isset($_POST['departmentid'])) {
		if (fCanSee($_SESSION['permissions']['department'][$_POST['departmentid']] >= 200)) {
			$queryDeleteDepartment = 'delete from departmentequipment
			where id = ' . $_POST['delete'];
			odbc_exec($conn, $queryDeleteDepartment);
			$_SESSION['sqlMessage'] = 'Department Equipment deleted!';
			$_SESSION['uiState'] = 'active';
		} else {
			$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
			$_SESSION['uiState'] = 'error';
		};
	};
};
fRedirect(); ?>