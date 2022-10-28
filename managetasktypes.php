<?PHP 
$title = 'Manage Task Types';
require_once 'includes/header.php';
if (!fCanSee(isset($_SESSION['edit']['groupedit']))) {
	$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
$hookReplace['searchicon'] = '<a href="#" data-text="Search" class="menucontext"><span class="icon-search icon-hover-hint icon-large"></span></a>';
$hookReplace['searchform'] = '<form id="search" action="managetasktypes.php" method="get">';
if (isset($_GET['search'])) {
	$hookReplace['searchform'] .= '<a href="managetasktypes.php"><span class="icon-remove-sign icon-large"></span></a> ';
};
$hookReplace['searchform'] .= '<input name="search" type="text"';
if (isset($_GET['search'])) {
	$hookReplace['searchform'] .= ' value="' . $_GET['search'] . '" ';
};
$hookReplace['searchform'] .= '/><input type="submit" value="Search!" /></form>';
$stdOut .= '<form class="createform" action="taskform.php" method="post"><h3>Create Tasks</h3>
	<input type="submit" name="add" value="Create"></form>';
$querySelect = 'select id, title 
from tasks';
if (isset($_GET['search'])) {
	$querySelect .= ' where title like \'%' . $_GET['search'] . '%\' and title <> \'Register\'';
} else {
	$querySelect .= ' where title <> \'Register\'';
};
$querySelect .= ' order by title asc';
$row = 0;
$dataSelect = odbc_exec($conn, $querySelect);
$taskTable = '';
while (odbc_fetch_row($dataSelect)) {
	if ($row % 2 == 0) {
		$taskTable .= '<tr class="oddRow">';
	} else {
		$taskTable .= '<tr class="evenRow">';
	};
	$row++;
	$taskTable .= '<td><a href="taskform.php?id=' . odbc_result($dataSelect, 1) . '">' . odbc_result($dataSelect, 2) . '</a></td><td><a href="includes/deletetask.php?id=' . odbc_result($dataSelect, 1) . '">Delete</a></td></tr>';
};
if (!empty($taskTable)) {
	$stdOut .= '<table class="recordssmall"><thead><tr><th>Title</th><th>Action</th></tr></thead><tbody>' . $taskTable . '</tbody></table>';
} else {
	$stdOut .= '<h2>No Task Types</h2>';
};
require_once 'includes/footer.php'; ?>