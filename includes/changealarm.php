<?PHP
require_once 'functions.php';
if (!fCanSee(@$_SESSION['permissions']['page'][12] >= 200)) {
	$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
function fCheckAlarm($departmentfk, $alarm) {
	global $conn;
	$query = 'select top 1 id from alarmgroup where departmentfk = ' . $departmentfk . ' and alarmgroup = \'' . $alarm . '\'';
	$data = odbc_exec($conn, $query);
	while(odbc_fetch_row($data)) {
		$set = true;
	};
	if (isset($set)) {
		return true;
	} else {
		return false;
	};
};
$equipment = 'NULL';
if (isset($_POST['equipment'])) {
	if ($_POST['equipment'] != 'none') {
		$equipment = $_POST['equipment'];
	};
};
if (isset($_POST['add'])) {
	if (isset($_POST['department']) && isset($_POST['alarm'])) {
		if ($_POST['alarm'] != "none") {
			if (fCanSee(@$_SESSION['permissions']['department'][$_POST['department']] >= 300)) {
				if ($valid = fTextDatabase($_POST['alarm'], 61)) {
					if (!fCheckAlarm($_POST['department'], $_POST['alarm'])) {
						$queryNewAlarm = 'insert into alarmgroup (departmentfk, alarmgroup, departmentequipmentfk)
						values (' . $_POST['department'] . ', \'' . $_POST['alarm'] . '\', ' . $equipment . ')';
						odbc_exec($conn, $queryNewAlarm);		
						$_SESSION['sqlMessage'] = 'Alarm assigned!';
						$_SESSION['uiState'] = 'active';
					} else {
						$_SESSION['sqlMessage'] = 'Alarm already exists!';
						$_SESSION['uiState'] = 'error';
					};
				} else {
					$_SESSION['sqlMessage'] = 'Alarm invalid!';
					$_SESSION['uiState'] = 'error';
				};
			} else {
				$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
				$_SESSION['uiState'] = 'error';
			};
		} else {
			$_SESSION['sqlMessage'] = 'Alarm not selected!';
			$_SESSION['uiState'] = 'error';
		};
	} else {
		$_SESSION['sqlMessage'] = 'Form incomplete!';
		$_SESSION['uiState'] = 'error';
	};
};
if (isset($_POST['update'])) {
	if (isset($_POST['id']) && isset($_POST['alarm']) && isset($_POST['department'])) {
		if ($_POST['alarm'] != "none") {
			if (fCanSee(@$_SESSION['permissions']['department'][$_POST['department']] >= 200)) {
				if ($valid = fTextDatabase($_POST['alarm'], 61)) {
					if (!fCheckAlarm($_POST['id'], $_POST['alarm'])) {
					$queryUpdateDiscipline = 'update alarmgroup
					set alarmgroup = \'' . $_POST['alarm'] . '\', departmentequipmentfk = ' . $equipment . ' 
					where id =' . $_POST['id'];
					odbc_exec($conn, $queryUpdateDiscipline);
					$_SESSION['sqlMessage'] = 'Alarm updated!';
					$_SESSION['uiState'] = 'active';
					} else {
						$_SESSION['sqlMessage'] = 'Alarm already exists!';
						$_SESSION['uiState'] = 'error';
					};
				} else {
					$_SESSION['sqlMessage'] = 'Alarm invalid!';
					$_SESSION['uiState'] = 'error';
				};
			} else {
				$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
				$_SESSION['uiState'] = 'error';
			};
		} else {
			$_SESSION['sqlMessage'] = 'Alarm not selected!';
			$_SESSION['uiState'] = 'error';
		};
	} else {
		$_SESSION['sqlMessage'] = 'Form incomplete!';
		$_SESSION['uiState'] = 'error';
	};
};
if (isset($_POST['delete'])) {
	if (isset($_POST['id']) && isset($_POST['department'])) {
		if (fCanSee(@$_SESSION['permissions']['department'][$_POST['department']] >= 300)) {
			$queryDeleteAlarm = 'delete from alarmgroup where id = ' . $_POST['id'];
			odbc_exec($conn, $queryDeleteAlarm);
			$_SESSION['sqlMessage'] = 'Alarm deleted!';
			$_SESSION['uiState'] = 'active';
		} else {
			$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
			$_SESSION['uiState'] = 'error';
		};
	} else {
		$_SESSION['sqlMessage'] = 'Form incomplete!';
		$_SESSION['uiState'] = 'error';
	};
};
fRedirect(); ?>