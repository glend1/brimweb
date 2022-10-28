<?PHP 
$title = 'Share Process Trend';
require_once 'includes/header.php';
if (!fCanSee(@$_SESSION['permissions']['page'][10] >= 100)) {
	$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
if (!isset($_GET['id'])) {
	$_SESSION['sqlMessage'] = 'You must select a trend you wish to share!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
$sharePermission = false;
if ($_SESSION['id'] == 1) {
	$sharePermission = true;
};
$queryCheckPermission = 'select userfk, name from processtrend where id = ' . $_GET['id'];
$dataCheckPermission = odbc_exec($conn, $queryCheckPermission);
if (odbc_fetch_row($dataCheckPermission)) {
	if ($_SESSION['id'] == odbc_result($dataCheckPermission, 1)) {
		$sharePermission = true;
	};
} else {
	$_SESSION['sqlMessage'] = 'Trend not found!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
if (!$sharePermission) {
	$_SESSION['sqlMessage'] = 'You do not have permission to share this trend!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
function fExcludeDBSearch($array, $index, $col) {
	if ($index == 'Users') {
		$array[$index][] = [null, $_SESSION['id'], null];
	};
	if (!empty($array[$index])) {
		$out = '';
		$sep = '';
		foreach ($array[$index] as $arrayArray) {
			$out .= $sep . $col . ' <> ' . $arrayArray[1];
			$sep .= ' and ';
		};
		return ' where (' . $out . ')';
	} else {
		return '';
	};
};
$hookReplace['searchicon'] = '<a href="#" data-text="Search" class="menucontext"><span class="icon-search icon-hover-hint icon-large"></span></a>';
$hookReplace['searchform'] = '<form id="search" action="shareprocesstrend.php" method="get">';
if (isset($_GET['search'])) {
	$hookReplace['searchform'] .= '<a href="shareprocesstrend.php?id=' . $_GET['id'] . '"><span class="icon-remove-sign icon-large"></span></a> ';
};
$hookReplace['searchform'] .= '<input name="search" type="text"';
if (isset($_GET['search'])) {
	$hookReplace['searchform'] .= ' value="' . $_GET['search'] . '" ';
};
$hookReplace['searchform'] .= '/><input type="hidden" name="id" value="' . $_GET['id'] . '"/><input type="submit" value="Search!" /></form>';
$stdOut .= '<h2>Share properties for ' . odbc_result($dataCheckPermission, 2) . '</h2>';
$queryShare = 'select processtrendshare.id, groupfk, userfk, users.name, grouptable.name 
from processtrendshare
left join users on userfk = users.id
left join grouptable on groupfk = grouptable.id
where processtrendfk = ' . $_GET['id'] . '
order by users.name asc, grouptable.name asc';
$dataShare = odbc_exec($conn, $queryShare);
$aShare = ['Groups' => array(), 'Users' => array()];
while (odbc_fetch_row($dataShare)) {
	if (odbc_result($dataShare, 2)) {
		$aShare['Groups'][] = [odbc_result($dataShare, 1), odbc_result($dataShare, 2), odbc_result($dataShare, 5)];
	};
	if (odbc_result($dataShare, 3)) {
		$aShare['Users'][] = [odbc_result($dataShare, 1), odbc_result($dataShare, 3), odbc_result($dataShare, 4)];
	};
};
$queryUsers = 'select id, name from users' . fExcludeDBSearch($aShare, 'Users', 'id');
$queryGroup = 'select id, name from grouptable' . fExcludeDBSearch($aShare, 'Groups', 'id');
if (isset($_GET['search'])) {
	if (empty($aShare['Users'])) {
		$queryUsers .= ' where name like \'%' . $_GET['search'] . '%\'';
	} else {
		$queryUsers .= ' and name like \'%' . $_GET['search'] . '%\'';
	};
	if (empty($aShare['Groups'])) {
		$queryGroup .= ' where name like \'%' . $_GET['search'] . '%\'';
	} else {
		$queryGroup .= ' and name like \'%' . $_GET['search'] . '%\'';
	};
};
$queryUsers .= ' order by name';
$queryGroup .= ' order by name';
$dataGroup = odbc_exec($conn, $queryGroup);
$stdOut .= '<div class="tagnameclass"><h3>Available Groups</h3><ul>';
while (odbc_fetch_row($dataGroup)) {
	$stdOut .= '<li><a href="includes/changeprocesstrendshare.php?add=' . $_GET['id'] . '&group=' . odbc_result($dataGroup, 1) . '"><span class="icon-plus"></span></a>' . odbc_result($dataGroup, 2) . '</li>';
};
$stdOut .= '</ul>';
$dataUsers = odbc_exec($conn, $queryUsers);
$stdOut .= '<h3>Available Users</h3><ul>';
while (odbc_fetch_row($dataUsers)) {
	$stdOut .= '<li><a href="includes/changeprocesstrendshare.php?add=' . $_GET['id'] . '&users=' . odbc_result($dataUsers, 1) . '"><span class="icon-plus"></span></a>' . odbc_result($dataUsers, 2) . '</li>';
};
$stdOut .= '</ul></div><div class="tagnameclass">';
foreach ($aShare as $key => $array) {
	$stdOut .= '<h3>Current ' . $key . '</h3>';
		if (!empty($array)) {
			$stdOut .= '<ul>';
			foreach ($array as $arrayArray) {
				$stdOut .= '<li><a href="includes/changeprocesstrendshare.php?delete=' . $_GET['id'] . '&key=' . $arrayArray[0] . '"><span class="icon-trash"></span></a>' . $arrayArray[2] . '</li>';
			};
			$stdOut .= '<ul>';
		};
};
$stdOut .= '</div>';
$hookReplace['help'] = '<a href="#">Searching</a><div>This filters both Users and Groups simultaneously.</div>';
require_once 'includes/footer.php'; ?>