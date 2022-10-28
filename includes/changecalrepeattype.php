<?PHP
require_once 'functions.php';
if ($_SESSION['id'] != 1) {
	$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
if (isset($_POST['type']) && isset($_POST['repeat'])) {
	$formComplete = true;
};
if (isset($_POST['add'])) {
	if ($formComplete) {
		$queryNewReport = 'insert into ReportCalendarTypeRepeat (ReportCalendarTypeFK, ReportCalendarRepeatFK)
		values (' . $_POST['type'] . ', ' . $_POST['repeat'] . ')';
		odbc_exec($conn, $queryNewReport);
		$_SESSION['sqlMessage'] = 'Repeat Type added!';
		$_SESSION['uiState'] = 'active';
	} else {
		$_SESSION['sqlMessage'] = 'Repeat Type creation failed!';
		$_SESSION['uiState'] = 'error';
	};
};
if (isset($_POST['update'])) {
	if ($formComplete) {
		$queryUpdateReport = 'update ReportCalendarTypeRepeat
		set ReportCalendarTypeFK = ' . $_POST['type'] . ' , ReportCalendarRepeatFK = ' . $_POST['repeat'] . ' 
		where id =' . $_POST['update'];
		odbc_exec($conn, $queryUpdateReport);
		$_SESSION['sqlMessage'] = 'Repeat Option updated!';
		$_SESSION['uiState'] = 'active';
	} else {
		$_SESSION['sqlMessage'] = 'Repeat Option update failed!';
		$_SESSION['uiState'] = 'error';
	};
};
if (isset($_POST['delete'])) {
		$queryDeleteReport = 'delete from ReportCalendarTypeRepeat
		where id = ' . $_POST['delete'];
		odbc_exec($conn, $queryDeleteReport);
		$_SESSION['sqlMessage'] = 'Repeat Option deleted!';
		$_SESSION['uiState'] = 'active';
};
fRedirect(); ?>