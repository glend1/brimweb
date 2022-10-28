<?PHP 
$title = 'Manage Groups';
require_once 'includes/header.php'; 
if (!fCanSee(isset($_SESSION['edit']['groupedit']))) {
	$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
$hookReplace['searchicon'] = '<a href="#" data-text="Search" class="menucontext"><span class="icon-search icon-hover-hint icon-large"></span></a>';
$hookReplace['searchform'] = '<form id="search" action="managegroup.php" method="get">';
if (isset($_GET['search'])) {
	$hookReplace['searchform'] .= '<a href="managegroup.php"><span class="icon-remove-sign icon-large"></span></a> ';
};
$hookReplace['searchform'] .= '<input name="search" type="text"';
if (isset($_GET['search'])) {
	$hookReplace['searchform'] .= ' value="' . $_GET['search'] . '" ';
};
$hookReplace['searchform'] .= '/><input type="submit" value="Search!" /></form>';
$stdOut .= '<script type="text/javascript">
	$(function() {
		$("#groupbutton").click(function() { 
			var bValid = true;
			var fields = $(this).parent();
			var group = fields.find("[name=\'group\']");
			$("input").removeClass( "ui-state-error" );
			bValid = bValid && checkLength( group, "name", 1, 60 );
			if (bValid == false) {
				return false;
			};
		});
		$(".renamebutton").click(function() {
			var bValid = true;
			var fields = $(this).parent();
			var name = fields.find("[name=\'name\']");
			$("input").removeClass( "ui-state-error" );
			bValid = bValid && checkLength( name, "name", 1, 60 );
			if (bValid == false) {
				return false;
			};
		});
	});
</script>';
	$queryGroup = 'select id, name from grouptable ';
	if ($_SESSION['id'] != 1) {
		fOrThem($_SESSION['permissions']['group'], 200, 'id', $aQueryGroup);
		fGenerateWhere($queryGroup, $aQueryGroup);
	};
	if (isset($_GET['search'])) {
		if ($_SESSION['id'] != 1) {
			$queryGroup .= ' and name like \'%' . $_GET['search'] . '%\'';
		} else {
			$queryGroup .= ' where name like \'%' . $_GET['search'] . '%\'';
		};
	};
	$dataGroup = odbc_exec($conn, $queryGroup);
	$queryMembers = 'select groupjunction.id, groupjunction.userid,
	groupjunction.groupid, Users.name 
	from groupjunction
	join users on Users.ID = groupjunction.userid';
	if ($_SESSION['id'] != 1) {
		fOrThem($_SESSION['permissions']['group'], 200, 'groupjunction.groupid', $aQueryMembers);
		fGenerateWhere($queryMembers, $aQueryMembers);
	};
	$dataMembers = odbc_exec($conn, $queryMembers);
	while (odbc_fetch_row($dataMembers)) {
		if (fCanSee(@$_SESSION['permissions']['group'][odbc_result($dataMembers, 3)] >= 300)) {
			$sdeleteUser = '<form action="includes/groupjunction.php" method="post"><input type="hidden" name="delete" value="' . odbc_result($dataMembers, 1) . '"></input><input type="submit" value="Remove"></input></form> ';
		} else {
			$sdeleteUser = '';
		};
		$memberList[odbc_result($dataMembers, 3)][] = odbc_result($dataMembers, 4) . ' ' . $sdeleteUser;
	};
	$j = 0;
	$group = '';
	if ($dataGroup != 0) {
	$group = '<table class="records"><thead><tr><th>Name</th><th>Members</th><th>Edit&nbsp;Permissions</th>';
	if (fCanSee(isset($_SESSION['admin']['groupadmin']))) {
		$group .= '<th>Action</th>';
	};
	$group .= '</tr></thead><tbody>';
		while (odbc_fetch_row($dataGroup)) {
			if ($j % 2 == 0) {
				$group .= '<tr class="oddRow">';
			} else {
				$group .= '<tr class="evenRow">';
			}
			$j++;
			$group .= '<td>';
			if (fCanSee(@$_SESSION['permissions']['group'][odbc_result($dataGroup, 1)] >= 300)) {
				$group .= '<form method="post" action="includes/renamegroup.php" class="forcesingleline"><input name="id" type="hidden" value="' . odbc_result($dataGroup, 1) . '" /><input name="name" type="text" value="' . odbc_result($dataGroup, 2) . '" /><input class="renamebutton" type="submit" value="Update" /></form>';
			} else {
				$group .= str_replace(' ', '&nbsp;', odbc_result($dataGroup, 2));
			};
			$group .= '</td><td>';
			if (isset($memberList[odbc_result($dataGroup, 1)])) {
				foreach ($memberList[odbc_result($dataGroup, 1)] as $key => $value) {
					$group .= $memberList[odbc_result($dataGroup, 1)][$key];
				};
			};
			$group .= '</td><td><a href="managepermissions.php?id=' . odbc_result($dataGroup, 1) . '">Edit&nbsp;Permissions</a></td>';
			if (fCanSee(@$_SESSION['permissions']['group'][odbc_result($dataGroup, 1)] >= 300)) {
				$group .= '<td><form action="includes/deletegroup.php" method="POST" ><input name="id" type="hidden" value="' . odbc_result($dataGroup, 1) . '"></input><input type="submit" value="Delete"></input></form></td>';
			};
		};
		$group .= '</tr></tbody>';
		if (fCanSee(isset($_SESSION['admin']['groupadmin']))) {
			$group .= '<tfoot>';
			if ($j % 2 == 0) {
				$group .= '<tr class="oddRow">';
			} else {
				$group .= '<tr class="evenRow">';
			};
			$group .= '<td colspan="4"><form action="includes/newgroup.php" method="POST" >Group Name:<input name="group" id="group" type="text"></input><span class="required"> * </span> Group Admin:' . fGroupSelect()  . '<input id="groupbutton"  type="submit" value="Add new group"></input></form></td></tr></tfoot>';
		};
		$group .= '</table>';
	};
	$stdOut .= $group . '<div class="requiredhint"><span class="required"> * </span>are required fields.</div>';
	$hookReplace['help'] = '<div class="notice">Users must create themselves.';
	if (fCanSee(isset($_SESSION['admin']['groupadmin']))) {
		$hookReplace['help'] .= ' To assign Users to Groups <a href="manageuser.php">Click Here</a>';
	};
	$hookReplace['help'] .= '</div>';
	$hookReplace['help'] .= $helptext['search'] . $helptext['groupconflict'] . $helptext['groupadmingroup'] . $helptext['add'] . $helptext['editpermissions'] . '<a href="#">Removing Members</a><div>Removing Members allow you to remove Users from specific User Groups. The User Group will not be deleted.</div><a href="#">Deleting Groups</a><div>Deleteing Groups removes the selected group from the database. this action cannot be undone and is permanent.</div>';
	require_once 'includes/footer.php'; ?>