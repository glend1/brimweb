<?PHP 
$title = 'Manage Process Trends';
require_once 'includes/header.php';
if (!fCanSee(@$_SESSION['permissions']['page'][10] >= 100)) {
	$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
if ($_SESSION['id'] != 1) {
	$joinWhere = 'where userfk = ' . $_SESSION['id'] . ' or ' . fOrThemReturn($_SESSION['permissions']['group'], 100, 'groupfk');
} else {
	$joinWhere = 'where userfk = 0';
};
$queryTrends = 'select processtrend.ID, processtrend.name, publicbool, users.name, share
from ProcessTrend
left join (select distinct processtrendfk, 1 as share from ProcessTrendShare ' . $joinWhere . ') as shares on ProcessTrend.id = shares.ProcessTrendFK
join users on users.id = processtrend.userfk';
if ($_SESSION['id'] > 1) {
	$queryTrends .= ' where publicbool = 1 or processtrend.userfk = ' . $_SESSION['id'];
};
$aTrends = ['My Trends' => array(), 'Shared Trends' => array(), 'Public Trends' => array()];
$dataTrends = odbc_exec($conn, $queryTrends);
while (odbc_fetch_row($dataTrends)) {
	if (fCanSee(odbc_result($dataTrends, 4) == $_SESSION['user'])) {
		$aTrends['My Trends'][] = '<td>' . odbc_result($dataTrends, 2) . '</td><td><a href="formprocesstrend.php?id=' . odbc_result($dataTrends, 1) . '">Edit</a></td><td><a href="shareprocesstrend.php?id=' . odbc_result($dataTrends, 1) . '">Share</a></td><td><a href="includes/changeprocesstrend.php?delete=' . odbc_result($dataTrends, 1) . '">Delete</a></td>';
	};
	if (odbc_result($dataTrends, 3) == 1) {
		$aTrends['Public Trends'][] = '<td>' . odbc_result($dataTrends, 2) . '</td><td>' . odbc_result($dataTrends, 4) . '</td><td><a href="formprocesstrend.php?id=' . odbc_result($dataTrends, 1) . '">Use as Template</a></td>';
	};
	if (odbc_result($dataTrends, 5) == 1) {
		$aTrends['Shared Trends'][] = '<td>' . odbc_result($dataTrends, 2) . '</td><td>' . odbc_result($dataTrends, 4) . '</td><td><a href="formprocesstrend.php?id=' . odbc_result($dataTrends, 1) . '">Use as Template</a></td>';
	};
};
$stdOut .= '<form class="createform" action="formprocesstrend.php" method="post"><h3>Create Process Trend</h3><input type="submit" value="Create"></form>';
$rowId = 1;
foreach ($aTrends as $key => $array) {
	if (!empty($array)) {
		if ($rowId % 2 == 0) {
			$rowType = 'oddRow';
		} else {
			$rowType = 'evenRow';
		};
		$rowId++;
		$stdOut .= '<div class="inlineclass ' . $rowType . '"><h3>' . $key . '</h3><table class="recordssmall"><thead>';
		switch ($key) {
			case 'My Trends':
				$stdOut .= '<tr><th>Name</th><th>Edit</th><th>Share</th><th>Delete</th></tr>';
				break;
			case 'Shared Trends':
				$stdOut .= '<tr><th>Name</th><th>Author</th><th>Edit</th></tr>';
				break;
			case 'Public Trends':
				$stdOut .= '<tr><th>Name</th><th>Author</th><th>Edit</th></tr>';
				break;
		};
		$stdOut .= '</thead><tbody>';
		$i = 0;
		foreach ($array as $row) {
			if ($i % 2 == 0) {
				$stdOut .= '<tr class="oddRow">';
			} else {
				$stdOut .= '<tr class="evenRow">';
			};
			$stdOut .= $row . '</tr>';
			$i++;
		};
		$stdOut .= '</tbody></table></div>';
	};
};
require_once 'includes/footer.php'; ?>