<?PHP 
$title = 'Batch Equipment';
require_once 'includes/header.php';
if (!fCanSeePublic(@$_SESSION['permissions']['page'][20] >= 100)) {
	$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
fSetDates($startDate, $endDate, 30);
$hookReplace['calicon'] = '<a data-text="Date/Time Picker" href="#" class="menucontext"><span class="icon-calendar-empty icon-hover-hint icon-large"></span></a>';
$hookReplace['calform'] = fStandardCal('batchunitsplash', '[2004, 05, 18]');
$bConn = odbc_connect('DRIVER={SQL Server};Server=INSQL2;Database=batchhistory;', $dbUsername, $dbPassword);
$queryEquipList = 'select distinct UnitOrConnection, equip.DepartmentFK 
from (select distinct unitorconnection
from [BatchHistory].[dbo].[batchdetail]
where datetime between \'' . $startDate . '\' and \'' . date('Y-m-d H:i:s', strtotime($endDate) + 1) . '\'
union
select distinct unitorconnection
from [oldBatchHistory].[dbo].[batchdetail]
where datetime between \'' . $startDate . '\' and \'' . date('Y-m-d H:i:s', strtotime($endDate) + 1) . '\') as temp
left join [plantavail].[dbo].[equip] as equip on unitorconnection like \'%\' + equip + \'%\'
left join [plantavail].[dbo].[eequip] as eequip on unitorconnection like \'%\' + eequip + \'%\' and equip.DepartmentFK = eequip.departmentfk
where unitorconnection <> \'\' and eequip is null ';
if (@$_SESSION['id'] != 1 && isset($_SESSION['id'])) {
	$aQueryEquip[] = 'DepartmentFK is null';
	$queryEquipList .= ' and ' . fOrThemReturn($_SESSION['permissions']['department'], 100, 'departmentfk', $aQueryEquip);
};
$queryEquipList .= ' order by UnitOrConnection asc ';
$dataEquipList = odbc_exec($bConn, $queryEquipList);
while(odbc_fetch_row($dataEquipList)) {
	$aEquips[odbc_result($dataEquipList, 2)][] = odbc_result($dataEquipList, 1);
	if (!odbc_result($dataEquipList, 2)) {
		$aUnused[odbc_result($dataEquipList, 1)] = TRUE;
	};
};
$sepD = '';
$queryDepartment = 'select id, name from department ';
foreach ($aEquips as $department => $array) {
	if ($department) {
		if (!isset($whereSet)) {
			$queryDepartment .= 'where ';
			$whereSet = TRUE;
		};
		$queryDepartment .= $sepD . 'id = ' . $department . ' ';
		$sepD = ' or ';
	};
	$needles = '';
	$haystack = '';
	
	foreach ($array as $needle) {
		$bhaystacks = FALSE;
		foreach ($array as $haystack) {
			if (strpos($haystack, $needle) !== FALSE && $needle != $haystack) {
				if (!isset($aEquipList[$department][$needle][$haystack])) {
					$bhaystacks = TRUE;
					$breferences = FALSE;
					foreach ($array as $references) {
						if (strpos($haystack, $references) !== FALSE && $references != $haystack && $needle != $references) {
							$aEquipList[$department][$needle][$haystack][] = $references;
							$breferences = TRUE;
						};
					};
					if (!$breferences) {
						$aEquipList[$department][$needle][$haystack] = TRUE;
					};
				};
			} elseif (strpos($needle, $haystack) !== FALSE && $needle != $haystack) {
				$bhaystacks = TRUE;
			};
		};
		if (!$bhaystacks) {
			$aEquipList[$department][$needle] = TRUE;
		};
	};
};
if (isset($aUnused)) {
	$extraNotifications .= '<div class="ui-notif-error"><span class="icon-remove-sign"></span><b>Unassigned Equipment Please ';
	if (fCanSee(@$_SESSION['permissions']['page'][12] >= 200 && isset($_SESSION['edit']['departmentedit']))) {
		$extraNotifications .= '<a href="trainadmin.php">Fix</a>';
	} else {
		$extraNotifications .= 'Report';
	};
	$extraNotifications .= ':</b> ';
	$sep = '';
	foreach ($aUnused as $name => $bool) {
		$extraNotifications .= $sep . $name;
		$sep = ', ';
	};
	$extraNotifications .= '</div>';
};
$queryDepartment .= 'order by name asc';
$dataDepartment = odbc_exec($conn, $queryDepartment);
while (odbc_fetch_row($dataDepartment)) {
		$departmentNames[odbc_result($dataDepartment, 1)] = odbc_result($dataDepartment, 2);
};
$stdOut .= '<h2>Showing equipment used between ' . $startDate . ' and ' . $endDate . '</h2>';
if (isset($aEquipList)) {
	foreach ($aEquipList as $department => $array) {
		if (isset($departmentNames[$department])) {
			$departmentName = $departmentNames[$department];
		} else {
			$departmentName = 'Undefined';
		};
		$stdOut .= '<div class="equiplist"><h3>' . $departmentName . '</h3><ul>';
		foreach ($array as $needle => $arrayArray) {
			$stdOut .= '<li><a href="batchunit.php?startdate=' . $startDate . '&' . 'enddate=' . $endDate . '&department=' . $department . '&equip=' . $needle . '">' . $needle . '</a>';
			if (is_array($arrayArray)) {
				$stdOut .= ' <a href="#" data-display="none" data-unit="' . $needle . '"><span class="icon-caret-right icon-large"></span><span class="icon-caret-down icon-large"></span></a><ul data-unit="' . $needle . '">';
				foreach ($arrayArray as $haystack => $references) {
					$stdOut .= '<li><a href="batchunit.php?startdate=' . $startDate . '&' . 'enddate=' . $endDate . '&department=' . $department . '&equip=' . $haystack . '">' . $haystack . '</a>';
					if (is_array($references)) {
						$stdOut .= '<ul>';
						foreach ($references as $referencesNames) {
							$stdOut .= '<li><a href="batchunit.php?startdate=' . $startDate . '&' . 'enddate=' . $endDate . '&department=' . $department . '&equip=' . $referencesNames . '">' . $referencesNames . '</a> <a href="#" data-display="none" data-unit="' . $referencesNames . '"><span class="icon-caret-right icon-large"></span></a></li>';
						};
						$stdOut .= '</ul>';
					};
					$stdOut .= '</li>';
				};
				$stdOut .= '</ul>';
			};
			$stdOut .= '</li>';
		};
		$stdOut .= '</ul></div>';
	};
};

$stdOut .= '
	<script type="text/javascript">
	
				$(function() {
				
				$(".equiplist").find(\'[href="#"]\').click(function() {
					var equiplist = $(this).parentsUntil(".equiplist");
					var clickedUnit = $(this).data("unit");
					if ($(this).data("display") == "none") {
						equiplist.find("[data-unit]").each(function () {
							switch ($(this).prop("tagName")) {
								case "UL":
									if (clickedUnit == $(this).data("unit")) {
										$(this).css("display", "block");
									} else {
										$(this).css("display", "none");
									};
									break;
								case "A":
									if (clickedUnit == $(this).data("unit")) {
										$(this).find(".icon-caret-down").css("display", "inline");
										$(this).find(".icon-caret-right").css("display", "none");
									} else {
										$(this).find(".icon-caret-down").css("display", "none");
										$(this).find(".icon-caret-right").css("display", "inline");
									};
									break;
							};
						});
					} else {
						$(this).next().css("display", "none");
						$(this).find(".icon-caret-down").css("display", "inline");
						$(this).find(".icon-caret-right").css("display", "none");
					};
					return false;
				});
				
			});
	</script>';
odbc_close($bConn);
$hookReplace['help'] = $helptext['standardcal'] . '<a href="#">Equipment Traversal</a><div>Clicking the right-arrow beside the equipment name will open that group showing all related equipment and allow navigation between them.</div>' . $helptext['default30'];
require_once 'includes/footer.php'; ?>