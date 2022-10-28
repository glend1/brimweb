<?PHP
require_once 'functions.php';
if ($_SESSION['id'] != 1) {
	$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
if (isset($_POST['add'])) {
	if ($valid = fTextDatabase($_POST['rname'], 61)) {
		$queryNewReport = 'insert into ReportCalendarType (Name)
		values (\'' . $valid . '\')';
		odbc_exec($conn, $queryNewReport);
		$_SESSION['sqlMessage'] = 'Schedule Type added!';
		$_SESSION['uiState'] = 'active';
	} else {
		$_SESSION['sqlMessage'] = 'Schedule Type creation failed!';
		$_SESSION['uiState'] = 'error';
	};
};
if (isset($_POST['update'])) {
	if ($valid = fTextDatabase($_POST['rname'], 61)) {
		$queryUpdateReport = 'update ReportCalendarType
		set Name =\'' . $valid . '\'
		where id =' . $_POST['update'];
		odbc_exec($conn, $queryUpdateReport);
		$_SESSION['sqlMessage'] = 'Schedule Type updated!';
		$_SESSION['uiState'] = 'active';
	} else {
		$_SESSION['sqlMessage'] = 'Schedule Type update failed!';
		$_SESSION['uiState'] = 'error';
	};
};
if (isset($_POST['delete'])) {
		$queryDeleteReport = 'delete from ReportCalendarType
		where id = ' . $_POST['delete'];
		odbc_exec($conn, $queryDeleteReport);
		$_SESSION['sqlMessage'] = 'Schedule Type deleted!';
		$_SESSION['uiState'] = 'active';
};
fRedirect(); ?>