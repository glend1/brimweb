<?PHP
require_once 'functions.php';
if (!isset($_SESSION['id'])) {
	$_SESSION['sqlMessage'] = 'You must be logged in to perform this action!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
if (!isset($_GET['id']) || !isset($_GET['action'])) {
	$_SESSION['sqlMessage'] = 'You must complete the form!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
$queryWho = 'select top 1 SubmittedUserFK from wmo where id = ' . $_GET['id'];
$dataWho = odbc_exec($conn, $queryWho);
if (odbc_fetch_row($dataWho)) {
	if (odbc_result($dataWho, 1) == $_SESSION['id']) {
		$_SESSION['sqlMessage'] = 'You cannot vote on WMOs you\'ve created!';
		$_SESSION['uiState'] = 'error';
		fRedirect();
	};
} else {
	$_SESSION['sqlMessage'] = 'WMO not found!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
$queryVoted = 'select top 1 id from wmovotes where userfk = ' . $_SESSION['id'] . ' and wmofk = ' . $_GET['id'];
$dataVoted = odbc_exec($conn, $queryVoted);
if (odbc_fetch_row($dataVoted)) {
	$haveVoted = True;
};
$querySubscription = 'select id
from subscriptions
where subscriptiontypefk = 1 and userfk = ' . $_SESSION['id'] . ' and subscriptionfk = ' . $_GET['id'];
$dataSubscription = odbc_exec($conn, $querySubscription);
if (!odbc_fetch_row($dataSubscription)) {
	$insertSubscription = 'insert into subscriptions (subscriptiontypefk, userfk, subscriptionfk)
	VALUES (1, ' . $_SESSION['id'] . ', ' . $_GET['id'] . ')';
	odbc_exec($conn, $insertSubscription);
};
switch ($_GET['action']) {
	case 'up':
		if (isset($haveVoted)) {
			$queryVote = 'update wmovotes set rating = 1 where userfk = ' . $_SESSION['id'] . ' and wmofk = ' . $_GET['id'];
		} else {
			$queryVote = 'insert into wmovotes (wmofk, userfk, rating) values (' . $_GET['id'] . ', ' . $_SESSION['id'] . ', ' . '1 )';
		};
		break;
	case 'down':
		if (isset($haveVoted)) {
			$queryVote = 'update wmovotes set rating = -1 where userfk = ' . $_SESSION['id'] . ' and wmofk = ' . $_GET['id'];
		} else {
			$queryVote = 'insert into wmovotes (wmofk, userfk, rating) values (' . $_GET['id'] . ', ' . $_SESSION['id'] . ', ' . '-1 )';
		};
		break;
	case 'minus':
		if (isset($haveVoted)) {
			$queryVote = 'delete from wmovotes where userfk = ' . $_SESSION['id'] . ' and wmofk = ' . $_GET['id'];
		} else {
			//nothing
		};
		break;
};
if (isset($queryVote)) {
	odbc_exec($conn, $queryVote);
	$_SESSION['sqlMessage'] = 'Vote counted!';
	$_SESSION['uiState'] = 'active';
} else {
	$_SESSION['sqlMessage'] = 'Voting failed!';
	$_SESSION['uiState'] = 'error';
};
odbc_close($conn);
fRedirect(); ?>