<?PHP
require_once 'functions.php';
if ($_SESSION['id'] != 1) {
	$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
if (isset($_POST['add'])) {
	if ($valid = fTextDatabase($_POST['sname'], 61)) {
		$queryNewSubscriptionType = 'insert into subscriptiontype (Name)
		values (\'' . $valid . '\')';
		odbc_exec($conn, $queryNewSubscriptionType);
		$_SESSION['sqlMessage'] = 'Subscription Type added!';
		$_SESSION['uiState'] = 'active';
	} else {
		$_SESSION['sqlMessage'] = 'Subscription Type creation failed!';
		$_SESSION['uiState'] = 'error';
	};
};
if (isset($_POST['update'])) {
	if ($valid = fTextDatabase($_POST['sname'], 61)) {
		$queryUpdateSubscriptionType = 'update subscriptiontype
		set Name=\'' . $valid . '\'
		where id =' . $_POST['update'];
		odbc_exec($conn, $queryUpdateSubscriptionType);
		$_SESSION['sqlMessage'] = 'Subscription Type updated!';
		$_SESSION['uiState'] = 'active';
	} else {
		$_SESSION['sqlMessage'] = 'Subscription Type update failed!';
		$_SESSION['uiState'] = 'error';
	};
};
if (isset($_POST['delete'])) {
		$queryDeleteSubscriptionType = 'delete from subscriptiontype
		where id = ' . $_POST['delete'];
		odbc_exec($conn, $queryDeleteSubscriptionType);
		$_SESSION['sqlMessage'] = 'Subscription Type deleted!';
		$_SESSION['uiState'] = 'active';
};
fRedirect(); ?>