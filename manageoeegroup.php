<?PHP 
$title = 'Manage OEE Group';
require_once 'includes/header.php';
if (!fCanSee(@$_SESSION['permissions']['page'][1] >= 300)) {
	$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
$hookReplace['searchicon'] = '<a href="#" data-text="Search" class="menucontext"><span class="icon-search icon-hover-hint icon-large"></span></a>';
$hookReplace['searchform'] = '<form id="search" action="manageoeegroup.php" method="get">';
if (isset($_GET['search'])) {
	$hookReplace['searchform'] .= '<a href="manageoeegroup.php"><span class="icon-remove-sign icon-large"></span></a> ';
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
			var validateText = fields.find("[name=\'gname\']");
			$("input").removeClass( "ui-state-error" );
			bValid = bValid && checkLength( validateText, "group", 1, 60 );
			if (bValid == false) {
				return false;
			};
		});
	});
</script>';
$queryGroup = 'select id, Name from oeegroup';
if (isset($_GET['search'])) {
	$queryGroup .= ' where name like \'%' . $_GET['search'] . '%\'';
};
$dataGroup = odbc_exec($conn, $queryGroup);
$sCategoryGroup = '<form class="createform" action="includes/changeoeegroup.php" method="post"><h3>Create OEE Group</h3>
<div class="formelement"><label for="name">OEE Group Name</label><br /><input type="text" id="name" name="gname" value=""><span class="required"> * </span></div>
<input class="validatetextbutton" type="submit" name="add" value="Create"></form>
<table class="recordssmall"><thead><tr><th>Rename OEE Group</th><th>Delete OEE Group</th></tr></thead><tbody>';
$row = 0;
while (odbc_fetch_row($dataGroup)) {
	if ($row % 2 == 0) {
		$sCategoryGroup .= '<tr class="oddRow">';
	} else {
		$sCategoryGroup .= '<tr class="evenRow">';
	};
	$row++;
	$sCategoryGroup .= '<td><form action="includes/changeoeegroup.php" method="post"><input type="hidden" name="update" value="' . odbc_result($dataGroup, 1) . '"><input type="text" name="gname" value="' . odbc_result($dataGroup, 2) . '"><span class="required"> * </span><input class="validatetextbutton" type="submit" value="Update"></form></td>
	<td><form action="includes/changeoeegroup.php" method="post">
	<input type="hidden" name="delete" value="' . odbc_result($dataGroup, 1) . '"><input type="submit" value="Delete"></form></td></tr>';
};
$sCategoryGroup .= '</tbody></table>';
$stdOut .= $sCategoryGroup . '<div class="requiredhint"><span class="required"> * </span>are required fields.</div>';
$hookReplace['help'] = $helptext['search'] . $helptext['update'] . $helptext['delete'] . $helptext['add'];
require_once 'includes/footer.php'; ?>