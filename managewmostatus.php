<?PHP 
$title = 'Manage WMO Status Codes';
require_once 'includes/header.php';
if ($_SESSION['id'] != 1) {
	$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
$hookReplace['searchicon'] = '<a href="#" data-text="Search" class="menucontext"><span class="icon-search icon-hover-hint icon-large"></span></a>';
$hookReplace['searchform'] = '<form id="search" action="managewmostatus.php" method="get">';
if (isset($_GET['search'])) {
	$hookReplace['searchform'] .= '<a href="managewmostatus.php"><span class="icon-remove-sign icon-large"></span></a> ';
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
			var validateText = fields.find("[name=\'name\']");
			$("input").removeClass( "ui-state-error" );
			bValid = bValid && checkLength( validateText, "description", 1, 60 );
			if (bValid == false) {
				return false;
			};
		});
	});
</script>';
$queryStatus = 'select id, Description from WMOStatusCode';
if (isset($_GET['search'])) {
		$queryStatus .= ' where Description like \'%' . $_GET['search'] . '%\'';
};
$queryStatus .= ' order by Description asc';
$dataStatus = odbc_exec($conn, $queryStatus);
$sStatusForms = '<form class="createform" action="includes/changewmostatus.php" method="post"><h3>Create WMO Status Codes</h3>
<div class="formelement"><label for="name">Status</label><br /><input type="text" id="name" name="name" value=""><span class="required"> * </span></div>
<input class="validatetextbutton" type="submit" name="add" value="Create"></form>';
$sStatusForms .= '<table class="recordssmall"><thead><tr><th>Rename Status</th><th>Delete Status</th></tr></thead><tbody>';
$row = 0;
while (odbc_fetch_row($dataStatus)) {
	if ($row % 2 == 0) {
		$sStatusForms .= '<tr class="oddRow">';
	} else {
		$sStatusForms .= '<tr class="evenRow">';
	};
	$row++;
	$sStatusForms .= '<td><form action="includes/changewmostatus.php" method="post"><input type="hidden" name="update" value="' . odbc_result($dataStatus, 1) . '"><label for="name' . $row . '">Status:</label><input type="text" id="name' . $row . '" name="name" value="' . odbc_result($dataStatus, 2) . '"><span class="required"> * </span><input class="validatetextbutton" type="submit" value="Update"></form></td>
	<td><form action="includes/changewmostatus.php" method="post">';
	$sStatusForms .= '<input type="hidden" name="delete" value="' . odbc_result($dataStatus, 1) . '"><input type="submit" value="Delete"></form></td></tr>';
};
$sStatusForms .= '</tbody></table>';
$stdOut .= $sStatusForms . '<div class="requiredhint"><span class="required"> * </span>are required fields.</div>';
$hookReplace['help'] = $helptext['search'] . $helptext['update'] . $helptext['delete'] . $helptext['add'];
require_once 'includes/footer.php'; ?>