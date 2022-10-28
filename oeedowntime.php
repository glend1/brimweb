<?PHP 
$title = 'OEE Downtime Analysis';
require_once 'includes/header.php';
if (!fCanSeePublic(@$_SESSION['permissions']['page'][1] >= 100)) {
	$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
$hookReplace['calicon'] = '<a data-text="Date/Time Picker" href="#" class="menucontext"><span class="icon-calendar-empty icon-hover-hint icon-large"></span></a>';
$hookReplace['calform'] = fStandardCal('oeedowntime', '[2014, 01, 27]');
fSetDates($startDate, $endDate, 7);
$totDur = strtotime($endDate) - strtotime($startDate);
if (isset($_GET['step'])) {
	switch ($_GET['step']) {
		case 'area';
			$step = 'area';
			$nextStep = 'discipline';
			$queryStep = 'area.name as step,';
			$queryStepSelect = 'step as area,';
			$queryStepName = 'step,';
			break;
		case 'discipline';
			$step = 'discipline';
			$nextStep = 'oeename';
			$queryStep = 'discipline.name as step,';
			$queryStepSelect = 'step as discipline,';
			$queryStepName = 'step,';
			break;
		case 'oeename';
			$step = 'oeename';
			$nextStep = NULL;
			$queryStep = 'oeename.name as step,';
			$queryStepSelect = 'step as oeename,';
			$queryStepName = 'step,';
			break;
		default:
			$step = NULL;
			$nextStep = 'area';
			$queryStep = '';
			$queryStepSelect = '';
			$queryStepName = '';
			break;
	};
} else {
	$step = NULL;
	$nextStep = 'area';
	$queryStep = '';
	$queryStepSelect = '';
	$queryStepName = '';
};
if (isset($_GET['top'])) {
	$top = $_GET['top'];
} else {
	$top = 20;
};
if (isset($_GET['mode'])) {
	if ($_GET['mode'] == 'equipment') {
		$equipment = 'checked';
		$mode = 'departmentequipment';
		$modeCal = 'equipment';
	} else {
		$equipment = '';
	};
	if ($_GET['mode'] == 'department') {
		$department = 'checked';
		$mode = 'department';
		$modeCal = 'department';
	} else {
		$department = '';
	};
} else {
	$equipment = '';
	$department = 'checked';
	$mode = 'department';
	$modeCal = 'department';
};
if (isset($_GET['departmentequipment']) || isset($_GET['department'])) {
	$modeId = $_GET[$mode];
};
if ($step == NULL) {
	$thisId = $mode;
} else {
	$thisId = $step;
};
if (isset($_GET['groupcategory'])) {
	$groupcategory = 'checked';
	$queryCategory = 'oeecategoryfk,';
} else {
	$groupcategory = '';
	$queryCategory = '';
};
if (isset($_GET['grouptime'])) {
	$groupTime = 'checked';
} else {
	$groupTime = '';
};
if ($totDur > 31536000 && isset($_GET['grouptime'])) {
	$queryTime = 'RIGHT(\'0000\' + cast(datepart(YYYY, cte.startdatetime) as varchar(4)), 4) as dategroup, datediff(ss, 
		case when cte.startdatetime < \'' . $startDate . '\' then \'' . $startDate . '\' else cte.startdatetime end,
		case when cte.enddatetime > \'' . $endDate . '\' then \'' . $endDate . '\' else cte.enddatetime end) as dur,';
	$queryDateGroup = 'dategroup,';
	$queryCTE = 'WITH cte AS
    (
        SELECT Id, startDateTime, 

            CASE 
                WHEN cast(datepart(YYYY, startdatetime) as char(4)) = cast(datepart(YYYY, EndDateTime) as char(4)) THEN endDateTime
                ELSE dateadd(YYYY, 1, cast(datepart(YYYY, startdatetime) as char(4)) + \'-01-01\')
            END AS endDateTime,     

            endDateTime AS finishDateTime

        FROM records

        UNION ALL

        SELECT Id, endDateTime, 

            CASE WHEN DATEADD( year, 1, endDateTime ) > finishDateTime THEN finishDateTime
                ELSE DATEADD( year, 1, endDateTime )
            END, 

            finishDatetime

        FROM cte
        WHERE endDateTime < finishDatetime
    )';
} elseif ($totDur > 2678400 && isset($_GET['grouptime'])) {
	$queryTime = 'RIGHT(\'0000\' + cast(datepart(YYYY, cte.startdatetime) as varchar(4)), 4) + \'-\' + RIGHT(\'00\' + cast(datepart(MM, cte.startdatetime) as varchar(2)), 2) as dategroup, datediff(ss, 
		case when cte.startdatetime < \'' . $startDate . '\' then \'' . $startDate . '\' else cte.startdatetime end,
		case when cte.enddatetime > \'' . $endDate . '\' then \'' . $endDate . '\' else cte.enddatetime end) as dur,';
	$queryDateGroup = 'dategroup,';
	$queryCTE = 'WITH cte AS
    (
        SELECT Id, startDateTime,  
		     
            CASE 
                WHEN cast(datepart(YYYY, startdatetime) as char(4)) + \'-\' + cast(datepart(MM, startdatetime) as char(2)) = cast(datepart(YYYY, EndDateTime) as char(4)) + \'-\' + cast(datepart(MM, EndDateTime) as char(2)) THEN endDateTime
                ELSE dateadd(MM, 1, cast(datepart(YYYY, startdatetime) as char(4)) + \'-\' + cast(datepart(MM, startdatetime) as char(2)) + \'-01\')
            END AS endDateTime,

            endDateTime AS finishDateTime

        FROM records

        UNION ALL

        SELECT Id, endDateTime,

            CASE WHEN DATEADD( month, 1, endDateTime ) > finishDateTime THEN finishDateTime
                ELSE DATEADD( month, 1, endDateTime )
            END, 

            finishDatetime

        FROM cte
        WHERE endDateTime < finishDatetime
    )';
} elseif ($totDur > 86400 && isset($_GET['grouptime'])) {
	$queryTime = 'RIGHT(\'0000\' + cast(datepart(YYYY, cte.startdatetime) as varchar(4)), 4) + \'-\' + RIGHT(\'00\' + cast(datepart(MM, cte.startdatetime) as varchar(2)), 2) + \'-\' + RIGHT(\'00\' + cast(datepart(DD, cte.startdatetime) as varchar(2)), 2) as dategroup, datediff(ss, 
		case when cte.startdatetime < \'' . $startDate . '\' then \'' . $startDate . '\' else cte.startdatetime end,
		case when cte.enddatetime > \'' . $endDate . '\' then \'' . $endDate . '\' else cte.enddatetime end) as dur,';
	$queryDateGroup = 'dategroup,';
	$queryCTE = 'WITH cte AS
    (
        SELECT Id, startDateTime,
		
            CASE 
                WHEN cast(datepart(YYYY, startdatetime) as char(4)) + \'-\' + cast(datepart(MM, startdatetime) as char(2)) + \'-\' + cast(datepart(DD, startdatetime) as char(2)) = cast(datepart(YYYY, enddatetime) as char(4)) + \'-\' + cast(datepart(MM, enddatetime) as char(2)) + \'-\' + cast(datepart(DD, enddatetime) as char(2)) THEN endDateTime
				ELSE dateadd(DD, 1, cast(datepart(YYYY, startdatetime) as char(4)) + \'-\' + cast(datepart(MM, startdatetime) as char(2)) + \'-\' + cast(datepart(DD, startdatetime) as char(2)))
            END AS endDateTime,

            endDateTime AS finishDateTime

        FROM records

        UNION ALL

        SELECT Id, endDateTime, 

            CASE WHEN DATEADD( day, 1, endDateTime ) > finishDateTime THEN finishDateTime
                ELSE DATEADD( day, 1, endDateTime )
            END,

            finishDatetime

        FROM cte
        WHERE endDateTime < finishDatetime
    )';
} else {
	$queryTime = ' datediff(ss, 
		case when records.startdatetime < \'' . $startDate . '\' then \'' . $startDate . '\' else records.startdatetime end,
		case when records.enddatetime > \'' . $endDate . '\' then \'' . $endDate . '\' else records.enddatetime end) as dur,';
	$queryDateGroup = '';
	$queryCTE = '';
};
$hookReplace['contexticon'] = '<a href="#" data-text="Context Sensitive Menu"  class="menucontext"><span class="icon-briefcase icon-hover-hint icon-large"></span></a>';
$stdOut .= '<form id="subnav" action="oeedowntime.php" method="get">';
if ($step != NULL) {
	$stdOut .= '<div>Calendar</div><ul><li><a href="oeecalendar.php?mode=' . $modeCal . '&id=' . $modeId . '">View Calendar</a></li></ul>';
};
if ($step == NULL) {
	$stdOut .= '<div>View:</div>
	<ul><li><input type="radio" name="mode" id="radioequipment" ' . $equipment . ' value="equipment"/><label for="radioequipment">View as Equipment</label></li><li>
	<input type="radio" name="mode" id="radiodepartment" ' . $department . ' value="department"/><label for="radiodepartment">View as Department</label></li></ul>';
};
$stdOut .= '<div>Group:</div>
<ul><li><input type="checkbox" name="groupcategory" id="groupcategory" value="true" ' . $groupcategory . ' /><label for="groupcategory">Group by Category</label></li>';
if ($totDur > 86400) {
	$stdOut .= '<li><input type="checkbox" name="grouptime" id="grouptime" value="true" ' . $groupTime . ' /><label for="grouptime">Group by Time</label></li>';
};
$stdOut .= '</ul><div>Pareto:</div>
<ul><li><label for="top">Top:</label> <input id="top" type="text" name="top" value="' . $top . '"</li>
</ul>' . fQueryString(['exclude' => ['grouptime', 'groupcategory', 'mode', 'top'], 'output' => 'hidden']) . '
<input type="submit" value="Go!" />
</form>';
switch ($thisId) {
	case 'departmentequipment':
		$oeeType = 'Equipment';
		break;
	case 'oeename':
		$oeeType = 'Reason';
		break;
	default:
		$oeeType = ucwords($thisId);
		break;
};
$tempStore = '<h2>Showing Downtime (Top ' . $top . ') between ' . $startDate . ' and ' . $endDate . '</h2><div id="bar"></div><h3>' . $oeeType . '</h3><div id="legend"></div>';
if (isset($_GET['area'])) {
	$aQueryDetail[] = '(select top 1 name as area from area where id = ' . $_GET['area'] . ') as area';
};
if (isset($_GET['discipline'])) {
	$aQueryDetail[] = '(select top 1 name as discipline from discipline where id = ' . $_GET['discipline'] . ') as disc';
};
if (isset($aQueryDetail)) {
	$queryDetail = 'select * from ' . implode(', ', $aQueryDetail);
	$dataDetail = odbc_exec($conn, $queryDetail);
	for ($l=1; $l<=odbc_num_fields($dataDetail); $l++) {
		$aHeadersDetail[odbc_field_name($dataDetail, $l)] = $l;
	};
	$tempStore .= '<h3>Detail</h3><table class="records"><thead><tr>';
	foreach ($aHeadersDetail as $key => $value) {
		$tempStore .= '<th>' . ucwords($key) . '</th>';
	};
	$tempStore .= '</tr></thead><tbody>';
	$iRowDetail = 0;
	while(odbc_fetch_row($dataDetail)) {
		if ($iRowDetail % 2 == 0) {
			$rowHeader = 'oddRow';
		} else {
			$rowHeader = 'evenRow';
		};
		$iRowDetail++;
		$tempStore .= '<tr class="' . $rowHeader . '">';
		foreach ($aHeadersDetail as $key => $value) {
			$tempStore .= '<td><a href="oeedowntime.php' . fQueryString(['exclude' => [$key]]) . '"><span class="icon-remove"></span></a> ' .  odbc_result($dataDetail, $aHeadersDetail[$key]) . '</td>';
		};
		$tempStore .= '</tr>';
	};
	$tempStore .= '</tbody></table>';
};
$queryCategoryLookup = 'select id, name from oeecategory where id = 1 or id = 8';
$dataCategoryLookup = odbc_exec($conn, $queryCategoryLookup);
while(odbc_fetch_row($dataCategoryLookup)) {
	$aCategoryLookup[odbc_result($dataCategoryLookup, 1)] = odbc_result($dataCategoryLookup, 2);
};
$queryOeeDowntime = $queryCTE . 'select ' . $queryStepSelect . ' mode as ' . $mode . ', ' . $queryDateGroup . ' ' . $queryCategory . ' sum(dur) as dur, id
from (select ' . $queryTime . ' ' . $queryStep . ' ' . $queryCategory . ' ' . $mode . '.name as mode, ' . $thisId . '.id as id
from records
join type on typefk = type.ID
join DepartmentEquipment on DepartmentEquipmentFK = DepartmentEquipment.id ';
if ($mode == 'department') {
	$queryOeeDowntime .= 'join Department on DepartmentFK = Department.id ';
};
if ($step == 'area' || $step == 'oeename') {
	$queryOeeDowntime .= 'join discipline on disciplinefk = discipline.id ';	
};
if ($step == 'oeename') {
	$queryOeeDowntime .= 'join area on areafk = area.id ';	
};
if ($step != NULL) {
	$queryOeeDowntime .= 'join ' . $step . ' on ' . $step . 'FK = ' . $step . '.id ';
};
if (isset($_GET['grouptime']) && $totDur > 86400) {
	$queryOeeDowntime .= 'join cte on cte.id = records.id ';
};
$queryOeeDowntime .= 'where (OEECategoryFK = 1 or OEECategoryFK = 8) ';
if (isset($_GET['department'])) {
	$queryOeeDowntime .= 'and departmentfk = ' . $_GET['department'] . ' ';
};
if (isset($_GET['departmentequipment'])) {
	$queryOeeDowntime .= 'and departmentequipmentfk = ' . $_GET['departmentequipment'] . ' ';
};
if (isset($_GET['area'])) {
	$queryOeeDowntime .= 'and areafk = ' . $_GET['area'] . ' ';
};
if (isset($_GET['discipline'])) {
	$queryOeeDowntime .= 'and disciplinefk = ' . $_GET['discipline'] . ' ';
};
if (isset($_GET['oeename'])) {
	$queryOeeDowntime .= 'and oeenamefk = ' . $_GET['oeename'] . ' ';
};
$queryOeeDowntime .= 'and ((records.StartDateTime between \'' . $startDate . '\' and \'' . date('Y-m-d H:i:s', strtotime($endDate) + 1) . '\' or records.enddatetime between \'' . $startDate . '\' and \'' . date('Y-m-d H:i:s', strtotime($endDate) + 1) . '\') or ((records.StartDateTime < \'' . $startDate . '\' and records.StartDateTime < \'' . date('Y-m-d H:i:s', strtotime($endDate) + 1) . '\') and (records.enddatetime > \'' . $startDate . '\' and records.enddatetime > \'' . date('Y-m-d H:i:s', strtotime($endDate) + 1) . '\')))';
if (@$_SESSION['id'] != 1 && isset($_SESSION['id'])) {
	$queryOeeDowntime .= ' and ' . fOrThemReturn($_SESSION['permissions']['department'], 100, 'departmentfk');
};
$queryOeeDowntime .= ') as temp1 ';
if (isset($_GET['grouptime']) && $totDur > 86400) {
	$queryOeeDowntime .= 'where dategroup between \'' . substr($startDate, 0, 10) . '\' and \'' . substr($endDate, 0, 10) . '\' ';
};
$queryOeeDowntime .= 'group by ' . $queryDateGroup . ' ' . $queryStepName . ' ' . $queryCategory . ' mode, id
order by ' . $queryDateGroup . ' dur desc, ' . $queryStepName . ' ' . $queryCategory . ' mode';
//print('<pre>' . $queryOeeDowntime . '</pre>');
$dataOeeDowntime = odbc_exec($conn, $queryOeeDowntime);
for ($j=1; $j<=odbc_num_fields($dataOeeDowntime); $j++) {
	$aHeaders[odbc_field_name($dataOeeDowntime, $j)] = $j;
};
$tempStore .= '<h3>Records <a data-text="Table Headers" data-id="oee" class="table-row-button" href="none.php"><span class="icon-list icon-hover-hint"></span></a></h3><div class="tablesorter-row-hider" id="oee-table-rows"></div><table class="records" id="oee-sort"><thead><tr>';
$cols = 0;
foreach ($aHeaders as $key => $value) {
	switch ($key) {
		case 'departmentequipment':
			$tempStore .= '<th>Equipment</th>';
			$cols++;
			break;
		case 'department':
			$tempStore .= '<th>Department</th>';
			$cols++;
			break;
		case 'dategroup':
			$tempStore .= '<th>Date</th>';
			$datePos = $cols;
			$cols++;
			break;
		case 'oeecategoryfk':
			$categoryPos = $cols;
			$tempStore .= '<th>Category</th>';
			$cols++;
			break;
		case 'dur':
			$durPos = $cols;
			$tempStore .= '<th>Duration</th>';
			$cols++;
			break;
		case 'id':
			break;
		case 'oeename':
			$tempStore .= '<th>Reason</th>';
			$cols++;
			break;
		default:
			$tempStore .= '<th>' . ucwords($key) . '</th>';
			$cols++;
			break;
	};
};
$tempStore .= '</tr></thead><tbody>';
$iRowNo = 0;
$modeCounter = 0;
$dateCounter = 0;
$axisCounter = 0;
$aDate = array('' => $dateCounter);
$aMode = array('' => $modeCounter);
$aAxis = array('' => $axisCounter);
while(odbc_fetch_row($dataOeeDowntime)) {
	$dataFound = true;
	if ($iRowNo % 2 == 0) {
		$rowHeader = 'oddRow';
	} else {
		$rowHeader = 'evenRow';
	};
	$iRowNo++;
	$tempStore .= '<tr class="' . $rowHeader . '">';
	$dateGroupKey = NULL;
	$categoryKey = NULL;
	$durValue = NULL;
	$modeKey = NULL;
	$axisKey = NULL;
	$LinkText = NULL;
	$cells = array();
	foreach ($aHeaders as $key => $value) {
		switch ($key) {
			case 'dategroup':
				$cells['dategroup'] = '<td>' . odbc_result($dataOeeDowntime, $aHeaders[$key]) . '</td>';
				if (!isset($aDate[odbc_result($dataOeeDowntime, $aHeaders[$key])])) {
					$aDate[odbc_result($dataOeeDowntime, $aHeaders[$key])] = ++$dateCounter;
				};
				$dateGroupKey = odbc_result($dataOeeDowntime, $aHeaders[$key]);
				break;
			case 'oeecategoryfk':
				$cells['category'] = '<td>' . $aCategoryLookup[odbc_result($dataOeeDowntime, $aHeaders[$key])] . '</td>';
				$categoryKey = $aCategoryLookup[odbc_result($dataOeeDowntime, $aHeaders[$key])];
				break;
			case 'dur':
				$cells['dur'] = '<td data-duration="';
				if (odbc_result($dataOeeDowntime, $aHeaders[$key]) == "") {
					$cells['dur'] .= 0;
				} else {
					$cells['dur'] .= odbc_result($dataOeeDowntime, $aHeaders[$key]);
				};
				$cells['dur'] .= '">' . fToTime(odbc_result($dataOeeDowntime, $aHeaders[$key])) . '</td>';
				$durValue = odbc_result($dataOeeDowntime, $aHeaders[$key]) * 1000;
				break;
			case 'id':
				$iId = odbc_result($dataOeeDowntime, $aHeaders[$key]);
				if (!isset($aAxis[$iId])) {
					$aAxis[$iId] = ++$axisCounter;
				};
				break;
			case 'departmentequipment':
			case 'department':
				if (!isset($aMode[odbc_result($dataOeeDowntime, $aHeaders[$key])])) {
					$aMode[odbc_result($dataOeeDowntime, $aHeaders[$key])] = ++$modeCounter;
				};
				$modeKey = odbc_result($dataOeeDowntime, $aHeaders[$key]);
			case 'area';
			case 'discipline';
			case 'oeename';
				if (($step == NULL && ($key == 'departmentequipment' || $key == 'department')) || ($step != NULL && ($key == 'area' || $key == 'discipline' || $key == 'oeename'))) {
					$axisKey = odbc_result($dataOeeDowntime, $aHeaders[$key]);
					$cells['step'] = ucwords(odbc_result($dataOeeDowntime, $aHeaders[$key]));
				} else {
					$cells['mode'] = '<td>' . ucwords(odbc_result($dataOeeDowntime, $aHeaders[$key])) . '</td>';
				};
				break;
			default:
				$cells[] = '<td>' . ucwords(odbc_result($dataOeeDowntime, $aHeaders[$key])) . '</td>';
				break;
		};
	};
	if ($dateGroupKey != '') {
		$dGroupEnd = new DateTime($dateGroupKey);
		$dGroupStart = $dGroupEnd->format('Y-m-d H:i:s');
		switch (strlen($dateGroupKey)) {
			case 4:
				$dGroupEnd->add(new DateInterval('P1Y'));
				break;
			case 7:
				$dGroupEnd->add(new DateInterval('P1M'));
				break;
			case 10:
				$dGroupEnd->add(new DateInterval('P1D'));
				break;
			default:
				break;
		};
		$dGroupEnd = $dGroupEnd->format('Y-m-d H:i:s');
		if ($dGroupStart < $startDate) {
			$dGroupStart = $startDate;
		};
		if ($dGroupEnd > $endDate) {
			$dGroupEnd = $endDate;
		};
	} else {
		$dGroupStart = $startDate;
		$dGroupEnd = $endDate;
	};
	$link = '';
	if ($cells['step'] && $nextStep == NULL) {
		$cells['step'] = '<td data-checkbox="' . trim($cells['step'] . ' ' . $categoryKey) . '"><a data-text="Compare Similar" href="oeecompare.php' . fQueryString(['include' => ['id' =>  $iId], 'exclusive' => ['startdate', 'enddate']]) . '"><span class="icon-link icon-hover-hint"></span></a> ' . $cells['step'] . '</td>';
	} elseif ($cells['step']) {
		$link = 'oeedowntime.php' . fQueryString(['include' => [$thisId => $iId, 'startdate' => $dGroupStart, 'enddate' => $dGroupEnd, 'step' => $nextStep]]);
		$cells['step'] = '<td data-checkbox="' . trim($cells['step'] . ' ' . $categoryKey) . '"><a href="' . $link . '">' . $cells['step'] . '</a></td>';
	};
	$tempStore .= implode('', $cells) . '</tr>';
	if (!isset($lookupTable[$iId])) {
		$lookupTable[$iId] = $axisKey;
	};
	if (count($lookupTable) <= $top) {
		$aTable[$iId][$categoryKey][$dateGroupKey][] = [$durValue, $link];
	};
};
$sideBySide = count($aAxis) - 1;
if (count($aAxis) > 1) {
	$aDates = array();
	$dateCounter = 0;
	$jData = '';
	$jDataSep = '';
	if (isset($aTable)) {
		if (!empty($aTable)) {
			foreach ($aTable as $pAxisKey => $categoryArray) {
				if (!empty($categoryArray)) {
					foreach ($categoryArray as $categoryKey => $dateGroupArray) {
						$jData .= $jDataSep . '{label: "' . $lookupTable[$pAxisKey] . ' ' . $categoryKey . '", bars: {order:' . $aAxis[$pAxisKey] . '}, data:[';
						if (!empty($dateGroupArray)) {
							$dataSep = '';
							foreach ($dateGroupArray as $dateGroupKey => $durArray) {
								foreach ($durArray as $dataKey => $dataArray) {
									$jData .= $dataSep . '[' . $dataArray[0] . ', ' . $aDate[$dateGroupKey] . ', 0, 0, "' . $dateGroupKey . ' ' . $lookupTable[$pAxisKey] . ' ' . $categoryKey . '", "' . $dataArray[1] . '"]';
									$dataSep = ', ';
								};
							};
						};
						$jData .= ']}';
						$jDataSep = ', ';
					};
				};
			};
		};
	};
	$jTicks = '';
	$tickSep = '';
	foreach ($aDate as $key => $val) {
		if ($key != NULL) {
			$jTicks .= $tickSep . '[' . $val . ', "' . $key . '"]';
			$tickSep = ', ';
		};
	};
	$tempStore .= '</tbody>' . fTableFooter(['id' => 'oee-sort', 'cols' => $cols]) . '</table>
	<script language="javascript">
		
		var searchArray = new Array();
		$(function() {
			fTableSorter({sorttable: "#oee-sort", 
				sortorder: [';
	if (isset($datePos)) {
				$tempStore .= '[' . $datePos . ', 0],[' . $durPos . ',1]';
	} else {
				$tempStore .= '[' . $durPos . ',1]';
	};
				$tempStore .= '],
				rowheaders: "oee",
				headers: {0 : { sorter: "checkbox", filter: "parsed" },
					' . $durPos . ': { sorter: "duration" }}
			});
		
		var data = [ ' . $jData . '	];
		var options = {
			colors: trendcolors,
			series: {
				stack: true
			},
			yaxis: {
				axisLabel: "Date",
				ticks: [' . $jTicks . ']
			},
			xaxis: {
				axisLabel: "Duration",
				ticks: durTicks
			},
			grid: {
				hoverable: true,
				clickable: true
			},
			bars:{
				show: true,
				barWidth: ' . ((1 / $sideBySide) - ((1 / $sideBySide) / 2)) . ',
				horizontal:true
			},
			legend: {
				noColumns: 5,
				container:$("#legend"),
				labelFormatter: function(label, series) { 
					//series.data 
					var out = "";
					if (series.data[0][5] != "") {
						out += \'<a href="\' + series.data[0][5] + \'">\';
					};
					out += label;
					if (series.data[0][5] != "") {
						out += \'</a>\'
					};
					return out;
				}
			}
		};
		
		var plot = $.plot($("#bar"), data, options);	
		options.legend.show = false;
		options.legend.container = false;
		

		$("#bar").bind("mouseout", function() {
			$("#tooltip").remove();
			$(this).data("previous-post", -1);
		});
		$("#bar").bind("plotclick", function(event,pos,item) {
				if (item) {
					if (item.series.data[item.dataIndex][5]) {
						window.location.href = item.series.data[item.dataIndex][5];
					};
				};
			});
		$("#bar").bind("plothover", function(event, pos, item) {
			if (item) {
				if ($(this).data("previous-post") != item.seriesIndex) {
					$(this).data("previous-post", item.seriesIndex);
				}
				$("#tooltip").remove();
				showTooltip(pos.pageX, pos.pageY, item.series.data[item.dataIndex][4] + " (" + msToTime(item.series.data[item.dataIndex][0]) + ")");
			} else {
				$("#tooltip").remove();
				previousPost = $(this).data("previous-post", -1);
			}
		});

		var i = 0;
		$.each(data, function(key, val) {
			val.color = i;
			++i;
		});
		// insert checkboxes 
			var choiceContainer = $("#legend");
			var legendChoiceContainer = choiceContainer.find(".legendColorBox");
			$.each(data, function(key, val) {
				iText = "<td><input type=\'checkbox\' name=\'" + key + "\' checked=\'checked\' id=\'id" + key + "\'></input></td>";
				$(legendChoiceContainer.eq(key)).before(iText);
			});
			choiceContainer.find("input").click(plotAccordingToChoices);
			function plotAccordingToChoices() {
				var newData = [];
				var labelcheckbox = new Array();
				choiceContainer.find("input:checked").each(function () {
					var key = $(this).attr("name");
					if (key && data[key]) {
						newData.push(data[key]);
						labelcheckbox.push("^" + data[key].label.trim().replace("\/", "\\\/") + "$");
					}
				});
				if (newData.length > 0) {
					searchArray[0] = "/" + labelcheckbox.join("|") + "/i";
					$("#oee-sort").trigger(\'search\', [searchArray]);
					$.plot($("#bar"),newData,options);
				}
			};
		});
		</script>';
};
if (isset($dataFound)) {
	$stdOut .= $tempStore;
} else {
	$stdOut .= '<h2>No data available</h2>';
};
$hookReplace['help'] = '<a href="#">Basic Usage</a><div>Clicking an item in the graph, legend or record table will reload the page showing you the next level of detail, this will adjust itself to the necessary timescale if required. The order of detail is;<ul><li>Department/Equipment</li><li>Area</li><li>Discipline</li><li>Reason</li></ul></div><a href="#">Group by Category</a><div>This option divides the original bar data into two distinct groups</div><a href="#">Group by Time</a><div>This option divides the data into different date ranges. Each date group will stack on top of each other, data will be shown in the same order as the other groups because of this behavior sorting the data by duration within the graph is unrelable but still attempted.</div><a href="#">Mode Toggle</a><div>This option allows you to choose whether to view the data as either Equipment or Department. This option is only available during the first step</div><a href="#">Calendar View</a><div>This option allows you to choose a range of predefined dates from a simple calendar interface using this option will show only final downtime reasons. This option is available on any step except the initial step</div><a href="#">Data Filters</a><div>Once you have selected an area a Detail table will appear, clicking the cross in the one of the tables cell will remove that filter from the selection.</div>' . $helptext['barhover'] . $helptext['graphtoggle'] . '<a href="#">OEE Comparison</a><div>Clicking the link icon in the Reason column of the records will take you to the OEE comparison page for the selected reason</div>' . $helptext['default7'] . $helptext['tablesorter'] . $helptext['recordsetcolumns'] . $helptext['graphtoggle'] . $helptext['tablesorterflot'];
require_once 'includes/footer.php'; ?>