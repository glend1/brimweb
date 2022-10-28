<?PHP 
$title = 'Read Mail';
require_once 'includes/header.php';
if (!fCanSee(isset($_SESSION['id']))) {
	$_SESSION['sqlMessage'] = 'You must be logged in to use this page!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
if (isset($_GET['id'])) {
	$queryGet = 'select subject, message, users.name, fromid, toid, readbit
	from message
	left join users on users.ID = fromid
	where message.id = ' . $_GET['id'];
	$dataGet = odbc_exec($conn, $queryGet);
	if (odbc_fetch_row($dataGet)) {
		$subject = odbc_result($dataGet, 1);
		$message = '';
		while($commentOut = odbc_result($dataGet, 2)) {
			$message .= $commentOut;
			unset($commentOut);
		};
		if (odbc_result($dataGet, 3)) {
			$from = odbc_result($dataGet, 3);
		} else {
			$from = 'System';
		};
		$fromid = odbc_result($dataGet, 4);
		$toid = odbc_result($dataGet, 5);
		$readbit = odbc_result($dataGet, 6);
		if (!($_SESSION['id'] == $fromid || $_SESSION['id'] == $toid)) {
			$_SESSION['sqlMessage'] = 'This is not your mail!';
			$_SESSION['uiState'] = 'error';
			fRedirect();
		} elseif ($_SESSION['id'] == $toid && odbc_result($dataGet, 6) == false) {
			$readbit = true;
			$queryUpdate = 'update message
			set readbit = 1
			where id = ' . $_GET['id'];
			odbc_exec($conn, $queryUpdate);
		};
	} else {
		$_SESSION['sqlMessage'] = 'No Mail Found!';
		$_SESSION['uiState'] = 'error';
		fRedirect();
	};
} else {
	$_SESSION['sqlMessage'] = 'No mail selected!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
$hookReplace['contexticon'] = '<a href="#" data-text="Context Sensitive Menu"  class="menucontext"><span class="icon-briefcase icon-hover-hint icon-large"></span></a>';
$stdOut .= fMailNav() . '<h2>' . $subject . '</h2>
<div id="mailbar">
	<a data-text="Forward" href="compose.php?id=' . $_GET['id'] . '&type=forward"><span class="icon-large icon-hover-hint icon-share-alt"></span></a>';
	if ($_SESSION['id'] == $toid) {
		$stdOut .= '<a data-text="Reply" href="compose.php?id=' . $_GET['id'] . '&type=reply"><span class="icon-large icon-hover-hint icon-reply"></span></a>';
		if ($readbit) {
			$stdOut .= '<a data-text="Flag as Unread" href="includes/changemail.php?action=unread&id=' . $_GET['id'] . '"><span class="icon-large icon-hover-hint icon-flag"></span></a>';
		};
	};
	if ($_SESSION['id'] == $toid || ($_SESSION['id'] == $fromid && odbc_result($dataGet, 6) == false)) {
		$stdOut .= '<a data-text="Delete" href="includes/changemail.php?action=delete&id=' . $_GET['id'] . '"><span class="icon-large icon-hover-hint icon-trash"></span></a>';
	};
$stdOut .= '</div>
<h3>From: ' . $from . '</h3><div>' . $message . '</div>';
$hookReplace['help'] .= $helptext['mailcontext'];
require_once 'includes/footer.php'; ?>