<?PHP 
$title = 'Select OEE Types';
require_once 'includes/header.php';
if (!fCanSee(@$_SESSION['permissions']['page'][1] >= 200)) {
	$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
/*if (!fCanSee(isset($_SESSION['edit']['disciplineedit']))) {
	$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
if (!fCanSee(isset($_SESSION['edit']['departmentedit']))) {
	$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};*/
$stdOut .= '<script type="text/javascript">
	$(function() {
		$("#downtimetype").click(function() {
			var bValid = true;
			var checkedDeptEquip = $(\'[name="departmentequipment"]:checked\');
			var deptEquip = $(\'[name="departmentequipment"]\');
			var checkedDisc = $(\'[name="discipline"]:checked\');
			var disc = $(\'[name="discipline"]\');
			var checkedCategory = $(\'[name="oeecategory"]:checked\');
			var category = $(\'[name="oeecategory"]\');
			$("td").removeClass( "ui-state-error" );
			if (checkedDeptEquip.length < 1) {
				bValid = bValid && false;
				deptEquip.parent().addClass( "ui-state-error" );
				updateTips("You must select a department equipment.");
				return false;
			};
			if (checkedDisc.length < 1) {
				bValid = bValid && false;
				disc.parent().addClass( "ui-state-error" );
				updateTips("You must select a Department.");
				return false;
			};
			if (checkedCategory.length < 1) {
				bValid = bValid && false;
				category.parent().addClass( "ui-state-error" );
				updateTips("You must select a Category.");
				return false;
			};
			if (bValid == false) {
				return false;
			};
		});
	});
</script>';
function fGetTypes($database, $queryList) {
	global $aTable;
	global $conn;
	$dataList = odbc_exec($conn, $queryList);
	while (odbc_fetch_row($dataList)) {
		$aTable[$database][] = '<td><input type="radio" name="' . $database . '" value="' . odbc_result($dataList, 1) . '" id="' . $database . odbc_result($dataList, 1) . '"><label for="' . $database . odbc_result($dataList, 1) . '">'  . odbc_result($dataList, 2) . '</label></td>';
	};
};
$queryCategoryList = 'select id, name from oeecategory order by name asc';
fGetTypes('oeecategory', $queryCategoryList);
$queryDeptList = 'select id, name from departmentequipment';
if ($_SESSION['id'] != 1) {
	fOrThem($_SESSION['permissions']['department'], 200, 'departmentfk', $aDeptList);
	fGenerateWhere($queryDeptList, $aDeptList);
};
$queryDeptList .= ' order by name asc';
fGetTypes('departmentequipment', $queryDeptList);
fGetTypes('discipline', 'select id, name from discipline order by name asc');
$aTableCount = max([count($aTable['departmentequipment']), count($aTable['discipline'])]);
$downtimeTypes = '';
if (@$_SESSION['permissions']['page'][1] >= 300 && isset($_SESSION['admin']['departmentadmin'])) {
	$bAdmin = TRUE;
};
if (fCanSee(isset($bAdmin))) {
	$downtimeTypes .= '<form class="createform" action="changeoeetypeform.php" method="get"><h3>Create Type</h3><input type="submit" value="Create" name="add" class="validatetextbutton" /></form>';
};
for ($i=0; $i < $aTableCount; $i++) {
	if ($i == 0) {
		$downtimeTypes .= '<form method="get" action="manageoeetype.php" class="typelist"><table class="recordssmall"><thead><tr><th>Department Equipment<span class="required"> * </span></th><th>Discipline<span class="required"> * </span></th><th>Category<span class="required"> * </span></th></thead><tbody>';
	};
	if (!isset($aTable['departmentequipment'][$i])) {
		$aTable['departmentequipment'][$i] = '<td></td>';
	};
	if (!isset($aTable['discipline'][$i])) {
		$aTable['discipline'][$i] = '<td></td>';
	};
	if (!isset($aTable['oeecategory'][$i])) {
		$aTable['oeecategory'][$i] = '<td></td>';
	};
	if ($i % 2 == 0) {
		$downtimeTypes .= '<tr class="oddRow">';
	} else {
		$downtimeTypes .= '<tr class="evenRow">';
	};
	$downtimeTypes .= $aTable['departmentequipment'][$i] . $aTable['discipline'][$i] . $aTable['oeecategory'][$i] . '</tr>';
	if ($i + 1 == $aTableCount) {
		$downtimeTypes .= '</tbody><tfoot>';
		if (($i + 1) % 2 == 0) {
			$downtimeTypes .= '<tr class="oddRow">';
		} else {
			$downtimeTypes .= '<tr class="evenRow">';
		};
		$downtimeTypes .= '<td colspan="4"><input type="submit" id="downtimetype" value="Submit!"></td></tr></tfoot></table></form>';
	};
};
$stdOut .= $downtimeTypes . '<div class="requiredhint"><span class="required"> * </span>are required fields.</div>';
if (fCanSee(isset($_SESSION['edit']['departmentedit']))) {
	$aNoticeList[] = 'To Create a Department Equipment <a href="managedepartmentequipment.php">Click Here</a>.';
};
if (fCanSee(isset($_SESSION['edit']['disciplineedit']))) {
	$aNoticeList[] = 'To Create a Discipline <a href="managediscipline.php">Click Here</a>.';
};
if (fCanSee(@$_SESSION['permissions']['page'][1] >= 300)) {
	$aNoticeList[] = 'To Create a Category <a href="manageoeecategory.php">Click Here</a>.';
};
if (isset($aNoticeList)) {
	$hookReplace['help'] = '<div class="notice">' . implode(' ', $aNoticeList) . '</div>' . $helptext['add'];
};
require_once 'includes/footer.php'; ?>