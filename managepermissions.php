<?PHP 
	if (!isset($_GET['id'])) {
		require_once 'includes/functions.php';
		$_SESSION['sqlMessage'] = 'Please select a group!';
		$_SESSION['uiState'] = 'error';
		fRedirect();
	};
	$title = 'Manage Permissions';
	require_once 'includes/header.php';
	if (@!fCanSee($_SESSION['permissions']['group'][$_GET['id']] >= 200)) {
		$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
		$_SESSION['uiState'] = 'error';
		fRedirect();
	};
$stdOut .= '<script type="text/javascript">
		$(function() {
			var iErrorCount = 0;
			var selects = $(".content select");
			var checkboxes = $(".content :checkbox");
			//console.log(selects);
			//console.log(checkboxes);
			$("#update").click(function() {
				$("select").removeClass( "ui-state-error" );
				$("td").removeClass( "ui-state-error" );
				if (iErrorCount > 0) {
					iErrorCount = 0;
				};
				for (var i = 0; i < selects.length; i++) {
					if (checkboxes.eq(i).prop("checked") && selects.eq(i).val() != "none") {
					} else if (checkboxes.eq(i).prop("checked") || selects.eq(i).val() != "none") {
						if (checkboxes.eq(i).prop("checked")) {
							selects.eq(i).addClass("ui-state-error");
							updateTips("Form is incomplete.");
							return false;
						};
						if (selects.eq(i).val() != "none") {
							checkboxes.eq(i).parent().addClass("ui-state-error");
							updateTips("Form is incomplete and data will be lost.");
							iErrorCount++;
						};
					};
				};
				if (iErrorCount > 0) {
					var dConfirm=confirm("Selection seems invalid would you like to continue anyway?");
					if (dConfirm == true) {
						return true;
					} else {
						return false;
					}
				};
			});
		});
	</script>';
	$queryGroupName = 'select distinct name from grouptable where grouptable.id = ' . $_GET['id'];
	$dataGroupName = odbc_exec($conn, $queryGroupName);
	while (odbc_fetch_row($dataGroupName)) {
		$sGroupName = odbc_result($dataGroupName, 1);
	};
	$permissionTable = '<form id="permissions" method="post" action="includes/changepermissions.php">';
	function fOptions($select, $sTableName, $aOptions) {
		$out = '';
		foreach ($aOptions as $key => $value) {
			$out .= '<option value="' . $value[0] . '"';
			if ($value[0] === (int)$select && $select != 'none') {
				$out .= ' selected ';
			};
			if ($value[0] === 0 && $sTableName == 'groupadmin') {
				$out .= ' disabled ';
			};
			$out .= '>' . $value[1] . '</option>';
		};
		return $out;
	};
	function fGetData($sTableName) {
		global $aGroupPermissions;
		global $permissionTable;
		global $conn;
		global $sGroupName;
		global $aPermissionTable;
		global $aTableOrder;
		$sReTableName = $sTableName;
		$queryTable = 'select id, name from ' . $sTableName . ' order by name';
		if ($sTableName == 'grouptable') {
			$sTableName = 'groupadmin';
			$sReTableName = 'group';
		} else if ($sTableName == 'pages') {
			$sTableName = 'page';
		};
		$dataTable = odbc_exec($conn, $queryTable);
		while (odbc_fetch_row($dataTable)) {
				$aOptions = array(['none', 'clear'], [0, 'none'],[100, 'view'],[200, 'edit']);
			if (fCanSee(@$_SESSION['permissions'][$sReTableName][odbc_result($dataTable, 1)] >= 300)) {
				$aOptions = array(['none', 'clear'], [0, 'none'],[100, 'view'],[200, 'edit'],[300, 'admin']);
			};
			if (odbc_result($dataTable, 2) == $sGroupName && $_SESSION['id'] != 1) {
				$aTable[odbc_result($dataTable, 1)] = '<td colspan="3"><input type="hidden" name="' . $sTableName . 'check[' . odbc_result($dataTable, 1) . ']" value="TRUE" /><input type="hidden" name="accesslevel' . $sTableName . '[' . odbc_result($dataTable, 1) . ']" value="' . $aGroupPermissions[$sTableName][odbc_result($dataTable, 1)] . '">cant edit this group:' . odbc_result($dataTable, 2) . '</td>';
			} else if (isset($aGroupPermissions[$sTableName][odbc_result($dataTable, 1)])) {
				if (@$_SESSION['permissions'][$sReTableName][odbc_result($dataTable, 2)] == $sGroupName) {
					$aTable[odbc_result($dataTable, 1)] = '<td><input type="checkbox" name="' . $sTableName . 'check[' . odbc_result($dataTable, 1) . ']" value="TRUE" checked /></td><td>' . odbc_result($dataTable, 2) . '</td><td><select name="accesslevel' . $sTableName . '[' . odbc_result($dataTable, 1) . ']">' . fOptions($aGroupPermissions[$sTableName][odbc_result($dataTable, 1)], $sTableName, $aOptions) . '</select></td>';
				} else if (fCanSee(@$_SESSION['permissions'][$sReTableName][odbc_result($dataTable, 1)] >= 200)) {
					if (@$_SESSION['permissions'][$sReTableName][odbc_result($dataTable, 1)] >= $aGroupPermissions[$sTableName][odbc_result($dataTable, 1)] || $_SESSION['id'] == 1) {
						$aTable[odbc_result($dataTable, 1)] = '<td><input type="checkbox" name="' . $sTableName . 'check[' . odbc_result($dataTable, 1) . ']" value="TRUE" checked /></td><td>' . odbc_result($dataTable, 2) . '</td><td><select name="accesslevel' . $sTableName . '[' . odbc_result($dataTable, 1) . ']">' . fOptions($aGroupPermissions[$sTableName][odbc_result($dataTable, 1)], $sTableName, $aOptions) . '</select></td>';
					} else {
						$aTable[odbc_result($dataTable, 1)] = '<td colspan="3"><input type="hidden" name="' . $sTableName . 'check[' . odbc_result($dataTable, 1) . ']" value="TRUE" /><input type="hidden" name="accesslevel' . $sTableName . '[' . odbc_result($dataTable, 1) . ']" value="' . $aGroupPermissions[$sTableName][odbc_result($dataTable, 1)] . '">current access too high for you to edit:' . odbc_result($dataTable, 2) . '</td>';
					};
				} else {
					$aTable[odbc_result($dataTable, 1)] = '<td colspan="3"><input type="hidden" name="' . $sTableName . 'check[' . odbc_result($dataTable, 1) . ']" value="TRUE" /><input type="hidden" name="accesslevel' . $sTableName . '[' . odbc_result($dataTable, 1) . ']" value="' . $aGroupPermissions[$sTableName][odbc_result($dataTable, 1)] . '">access denied for:' . odbc_result($dataTable, 2) . '</td>';
				};
			} else if (@$_SESSION['permissions'][$sReTableName][odbc_result($dataTable, 1)] >= 200) {
				$aTable[odbc_result($dataTable, 1)] = '<td><input type="checkbox" name="' . $sTableName . 'check[' . odbc_result($dataTable, 1) . ']" value="TRUE" /></td><td>' . odbc_result($dataTable, 2) . '</td><td><select name="accesslevel' . $sTableName . '[' . odbc_result($dataTable, 1) . ']">' . fOptions('none', $sTableName, $aOptions) . '</select></td>';
			} else if ($_SESSION['id'] == 1) {
				$aTable[odbc_result($dataTable, 1)] = '<td><input type="checkbox" name="' . $sTableName . 'check[' . odbc_result($dataTable, 1) . ']" value="TRUE" /></td><td>' . odbc_result($dataTable, 2) . '</td><td><select name="accesslevel' . $sTableName . '[' . odbc_result($dataTable, 1) . ']">' . fOptions('none', $sTableName, $aOptions) . '</select></td>';
			};
		};		
		if (isset($aTable)) {
			if ($sTableName == 'groupadmin') {
				$sTableName = 'group';
			};
			$aTableOrder[$sTableName] = count($aTable);
			$aPermissionTable[$sTableName] = '<table class="recordssmall recordssmallspacing"><thead><tr><th colspan="3">' . ucfirst($sTableName) . '</th></tr></thead><tbody>';
			$i = 0;
			foreach ($aTable as $key => $value) {
				if ($i % 2 == 0) {
					$aPermissionTable[$sTableName] .= '<tr class="oddRow">';
				} else {
					$aPermissionTable[$sTableName] .= '<tr class="evenRow">';
				}
				$aPermissionTable[$sTableName] .= $value . '</tr>';
				$i++;
			};
			$aPermissionTable[$sTableName] .= '</tbody></table>';
		};
	};
	$queryGroupPermissionsGet = 'select distinct Permissions.areafk, Permissions.departmentfk, Permissions.pagefk, Permissions.groupadminfk, Permissions.level, GroupTable.ID, GroupTable.Name, permissions.disciplinefk 
	from Permissions, grouptable 
	where Permissions.GroupFK = GroupTable.ID
	and GroupTable.ID = ' . $_GET['id'];
	$dataGPG = odbc_exec($conn, $queryGroupPermissionsGet);
	while (odbc_fetch_row($dataGPG)) {
		if (odbc_result($dataGPG, 1)) {
			$aGroupPermissions['area'][odbc_result($dataGPG, 1)] = odbc_result($dataGPG, 5);
		};
		if (odbc_result($dataGPG, 2)) {
			$aGroupPermissions['department'][odbc_result($dataGPG, 2)] = odbc_result($dataGPG, 5);
		};
		if (odbc_result($dataGPG, 3)) {
			$aGroupPermissions['page'][odbc_result($dataGPG, 3)] = odbc_result($dataGPG, 5);
		};
		if (odbc_result($dataGPG, 8)) {
			$aGroupPermissions['discipline'][odbc_result($dataGPG, 8)] = odbc_result($dataGPG, 5);
		};
		if (odbc_result($dataGPG, 4)) {
			$aGroupPermissions['groupadmin'][odbc_result($dataGPG, 4)] = odbc_result($dataGPG, 5);
		};
	};
	$permissionTable .= '<h3>Editing: "' . $sGroupName . '"</h3>';
	$aPermissionTable = array();
	$aTableOrder = array();
	fGetData('area');
	fGetData('grouptable');
	fGetData('pages');
	fGetData('discipline');
	fGetData('department');
	arsort($aTableOrder);
	foreach ($aTableOrder as $key => $value) {
		$permissionTable .= $aPermissionTable[$key];
	};
	$permissionTable .= '<input name="id" type="hidden" value="' . $_GET['id'] . '"></input><div class="clearbutton"><input id="update" type="submit" value="Update Permissions"></input></div></form>';
	$stdOut .= $permissionTable;
	if ($_SESSION['id'] == 1) {
		$aNoticeList[] = 'To Create a Page <a href="managepages.php">Click Here</a>.';
	}
	if (fCanSee(isset($_SESSION['edit']['departmentedit']))) {
		$aNoticeList[] = 'To Create a Department <a href="managedepartment.php">Click Here</a>.';
	};
	if (fCanSee(isset($_SESSION['edit']['areaedit']))) {
		$aNoticeList[] = 'To Create an Area <a href="managearea.php">Click Here</a>.';
	};
	if (fCanSee(isset($_SESSION['edit']['disciplineedit']))) {
		$aNoticeList[] = 'To Create a Discipline <a href="managediscipline.php">Click Here</a>.';
	};
	if (fCanSee(isset($_SESSION['edit']['groupedit']))) {
		$aNoticeList[] = 'To Create a User Group <a href="managegroup.php">Click Here</a>.';
	};
	$hookReplace['help'] = '';
	if (isset($aNoticeList)) {
		$hookReplace['help'] .= '<div class="notice">' . implode(' ', $aNoticeList) . '</div>';
	};
	$hookReplace['help'] .= $helptext['groupconflict'] . $helptext['groupadmingroup'] . $helptext['editpermissions'];
	require_once 'includes/footer.php'; ?>