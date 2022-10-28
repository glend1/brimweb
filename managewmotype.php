<?PHP 
$title = 'Manage WMO Types';
require_once 'includes/header.php';
if ($_SESSION['id'] != 1) {
	$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
$hookReplace['searchicon'] = '<a href="#" data-text="Search" class="menucontext"><span class="icon-search icon-hover-hint icon-large"></span></a>';
$hookReplace['searchform'] = '<form id="search" action="managewmotype.php" method="get">';
if (isset($_GET['search'])) {
	$hookReplace['searchform'] .= '<a href="managewmotype.php"><span class="icon-remove-sign icon-large"></span></a> ';
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
			var validateOrder = fields.find("[name=\'order\']");
			$("input").removeClass( "ui-state-error" );
			bValid = bValid && checkLength( validateText, "description", 1, 60 );
			bValid = bValid && checkRegexp( validateID, /^[0-9]+$/, "Order must be Numeric." );
			if (bValid == false) {
				return false;
			};
		});
	});
</script>';
$queryType = 'select id, Description, typeorder from WMOType';
if (isset($_GET['search'])) {
		$queryType .= ' where Description like \'%' . $_GET['search'] . '%\'';
};
$queryType .= ' order by Description asc';
$dataType = odbc_exec($conn, $queryType);
$sTypeForms = '<form class="createform" action="includes/changewmotype.php" method="post"><h3>Create WMO Types</h3>
<div class="formelement"><label for="name">Type</label><br /><input type="text" id="name" name="name" value=""><span class="required"> * </span></div><div class="formelement"><label for="order">Order</label></br /><input type="text" id="order" name="order" value=""><span class="required"> * </span></div>
<input class="validatetextbutton" type="submit" name="add" value="Create"></form>';
$sTypeForms .= '<table class="recordssmall"><thead><tr><th>Rename Type</th><th>Delete Type</th></tr></thead><tbody>';
$row = 0;
while (odbc_fetch_row($dataType)) {
	if ($row % 2 == 0) {
		$sTypeForms .= '<tr class="oddRow">';
	} else {
		$sTypeForms .= '<tr class="evenRow">';
	};
	$row++;
	$sTypeForms .= '<td><form action="includes/changewmotype.php" method="post"><input type="hidden" name="update" value="' . odbc_result($dataType, 1) . '"><label for="name' . $row . '">Type:</label><input type="text" id="name' . $row . '" name="name" value="' . odbc_result($dataType, 2) . '"><span class="required"> * </span><label for="order' . $row . '">Order:</label><input type="text" id="order' . $row . '" name="order" value="' . odbc_result($dataType, 3) . '"><span class="required"> * </span><input class="validatetextbutton" type="submit" value="Update"></form></td>
	<td><form action="includes/changewmotype.php" method="post">';
	$sTypeForms .= '<input type="hidden" name="delete" value="' . odbc_result($dataType, 1) . '"><input type="submit" value="Delete"></form></td></tr>';
};
$sTypeForms .= '</tbody></table>';
$stdOut .= $sTypeForms . '<div class="requiredhint"><span class="required"> * </span>are required fields.</div>';
$hookReplace['help'] = $helptext['search'] . $helptext['update'] . $helptext['delete'] . $helptext['add'];
require_once 'includes/footer.php'; ?>