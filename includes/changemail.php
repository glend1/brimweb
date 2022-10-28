<?PHP 
require_once 'functions.php';
if (!fCanSee(isset($_SESSION['id']))) {
	$_SESSION['sqlMessage'] = 'You must be logged in to use this page!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
if (isset($_GET['id']) && isset($_GET['action'])) {
	$queryPermissions = 'select fromid, toid from message where id = ' . $_GET['id'];
	$dataPermissions = odbc_exec($conn, $queryPermissions);
	if (odbc_fetch_row($dataPermissions)) {
		switch ($_GET['action']) {
			case 'delete':
				if (!($_SESSION['id'] == odbc_result($dataPermissions, 1) || $_SESSION['id'] == odbc_result($dataPermissions, 2))) {
					$_SESSION['sqlMessage'] = 'This is not your mail!';
					$_SESSION['uiState'] = 'error';
					fRedirect();
				} else {
					$queryDelete = 'delete from message where id = ' . $_GET['id'];
					odbc_exec($conn, $queryDelete);
					$_SESSION['sqlMessage'] = 'Mail deleted!';
					$_SESSION['uiState'] = 'active';
					header('Location:../inbox.php');
				};
				break;
			case 'unread':
				if ($_SESSION['id'] == odbc_result($dataPermissions, 2)) {
					$queryUnread = 'update message set readbit = 0 where id = ' . $_GET['id'];
					$dataUnread = odbc_exec($conn, $queryUnread);
					$_SESSION['sqlMessage'] = 'Mail Flagged!';
					$_SESSION['uiState'] = 'active';
					header('Location:../inbox.php');
				} else {
					$_SESSION['sqlMessage'] = 'This is not your mail!';
					$_SESSION['uiState'] = 'error';
					fRedirect();
				};
				break;
		};
	};
} else {
	$_SESSION['sqlMessage'] = 'Form incomplete!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
?>