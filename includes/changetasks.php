<?PHP
require_once 'functions.php';
if (!fCanSee(isset($_SESSION['edit']['groupedit']))) {
	$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
function fCheckTask($group, $task) {
	global $conn;
	$query = 'select top 1 id from taskjunction where groupfk = ' . $group . ' and taskfk = ' . $task;
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
if (isset($_POST['add'])) {
	if (isset($_POST['group']) && isset($_POST['task'])) {
		if ($_POST['task'] != "none") {
			if (fCanSee(@$_SESSION['permissions']['group'][$_POST['group']] >= 300)) {
				if ($valid = fTextDatabase($_POST['task'], 61)) {
					if (!fCheckTask($_POST['group'], $_POST['task'])) {
						$queryNewAlarm = 'insert into taskjunction (groupfk, taskfk)
						values (' . $_POST['group'] . ', ' . $_POST['task'] . ')';
						odbc_exec($conn, $queryNewAlarm);		
						$_SESSION['sqlMessage'] = 'Task assigned!';
						$_SESSION['uiState'] = 'active';
					} else {
						$_SESSION['sqlMessage'] = 'Task already exists!';
						$_SESSION['uiState'] = 'error';
					};
				} else {
					$_SESSION['sqlMessage'] = 'Task invalid!';
					$_SESSION['uiState'] = 'error';
				};
			} else {
				$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
				$_SESSION['uiState'] = 'error';
			};
		} else {
			$_SESSION['sqlMessage'] = 'Task not selected!';
			$_SESSION['uiState'] = 'error';
		};
	} else {
		$_SESSION['sqlMessage'] = 'Form incomplete!';
		$_SESSION['uiState'] = 'error';
	};
};
if (isset($_POST['update'])) {
	if (isset($_POST['id']) && isset($_POST['task']) && isset($_POST['group'])) {
		if ($_POST['task'] != "none") {
			if (fCanSee(@$_SESSION['permissions']['group'][$_POST['group']] >= 200)) {
				if ($valid = fTextDatabase($_POST['task'], 61)) {
					if (!fCheckTask($_POST['id'], $_POST['task'])) {
					$queryUpdateDiscipline = 'update taskjunction
					set taskfk = ' . $_POST['task'] . '
					where id = ' . $_POST['id'];
					odbc_exec($conn, $queryUpdateDiscipline);
					$_SESSION['sqlMessage'] = 'Task updated!';
					$_SESSION['uiState'] = 'active';
					} else {
						$_SESSION['sqlMessage'] = 'Task already exists!';
						$_SESSION['uiState'] = 'error';
					};
				} else {
					$_SESSION['sqlMessage'] = 'Task invalid!';
					$_SESSION['uiState'] = 'error';
				};
			} else {
				$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
				$_SESSION['uiState'] = 'error';
			};
		} else {
			$_SESSION['sqlMessage'] = 'Task not selected!';
			$_SESSION['uiState'] = 'error';
		};
	} else {
		$_SESSION['sqlMessage'] = 'Form incomplete!';
		$_SESSION['uiState'] = 'error';
	};
};
if (isset($_POST['delete'])) {
	if (isset($_POST['id']) && isset($_POST['group'])) {
		if (fCanSee(@$_SESSION['permissions']['group'][$_POST['group']] >= 300)) {
			$queryDeleteAlarm = 'delete from taskjunction where id = ' . $_POST['id'];
			odbc_exec($conn, $queryDeleteAlarm);
			$_SESSION['sqlMessage'] = 'Task deleted!';
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