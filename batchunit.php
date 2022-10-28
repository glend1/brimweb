<?PHP 
$title = 'Batch Equipment';
require_once 'includes/header.php';
if (!fCanSeePublic(@$_SESSION['permissions']['page'][20] >= 100)) {
	$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
$aDE = fPermissionDE();
$bConn = odbc_connect('DRIVER={SQL Server};Server=INSQL2;Database=batchhistory;', $dbUsername, $dbPassword);
if (!isset($_GET['equip'])) {
	$_SESSION['sqlMessage'] = 'Please select a piece of Equipment or Connection!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
fSetDates($startDate, $endDate, 30);
$hookReplace['calicon'] = '<a data-text="Date/Time Picker" href="#" class="menucontext"><span class="icon-calendar-empty icon-hover-hint icon-large"></span></a>';
$hookReplace['calform'] = fStandardCal('batchunit', '[2004, 05, 18]');
$queryEquipList = 'select distinct UnitOrConnection 
from (select distinct unitorconnection
from [BatchHistory].[dbo].[batchdetail]
where datetime between \'' . $startDate . '\' and \'' . date('Y-m-d H:i:s', strtotime($endDate) + 1) . '\'
union
select distinct unitorconnection
from [oldBatchHistory].[dbo].[batchdetail]
where datetime between \'' . $startDate . '\' and \'' . date('Y-m-d H:i:s', strtotime($endDate) + 1) . '\') as temp
left join [plantavail].[dbo].[equip] as equip on unitorconnection like \'%\' + equip + \'%\' 
left join [plantavail].[dbo].[eequip] as eequip on unitorconnection like \'%\' + eequip + \'%\' and equip.DepartmentFK = eequip.departmentfk
where unitorconnection <> \'\' and eequip is null and ';
if (!isset($aDE['department'])) {
	$queryEquipList .= ' equip.departmentfk is NULL';
} else {
	$queryEquipList .= ' equip.departmentfk = ' . $aDE['department'];
};
$queryEquipList .= ' order by unitorconnection asc';
$dataEquipList = odbc_exec($bConn, $queryEquipList);
$hookReplace['contexticon'] = '<a href="#" data-text="Context Sensitive Menu"  class="menucontext"><span class="icon-briefcase icon-hover-hint icon-large"></span></a>';
$hookReplace['contextmenu'] = '<div id="subnav"><div>Similar Equipment/Unit or Connections:</div><ul>';
while(odbc_fetch_row($dataEquipList)) {
	$hookReplace['contextmenu'] .= '<li><a href="batchunit.php?department=' . $aDE['department'] . '&startdate=' . $startDate . '&enddate=' . $endDate . '&equip=' . odbc_result($dataEquipList, 1) . '">' . odbc_result($dataEquipList, 1) . '</a></li>';
};
$hookReplace['contextmenu'] .= '</ul></div>';
$queryEquip = '	select UnitOrConnection, Action_CD, datetime, [BatchHistory].[dbo].[batchidlog].[batch_log_id], \'batchhistory\', [BatchHistory].[dbo].[batchidlog].[campaign_id], [BatchHistory].[dbo].[batchidlog].[lot_id], [BatchHistory].[dbo].[batchidlog].[batch_id], [BatchHistory].[dbo].[batchidlog].[train_id], [BatchHistory].[dbo].[batchidlog].[recipe_id], [BatchHistory].[dbo].[batchidlog].[recipe_version], [plantavail].[dbo].[train].[departmentequipmentfk], [plantavail].[dbo].[train].[departmentfk]
from [BatchHistory].[dbo].[batchdetail]
join [BatchHistory].[dbo].[batchidlog] on [BatchHistory].[dbo].[batchidlog].[batch_log_id] = [BatchHistory].[dbo].[batchdetail].[batch_log_id]
left join [plantavail].[dbo].[train] on [plantavail].[dbo].[train].[train] = [batchhistory].[dbo].[batchidlog].[train_id]
where UnitOrConnection = \'' . $_GET['equip'] . '\' and datetime between \'' . $startDate . '\' and \'' . date('Y-m-d H:i:s', strtotime($endDate) + 1) . '\'
and Action_CD between 210 and 211
union
select UnitOrConnection, Action_CD, datetime, [oldBatchHistory].[dbo].[batchidlog].[batch_log_id], \'oldbatchhistory\', [oldBatchHistory].[dbo].[batchidlog].[campaign_id], [oldBatchHistory].[dbo].[batchidlog].[lot_id], [oldBatchHistory].[dbo].[batchidlog].[batch_id], [oldBatchHistory].[dbo].[batchidlog].[train_id], [oldBatchHistory].[dbo].[batchidlog].[recipe_id], [oldBatchHistory].[dbo].[batchidlog].[recipe_version], [plantavail].[dbo].[train].[departmentequipmentfk], [plantavail].[dbo].[train].[departmentfk]
from [oldBatchHistory].[dbo].[batchdetail]
join [oldBatchHistory].[dbo].[batchidlog] on [oldBatchHistory].[dbo].[batchidlog].[batch_log_id] = [oldBatchHistory].[dbo].[batchdetail].[batch_log_id]
left join [plantavail].[dbo].[train] on [plantavail].[dbo].[train].[train] = [oldbatchhistory].[dbo].[batchidlog].[train_id]
where UnitOrConnection = \'' . $_GET['equip'] . '\' and datetime between \'' . $startDate . '\' and \'' . date('Y-m-d H:i:s', strtotime($endDate) + 1) . '\'
and Action_CD between 210 and 211
order by DateTime, action_cd desc';
$dataEquip = odbc_exec($bConn, $queryEquip);
if (odbc_num_rows($dataEquip) > 0) {
	$sBatchUnit = '<div id="pielegend"></div><div id="unitpie"></div><div class="tablesorter-row-hider" id="unit-table-rows"></div><h3>Records <a data-text="Table Headers" data-id="unit" class="table-row-button" href="none.php"><span class="icon-list icon-hover-hint"></span></a></h3><table class="records" id="batch-unit"><thead><tr><th>Batch ID</th><th>Train</th><th>Recipe</th><th>Version</th><th>Start Date/Time</th><th>End Date/Time</th><th>Used</th><th>Unused</th></tr></thead><tbody>';
	$aEquip = array();
	$row = 0;
	while(odbc_fetch_row($dataEquip)) {
		if (!isset($tableStart)) {
			$tableStart = odbc_result($dataEquip, 3);
		};
		$tableEnd = odbc_result($dataEquip, 3);
		if (odbc_result($dataEquip, 2) == 210) {
			$aEquip[$row]['availend'] = odbc_result($dataEquip, 3);
			$row++;
			$aEquip[$row]['usedstart'] = odbc_result($dataEquip, 3);
			$aEquip[$row]['batchid'] = odbc_result($dataEquip, 4);
			$aEquip[$row]['batchdb'] = odbc_result($dataEquip, 5);
			$aEquip[$row]['train'] = odbc_result($dataEquip, 9);
			$aEquip[$row]['equipment'] = odbc_result($dataEquip, 12);
			$aEquip[$row]['department'] = odbc_result($dataEquip, 13);
			$aEquip[$row]['recipe'] = odbc_result($dataEquip, 10);
			$aEquip[$row]['version'] = odbc_result($dataEquip, 11);
			$aEquip[$row]['batchname'] = odbc_result($dataEquip, 6) . '/' . odbc_result($dataEquip, 7) . '/' . odbc_result($dataEquip, 8);
		} elseif (odbc_result($dataEquip, 2) == 211) {
			$aEquip[$row]['usedend'] = odbc_result($dataEquip, 3);
			$aEquip[$row]['availstart'] = odbc_result($dataEquip, 3);
		};
	};
	$dataDuration = array();
	$total['Used'] = 0;
	$total['Unused'] = 0;
	$total['Other'] = strtotime($tableEnd) - strtotime($tableStart);
	foreach ($aEquip as $key => $array) {
		if (isset($array['usedstart'])) {
			$start = substr($array['usedstart'], 0, -4);
		} else {
			$start = '';
		};
		if (isset($array['usedend'])) {
			$end = substr($array['usedend'], 0, -4);
		} else {
			$end = '';
		};
		if (isset($array['usedend']) && isset($array['usedstart'])) {
			$usedtime = strtotime($end) - strtotime($start);
			$total['Used'] += $usedtime;
		} else {
			$usedtime = 0;
		};
		if (isset($array['availstart'])) {
			$availstart = substr($array['availstart'], 0, -4);
		} else {
			$availstart = '';
		};
		if (isset($array['availend'])) {
			$availend = substr($array['availend'], 0, -4);
		} else {
			$availend = '';
		};
		if (isset($array['availend']) && isset($array['availstart'])) {
			$unusedtime = strtotime($availend) - strtotime($availstart);
			$total['Unused'] += $unusedtime;
		} else {
			$unusedtime = 0;
		};
		if ($key != 0) {
			if (($key + 1) % 2 == 0) {
				$sBatchUnit .= '<tr class="oddRow">';
			} else {
				$sBatchUnit .= '<tr class="evenRow">';
			};
			$equipmentUrl = '';
			if (isset($array['equipment'])) {
				$equipmentUrl = 'equipment=' . $array['equipment'] . '&';
			};
			$departmentURL = '';
			if (isset($array['department'])) {
				$departmentURL = 'department=' . $array['department'] . '&';
			};
			$sBatchUnit .= '<td><a href="batchsummary.php?' . $departmentURL . $equipmentUrl . 'batch=' . $array['batchid'] . '&dbname=' . $array['batchdb'] . '">' . $array['batchname'] . '</td><td>' . $array['train'] . '</td><td><a href="batch.php?' . $departmentURL . 'recipe=' . $array['recipe']  . '&startdate=' . $startDate . '&enddate=' . $endDate . '">' . $array['recipe'] . '</a></td><td>' . $array['version'] . '</td><td>' . $start . '</td><td>' . $end . '</td><td data-duration="' . $usedtime . '">' . fToTime($usedtime) . '</td><td data-duration="' . $unusedtime . '">' . fToTime($unusedtime) . '</td></tr>';
			$dataDuration['Used'][] = '[' . (strtotime($start) * 1000) . ', ' . $usedtime . ', "' . $array['batchname'] . ' ' . fToTime($usedtime) . '"]';
			$dataDuration['Unused'][] = '[' . (strtotime($end) * 1000) . ', ' . $unusedtime . ', "' . $array['batchname'] . ' ' . fToTime($unusedtime) . '"]';
		};
	};
	$sBatchUnit .= '</tbody>' . fTableFooter(['id' => 'batch-unit', 'cols' => 8, 'totals' => [[0 => "count", 6 => 'time-mean', 7 => 'time-mean'], [6 => 'time-sum', 7 => 'time-sum']]]) . '</table>';
	$total['Other'] = $total['Other'] - ($total['Used'] + $total['Unused']);
	if ($total['Other'] == 0) {
		unset($total['Other']);
	};
	$sDataDuration = '';
	$sep = '';
	foreach ($dataDuration as $key => $array) {
		$sDataDuration .= $sep . '{label:"' . $key . '", data:[' . implode(',', $array) . ']}';
		$sep = ', ';
	};
	$sData = '';
	$sep = '';
	foreach ($total as $label => $data) {
		$sData .= $sep . '{ label: "' . $label . '", data: ' . $data . '}';
		$sep = ', ';
	};
	$sBatchUnit .= '
	<script type="text/javascript">

	$(function() {
	
	fTableSorter({sorttable: "#batch-unit", 
			sortorder: [[4,0]],
			rowheaders: "unit",
			headers: {3 : { columnSelector: false },
					6 : { sorter: "duration" },
					7 : { sorter: "duration" }}
		});
			
	var data = [' . $sData . ']; 
			
	$.plot("#unitpie", data, {
		colors: trendcolors,
		series: {
			pie: {
				show: true,
				radius: 1,
				stroke: {
					width: 0
				},
				label: {
					show: true,
					radius: .6,
					formatter: labelFormatter,
					background: {
						opacity: .7,
						color: "#FFFFFF"
					}
				}
			}
		},
		grid: {
			hoverable: true
		},
		legend: {
			show: true,
			container:$("#pielegend"),
			labelFormatter: legendFormatter
		}
	});
	function legendFormatter(label, series) {
			return label + \' (\' + msToTime(series.data[0][1] * 1000) + \'mins)\';
	}
	function labelFormatter(label, series) {
			return "<div class=\'label\'>" + Math.round(series.percent) + "%</div>";
		}
	 $("#unitpie").bind("mouseout", function() {
		$("#tooltip").remove();
		$(this).data("previous-post", -1);
	});
	$("#unitpie").bind("plothover", function(event, pos, item) {
		if (item) {
			if ($(this).data("previous-post") != item.seriesIndex) {
				$(this).data("previous-post", item.seriesIndex);
			}
			$("#tooltip").remove();
			showTooltip(pos.pageX, pos.pageY, item.series.label + ": " + msToTime(item.series.data[0][1] * 1000) + "mins (" + Math.round(item.series.percent) + "%)");
		} else {
			$("#tooltip").remove();
			previousPost = $(this).data("previous-post", -1);
		}
	});
	
			
		});
		
	</script>';
$stdOut .= '<h2>Showing ' . $_GET['equip'] . ' usage between ' . $startDate . ' and ' . $endDate . '</h2>' . $sBatchUnit;
} else {
	$stdOut .= '<h2>No data availible</h2>';
};
$hookReplace['help'] = $helptext['standardcal'] . '<a href="#">Context Sensitive "Briefcase" Menu</a><div>Click the context sensitive menu to view Equipment in the selected Department.</div>' .  $helptext['piehover'] . $helptext['default30'] . $helptext['tablesorter'] . $helptext['recordsetcolumns'] ;
odbc_close($bConn);
require_once 'includes/footer.php'; ?>