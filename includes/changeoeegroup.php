<?PHP
require_once 'functions.php';
if (!fCanSee(@$_SESSION['permissions']['page'][1] >= 300)) {
	$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
if (isset($_POST['add'])) {
		if ($valid = fTextDatabase($_POST['gname'], 61)) {
			$queryNewGroup = 'insert into oeegroup (name)
			values (\'' . $valid . '\')';
			odbc_exec($conn, $queryNewGroup);		
			$_SESSION['sqlMessage'] = 'OEE Group added!';
			$_SESSION['uiState'] = 'active';
		} else {
			$_SESSION['sqlMessage'] = 'OEE Group creation failed!';
			$_SESSION['uiState'] = 'error';
		};
};
if (isset($_POST['update'])) {
	if ($valid = fTextDatabase($_POST['gname'], 61)) {
		$queryUpdateGroup = 'update oeegroup
		set name=\'' . $_POST['gname'] . '\'
		where id =' . $_POST['update'];
		odbc_exec($conn, $queryUpdateGroup);
		$_SESSION['sqlMessage'] = 'OEE Group updated!';
		$_SESSION['uiState'] = 'active';
	} else {
		$_SESSION['sqlMessage'] = 'OEE Group failed to update!';
		$_SESSION['uiState'] = 'error';
	};
};
if (isset($_POST['delete'])) {
	$queryDeleteGroup = 'delete from oeegroup
	where id = ' . $_POST['delete'];
	odbc_exec($conn, $queryDeleteGroup);
	$_SESSION['sqlMessage'] = 'OEE Group deleted!';
	$_SESSION['uiState'] = 'active';
};
fRedirect(); ?>