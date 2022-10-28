<?PHP $title = 'Manage OEE Type';
require_once 'includes/header.php';
if (!fCanSee(@$_SESSION['permissions']['page'][1] >= 200)) {
	$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
if (isset($_GET['add'])) {
	$type['type'] = 'add';
} elseif (isset($_GET['update'])) {
	$type['type'] = 'update';
};
if (isset($_GET['category'])) {
	$type['category'] = $_GET['category'];
} else {
	$type['category'] = 0;
};
if (isset($_GET['discipline'])) {
	$type['discipline'] = $_GET['discipline'];
} else {
	$type['discipline'] = 0;
};
if (isset($_GET['departmentequipment'])) {
	$queryDeptEquip = 'select top 1 departmentfk from departmentequipment where id = ' . $_GET['departmentequipment'];
	$dataDeptEquip = odbc_exec($conn, $queryDeptEquip);
	if (odbc_fetch_row($dataDeptEquip)) {
		$type['department'] = odbc_result($dataDeptEquip, 1);
		if (!fCanSee(@$_SESSION['permissions']['department'][$type['department']] >= 200)) {
			$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
			$_SESSION['uiState'] = 'error';
			fRedirect();
		} else {
			$type['departmentequipment'] = $_GET['departmentequipment'];
		};	
	} else {
		$_SESSION['sqlMessage'] = 'Associated department not found!';
		$_SESSION['uiState'] = 'error';
		fRedirect();
	};
} else {
	$type['departmentequipment'] = 0;
};
if (isset($_GET['name'])) {
	$type['name'] = $_GET['name'];
} else {
	$type['name'] = 0;
};
if (isset($_GET['id'])) {
	$queryType = 'select top 1 oeecategoryfk, disciplinefk, departmentequipmentfk, oeenamefk
	from type
	join departmentequipment on departmentequipmentfk = departmentequipment.id
	where type.id = ' . $_GET['id'];
	if (@$_SESSION['id'] != 1 && isset($_SESSION['id'])) {
		$queryType .= ' and ' . fOrThemReturn($_SESSION['permissions']['department'], 200, 'departmentfk');
	};
	$queryType .= ' order by name asc';
	$dataType = odbc_exec($conn, $queryType);
	if (odbc_fetch_row($dataType)) {
		$type['category'] = odbc_result($dataType, 1);
		$type['discipline'] = odbc_result($dataType, 2);
		$type['departmentequipment'] = odbc_result($dataType, 3);
		$type['name'] = odbc_result($dataType, 4);
		$type['id'] = $_GET['id'];
	} else {
		$_SESSION['sqlMessage'] = 'Type not found!';
		$_SESSION['uiState'] = 'error';
		fRedirect();
	};
};
function fGetOptions($database, $select, $queryOptions, $size = 8) {
	global $conn;
	$dataOptions = odbc_exec($conn, $queryOptions);
	$sOptions = '<select name="' . $database . '" size="' . $size . '">';
	while (odbc_fetch_row($dataOptions)) {
		$sOptions .= '<option value="' . odbc_result($dataOptions, 1) . '"';
		if ($select == odbc_result($dataOptions, 1)) {
			$sOptions .= ' selected ';
		};
		$sOptions .= '>' . odbc_result($dataOptions, 2) . '</option>';
	};
	$sOptions .= '</select>';
	return $sOptions;
};
$queryDeptOptions = 'select id, name from departmentequipment';
if ($_SESSION['id'] != 1) {
	fOrThem($_SESSION['permissions']['department'], 300, 'departmentfk', $aDeptOptions);
	fGenerateWhere($queryDeptOptions, $aDeptOptions);
};
$queryDeptOptions .= ' order by name asc';
$selectDepartmentEquip = fGetOptions('departmentequipment', $type['departmentequipment'], $queryDeptOptions);
$queryDiscOptions = 'select id, name from discipline order by name asc';
$selectDiscipline = fGetOptions('discipline', $type['discipline'], $queryDiscOptions);
$queryCatOptions = 'select id, name from oeecategory order by name asc';
$selectCategory = fGetOptions('category', $type['category'], $queryCatOptions);
$queryNameOptions = 'select id, name from oeename order by name asc';
$selectName = fGetOptions('name', $type['name'], $queryNameOptions, 35);
$stdOut .= '<form class="typeoptions" method="post" action="includes/changeoeetype.php"><div class="oeeform">
<div><h3 for="departmentequipment">Department Equipment:<span class="required"> * </span></h3>' . $selectDepartmentEquip . 
'</div><div><h3 for="discipline">Discipline:<span class="required"> * </span></h3>' . $selectDiscipline . '' .
'</div><div><h3 for="Category">Category:<span class="required"> * </span></h3>' . $selectCategory .
'</div></div><div  class="oeeform">
<div><h3 for="Name">Name:<span class="required"> * </span></h3>' . $selectName . '</div></div>';
if (isset($type['id'])) {
	$stdOut .= '<input type="hidden" name="id" value="' . $type['id'] . '" />';
};
$stdOut .= '<div class="oeesubmit"><input name="' . $type['type'] . '" id="typebutton" type="submit" value="Submit!"></div></form>';
if (isset($type['id'])) {
	$queryGroup = 'select oeegroup.id, name, oeetypefk
	from oeegroup
	left join oeegroupjunction on oeegroup.id = oeegroupfk
	where oeetypefk = ' . $type['id'] . ' or oeetypefk is null
	order by name asc';
	$sGroup['remove'] = '<select name="options[]" multiple size="10">';
	$sGroup['add'] = '<select name="options[]" multiple size="10">';
	$dataGroup = odbc_exec($conn, $queryGroup);
	while (odbc_fetch_row($dataGroup)) {
		if (odbc_result($dataGroup, 3)) {
			$sGroup['remove'] .= '<option value="' . odbc_result($dataGroup, 1) . '">' .odbc_result($dataGroup, 2) . '</option>';
		} else {
			$sGroup['add'] .= '<option value="' . odbc_result($dataGroup, 1) . '">' .odbc_result($dataGroup, 2) . '</option>';
		};
	};
	$sGroup['remove'] .= '</select>';
	$sGroup['add'] .= '</select>';
	$stdOut .= '<div id="oeegroup">
	<form method="post" action="includes/changeoeegroupjunction.php" class="oeeformcolor"><h3>Remove Groups</h3>' . $sGroup['remove'] . '
	<input type="hidden" name="id" value="' . $type['id'] . '" /><div class="oeesubmit"><input class="editgroups" name="delete" type="submit" value="Remove!"></div></form>
	<form method="post" action="includes/changeoeegroupjunction.php" class="oeeformcolor"><h3>Add Groups</h3>' . $sGroup['add'] . '
	<input type="hidden" name="id" value="' . $type['id'] . '" /><div class="oeesubmit"><input class="editgroups" name="add" type="submit" value="Add!"></div></form>
	<div class="clear"></div></div>';
	$stdOut .= '<form id="oeeauto">
	<h3>OEE Automation</h3>
	<div class="oeesubmit"><input disabled type="submit" value="Disabled!"></div></form>';
};
$stdOut .= '<div class="requiredhint"><span class="required"> * </span>are required fields.</div>
<script type="text/javascript">
	$(function() {
		$(".editgroups").click(function() {
			var bValid = true;
			$("input").removeClass( "ui-state-error" );
			$("select").removeClass( "ui-state-error" );
			var groupSelect = $(this).parent().parent().find("select");
			bValid = bValid && checkOptionSelected( groupSelect, "You must select a Group.");
			if (bValid == false) {
				return false;
			};
		});
		$("#typebutton").click(function() {
			var bValid = true;
			var fields = $(this).parent().parent();
			var name = fields.find("[name=\'name\']");
			var departmentEquipment = fields.find("[name=\'departmentequipment\']");
			var discipline = fields.find("[name=\'discipline\']");
			var category = fields.find("[name=\'category\']");
			$("input").removeClass( "ui-state-error" );
			$("select").removeClass( "ui-state-error" );
			bValid = bValid && checkOptionSelected( departmentEquipment, "You must select a Department Equipment.");
			bValid = bValid && checkOptionSelected( discipline, "You must select a Discipline.");
			bValid = bValid && checkOptionSelected( name, "You must select a Name.");
			bValid = bValid && checkOptionSelected( category, "You must select a Category.");
			if (bValid == false) {
				return false;
			};
		});
	});
</script>';

if (fCanSee(isset($_SESSION['edit']['departmentedit']))) {
	$aNoticeList[] = 'To Create a Department Equipment <a href="managedepartmentequipment.php">Click Here</a>.';
};
if (fCanSee(isset($_SESSION['edit']['disciplineedit']))) {
	$aNoticeList[] = 'To Create a Discipline <a href="managediscipline.php">Click Here</a>.';
};
if (fCanSee(@$_SESSION['permissions']['page'][1] >= 300)) {
	$aNoticeList[] = 'To Create a Category <a href="manageoeecategory.php">Click Here</a>. To Create a Reason <a href="manageoeename.php">Click Here</a>.';
};
$hookReplace['help'] = '';
if (isset($aNoticeList)) {
	$hookReplace['help'] .= '<div class="notice">' . implode(' ', $aNoticeList) . '</div>';
};
$hookReplace['help'] .= $helptext['update'] . $helptext['add'] . '<a href="#">Groups</a><div>Groups are used to create relationships between distinct Types. Groups can only be added to Types that already exsist.</div><a href="#">Adding/Removing Multiple Groups</a><div>Holding shift whilst clicking on different group titles will select multiple groups, performing an action will either delete or add groups to the selected type.</div><a href="#">OEE Automation</a><div>This is a placeholder division until further notice.</div>';
require_once 'includes/footer.php'; ?>