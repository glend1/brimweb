<?PHP 
$title = 'Manage News';
require_once 'includes/header.php';
if ($_SESSION['id'] != 1) {
	$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
$hookReplace['searchicon'] = '<a href="#" data-text="Search" class="menucontext"><span class="icon-search icon-hover-hint icon-large"></span></a>';
$hookReplace['searchform'] = '<form id="search" action="managenews.php" method="get">';
if (isset($_GET['search'])) {
	$hookReplace['searchform'] .= '<a href="managenews.php"><span class="icon-remove-sign icon-large"></span></a> ';
};
$hookReplace['searchform'] .= '<input name="search" type="text"';
if (isset($_GET['search'])) {
	$hookReplace['searchform'] .= ' value="' . $_GET['search'] . '" ';
};
$hookReplace['searchform'] .= '/><input type="submit" value="Search!" /></form>';
$stdOut .= '<form class="createform" action="newsform.php" method="post"><h3>Create News</h3>
	<input type="submit" name="add" value="Create"></form>';
$querySelect = 'select id, timestamp, title 
from news';
if (isset($_GET['search'])) {
	$querySelect .= ' where title like \'%' . $_GET['search'] . '%\'';
};
$querySelect .= ' order by timestamp asc';
$row = 0;
$dataSelect = odbc_exec($conn, $querySelect);
$newsTable = '';
while (odbc_fetch_row($dataSelect)) {
	if ($row % 2 == 0) {
		$newsTable .= '<tr class="oddRow">';
	} else {
		$newsTable .= '<tr class="evenRow">';
	};
	$row++;
	$newsTable .= '<td>' . substr(odbc_result($dataSelect, 2), 0, -4) . '</td><td><a href="newsform.php?id=' . odbc_result($dataSelect, 1) . '">' . odbc_result($dataSelect, 3) . '</a></td><td><a href="includes/deletenews.php?id=' . odbc_result($dataSelect, 1) . '">Delete</a></td></tr>';
};
if (!empty($newsTable)) {
	$stdOut .= '<table class="recordssmall"><thead><tr><th>Timestamp</th><th>Title</th><th>Action</th></tr></thead><tbody>' . $newsTable . '</tbody></table>';
} else {
	$stdOut .= '<h2>No News Items</h2>';
};
require_once 'includes/footer.php'; ?>