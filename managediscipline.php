<?PHP 
$title = 'Manage Disciplines';
require_once 'includes/header.php';
if (isset($_SESSION['edit']['areaedit']) || isset($_SESSION['edit']['disciplineedit'])) {
	$bEditDisc = true;
};
if (!fCanSee(isset($bEditDisc))) {
	$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
if (isset($_SESSION['admin']['disciplineadmin']) && isset($_SESSION['admin']['groupadmin'])) {
	$bAdminDisc = true;
};
$hookReplace['searchicon'] = '<a href="#" data-text="Search" class="menucontext"><span class="icon-search icon-hover-hint icon-large"></span></a>';
$hookReplace['searchform'] = '<form id="search" action="managediscipline.php" method="get">';
if (isset($_GET['search'])) {
	$hookReplace['searchform'] .= '<a href="managediscipline.php"><span class="icon-remove-sign icon-large"></span></a> ';
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
			var validateText = fields.find("[name=\'dname\']");
			var areaoption = fields.find("[name=\'area\']");
			$("input").removeClass( "ui-state-error" );
			$("select").removeClass( "ui-state-error" );
			bValid = bValid && checkLength( validateText, "discipline", 1, 60 );
			bValid = bValid && checkOptionSelected( areaoption, "You must select a area.");
			if (bValid == false) {
				return false;
			};
		});
		$(".validatecreatetextbutton").click(function() {
			var bValid = true;
			var fields = $(this).parent();
			var validateText = fields.find("[name=\'dname\']");
			var areaoption = fields.find("[name=\'area\']");
			var groupoption = fields.find("[name=\'groupadmin\']");
			$("input").removeClass( "ui-state-error" );
			$("select").removeClass( "ui-state-error" );
			bValid = bValid && checkLength( validateText, "discipline", 1, 60 );
			bValid = bValid && checkOptionSelected( areaoption, "You must select a area.");
			bValid = bValid && checkOptionSelected( groupoption, "You must select a group.");
			if (bValid == false) {
				return false;
			};
			return false;
		});
	});
</script>';
$queryArea = 'select id, Name from Area';
if ($_SESSION['id'] != 1) {
	fOrThem($_SESSION['permissions']['area'], 200, 'id', $aQueryArea);
	fGenerateWhere($queryArea, $aQueryArea);
};
$dataArea = odbc_exec($conn, $queryArea);
$aArea = '';
while (odbc_fetch_row($dataArea)) {
	$aArea[odbc_result($dataArea, 1)] = odbc_result($dataArea, 2);
};
function fAreaSelect($iSelect) {
	global $aArea;
	$sAreaSelect = '<select id="area" name="area"><option>none</option>';
	foreach ($aArea as $key => $value) {
		$sAreaSelect .= '<option value="' . $key . '" ';
		if ($iSelect == $key) {
			$sAreaSelect .= 'selected';
		};
		$sAreaSelect .= '>' . $value . '</option>';
	};
	$sAreaSelect .= '</select>';
	return $sAreaSelect;
};
$queryDiscipline = 'select id, areafk, Name from Discipline';
if ($_SESSION['id'] != 1) {
	fOrThem($_SESSION['permissions']['discipline'], 200, 'id', $aQueryDiscipline);
	fOrThem($_SESSION['permissions']['area'], 200, 'areafk', $aQueryDiscipline);
	fGenerateWhere($queryDiscipline, $aQueryDiscipline, 'or');
};
if (isset($_GET['search'])) {
	if ($_SESSION['id'] != 1) {
		$queryDiscipline .= ' and name like \'%' . $_GET['search'] . '%\'';
	} else {
		$queryDiscipline .= ' where name like \'%' . $_GET['search'] . '%\'';
	};
};
$dataDiscipline = odbc_exec($conn, $queryDiscipline);
$sDisciplineForms = '';
if (fCanSee(isset($bAdminDisc))) {
	$sDisciplineForms .= '<form class="createform" action="includes/changediscipline.php" method="post"><h3>Create Discipline</h3>
	<div class="formelement"><label for="name">Discipline Name</label><br /><input type="text" id="name" name="dname" value=""><span class="required"> * </span></div>
	<div class="formelement"><label>Area</label><br />' . fAreaSelect('none') . '<span class="required"> * </span></div>
	<div class="formelement"><label for="groupadmin">Group Owner</label><br />' . fGroupSelect() . '<span class="required"> * </span></div>
	<input class="validatecreatetextbutton" type="submit" name="add" value="Create"></form>';
};
$sDisciplineForms .= '<table class="recordssmall"><thead><tr><th>Rename/Reassign Discipline</th><th>Delete Discipline</th></tr></thead><tbody>';
$row = 0;
while (odbc_fetch_row($dataDiscipline)) {
	if ($row % 2 == 0) {
		$sDisciplineForms .= '<tr class="oddRow">';
	} else {
		$sDisciplineForms .= '<tr class="evenRow">';
	};
	$row++;
	$sDisciplineForms .= '<td><form action="includes/changediscipline.php" method="post"><input type="hidden" name="update" value="' . odbc_result($dataDiscipline, 1) . '">';
	if (fCanSee(@$_SESSION['permissions']['discipline'][odbc_result($dataDiscipline, 1)] >= 200)) {
		$sDisciplineForms .= '<label for="dname' . odbc_result($dataDiscipline, 3) . '">Name:</label><input type="text" id="dname' . odbc_result($dataDiscipline, 3) . '" name="dname" value="' . odbc_result($dataDiscipline, 3) . '"><span class="required"> * </span>';
	} else {
		$sDisciplineForms .= odbc_result($dataDiscipline, 3) . ' <input type="hidden" name="dname" value="' . odbc_result($dataDiscipline, 3) . '">';
	};
	if (fCanSee(@$_SESSION['permissions']['area'][odbc_result($dataDiscipline, 2)] >= 200)) {
		$sDisciplineForms .= 'Area:' . fAreaSelect(odbc_result($dataDiscipline, 2)) . '<span class="required"> * </span>';
	} else {
		$sDisciplineForms .= $aArea[odbc_result($dataDiscipline, 2)] . '<input type="hidden" name="area" value="' . odbc_result($dataDiscipline, 2) . '">';
	};
	$sDisciplineForms .= '<input class="validatetextbutton" type="submit" value="Update"></form></td>
	<td><form action="includes/changediscipline.php" method="post">';
	if (fCanSee(@$_SESSION['permissions']['discipline'][odbc_result($dataDiscipline, 1)] >= 300)) {
		$sDisciplineForms .= '<input type="hidden" name="delete" value="' . odbc_result($dataDiscipline, 1) . '"><input type="submit" value="Delete"></form></td></tr>';
	};
};
$sDisciplineForms .= '</tbody></table>';
$stdOut .= $sDisciplineForms . '<div class="requiredhint"><span class="required"> * </span>are required fields.</div>';
$hookReplace['help'] = '';
if (fCanSee(isset($_SESSION['edit']['areaedit']))) {
	$hookReplace['help'] .= '<div class="notice">To add Areas <a href="managearea.php">Click Here</a></div>';
};
$hookReplace['help'] .= $helptext['search'] . $helptext['update'] . $helptext['delete'] . $helptext['add'] . $helptext['groupadmin'];
require_once 'includes/footer.php'; ?>