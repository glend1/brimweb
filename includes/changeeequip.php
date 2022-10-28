<?PHP
require_once 'functions.php';
if (!fCanSee(@$_SESSION['permissions']['page'][20] >= 200)) {
	$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
if (isset($_POST['add'])) {
	if (isset($_POST['department']) && isset($_POST['equip'])) {
		if ($_POST['equip'] != "none") {
			if (fCanSee(@$_SESSION['permissions']['department'][$_POST['department']] >= 300)) {
				if ($valid = fTextDatabase($_POST['equip'], 61)) {
					if (!fCheckEquip($_POST['department'], $_POST['equip'])) {
						$queryNewEquip = 'insert into eequip (departmentfk, eequip)
						values (' . $_POST['department'] . ', \'' . $_POST['equip'] . '\')';
						odbc_exec($conn, $queryNewEquip);		
						$_SESSION['sqlMessage'] = 'Equip excluded!';
						$_SESSION['uiState'] = 'active';
					} else {
						$_SESSION['sqlMessage'] = 'Equip already exists!';
						$_SESSION['uiState'] = 'error';
					};
				} else {
					$_SESSION['sqlMessage'] = 'Equip invalid!';
					$_SESSION['uiState'] = 'error';
				};
			} else {
				$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
				$_SESSION['uiState'] = 'error';
			};
		} else {
			$_SESSION['sqlMessage'] = 'Equip not selected!';
			$_SESSION['uiState'] = 'error';
		};
	} else {
		$_SESSION['sqlMessage'] = 'Form incomplete!';
		$_SESSION['uiState'] = 'error';
	};
};
if (isset($_POST['update'])) {
	if (isset($_POST['id']) && isset($_POST['equip']) && isset($_POST['department'])) {
		if ($_POST['equip'] != "none") {
			if (fCanSee(@$_SESSION['permissions']['department'][$_POST['department']] >= 200)) {
				if ($valid = fTextDatabase($_POST['equip'], 61)) {
					if (!fCheckEquip($_POST['id'], $_POST['equip'])) {
					$queryUpdateDiscipline = 'update eequip
					set eequip = \'' . $_POST['equip'] . '\' 
					where id =' . $_POST['id'];
					odbc_exec($conn, $queryUpdateDiscipline);
					$_SESSION['sqlMessage'] = 'Equip updated!';
					$_SESSION['uiState'] = 'active';
					} else {
						$_SESSION['sqlMessage'] = 'Equip already exists!';
						$_SESSION['uiState'] = 'error';
					};
				} else {
					$_SESSION['sqlMessage'] = 'Equip invalid!';
					$_SESSION['uiState'] = 'error';
				};
			} else {
				$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
				$_SESSION['uiState'] = 'error';
			};
		} else {
			$_SESSION['sqlMessage'] = 'Equip not selected!';
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
			$queryDeleteEquip = 'delete from eequip where id = ' . $_POST['id'];
			odbc_exec($conn, $queryDeleteEquip);
			$_SESSION['sqlMessage'] = 'Equip deleted!';
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