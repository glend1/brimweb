<?PHP 
$title = 'Manage Abstract Permissions';
require_once 'includes/header.php';
if ($_SESSION['id'] != 1) {
	$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
$hookReplace['searchicon'] = '<a href="#" data-text="Search" class="menucontext"><span class="icon-search icon-hover-hint icon-large"></span></a>';
$hookReplace['searchform'] = '<form id="search" action="manageabstract.php" method="get">';
if (isset($_GET['search'])) {
	$hookReplace['searchform'] .= '<a href="manageabstract.php"><span class="icon-remove-sign icon-large"></span></a> ';
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
			var validateText = fields.find("[name=\'aname\']");
			var validateID = fields.find("[name=\'id\']");
			$("input").removeClass( "ui-state-error" );
			bValid = bValid && checkLength( validateText, "abstract permission", 1, 60 );
			bValid = bValid && checkRegexp( validateID, /^[0-9]+$/, "Identification must be Numeric." );
			if (bValid == false) {
				return false;
			};
		});
	});
</script>';
$queryAbstract = 'select id, abstractName, permissionfk from PermissionAbstract';
if (isset($_GET['search'])) {
		$queryAbstract .= ' where abstractname like \'%' . $_GET['search'] . '%\'';
};
$queryAbstract .= ' order by abstractname asc';
$dataAbstract = odbc_exec($conn, $queryAbstract);
$sAbstractForms = '<form class="createform" action="includes/changeabstract.php" method="post"><h3>Create Abstract Permission</h3>
<div class="formelement"><label for="name">Abstract Name</label><br /><input type="text" id="name" name="aname" value=""><span class="required"> * </span></div><div class="formelement"><label for="id">Identification</label></br /><input type="text" id="id" name="id" value=""><span class="required"> * </span></div>
<input class="validatetextbutton" type="submit" name="add" value="Create"></form>';
$sAbstractForms .= '<table class="recordssmall"><thead><tr><th>Rename/Reassign Abstract</th><th>Delete Abstract</th></tr></thead><tbody>';
$row = 0;
while (odbc_fetch_row($dataAbstract)) {
	$aAbstract[] = odbc_result($dataAbstract, 2);
	if ($row % 2 == 0) {
		$sAbstractForms .= '<tr class="oddRow">';
	} else {
		$sAbstractForms .= '<tr class="evenRow">';
	};
	$row++;
	$sAbstractForms .= '<td><form action="includes/changeabstract.php" method="post"><input type="hidden" name="update" value="' . odbc_result($dataAbstract, 1) . '"><label for="name' . $row . '">Name:</label><input type="text" id="name' . $row . '" name="aname" value="' . odbc_result($dataAbstract, 2) . '"><span class="required"> * </span><label for="id' . $row . '">Identification:</label><input type="text" id="id' . $row . '" name="id" value="' . odbc_result($dataAbstract, 3) . '"><span class="required"> * </span><input class="validatetextbutton" type="submit" value="Update"></form></td>
	<td><form action="includes/changeabstract.php" method="post">';
	$sAbstractForms .= '<input type="hidden" name="delete" value="' . odbc_result($dataAbstract, 1) . '"><input type="submit" value="Delete"></form></td></tr>';
};
$sAbstractForms .= '</tbody></table>';
if (!isset($_GET['search'])) {
	$directory = dir(substr($_SERVER['SCRIPT_FILENAME'], 0, -18));
	$includesdir = dir(substr($_SERVER['SCRIPT_FILENAME'], 0, -18) . 'includes/');
	while (false !== ($entry = $directory->read())) {
		if (substr($entry, -4) == '.php') {
			if (!in_array($entry, $aAbstract)) {
				$unusedScripts[] = $entry;
			};
		};
	}
	while (false !== ($entry = $includesdir->read())) {
		if (substr($entry, -4) == '.php') {
			if (!in_array($entry, $aAbstract)) {
				$unusedScripts[] = $entry;
			};
		};
	};
	if (isset($unusedScripts)) {
		$extraNotifications .= '<div class="ui-notif-error"><span class="icon-remove-sign"></span><b>Unassigned Scripts:</b> ' . implode(', ', $unusedScripts) . '</div>';
	};
};
$stdOut .= $sAbstractForms . '<div class="requiredhint"><span class="required"> * </span>are required fields.</div>';
$hookReplace['help'] = $helptext['search'] . $helptext['update'] . $helptext['delete'] . $helptext['add'];
require_once 'includes/footer.php'; ?>