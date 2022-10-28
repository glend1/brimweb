<?PHP
require_once 'functions.php';
if (!isset($_POST['action'])) {
	$_SESSION['sqlMessage'] = 'You must complete the form!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
if (empty($_POST['action'])) {
	$_SESSION['sqlMessage'] = 'You must complete the form!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
if ($_POST['action'] == 'find') {
	$queryString = '';
	$qsSep = '?';
	foreach($_POST as $key => $value) {
		switch ($key) {
			case 'startdate':
			case 'enddate':
			case 'type':
			$queryString .= $qsSep . $key . '=' . $value;
			$qsSep= '&';
		};
	};
	header('Location:' . $urlPath . 'manageoeerecord.php' . $queryString);
	exit();
};
if (isset($_POST['type'])) {
	$queryGetDepartment = 'select top 1 departmentfk
	from Type
	join DepartmentEquipment on DepartmentEquipment.ID = DepartmentEquipmentFK
	where type.id = ' . $_POST['type'];
} elseif (isset($_POST['id'])) {
	$queryGetDepartment = 'select top 1 departmentfk
	from Type
	join DepartmentEquipment on DepartmentEquipment.ID = DepartmentEquipmentFK
	join Records on Records.TypeFK = Type.id
	where records.ID = ' . $_POST['id'];
};
if (isset($queryGetDepartment)) {
	$dataGetDepartment = odbc_exec($conn, $queryGetDepartment);
	if (odbc_fetch_row($dataGetDepartment)) {
		$department = odbc_result($dataGetDepartment, 1);
	} else {
		$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
		$_SESSION['uiState'] = 'error';
		fRedirect();
	};
} else {
	$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
if ($_SESSION['permissions']['page'][1] >= 300 && $_SESSION['permissions']['department'][$department] >= 300) {
	$bAdmin = TRUE;
};
if ($_SESSION['permissions']['page'][1] >= 200 && $_SESSION['permissions']['department'][$department] >= 200) {
	$bEdit = TRUE;
};
if (isset($_POST['startdate']) && isset($_POST['enddate'])) {
	if ($_POST['startdate'] > $_POST['enddate']) {
		$_SESSION['sqlMessage'] = 'Invalid date time range selected!';
		$_SESSION['uiState'] = 'error';
		fRedirect();
	} else {
		$duration = strtotime($_POST['enddate']) - strtotime($_POST['startdate']);
	};
};
if ($_POST['action'] == 'add') {
	if (fCanSee(isset($bAdmin))) {
		if ($valid = fTextDatabase($_POST['comment'], 131)) {
			$valid = '\'' . $valid . '\'';
		} elseif (strlen(trim($_POST['comment'])) == 0) {
			$valid = 'NULL';
		} else {
			$invalid = true;
		};
		if (isset($invalid)) {
			$_SESSION['sqlMessage'] = 'Record creation failed!';
			$_SESSION['uiState'] = 'error';
		} else {
			$queryNewDowntime = 'insert into records (comment, typefk, startdatetime, enddatetime, duration)
			values (' . $valid . ', ' . $_POST['type'] . ', \'' . $_POST['startdate'] . '\', \'' . $_POST['enddate'] . '\', ' . $duration . ')';
			odbc_exec($conn, $queryNewDowntime);
			$_SESSION['sqlMessage'] = 'Record added!';
			$_SESSION['uiState'] = 'active';
		};
	} else {
		$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
		$_SESSION['uiState'] = 'error';
	};
};
if ($_POST['action'] == 'edit') {
	if (fCanSee(isset($bEdit))) {
		if ($valid = fTextDatabase($_POST['comment'], 131)) {
			$valid = '\'' . $valid . '\'';
		} elseif (strlen(trim($_POST['comment'])) == 0) {
			$valid = 'NULL';
		} else {
			$invalid = true;
		};
		if (isset($invalid)) {
			$_SESSION['sqlMessage'] = 'Record failed to update!';
			$_SESSION['uiState'] = 'error';
		} else {
			$queryUpdateDowntime = 'update records
			set comment=' . $valid . ', typefk = ' . $_POST['type'] . ', startdatetime = \'' . $_POST['startdate'] . '\', enddatetime = \'' . $_POST['enddate'] . '\', duration = ' . $duration . ' 
			where id = ' . $_POST['id'];
			odbc_exec($conn, $queryUpdateDowntime);
			$_SESSION['sqlMessage'] = 'Record updated!';
			$_SESSION['uiState'] = 'active';
		};
	} else {
		$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
		$_SESSION['uiState'] = 'error';
	};
};
if ($_POST['action'] == 'delete') {
	if (fCanSee(isset($bAdmin))) {
		$queryDeleteDowntime = 'delete from records
		where id = ' . $_POST['id'];
		odbc_exec($conn, $queryDeleteDowntime);
		$_SESSION['sqlMessage'] = 'OEE record deleted!';
		$_SESSION['uiState'] = 'active';
	} else {
		$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
		$_SESSION['uiState'] = 'error';
	};
};
fRedirect(); ?>