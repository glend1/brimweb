<?PHP
require_once 'functions.php';
if (!isset($_SESSION['id'])) {
	$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
if (isset($_GET['delete'])) {
	$querySubscription = 'select top 1 id from subscriptions where id = ' . $_GET['delete'];
	$dataSubscription = odbc_exec($conn, $querySubscription);
	if (odbc_fetch_row($dataSubscription)) {
		$deleteSubscription = 'delete from subscriptions
		where id = ' . $_GET['delete'];
		odbc_exec($conn, $deleteSubscription);
		$_SESSION['sqlMessage'] = 'Subscription deleted!';
		$_SESSION['uiState'] = 'active';
	} else {
		$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
		$_SESSION['uiState'] = 'error';
	};
} else {
	$_SESSION['sqlMessage'] = 'No action selected!';
	$_SESSION['uiState'] = 'error';
};
fRedirect(); ?>