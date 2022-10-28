<?PHP 
require_once 'functions.php';
if (!fCanSee(isset($_SESSION['edit']['groupedit']))) {
	$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
$strippedBody = trim(strip_tags($_POST['body']));
if (empty($_POST['title']) || empty($strippedBody)) {
	$_SESSION['sqlMessage'] = 'Form incomplete!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
if (!($title = fTextDatabase($_POST['title'], 225))) {
	$_SESSION['sqlMessage'] = 'Task Invalid!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
if (!($body = fTextDatabase($_POST['body'], 'na', true))) {
	$_SESSION['sqlMessage'] = 'HTML Invalid!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
if (isset($_POST['id'])) {
	$queryUpdate = 'update tasks
	set title = \'' . $title . '\', HTML = \'' . $body . '\' 
	where id = ' . $_POST['id'];
	odbc_exec($conn, $queryUpdate);
	$_SESSION['sqlMessage'] = 'Task Updated!';
	$_SESSION['uiState'] = 'active';
	header('Location:' . $urlPath . 'managetasktypes.php');
} else {
	$timeUTC = date_create($localTime);
	date_timezone_set($timeUTC, timezone_open('UTC'));
	$queryInsert = 'insert into tasks (title, HTML)
	values (\'' . $title . '\', \'' . $body . '\')';
	odbc_exec($conn, $queryInsert);
	$_SESSION['sqlMessage'] = 'Task Created!';
	$_SESSION['uiState'] = 'active';
	header('Location:' . $urlPath . 'managetasktypes.php');
};
?>