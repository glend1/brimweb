<?PHP 
$title = 'Manage Schedule Types';
require_once 'includes/header.php';
if ($_SESSION['id'] != 1) {
	$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
$hookReplace['searchicon'] = '<a href="#" data-text="Search" class="menucontext"><span class="icon-search icon-hover-hint icon-large"></span></a>';
$hookReplace['searchform'] = '<form id="search" action="managecaltypes.php" method="get">';
if (isset($_GET['search'])) {
	$hookReplace['searchform'] .= '<a href="managecaltypes.php"><span class="icon-remove-sign icon-large"></span></a> ';
};
$hookReplace['searchform'] .= '<input name="search" type="text"';
if (isset($_GET['search'])) {
	$hookReplace['searchform'] .= ' value="' . $_GET['search'] . '" ';
};
$hookReplace['searchform'] .= '/><input type="submit" value="Search!" /></form>';
$stdOut .= '<script type="text/javascript">
	$(function() {
		$(".validatetextbutton").click(function() {
			var bValid = true;
			var fields = $(this).parent();
			var validateText = fields.find("[name=\'rname\']");
			$("input").removeClass( "ui-state-error" );
			$("select").removeClass( "ui-state-error" );
			bValid = bValid && checkLength( validateText, "schedule type", 1, 60 );
			if (bValid == false) {
				return false;
			};
		});
	});
</script>';
$queryReports = 'select id, Name from ReportCalendarType';
if (isset($_GET['search'])) {
	$queryReports .= ' where name like \'%' . $_GET['search'] . '%\'';
};
$dataReports = odbc_exec($conn, $queryReports);
$sReportForms = '<form class="createform" action="includes/changecaltypes.php" method="post"><h3>Create Schedule Type</h3>
	<div class="formelement"><label for="name">Schedule Type Name</label><br /><input type="text" id="name" name="rname" value=""><span class="required"> * </span></div>
	<input class="validatetextbutton" type="submit" name="add" value="Create"></form><table class="recordssmall"><thead><tr><th>Rename Schedule Type</th><th>Delete Schedule Type</th></tr></thead><tbody>';
$row = 0;
while (odbc_fetch_row($dataReports)) {
	if ($row % 2 == 0) {
		$sReportForms .= '<tr class="oddRow">';
	} else {
		$sReportForms .= '<tr class="evenRow">';
	};
	$row++;
	$sReportForms .= '<td><form action="includes/changecaltypes.php" method="post"><input type="hidden" name="update" value="' . odbc_result($dataReports, 1) . '"><input type="text" name="rname" value="' . odbc_result($dataReports, 2) . '"><span class="required"> * </span><input class="validatetextbutton" type="submit" value="Update"></form></td>
	<td><form action="includes/changecaltypes.php" method="post">
	<input type="hidden" name="delete" value="' . odbc_result($dataReports, 1) . '"><input type="submit" value="Delete"></form></td></tr>';
};
$sReportForms .= '</tbody></table>';
$stdOut .= $sReportForms . '<div class="requiredhint"><span class="required"> * </span>are required fields.</div>';
$hookReplace['help'] = $helptext['search'] . $helptext['update'] . $helptext['delete'] . $helptext['add'];
require_once 'includes/footer.php'; ?>