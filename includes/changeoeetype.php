<?PHP
require_once 'functions.php';
if (!fCanSee(@$_SESSION['permissions']['page'][1] >= 200)) {
	$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
if (isset($_POST['add']) || isset($_POST['update'])) {
	if (isset($_POST['departmentequipment'])) {
		$queryDeptEquip = 'select top 1 departmentfk from departmentequipment where id = ' . $_POST['departmentequipment'];
		$dataDeptEquip = odbc_exec($conn, $queryDeptEquip);
		if (odbc_fetch_row($dataDeptEquip)) {
			$department = odbc_result($dataDeptEquip, 1);
		} else {
			$_SESSION['sqlMessage'] = 'Associated department not found!';
			$_SESSION['uiState'] = 'error';
			fRedirect();
		};
	} else {
		$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
		$_SESSION['uiState'] = 'error';
		fRedirect();
	};
	if (!(isset($_POST['discipline']) && isset($_POST['departmentequipment']) && isset($_POST['name']) && isset($_POST['category']))) {
		$_SESSION['sqlMessage'] = 'You must complete the form!';
		$_SESSION['uiState'] = 'error';
		fRedirect();
	} else {
		$queryCheck = 'select id from type where oeenamefk = ' . $_POST['name'] . ' and departmentequipmentfk = ' . $_POST['departmentequipment'] . ' and disciplinefk = ' . $_POST['discipline'] . ' and oeecategoryfk = ' . $_POST['category'];
		$dataCheck = odbc_exec($conn, $queryCheck);
		if (odbc_fetch_row($dataCheck)) {
			$_SESSION['sqlMessage'] = 'Type already exists!';
			$_SESSION['uiState'] = 'error';
			fRedirect();
		};
	};
};
if (isset($_POST['add'])) {
	if ($_SESSION['permissions']['page'][1] >= 300 && $_SESSION['permissions']['department'][$department] >= 300) {
		$bAdmin = TRUE;
	};
	if (fCanSee(isset($bAdmin))) {
		$queryNewDowntime = 'insert into type (oeenamefk, departmentequipmentfk, disciplinefk, oeecategoryfk)
		values (' . $_POST['name'] . ', ' . $_POST['departmentequipment'] . ', ' . $_POST['discipline'] . ', ' . $_POST['category'] . ')';
		odbc_exec($conn, $queryNewDowntime);
		$_SESSION['sqlMessage'] = 'Type added!';
		$_SESSION['uiState'] = 'active';
	} else {
		$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
		$_SESSION['uiState'] = 'error';
	};
};
if (isset($_POST['update'])) {
	if ($_SESSION['permissions']['page'][1] >= 200 && $_SESSION['permissions']['department'][$department] >= 200) {
		$bEdit = TRUE;
	};
	if (isset($_POST['id'])) {
		$queryType = 'select top 1 type.id
		from type
		join departmentequipment on departmentequipmentfk = departmentequipment.id
		where type.id = ' . $_POST['id'];
		if (@$_SESSION['id'] != 1 && isset($_SESSION['id'])) {
			$queryType .= ' and ' . fOrThemReturn($_SESSION['permissions']['department'], 200, 'departmentfk');
		};
		$dataType = odbc_exec($conn, $queryType);
		if (!odbc_fetch_row($dataType)) {
			$_SESSION['sqlMessage'] = 'Type not found!';
			$_SESSION['uiState'] = 'error';
			fRedirect();
		} else {
			$permission = true;
		};
		if (fCanSee(isset($bEdit)) && isset($permission)) {
			$queryUpdateDowntime = 'update type
			set oeenamefk=' . $_POST['name'] . ', departmentequipmentfk = ' . $_POST['departmentequipment'] . ', disciplinefk = ' . $_POST['discipline'] .  ', oeecategoryfk = ' . $_POST['category'] . ' 
			where id =' . $_POST['id'];
			odbc_exec($conn, $queryUpdateDowntime);
			$_SESSION['sqlMessage'] = 'Type updated!';
			$_SESSION['uiState'] = 'active';
		} else {
			$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
			$_SESSION['uiState'] = 'error';
		};
	} else {
		$_SESSION['sqlMessage'] = 'Please Complete the form!';
		$_SESSION['uiState'] = 'error';
		fRedirect();
	};
};
if (isset($_POST['delete'])) {
	$queryCanDelete = 'select top 1 departmentfk, disciplinefk from type join DepartmentEquipment on DepartmentEquipmentFK = departmentequipment.id where type.id = ' . $_POST['delete'];
	$dataCanDelete = odbc_exec($conn, $queryCanDelete);
	if (odbc_fetch_row($dataCanDelete)) {
		if ($_SESSION['permissions']['page'][1] >= 300 && $_SESSION['permissions']['department'][odbc_result($dataCanDelete, 1)] >= 300) {
			$bAdmin = TRUE;
		};
	};
	if (fCanSee(isset($bAdmin))) {
		$queryDeleteDowntime = 'delete from type
		where id = ' . $_POST['delete'];
		odbc_exec($conn, $queryDeleteDowntime);
		$queryDeleteRecords = 'delete from records
		where typefk = ' . $_POST['delete'];
		odbc_exec($conn, $queryDeleteRecords);
		$_SESSION['sqlMessage'] = 'Type deleted!';
		$_SESSION['uiState'] = 'active';
	} else {
		$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
		$_SESSION['uiState'] = 'error';
	};
};
fRedirect(); ?>