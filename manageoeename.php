<?PHP 
$title = 'Manage OEE Reason';
require_once 'includes/header.php';
if (!fCanSee(@$_SESSION['permissions']['page'][1] >= 300)) {
	$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
$hookReplace['searchicon'] = '<a href="#" data-text="Search" class="menucontext"><span class="icon-search icon-hover-hint icon-large"></span></a>';
$hookReplace['searchform'] = '<form id="search" action="manageoeename.php" method="get">';
if (isset($_GET['search'])) {
	$hookReplace['searchform'] .= '<a href="manageoeename.php"><span class="icon-remove-sign icon-large"></span></a> ';
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
			bValid = bValid && checkLength( validateText, "name", 1, 60 );
			if (bValid == false) {
				return false;
			};
		});
	});
</script>';
$queryName = 'select oeename.id, oeename.Name, 
cast(stuff(
	(select distinct \', \' + departmentequipment.name from type join DepartmentEquipment on departmentequipmentfk = departmentequipment.id where type.OEENameFK = oeename.ID order by \', \' + departmentequipment.name for xml path(\'\'))
,1,1,\'\') as varchar(max)) as departmentequipmentnames
from oeename';
if (isset($_GET['search'])) {
	$queryName .= ' where oeename.name like \'%' . $_GET['search'] . '%\'';
};
$queryName .= ' order by oeename.name asc';
$dataName = odbc_exec($conn, $queryName);
$sCategoryName = '<form class="createform" action="includes/changeoeename.php" method="post"><h3>Create OEE Reason</h3>
<div class="formelement"><label for="name">OEE Name</label><br /><input type="text" id="name" name="name" value=""><span class="required"> * </span></div>
<input class="validatetextbutton" type="submit" name="add" value="Create"></form>
<table class="recordssmall"><thead><tr><th>Rename OEE Reason</th><th>Used in...</th><th>Delete</th></tr></thead><tbody>';
$row = 0;
while (odbc_fetch_row($dataName)) {
	if ($row % 2 == 0) {
		$sCategoryName .= '<tr class="oddRow">';
	} else {
		$sCategoryName .= '<tr class="evenRow">';
	};
	$row++;
	$sCategoryName .= '<td><form class="forcesingleline" action="includes/changeoeename.php" method="post"><input type="hidden" name="update" value="' . odbc_result($dataName, 1) . '"><input type="text" name="name" value="' . odbc_result($dataName, 2) . '"><span class="required"> * </span><input class="validatetextbutton" type="submit" value="Update"></form></td>
	<td>' . odbc_result($dataName, 3) . '</td>
	<td><form action="includes/changeoeename.php" method="post">
	<input type="hidden" name="delete" value="' . odbc_result($dataName, 1) . '"><input type="submit" value="Delete"></form></td></tr>';
};
$sCategoryName .= '</tbody></table>';
$stdOut .= $sCategoryName . '<div class="requiredhint"><span class="required"> * </span>are required fields.</div>';
$hookReplace['help'] = $helptext['search'] . $helptext['update'] . $helptext['delete'] . $helptext['add'];
require_once 'includes/footer.php'; ?>