<?PHP
require_once 'functions.php';
if (!fCanSee(@$_SESSION['permissions']['page'][10] >= 100)) {
	$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
if (!isset($_GET['delete'])) {
	if (!isset($_POST['data'])) {
		$_SESSION['sqlMessage'] = 'No Tagnames selected!';
		$_SESSION['uiState'] = 'error';
		fRedirect();
	};
	if (!isset($_POST['title'])) {
		$_SESSION['sqlMessage'] = 'No Title given!';
		$_SESSION['uiState'] = 'error';
		fRedirect();
	} elseif (strlen($_POST['title']) == 0) {
		$_SESSION['sqlMessage'] = 'No Title given!';
		$_SESSION['uiState'] = 'error';
		fRedirect();
	} else {
		if (!($valid = fTextDatabase($_POST['title'], 61))) {
			$_SESSION['sqlMessage'] = 'Title not valid!';
			$_SESSION['uiState'] = 'error';
			fRedirect();
		};
	};
	fPermissionDE();
	if (!isset($_POST['department'])) {
		$_SESSION['sqlMessage'] = 'No Department selected!';
		$_SESSION['uiState'] = 'error';
		fRedirect();
	} else {
		if ($_POST['department'] == 'none') {
			$_SESSION['sqlMessage'] = 'No Department selected!';
			$_SESSION['uiState'] = 'error';
			fRedirect();
		};
	};
	$department = $_POST['department'];
	$equipment = 'null';
	if (isset($_POST['equipment'])) {
		if ($_POST['equipment'] != 'none') {
			$equipment = $_POST['equipment'];
		};
	};
	foreach ($_POST['data'] as $array) {
		if (!(fValidFloat($array['max']) && fValidFloat($array['min']) && fValidNumber($array['dp']) && isset($array['type']))) {
			$_SESSION['sqlMessage'] = 'Form data invalid!';
			$_SESSION['uiState'] = 'error';
			fRedirect();
		};
	};
	if (isset($_POST['public'])) {
		$public = 1;
	} else {
		$public = 0;
	};
	If (isset($_POST['id'])) {
		$queryCheck = 'select top 1 userfk from processtrend where id = ' . $_POST['id'];
		$dataCheck = odbc_exec($conn, $queryCheck);
		if (odbc_fetch_row($dataCheck)) {
			if (fCanSee(odbc_result($dataCheck, 1) == $_SESSION['id'])) {
				$update = $_POST['id'];
			};
		};
		if (!isset($update)) {
			$add = true;
		};
	} else {
		$add = true;
	};
	if (isset($add)) {
		$queryInsert = 'insert into ProcessTrend (UserFK, Name, JSON, PublicBool, DepartmentFK, DepartmentEquipmentFK)
		values (' . $_SESSION['id'] . ', \'' . $valid . '\', \'' . json_encode($_POST['data']) . '\',' . $public . ', ' . $department . ', ' . $equipment . ')';
		odbc_exec($conn, $queryInsert);
		$_SESSION['sqlMessage'] = 'Trend created!';
		$_SESSION['uiState'] = 'active';
	} elseif (isset($update)) {
		$queryUpdate = 'update ProcessTrend
		set name = \'' . $valid . '\', json = \'' . json_encode($_POST['data']) . '\', publicbool = ' . $public . ', departmentfk = ' . $department . ', departmentequipmentfk = ' . $equipment . ' where id = ' . $update;
		odbc_exec($conn, $queryUpdate);
		$_SESSION['sqlMessage'] = 'Trend updated!';
		$_SESSION['uiState'] = 'active';
	};
} else { 
	$queryCheck = 'select top 1 userfk from processtrend where id = ' . $_GET['delete'];
	$dataCheck = odbc_exec($conn, $queryCheck);
	if (odbc_fetch_row($dataCheck)) {
		if (fCanSee(odbc_result($dataCheck, 1) == $_SESSION['id'])) {
			$queryDeleteMain = 'delete from processtrend where id = ' . $_GET['delete'];
			odbc_exec($conn, $queryDeleteMain);
			$queryDeleteShare = 'delete from processtrendshare where processtrendfk = ' . $_GET['delete'];
			odbc_exec($conn, $queryDeleteShare);
			$_SESSION['sqlMessage'] = 'Trend deleted!';
			$_SESSION['uiState'] = 'active';
		} else {
			$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
			$_SESSION['uiState'] = 'error';
			fRedirect();
		};
	};
};
fRedirect();
?>