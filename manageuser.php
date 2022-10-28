<?PHP 
$title = 'Manage Users';
require_once 'includes/header.php';
if (!fCanSee(isset($_SESSION['admin']['groupadmin']))) {
	$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
$hookReplace['searchicon'] = '<a href="#" data-text="Search" class="menucontext"><span class="icon-search icon-hover-hint icon-large"></span></a>';
$hookReplace['searchform'] = '<form id="search" action="manageuser.php" method="get">';
if (isset($_GET['search'])) {
	$hookReplace['searchform'] .= '<a href="manageuser.php"><span class="icon-remove-sign icon-large"></span></a> ';
};
$hookReplace['searchform'] .= '<input name="search" type="text"';
if (isset($_GET['search'])) {
	$hookReplace['searchform'] .= ' value="' . $_GET['search'] . '" ';
};
$hookReplace['searchform'] .= '/><input type="submit" value="Search!" /></form>';
$stdOut .= '<script type="text/javascript">
	$(function() {
		$(".group").click(function() {
			var newuseroption = $(this).prev();
			var bValid = true;
			var fields = $(this).parent();
			var newuseroption = fields.find("[name=\'group\']");
			$("select").removeClass( "ui-state-error" );
			bValid = bValid && checkOptionSelected( newuseroption , "You must select a group.");
			if (bValid == false) {
				return false;
			};
		});
	});
</script>';
	$queryUsers = 'select id, name, email from users where id <> 1';
	if (isset($_GET['search'])) {
		$queryUsers .= ' and name like \'%' . $_GET['search'] . '%\'';
	};
	$queryUsers .= ' order by name';
	$dataUsers = odbc_exec($conn, $queryUsers);
	$queryGroup = 'select id, name from grouptable';
	if ($_SESSION['id'] != 1) {
		fOrThem($_SESSION['permissions']['group'], 300, 'grouptable.id', $aQueryGroup);
		fGenerateWhere($queryGroup, $aQueryGroup);
	};
	$dataGroup = odbc_exec($conn, $queryGroup);
	$select = '<select name="group"><option value="none">None</option>';
	while (odbc_fetch_row($dataGroup)) {
		$select .= '<option value="' . odbc_result($dataGroup, 1) . '" >' . odbc_result($dataGroup, 2) . '</option>';
		$aGroupTable[odbc_result($dataGroup, 1)] = odbc_result($dataGroup, 2);
	};
	$select .= '</select>';
	$queryGroupJunction = 'select id, userid, groupid from groupjunction';
	if ($_SESSION['id'] != 1) {
		fOrThem($_SESSION['permissions']['group'], 300, 'groupid', $aGroupJunction);
		fGenerateWhere($queryGroupJunction, $aGroupJunction);
	};
	$dataGroupJunction = odbc_exec($conn, $queryGroupJunction);
	while (odbc_fetch_row($dataGroupJunction)) {
		$junction[odbc_result($dataGroupJunction, 2)][] = [odbc_result($dataGroupJunction, 1), odbc_result($dataGroupJunction, 3)];
	};
	$j = 0;
	$users = '<table class="records"><thead><tr><th>Name</th><th>Group</th>';
	if ($_SESSION['id'] == 1) {
		$users .= '<th>Details</th><th>Action</th><th>Activation</th>';
	};
	$users .= '</tr></thead><tbody>';
	while (odbc_fetch_row($dataUsers)) {
		if ($j % 2 == 0) {
			$users .= '<tr class="oddRow">';
		} else {
			$users .= '<tr class="evenRow">';
		}
		$j++;
		$users .= '<td>' . odbc_result($dataUsers, 2) . '</td><td>';
		$aUsedGroups = [];
		if (isset($junction[odbc_result($dataUsers, 1)])) {
			foreach ($junction[odbc_result($dataUsers, 1)] as $key => $value) {
				$aUserGroup['form'][odbc_result($dataUsers, 1)][] = '<form action="includes/groupjunction.php" method="post"><input name="update" type="hidden" value="' . $junction[odbc_result($dataUsers, 1)][$key][0] . '"></input>';
				$aUserGroup['options'][odbc_result($dataUsers, 1)][$junction[odbc_result($dataUsers, 1)][$key][1]][] = '<option value="' . $junction[odbc_result($dataUsers, 1)][$key][1] . '" selected >' . $aGroupTable[$junction[odbc_result($dataUsers, 1)][$key][1]] . '</option>';
				$sSelectedOption = '<option value="' . $junction[odbc_result($dataUsers, 1)][$key][1] . '" selected >' . $aGroupTable[$junction[odbc_result($dataUsers, 1)][$key][1]] . '</option>';
				$aUsedGroups[] = $junction[odbc_result($dataUsers, 1)][$key][1];
				$aUserGroup['/form'][odbc_result($dataUsers, 1)][] = '</select><span class="required"> * </span><input class="group" type="submit" value="Update"></form><form action="includes/groupjunction.php" method="post"><input name="delete" type="hidden" value="' . $junction[odbc_result($dataUsers, 1)][$key][0] . '"></input><input type="submit" value="Delete"></form><br />';
			};
		};
		$aUnusedGroups = array_diff(array_flip($aGroupTable), $aUsedGroups);
		$aUnusedGroups = array_flip($aUnusedGroups);
		$sUnusedSelect = '<select name="group"><option value="none">None</option>';
		foreach($aUnusedGroups as $key => $value) {
				$sUnusedSelect .= '<option value="' . $key . '" >' . $value . '</option>';
		};
		if (isset($aUserGroup['form'][odbc_result($dataUsers, 1)])) {
			foreach ($aUserGroup['form'][odbc_result($dataUsers, 1)] as $key => $value) {
				$users .= $aUserGroup['form'][odbc_result($dataUsers, 1)][$key] . $sUnusedSelect . $aUserGroup['options'][odbc_result($dataUsers, 1)][$junction[odbc_result($dataUsers, 1)][$key][1]][0] . $aUserGroup['/form'][odbc_result($dataUsers, 1)][$key];
			};
		};
		$sUnusedSelect .= '</select>';
		/*if (!isset($sUnusedSelect)) {
			$sUnusedSelect = '<select name="group"><option value="none">None</option></select>';
		};*/
		$users .= '<form action="includes/groupjunction.php" method="post"><input name="add" type="hidden" value="' . odbc_result($dataUsers, 1) . '"></input>' . $sUnusedSelect . '<span class="required"> * </span><input class="group" type="submit" value="Add"></form>
		</td>';
		if ($_SESSION['id'] == 1) {
			$users .= '<td><a href="changedetails.php?id=' . odbc_result($dataUsers, 1) . '">Change Details</a></td><td><form action="includes/deleteuser.php" method="POST" ><input name="id" type="hidden" value="' . odbc_result($dataUsers, 1) . '"></input><input type="submit" value="Delete"></input></form></td>
			<td>
			<form action="includes/resendactivation.php" method="POST">
			<input name="id" type="hidden" value="' . odbc_result($dataUsers, 1) . '"></input>
			<input name="name" type="hidden" value="' . odbc_result($dataUsers, 2) . '"></input>
			<input name="to" type="hidden" value="' . odbc_result($dataUsers, 3) . '"></input>
			<input type="submit" value="Resend Activation"></input>
			</form>
			</td>';
		};
	};
	$users .= '</tr>';
	$users .= '</tbody></table>';
	$stdOut .= $users . '<div class="requiredhint"><span class="required"> * </span>are required fields.</div>';
	$hookReplace['help'] = '<div class="notice">Users must create themselves.';
	if (fCanSee(isset($_SESSION['edit']['groupedit']))) {
		$hookReplace['help'] .= ' To create User Groups <a href="managegroup.php">Click Here</a>.';
	};
	$hookReplace['help'] .= '</div>' . $helptext['search'] . $helptext['groupconflict'] . '<a href="#">Updating User Groups in Users</a><div>Updating Users changes which User Groups they belong too.</div><a href="#">Deleting User Groups from Users</a><div>Deleting Users removes them from User Groups. Deleting users from User Groups will not remove the group.</div><a href="#">Adding User Groups to Users</a><div>Adding Users assigns User Groups they belong too.</div>';
	if ($_SESSION['id'] == 1) {
		$hookReplace['help'] .= '<a href="#">Changing Details</a><div>Changing Details allows you to change the information attached to each users account</div><a href="#">Deleting Users</a><div>Deleting Users removes the selected User from the database, the action cannot be undone and all data loss is permanent</div><a href="#">Resend Activation</a><div>Resend Activation allows you to resend the activation code to the Users E-Mail inbox so that they can validate their E-Mail address and account.</div>';
	};
	require_once 'includes/footer.php'; ?>