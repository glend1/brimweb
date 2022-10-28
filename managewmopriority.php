<?PHP 
$title = 'Manage WMO Priority Codes';
require_once 'includes/header.php';
if ($_SESSION['id'] != 1) {
	$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
$hookReplace['searchicon'] = '<a href="#" data-text="Search" class="menucontext"><span class="icon-search icon-hover-hint icon-large"></span></a>';
$hookReplace['searchform'] = '<form id="search" action="managewmopriority.php" method="get">';
if (isset($_GET['search'])) {
	$hookReplace['searchform'] .= '<a href="managewmopriority.php"><span class="icon-remove-sign icon-large"></span></a> ';
};
$hookReplace['searchform'] .= '<input name="search" type="text"';
if (isset($_GET['search'])) {
	$hookReplace['searchform'] .= ' value="' . $_GET['search'] . '" ';
};
$hookReplace['searchform'] .= '/><input type="submit" value="Search!" /></form>';
function fTypeSelect($iSelect) {
	global $aType;
	$sTypeSelect = '';
	foreach ($aType as $key => $value) {
		$sTypeSelect .= '<option value="' . $key . '" ';
		if ($iSelect == $key) {
			$sTypeSelect .= 'selected';
		};
		$sTypeSelect .= '>' . $value . '</option>';
	};
	return $sTypeSelect;
};
$stdOut .= '<script type="text/javascript">
	$(function() {
		$(".validatetextbutton").click(function() {
			var bValid = true;
			var fields = $(this).parent();
			var validateText = fields.find("[name=\'name\']");
			var validateOrder = fields.find("[name=\'order\']");
			$("input").removeClass( "ui-state-error" );
			$("select").removeClass( "ui-state-error" );
			bValid = bValid && checkLength( validateText, "description", 1, 60 );
			bValid = bValid && checkRegexp( validateOrder, /^[0-9]+$/, "Order must be Numeric." );
			if (bValid == false) {
				return false;
			};
		});
	});
</script>';
$queryPriority = 'select WMOPriorityCode.id, WMOPriorityCode.Description, PriorityOrder, TypeFK from WMOPriorityCode join wmotype on typefk = wmotype.id';
if (isset($_GET['search'])) {
		$queryPriority .= ' where Description like \'%' . $_GET['search'] . '%\'';
};
$queryPriority .= ' order by priorityorder desc, typeorder asc';
$dataPriority = odbc_exec($conn, $queryPriority);
$sPriorityForms = '<form class="createform" action="includes/changewmopriority.php" method="post"><h3>Create WMO Priority Codes</h3>
<div class="formelement"><label for="name">Description</label><br /><input type="text" id="name" name="name" value=""><span class="required"> * </span></div>
<div class="formelement"><label for="order">Order</label><br /><input type="text" class="intorder" id="order" name="order" value=""><span class="required"> * </span></div>
<div class="formelement"><label for="type">Type</label><br /><select id="type" name="type">';
$queryType = 'select id, description from wmotype order by description';
$dataType = odbc_exec($conn, $queryType);
while (odbc_fetch_row($dataType)) {
	$sPriorityForms .= '<option value="' . odbc_result($dataType, 1) . '">' . odbc_result($dataType, 2) . '</option>';
	$aType[odbc_result($dataType, 1)] = odbc_result($dataType, 2);
};
$sPriorityForms .= '</select><span class="required"> * </span></div>
<input class="validatetextbutton" type="submit" name="add" value="Create"></form>
<table class="recordssmall"><thead><tr><th>Rename/Reassign Priority</th><th>Delete Priority</th></tr></thead><tbody>';
$row = 0;
while (odbc_fetch_row($dataPriority)) {
	if ($row % 2 == 0) {
		$sPriorityForms .= '<tr class="oddRow">';
	} else {
		$sPriorityForms .= '<tr class="evenRow">';
	};
	$row++;
	$sPriorityForms .= '<td><form action="includes/changewmopriority.php" method="post"><input type="hidden" name="update" value="' . odbc_result($dataPriority, 1) . '"><label for="name' . $row . '">Description:</label><input type="text" id="name' . $row . '" name="name" value="' . odbc_result($dataPriority, 2) . '"><span class="required"> * </span>
	<label for="order' . $row . '">Order:</label><input type="text" class="intorder" id="order' . $row . '" name="order" value="' . odbc_result($dataPriority, 3) . '"><span class="required"> * </span>
	<label for="type' . $row . '">Type:</label><select name="type" id="type' . $row . '">' . fTypeSelect(odbc_result($dataPriority, 4)) . '</select><span class="required"> * </span>
	<input class="validatetextbutton" type="submit" value="Update"></form></td>
	<td><form action="includes/changewmopriority.php" method="post">';
	$sPriorityForms .= '<input type="hidden" name="delete" value="' . odbc_result($dataPriority, 1) . '"><input type="submit" value="Delete"></form></td></tr>';
};
$sPriorityForms .= '</tbody></table>';
$stdOut .= $sPriorityForms . '<div class="requiredhint"><span class="required"> * </span>are required fields.</div>';
$hookReplace['help'] = $helptext['search'] . $helptext['update'] . $helptext['delete'] . $helptext['add'];
require_once 'includes/footer.php'; ?>