<?PHP 
$title = 'Manage Areas';
require_once 'includes/header.php';
if (isset($_SESSION['admin']['areaadmin']) && isset($_SESSION['admin']['groupadmin'])) {
	$bAdminArea = true;
};
if (!fCanSee(isset($_SESSION['edit']['areaedit']))) {
	$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
$hookReplace['searchicon'] = '<a href="#" data-text="Search" class="menucontext"><span class="icon-search icon-hover-hint icon-large"></span></a>';
$hookReplace['searchform'] = '<form id="search" action="managearea.php" method="get">';
if (isset($_GET['search'])) {
	$hookReplace['searchform'] .= '<a href="managearea.php"><span class="icon-remove-sign icon-large"></span></a> ';
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
			var newuseroption = fields.find("[name=\'groupadmin\']");
			$("input").removeClass( "ui-state-error" );
			$("select").removeClass( "ui-state-error" );
			bValid = bValid && checkLength( validateText, "area", 1, 60 );
			bValid = bValid && checkOptionSelected( newuseroption, "You must select a group.");
			if (bValid == false) {
				return false;
			};
		});
		$(".validatetextbutton").click(function() {
			var bValid = true;
			var fields = $(this).parent();
			var validateText = fields.find("[name=\'aname\']");
			$("input").removeClass( "ui-state-error" );
			bValid = bValid && checkLength( validateText, "area", 1, 60 );
			if (bValid == false) {
				return false;
			};
		});
	});
</script>';
$queryArea = 'select id, Name from Area';
if ($_SESSION['id'] != 1) {
	fOrThem($_SESSION['permissions']['area'], 200, 'id', $aQueryArea);
	fGenerateWhere($queryArea, $aQueryArea);
};
if (isset($_GET['search'])) {
	if ($_SESSION['id'] != 1) {
		$queryArea .= ' and name like \'%' . $_GET['search'] . '%\'';
	} else {
		$queryArea .= ' where name like \'%' . $_GET['search'] . '%\'';
	};
};
$dataArea = odbc_exec($conn, $queryArea);
$sAreaForms = '';
if (fCanSee(isset($bAdminArea))) {
	$sAreaForms .= '<form class="createform" action="includes/changearea.php" method="post"><h3>Create Area</h3>
	<div class="formelement"><label for="name">Area Name</label><br /><input type="text" id="name" name="aname" value=""><span class="required"> * </span></div>
	<div class="formelement"><label for="groupadmin">Group Owner</label><br />' . fGroupSelect() . '<span class="required"> * </span></div>
	<input class="validatetextbutton" type="submit" name="add" value="Create"></form>';
};
$sAreaForms .= '<table class="recordssmall"><thead><tr><th>Rename Area</th><th>Delete Area</th></tr></thead><tbody>';
$row = 0;
while (odbc_fetch_row($dataArea)) {
	if ($row % 2 == 0) {
		$sAreaForms .= '<tr class="oddRow">';
	} else {
		$sAreaForms .= '<tr class="evenRow">';
	};
	$row++;
	$sAreaForms .= '<td><form action="includes/changearea.php" method="post"><input type="hidden" name="update" value="' . odbc_result($dataArea, 1) . '"><input type="text" name="aname" value="' . odbc_result($dataArea, 2) . '"><span class="required"> * </span><input class="validatetextbutton2" type="submit" value="Update"></form></td>
	<td><form action="includes/changearea.php" method="post">';
	fSetVar($_SESSION['permissions']['area'][odbc_result($dataArea, 1)]);
	if (fCanSee($_SESSION['permissions']['area'][odbc_result($dataArea, 1)] >= 300)) {
		$sAreaForms .= '<input type="hidden" name="delete" value="' . odbc_result($dataArea, 1) . '"><input type="submit" value="Delete"></form></td>';
	} else {
		$sAreaForms .= '<td></td>';
	};
	$sAreaForms .= '</tr>';
};
$sAreaForms .= '</tbody></table>';
$stdOut .= $sAreaForms . '<div class="requiredhint"><span class="required"> * </span>are required fields.</div>';
$hookReplace['help'] = '';
if (fCanSee(isset($_SESSION['edit']['disciplineedit']))) {
	$hookReplace['help'] .= '<div class="notice">To Associate Disciplines with Areas <a href="managediscipline.php">Click Here</a></div>';
};
$hookReplace['help'] .= $helptext['search'] . $helptext['update'] . $helptext['delete'] . $helptext['add'] . $helptext['groupadmin'];
require_once 'includes/footer.php'; ?>