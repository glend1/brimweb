<?PHP 
$title = 'Manage Reports';
require_once 'includes/header.php';
if ($_SESSION['id'] != 1) {
	$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
$hookReplace['searchicon'] = '<a href="#" data-text="Search" class="menucontext"><span class="icon-search icon-hover-hint icon-large"></span></a>';
$hookReplace['searchform'] = '<form id="search" action="managecalreportssplash.php" method="get">';
if (isset($_GET['search'])) {
	$hookReplace['searchform'] .= '<a href="managecalreportssplash.php"><span class="icon-remove-sign icon-large"></span></a> ';
};
$hookReplace['searchform'] .= '<input name="search" type="text"';
if (isset($_GET['search'])) {
	$hookReplace['searchform'] .= ' value="' . $_GET['search'] . '" ';
};
$hookReplace['searchform'] .= '/><input type="submit" value="Search!" /></form>';
$queryReports = 'select id, Name from reportcalendarrange';
if (isset($_GET['search'])) {
	$queryReports .= ' where name like \'%' . $_GET['search'] . '%\'';
};
$dataReports = odbc_exec($conn, $queryReports);
$sReportsTable = '<form class="createform" action="managecalreports.php" method="get"><h3>Create Report</h3>
	<input class="validatetextbutton" type="submit" name="add" value="Create"></form><table class="recordssmall"><thead><tr><th>Report</th><th>Action</th></tr></thead><tbody>';
$row = 0;
while (odbc_fetch_row($dataReports)) {
	if ($row % 2 == 0) {
		$sReportsTable .= '<tr class="oddRow">';
	} else {
		$sReportsTable .= '<tr class="evenRow">';
	};
	$row++;
	$sReportsTable .= '<td>' . odbc_result($dataReports, 2) . '</td><td><form action="managecalreports.php" method="get"><input type="hidden" name="id" value="' . odbc_result($dataReports, 1) . '"><input class="validatetextbutton" type="submit" value="Update"></form><form action="includes/changecalreports.php" method="post"><input type="hidden" name="delete" value="' . odbc_result($dataReports, 1) . '"><input type="submit" value="Delete"></form></td></tr>';
};
$sReportsTable .= '</tbody></table>';
$stdOut .= $sReportsTable;
$hookReplace['help'] = $helptext['search'] . $helptext['update'] . $helptext['delete'] . $helptext['add'] . $helptext['groupadmin'];
require_once 'includes/footer.php'; ?>