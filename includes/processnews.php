<?PHP 
require_once 'functions.php';
if ($_SESSION['id'] != 1) {
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
	$_SESSION['sqlMessage'] = 'Title Invalid!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
if (!($body = fTextDatabase($_POST['body'], 'na', true))) {
	$_SESSION['sqlMessage'] = 'Body Invalid!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
if (isset($_POST['id'])) {
	$queryUpdate = 'update news
	set title = \'' . $title . '\', news = \'' . $body . '\' 
	where id = ' . $_POST['id'];
	odbc_exec($conn, $queryUpdate);
	$_SESSION['sqlMessage'] = 'News Updated!';
	$_SESSION['uiState'] = 'active';
	header('Location:' . $urlPath . 'managenews.php');
} else {
	$timeUTC = date_create($localTime);
	date_timezone_set($timeUTC, timezone_open('UTC'));
	$queryInsert = 'insert into news (timestamp, title, news)
	values ( \'' . date_format($timeUTC, 'Y-m-d H:i:s') . '\', \'' . $title . '\', \'' . $body . '\')';
	odbc_exec($conn, $queryInsert);
	$_SESSION['sqlMessage'] = 'News Created!';
	$_SESSION['uiState'] = 'active';
	header('Location:' . $urlPath . 'managenews.php');
};
?>