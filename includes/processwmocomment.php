<?PHP 
require_once 'functions.php';
if (!isset($_SESSION['id'])) {
	$_SESSION['sqlMessage'] = 'You must be logged into to perform this action!';
	$_SESSION['uiState'] = 'error';
	$failed = true;
};
if (isset($_GET['delete'])) {
	if (!isset($_GET['id'])) {
		$_SESSION['sqlMessage'] = 'Form incomplete!';
		$_SESSION['uiState'] = 'error';
		$failed = true;
	};
} else {
	if (!isset($_POST['comment']) || !isset($_POST['id'])) {
		$_SESSION['sqlMessage'] = 'Form incomplete!';
		$_SESSION['uiState'] = 'error';
		$failed = true;
	} else {
		$strippedComment = trim(strip_tags($_POST['comment']));
	};
	if (empty($strippedComment) || !isset($_POST['id'])) {
		$_SESSION['sqlMessage'] = 'Form incomplete!';
		$_SESSION['uiState'] = 'error';
		$failed = true;
	};
	if (!($comment = fTextDatabase($_POST['comment'], 'na', true))) {
		$_SESSION['sqlMessage'] = 'Comment Invalid!';
		$_SESSION['uiState'] = 'error';
		$failed = true;
	};
};
if (!isset($failed)){
	if (isset($_POST['add'])) {
		if (isset($_POST['subscribe'])) {
			$querySubscription = 'select id
			from subscriptions
			where subscriptiontypefk = 1 and userfk = ' . $_SESSION['id'] . ' and subscriptionfk = ' . $_POST['id'];
			$dataSubscription = odbc_exec($conn, $querySubscription);
			if (!odbc_fetch_row($dataSubscription)) {
				$insertSubscription = 'insert into subscriptions (subscriptiontypefk, userfk, subscriptionfk)
				VALUES (1, ' . $_SESSION['id'] . ', ' . $_POST['id'] . ')';
				odbc_exec($conn, $insertSubscription);
			};
		};
		$timeUTC = date_create($localTime);
		date_timezone_set($timeUTC, timezone_open('UTC'));
		$datetimestamp = date_format($timeUTC, 'Y-m-d H:i:s');
		$queryInsert = 'insert into wmocomment (wmofk, submitteddatetime, userfk, comment) values (' . $_POST['id'] . ', \'' . $datetimestamp . '\', ' . $_SESSION['id'] . ', \'' . $comment . '\')';
		odbc_exec($conn, $queryInsert);
		$queryMessage = 'select userfk
		from subscriptions
		where subscriptionfk = ' . $_POST['id'] . ' and subscriptiontypefk = 1';
		$dataMessage = odbc_exec($conn, $queryMessage);
		while (odbc_fetch_row($dataMessage)) {
			$sendTo[] = odbc_result($dataMessage, 1);
		};
		if (isset($sendTo)) {
			$outMessage = 'New comment for <a href="viewwmo.php?id=' . $_POST['id'] . '">WMO #' . $_POST['id'] . '</a> posted';
			fSendMessages($sendTo, 1, $outMessage);
		};
		$_SESSION['sqlMessage'] = 'Comment Added!' . implode(', ', $outMessages);
		$_SESSION['uiState'] = 'active';
	};
	if (isset($_POST['edit'])) {
		$queryGetComment = 'select top 1 userfk, comment from wmocomment where id = ' . $_POST['id'];
		$dataGetComment = odbc_exec($conn, $queryGetComment);
		if (odbc_fetch_row($dataGetComment)) {
			if (!fCanSee($_SESSION['id'] == odbc_result($dataGetComment, 1))) {
				$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
				$_SESSION['uiState'] = 'error';
			} else {
				$updateComment = 'update wmocomment set comment = \'' . $comment . '\' where id = ' . $_POST['id'];
				odbc_exec($conn, $updateComment);
				$_SESSION['sqlMessage'] = 'Comment updated!';
				$_SESSION['uiState'] = 'active';
			};
		};
	};
	if (isset($_GET['delete'])) {
		$queryGetComment = 'select top 1 userfk, comment from wmocomment where id = ' . $_GET['id'];
		$dataGetComment = odbc_exec($conn, $queryGetComment);
		if (odbc_fetch_row($dataGetComment)) {
			if (!fCanSee($_SESSION['id'] == odbc_result($dataGetComment, 1))) {
				$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
				$_SESSION['uiState'] = 'error';
			} else {
				$deleteComment = 'delete from wmocomment where id = ' . $_GET['id'];
				odbc_exec($conn, $deleteComment);
				$_SESSION['sqlMessage'] = 'Comment deleted!';
				$_SESSION['uiState'] = 'active';
			};
		};
	};
};
fRedirect();
?>