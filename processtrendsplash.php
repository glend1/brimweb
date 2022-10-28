<?PHP 
$title = 'Process Trends';
require_once 'includes/header.php';
if (!fCanSeePublic(@$_SESSION['permissions']['page'][10] >= 100)) {
	$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
$joinWhere = 'where userfk = 0';
if (isset($_SESSION['id'])) {
	if ($_SESSION['id'] != 1) {
		$joinWhere = 'where userfk = ' . $_SESSION['id'] . ' or ' . fOrThemReturn($_SESSION['permissions']['group'], 100, 'groupfk');
	};
};
$queryTrends = 'select temp1.id, trendname, publicbool, username, share, realdepartmentfk, departmentequipmentfk, departmentequipmentname, department.name
from (select processtrend.userfk, processtrend.ID, processtrend.name as trendname, publicbool, users.name as username, share, case 
		when processtrend.DepartmentEquipmentFK IS NOT NULL 
			then departmentequipment.DepartmentFK 
			else processtrend.DepartmentFK end as realdepartmentfk,
	DepartmentEquipmentFK, DepartmentEquipment.Name as departmentequipmentname
	from ProcessTrend 
	left join (select distinct processtrendfk, 1 as share from ProcessTrendShare ' . $joinWhere . ') as shares on ProcessTrend.id = shares.ProcessTrendFK
	left join departmentequipment on processtrend.departmentequipmentfk = departmentequipment.ID
	join users on users.id = processtrend.userfk) as temp1
left join department on realdepartmentfk = department.id';
if (isset($_SESSION['id'])) {
	if ($_SESSION['id'] > 1) {
		$queryTrends .= ' where (publicbool = 1 and ' . fOrThemReturn($_SESSION['permissions']['department'], 100, 'realdepartmentfk') . ') or temp1.userfk = ' . $_SESSION['id'];
	};
} else {
	$queryTrends .= ' where publicbool = 1';
};
$queryTrends .= ' order by department.name asc, departmentequipmentfk asc, trendname asc';
$aTrends = ['My Trends' => array(), 'Shared Trends' => array(), 'Public Trends' => array()];
$dataTrends = odbc_exec($conn, $queryTrends);
while (odbc_fetch_row($dataTrends)) {
	$lookup['d' . odbc_result($dataTrends, 6)] = odbc_result($dataTrends, 9);
	$lookup['e' . odbc_result($dataTrends, 7)] = odbc_result($dataTrends, 8);
	$row = [odbc_result($dataTrends, 1), odbc_result($dataTrends, 2), odbc_result($dataTrends, 4)];
	if (isset($_SESSION['id'])) {
		if (fCanSee(odbc_result($dataTrends, 4) == $_SESSION['user'])) {
			if (odbc_result($dataTrends, 7)) {
				$aTrends['My Trends']['d' . odbc_result($dataTrends, 6)]['e' . odbc_result($dataTrends, 7)][] = $row;
			} else {
				$aTrends['My Trends']['d' . odbc_result($dataTrends, 6)][] = $row;
			};
		};
	};
	if (odbc_result($dataTrends, 3) == 1) {
		if (odbc_result($dataTrends, 7)) {
			$aTrends['Public Trends']['d' . odbc_result($dataTrends, 6)]['e' . odbc_result($dataTrends, 7)][] = $row;
		} else {
			$aTrends['Public Trends']['d' . odbc_result($dataTrends, 6)][] = $row;
		};
	};
	if (odbc_result($dataTrends, 5) == 1) {
		if (odbc_result($dataTrends, 7)) {
			$aTrends['Shared Trends']['d' . odbc_result($dataTrends, 6)]['e' . odbc_result($dataTrends, 7)][] = $row;
		} else {
			$aTrends['Shared Trends']['d' . odbc_result($dataTrends, 6)][] = $row;
		};
	};
};
$stdOut .= '<script type="text/javascript">
	$(function() {
		$(".listheader").click(function() {
			var jThis = $(this);
			var listItem = jThis.next("ul");
			if (listItem.css("display") == "none") {
				listItem.css("display", "block");
				jThis.children(".icon-caret-right").css("display", "none");
				jThis.children(".icon-caret-down").css("display", "inline");
			} else {
				listItem.css("display", "none");
				jThis.children(".icon-caret-right").css("display", "inline");
				jThis.children(".icon-caret-down").css("display", "none");
			};
			return false;
		});
	});
</script><form class="createform" action="formprocesstrend.php" method="post"><h3>Create Process Trend</h3><input type="submit" value="Create"></form>';
$rowId = 1;
foreach ($aTrends as $key => $array) {
	if (!empty($array)) {
		if ($rowId % 2 == 0) {
			$rowType = 'oddRow';
		} else {
			$rowType = 'evenRow';
		};
		$stdOut .= '<div class="inlineclass ' . $rowType . '"><h3>' . $key . '</h3>';
		$rowId++;
		foreach ($array as $deptKey => $deptArray) {
			$stdOut .= '<ul><li><a class="listheader" href="#">' . $lookup[$deptKey] . ' <span class="icon-caret-right icon-large"></span><span class="icon-caret-down icon-large"></span></a><ul>';
			foreach ($deptArray as $equipKey => $equipArray) {
				if (isset($lookup[$equipKey])) {
					$stdOut .= '<li><a class="listheader" href="#">' . $lookup[$equipKey] . ' <span class="icon-caret-right icon-large"></span><span class="icon-caret-down icon-large"></span></a><ul>';
					foreach ($equipArray as $trend) {
						$stdOut .= '<li><a href="processtrend.php?id=' . $trend[0] . '">' . $trend[1] . '</a><div class="hinttext">Author:' . $trend[2] . '</div></li>';
					};
					$stdOut .= '</ul></li>';
				} else {
					$stdOut .= '<li><a href="processtrend.php?id=' . $equipArray[0] . '">' . $equipArray[1] . '</a><div class="hinttext">Author:' . $equipArray[2] . '</div></li>';
				};
			};
			$stdOut .= '</ul></li></ul>';
		};
		$stdOut .= '</div>';
	};
};
require_once 'includes/footer.php'; ?>