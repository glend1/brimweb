<?PHP 
require_once 'functions.php';
if (!fCanSee(isset($_SESSION['edit']['groupedit']))) {
	$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
if (isset($_GET['id'])) {
	$queryDelete = 'delete from tasks
	where id = ' . $_GET['id'];
	odbc_exec($conn, $queryDelete);
	$_SESSION['sqlMessage'] = 'Task Deleted!';
	$_SESSION['uiState'] = 'active';
	header('Location:' . $urlPath . 'managetasktypes.php');
} else {
	$_SESSION['sqlMessage'] = 'No task selected!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
?>