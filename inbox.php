<?PHP 
$title = 'Inbox';
require_once 'includes/header.php';
if (!fCanSee(isset($_SESSION['id']))) {
	$_SESSION['sqlMessage'] = 'You must be logged in to use this page!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
$hookReplace['contexticon'] = '<a href="#" data-text="Context Sensitive Menu"  class="menucontext"><span class="icon-briefcase icon-hover-hint icon-large"></span></a>';
$stdOut .= fMailNav('inbox');
$queryTotal = 'select count(*)
from message
where toid = ' . $_SESSION['id'];
$dataTotal = odbc_exec($conn, $queryTotal);
if (odbc_fetch_row($dataTotal)) {
	$stdOut .= '<table class="records mail">
	<thead><tr><th>Time Sent (GMT)</th><th>From</th><th>Subject</th><th>Read</th><th>Action</th></tr></thead>
	<tbody>';
	$page = 1;
	if (isset($_GET['page'])) {
		$page = $_GET['page'];
	};
	$items = 40;
	$queryInbox = 'select * from (select top ' . $items . ' * from (select top ' . ($page * $items). ' message.id, timestamp, users.name, subject, readbit, fromid
	from message
	left join users on fromid = users.id
	where toid = ' . $_SESSION['id'] . '
	order by readbit asc, timestamp desc) as temp
	order by readbit desc, timestamp asc) as temp2
	order by readbit asc, timestamp desc';
	$dataInbox = odbc_exec($conn, $queryInbox);
	$row = 1;
	while(odbc_fetch_row($dataInbox)) {
		$localTime = date_create(date(odbc_result($dataInbox, 2)));
		//print(date_format($localTime, 'Y-m-d H:i:s')); UTC // actually GMT/BST
		date_timezone_set($localTime, timezone_open('Europe/London'));
		//print(date_format($localTime, 'Y-m-d H:i:s')); GMT/BST // actually UTC
		$stdOut .= '<tr class="evenRow">';
		if ($row % 2 == 0) {
			$stdOut .= '<tr class="oddRow">';
		} else {
			$stdOut .= '<tr class="evenRow">';
		};
		$row++;
		$stdOut .= '<td>' . date_format($localTime, 'Y-m-d h:i:s A') . '</td>
		<td>';
		if (odbc_result($dataInbox, 3)) {
			$stdOut .= '<a href="compose.php?to=' . odbc_result($dataInbox, 3) . '">' . odbc_result($dataInbox, 3) . '</a>';
		} else {
			$stdOut .= 'System';
		};
		$stdOut .= '</td>
		<td><a href="mail.php?id=' . odbc_result($dataInbox, 1) . '">' . odbc_result($dataInbox, 4) . '</a></td>
		<td>';
		switch (odbc_result($dataInbox, 5)) {
			case 0:
				$stdOut .= 'Unread';
				break;
			case 1:
				$stdOut .= 'Read';
				break;
		};
		$stdOut .= '</td><td>';
		if (odbc_result($dataInbox, 3)) {
			$stdOut .= '<a data-text="Forward" href="compose.php?id=' . odbc_result($dataInbox, 1) . '&type=forward"><span class="icon-large icon-hover-hint icon-share-alt"></span></a>
			<a data-text="Reply" href="compose.php?id=' . odbc_result($dataInbox, 6) . '&type=reply"><span class="icon-large icon-hover-hint icon-reply"></span></a>';
		};
		$stdOut .= '<a data-text="Delete" href="includes/changemail.php?action=delete&id=' . odbc_result($dataInbox, 1) . '"><span class="icon-large icon-hover-hint icon-trash"></span></a>';
		$stdOut .= '</td></tr>';
	};
	$stdOut .= '</tbody><tfoot><tr><td colspan=5">';
	$totalPages = ceil((odbc_result($dataTotal, 1)) / $items);
	if ($page < $totalPages) {
		$stdOut .= '<a class="next-news" href="inbox.php?page=' . ($page + 1) . '">Next Page</a>';
	};
	if ($page > 1) {
		$stdOut .= '<a class="previous-news" href="inbox.php?page=' . ($page - 1) . '">Previous Page</a>';
	};
	$stdOut .= '</td></tr></tfoot></table>';
} else {
	$stdOut .= '<h2>No Mail</h2>';
};
$hookReplace['help'] .= $helptext['mailcontext'];
require_once 'includes/footer.php'; ?>