<?PHP 
require_once 'functions.php';
if ($_SESSION['id'] != 1) {
	$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
if (isset($_GET['id'])) {
	$queryDelete = 'delete from news
	where id = ' . $_GET['id'];
	odbc_exec($conn, $queryDelete);
	$_SESSION['sqlMessage'] = 'News Deleted!';
	$_SESSION['uiState'] = 'active';
	header('Location:' . $urlPath . 'managenews.php');
} else {
	$_SESSION['sqlMessage'] = 'No news item selected!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
?>