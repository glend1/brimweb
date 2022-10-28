<?PHP
require_once 'functions.php';
if (!fCanSee(@$_SESSION['permissions']['page'][4] >= 200)) {
	$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
function fCheckTrain($departmentfk, $train) {
	global $conn;
	$query = 'select top 1 id from train where departmentfk = ' . $departmentfk . ' and train = \'' . $train . '\'';
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
	if (isset($_POST['department']) && isset($_POST['train'])) {
		if ($_POST['train'] != "none") {
			if (fCanSee(@$_SESSION['permissions']['department'][$_POST['department']] >= 300)) {
				if ($valid = fTextDatabase($_POST['train'], 61)) {
					if (!fCheckTrain($_POST['department'], $_POST['train'])) {
						$queryNewTrain = 'insert into train (departmentfk, train, departmentequipmentfk)
						values (' . $_POST['department'] . ', \'' . $_POST['train'] . '\', ' . $equipment . ')';
						odbc_exec($conn, $queryNewTrain);		
						$_SESSION['sqlMessage'] = 'Train assigned!';
						$_SESSION['uiState'] = 'active';
					} else {
						$_SESSION['sqlMessage'] = 'Train already exists!';
						$_SESSION['uiState'] = 'error';
					};
				} else {
					$_SESSION['sqlMessage'] = 'Train invalid!';
					$_SESSION['uiState'] = 'error';
				};
			} else {
				$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
				$_SESSION['uiState'] = 'error';
			};
		} else {
			$_SESSION['sqlMessage'] = 'Train not selected!';
			$_SESSION['uiState'] = 'error';
		};
	} else {
		$_SESSION['sqlMessage'] = 'Form incomplete!';
		$_SESSION['uiState'] = 'error';
	};
};
if (isset($_POST['update'])) {
	if (isset($_POST['id']) && isset($_POST['train']) && isset($_POST['department'])) {
		if ($_POST['train'] != "none") {
			if (fCanSee(@$_SESSION['permissions']['department'][$_POST['department']] >= 200)) {
				if ($valid = fTextDatabase($_POST['train'], 61)) {
					if (!fCheckTrain($_POST['id'], $_POST['train'])) {
					$queryUpdateDiscipline = 'update train
					set train = \'' . $_POST['train'] . '\', departmentequipmentfk = ' . $equipment . ' 
					where id =' . $_POST['id'];
					odbc_exec($conn, $queryUpdateDiscipline);
					$_SESSION['sqlMessage'] = 'Train updated!';
					$_SESSION['uiState'] = 'active';
					} else {
						$_SESSION['sqlMessage'] = 'Train already exists!';
						$_SESSION['uiState'] = 'error';
					};
				} else {
					$_SESSION['sqlMessage'] = 'Train invalid!';
					$_SESSION['uiState'] = 'error';
				};
			} else {
				$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
				$_SESSION['uiState'] = 'error';
			};
		} else {
			$_SESSION['sqlMessage'] = 'Train not selected!';
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
			$queryDeleteTrain = 'delete from train where id = ' . $_POST['id'];
			odbc_exec($conn, $queryDeleteTrain);
			$_SESSION['sqlMessage'] = 'Train deleted!';
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