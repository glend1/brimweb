<?PHP 
$title = 'Manage Tasks';
require_once 'includes/header.php';
if (!fCanSee(isset($_SESSION['edit']['groupedit']))) {
	$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
$hookReplace['searchicon'] = '<a href="#" data-text="Search" class="menucontext"><span class="icon-search icon-hover-hint icon-large"></span></a>';
$hookReplace['searchform'] = '<form id="search" action="managetasks.php" method="get">';
if (isset($_GET['search'])) {
	$hookReplace['searchform'] .= '<a href="managetasks.php"><span class="icon-remove-sign icon-large"></span></a> ';
};
$hookReplace['searchform'] .= '<input name="search" type="text"';
if (isset($_GET['search'])) {
	$hookReplace['searchform'] .= ' value="' . $_GET['search'] . '" ';
};
$hookReplace['searchform'] .= '/><input type="submit" value="Search!" /></form>';
$queryJoin = 'select groupfk, taskfk, id from taskjunction';
$dataJoin = odbc_exec($conn, $queryJoin);
$aJoin = [];
while(odbc_fetch_row($dataJoin)) {
	$aJoin[odbc_result($dataJoin, 1)][] = ['task' => odbc_result($dataJoin, 2), 'id' => odbc_result($dataJoin, 3)];
	$aExclude[odbc_result($dataJoin, 1)][] = odbc_result($dataJoin, 2);	
};
$queryTask = 'select id, title from tasks where title <> \'Register\' order by title asc';
$dataTask = odbc_exec($conn, $queryTask);
$aTask = [];
while(odbc_fetch_row($dataTask)) {
	$aTask[odbc_result($dataTask, 1)] = odbc_result($dataTask, 2);
};
function fTask($exclude = array(), $selected = NULL) {
	global $aTask;
	$sTaskSelect = '<select name="task"><option>none</option>';
		foreach ($aTask as $key => $value) {
			if ((!in_array($key, $exclude)) || $selected == $key) {
				$sTaskSelect .= '<option value="' . $key . '" ';
				if ($selected == $key) {
					$sTaskSelect .= 'selected';
				};
				$sTaskSelect .= '>' . $value . '</option>';
			};
		};
	$sTaskSelect .= '</select>';
	return $sTaskSelect;
};
$stdOut .= '<script type="text/javascript">
	$(function() {
		$(".validatebutton").click(function() {
			var bValid = true;
			var fields = $(this).parent();
			var taskoption = fields.find("[name=\'task\']");
			$("select").removeClass( "ui-state-error" );
			bValid = bValid && checkOptionSelected( areaoption, "You must select a Task.");
			if (bValid == false) {
				return false;
			};
		});
	});
</script>';
$stdOut .= '<table class="records"><thead><tr><th>Group</th><th>Create/Update/Delete</th></tr></thead><tbody>';
$queryGroups = 'select id, name from grouptable ';
if ($_SESSION['id'] != 1) {
	fOrThem($_SESSION['permissions']['group'], 200, 'id', $aQueryGroups);
	fGenerateWhere($queryGroups, $aQueryGroups);
};
if (isset($_GET['search'])) {
	if ($_SESSION['id'] != 1) {
		$queryGroups .= ' and name like \'%' . $_GET['search'] . '%\'';
	} else {
		$queryGroups .= ' where name like \'%' . $_GET['search'] . '%\'';
	};
};
$queryGroups .= ' order by name asc';
$dataGroups = odbc_exec($conn, $queryGroups);
$row = 0;
while(odbc_fetch_row($dataGroups)) {
	if ($row % 2 == 0) {
		$stdOut .= '<tr class="oddRow">';
	} else {
		$stdOut .= '<tr class="evenRow">';
	};
	$row++;
	$stdOut .= '<td>' . odbc_result($dataGroups, 2) . '</td><td>';
	if (isset($aJoin[odbc_result($dataGroups, 1)])) {
		foreach ($aJoin[odbc_result($dataGroups, 1)] as $key => $val) {
			$stdOut .= '<div class="tableform"><form method="post" action="includes/changetasks.php"><input type="hidden" name="group" value="' . odbc_result($dataGroups, 1) . '" /><input type="hidden" name="id" value="' . $val['id'] . '" />' . fTask($aExclude[odbc_result($dataGroups, 1)], $val['task']) . '<input class="validatebutton" type="submit" name="update" value="Update" /></form><form method="post" action="includes/changetasks.php"><input type="hidden" name="group" value="' . odbc_result($dataGroups, 1) . '" /><input type="hidden" name="id" value="' . $val['id'] . '" /><input type="submit" name="delete" value="Delete" /></form></div>';
		};
	};
	$stdOut .= '<form class="tableform" method="post" action="includes/changetasks.php"><input type="hidden" name="group" value="' . odbc_result($dataGroups, 1) . '" />';
	if (isset($aExclude[odbc_result($dataGroups, 1)])) {
		$stdOut .= fTask($aExclude[odbc_result($dataGroups, 1)]);
	} else {
		$stdOut .= fTask();
	};
	$stdOut .= '<input class="validatebutton" type="submit" name="add" value="Add" /></form></td></tr>';
};
$stdOut .= '</tbody></table><div class="requiredhint"><span class="required"> * </span>are required fields.</div>';
$hookReplace['help'] = '';
if (fCanSee(isset($_SESSION['edit']['group']))) {
	$hookReplace['help'] .= '<div class="notice">To Create a Group <a href="managegroup.php">Click Here</a>. To Create a Task Type <a href="managetasktypes.php">Click Here</a>.</div>';
};
$hookReplace['help'] .= $helptext['search'] . $helptext['update'] . $helptext['delete'] . $helptext['add'];
require_once 'includes/footer.php'; ?>