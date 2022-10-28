<?PHP 
$title = 'Batch Viewer';
require_once 'includes/header.php';
if (!fCanSeePublic(@$_SESSION['permissions']['page'][4] >= 100)) {
	$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
$aDE = fPermissionDE();
$departmentURL = '';
if (isset($aDE['department'])) {
	$departmentURL = 'department=' . $aDE['department'] . '&';
};
$equipmentUrl = '';
if (isset($aDE['equipment'])) {
	$equipmentUrl = 'equipment=' . $aDE['equipment'] . '&';
};
if (!isset($_GET['batch']) && !isset($_GET['dbname'])) {
	$_SESSION['sqlMessage'] = 'Select a Batch!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
if (isset($_GET['mode'])) {
	$mode = $_GET['mode'];
} else {
	$mode = 'phase';
};
$bConn = odbc_connect('DRIVER={SQL Server};Server=INSQL2;Database=' . $_GET['dbname'] . ';', $dbUsername, $dbPassword);
function fBatchMode ($query, $start, $end) {
	GLOBAL $bConn;
	GLOBAL $aDE;
	GLOBAL $departmentURL;
	GLOBAL $equipmentUrl;
	$dataBatchMode = odbc_exec($bConn, $query);
	$aProcessedData = array();
	$aNameCurrent = array();
	$counter = 1;
	$i = 1;
	while(odbc_fetch_row($dataBatchMode)) {
		if (!isset($aNameCurrent[odbc_result($dataBatchMode, 2)])) {
			$aNameCurrent[odbc_result($dataBatchMode, 2)] = 1;
		};
		if (!isset($aProcessedData[$aNameCurrent[odbc_result($dataBatchMode, 2)]]['name'])) {
			$aProcessedData[$aNameCurrent[odbc_result($dataBatchMode, 2)]]['name'] = odbc_result($dataBatchMode, 2);
		};
		if (odbc_result($dataBatchMode, 3) == $start) {
			if (isset($aProcessedData[$aNameCurrent[odbc_result($dataBatchMode, 2)]]['start'])) {
				$i++;
				$aNameCurrent[odbc_result($dataBatchMode, 2)] = $i;
				$aProcessedData[$aNameCurrent[odbc_result($dataBatchMode, 2)]]['name'] = odbc_result($dataBatchMode, 2);
			};
			$aProcessedData[$aNameCurrent[odbc_result($dataBatchMode, 2)]]['start'] = odbc_result($dataBatchMode, 1);
		};
		if (odbc_result($dataBatchMode, 3) == $end) {
			if (isset($aProcessedData[$aNameCurrent[odbc_result($dataBatchMode, 2)]]['end'])) {
				$i++;
				$aNameCurrent[odbc_result($dataBatchMode, 2)] = $i;
				$aProcessedData[$aNameCurrent[odbc_result($dataBatchMode, 2)]]['name'] = odbc_result($dataBatchMode, 2);
			};
			$aProcessedData[$aNameCurrent[odbc_result($dataBatchMode, 2)]]['end'] = odbc_result($dataBatchMode, 1);
		};
		if (isset($aProcessedData[$aNameCurrent[odbc_result($dataBatchMode, 2)]]['start']) && isset($aProcessedData[$aNameCurrent[odbc_result($dataBatchMode, 2)]]['end'])) {
			$aProcessedData[$aNameCurrent[odbc_result($dataBatchMode, 2)]]['duration'] = strtotime($aProcessedData[$aNameCurrent[odbc_result($dataBatchMode, 2)]]['end']) - strtotime($aProcessedData[$aNameCurrent[odbc_result($dataBatchMode, 2)]]['start']);
			$aProcessedData[$aNameCurrent[odbc_result($dataBatchMode, 2)]]['link'] = 'phase.php?' . $departmentURL . $equipmentUrl . 'startdate=' . substr($aProcessedData[$aNameCurrent[odbc_result($dataBatchMode, 2)]]['start'], 0, -4) . '&enddate=' . substr($aProcessedData[$aNameCurrent[odbc_result($dataBatchMode, 2)]]['end'], 0, -4) . '&name=' . odbc_result($dataBatchMode, 2) . '&mode=' . $_GET['mode'] . '&dbname=' . $_GET['dbname'] . '&batch=' . $_GET['batch'];
		};
		$endCounter = strtotime(odbc_result($dataBatchMode, 1)) * 1000;
	};
	$out = fRecordSwap(['rename' => 'Batch Detail', 'table' => true, 'exclude' => ['batch']]) . '<div class="tablesorter-row-hider" id="record-table-rows"></div><table class="records" id="batch-sort"><thead><tr><th>Name</th><th>Start Date/Time</th><th>End Date/Time</th><th>Duration</th></tr></thead><tbody>';
	$j = 0;
	foreach ($aProcessedData as $iRowNo => $array) {
		if ($j % 2 == 0) {
			$out .= '<tr class="oddRow">';
		} else {
			$out .= '<tr class="evenRow">';
		};
		$j++;
		$out .= '<td>';
		if (isset($array['name'])) {
			$out .= '<a href="' . $array['link'] . '">' . $array['name'] . '</a>';
		};
		$out .= '</td><td>';
		if (isset($array['start'])) {
			$out .= substr($array['start'], 0, -4);
			if (!isset($aGanttY)) {
				$aGanttY[1] = strtotime($array['start']) * 1000;
			};
		};
		$out .= '</td><td>';
		if (isset($array['end'])) {
			$out .= substr($array['end'], 0, -4);
		};
		$out .= '</td>';
		if (isset($array['duration'])) {
			$startTime = strtotime($array['start']) * 1000;
			$endTime = strtotime($array['end']) * 1000;
			$out .= '<td data-duration="' . $array['duration'] . '">' . fToTime($array['duration']) . '</td>';
			if (isset($aGanttY)) {
				$bGantt = true;
				foreach ($aGanttY as $pos => $datetime) {
					if ($startTime >= $datetime) {
						$aGanttY[$pos] = $endTime;
						$array['position'] = $pos;
						$bGantt = false;
						break;
					};
				};
				if ($bGantt) {
					$aGanttY[count($aGanttY) + 1] = $endTime;
					$array['position'] = count($aGanttY);
				};
			};
		} else {
			$out .= '<td data-duration="0"></td>';
		};
		$out .= '</td></tr>';
		if (isset($array['position'])) {
			$aGantt[$array['name']][] = '[' . $startTime . ',' . (12 - $array['position']) . ',' . $endTime . ',"' . $array['name'] . '", "' . $array['link'] . '"]';
		};
	};
	$out .= '</tbody>' . fTableFooter(['id' => 'batch-sort', 'cols' => 4, 'totals' => [[0 => "count"]]]) . '</table>';
	return [$out, $aGantt, $endCounter];
};
$hookReplace['contexticon'] = '<a href="#" data-text="Context Sensitive Menu"  class="menucontext"><span class="icon-briefcase icon-hover-hint icon-large"></span></a>';
$sBatchNav = '<div id="subnav"><div>Raw Data:</div><ul><li><a href="batchfull.php?' . $departmentURL . 'batch=' . $_GET['batch'] . '&dbname=' . $_GET['dbname'] . '">Raw Batch Information</a></li></ul><div>Mode:</div><ul><li><a href="batchsummary.php?' . $departmentURL . 'batch=' . $_GET['batch'] . '&dbname=' . $_GET['dbname'] . '&mode=complete">Complete Mode</a></li>
<li><a href="batchsummary.php?' . $departmentURL . 'batch=' . $_GET['batch'] . '&dbname=' . $_GET['dbname'] . '&mode=phase">Phase Mode</a></li><li><a href="batchsummary.php?' . $departmentURL . 'batch=' . $_GET['batch'] . '&dbname=' . $_GET['dbname'] . '&mode=unit">Unit Mode</a></li><li><a href="batchsummary.php?' . $departmentURL . 'batch=' . $_GET['batch'] . '&dbname=' . $_GET['dbname'] . '&mode=procedure">Procedure Mode</a></li><li><a href="batchsummary.php?' . $departmentURL . 'batch=' . $_GET['batch'] . '&dbname=' . $_GET['dbname'] . '&mode=operation">Operation Mode</a></li></ul>';
$queryBatchId = 'select top 1 Campaign_ID, Lot_ID, Batch_ID, Log_Open_DT, Log_Close_DT, datediff(ss, Log_Open_DT, Log_Close_DT) as duration,
CodeTable.Description, train_id, recipe_id, recipe_version, product_id 
from BatchIdLog 
left join (select top 1 Batch_Log_ID, action_cd from batchdetail where Batch_Log_ID = \'' . $_GET['batch'] . '\' and (action_cd = 205 or action_cd = 209 or action_cd = 400 or action_cd = 401 or action_cd = 402 or action_cd = 404 or action_cd = 405) order by datetime desc) as temp1 on temp1.Batch_Log_ID = batchidlog.Batch_Log_ID
left join CodeTable on temp1.Action_CD = codetable.Code
where BatchIdLog.Batch_Log_ID = \'' . $_GET['batch'] . '\'';
$dataBatchId = odbc_exec($bConn, $queryBatchId);
if (odbc_fetch_row($dataBatchId)) {
	$batchStart = strtotime(odbc_result($dataBatchId, 4)) * 1000;
	if(odbc_result($dataBatchId, 5)) {
		$batchEnd = strtotime(odbc_result($dataBatchId, 5)) * 1000;
		$bFinishedBatch = true;
	};
	$batchTitle = '<h2>Showing Batch ' . odbc_result($dataBatchId, 1) . '/' . odbc_result($dataBatchId, 2) . '/' . odbc_result($dataBatchId, 3) . ' from ' . substr(odbc_result($dataBatchId, 4), 0, -4) . '</h2>';
	$batchOverview = '<h3>Batch Overview</h3><table class="overviewtable"><thead><tr><th>Batch ID</th><th>Duration</th><th>Start Time</th><th>End Time</th><th>Product</th><th>Recipe</th><th>Version</th><th>Train</th><th>Status</th></tr></thead>
	<tbody><tr class="oddRow"><td>' . odbc_result($dataBatchId, 1) . '/' . odbc_result($dataBatchId, 2) . '/' . odbc_result($dataBatchId, 3) . '</td><td>' . fToTime(odbc_result($dataBatchId, 6)) . '</td><td>' . substr(odbc_result($dataBatchId, 4), 0, -4) . '</td><td>' . substr(odbc_result($dataBatchId, 5), 0, -4) . '</td><td>' . odbc_result($dataBatchId, 11) . '</td><td>' . odbc_result($dataBatchId, 9) . '</td><td>' . odbc_result($dataBatchId, 10) . '</td><td>' . odbc_result($dataBatchId, 8) . '</td><td>' . substr(odbc_result($dataBatchId, 7), 10) . '</td></tr><tbody></table>';
	if (substr(odbc_result($dataBatchId, 7), 10) == 'Done') {
		switch (odbc_result($dataBatchId, 9)) {
			case 'C9_Production':
			case 'C14_Production':
				$sBatchNav .= '<div>Reports:</div><ul><li><a href="batchreport.php?' . $departmentURL . 'batch=' . $_GET['batch'] . '&dbname=' . $_GET['dbname'] . '">Report</a></li></ul>';
				break;
			default:
				break;
		};
	};
	$queryBatchNav = 'select *
	from (select top 1 \'previous\' as nav, dbname, Batch_Log_ID
	from (select \'batchhistory\' as dbname, batch_log_id, Log_open_DT 
	from [BatchHistory].[dbo].[BatchIdLog]
	where Recipe_ID = \'' . odbc_result($dataBatchId, 9) . '\' and Train_ID = \'' . odbc_result($dataBatchId, 8) . '\' and Recipe_Version = ' . odbc_result($dataBatchId, 10) . ' and Log_open_DT < \'' . odbc_result($dataBatchId, 4) . '\'
	union
	select \'oldbatchhistory\' as dbname, batch_log_id, Log_open_DT 
	from [OldBatchHistory].[dbo].[BatchIdLog]
	where Recipe_ID = \'' . odbc_result($dataBatchId, 9) . '\' and Train_ID = \'' . odbc_result($dataBatchId, 8) . '\' and Recipe_Version = ' . odbc_result($dataBatchId, 10) . ' and Log_open_DT < \'' . odbc_result($dataBatchId, 4) . '\') as temp1
	order by Log_open_DT desc) as temp3
	union
	select * 
	from (select top 1 \'next\' as nav, dbname, Batch_Log_ID
	from (select \'batchhistory\' as dbname, batch_log_id, Log_open_DT 
	from [BatchHistory].[dbo].[BatchIdLog]
	where Recipe_ID = \'' . odbc_result($dataBatchId, 9) . '\' and Train_ID = \'' . odbc_result($dataBatchId, 8) . '\' and Recipe_Version = ' . odbc_result($dataBatchId, 10) . ' and Log_open_DT > \'' . odbc_result($dataBatchId, 4) . '\'
	union
	select \'oldbatchhistory\' as dbname, batch_log_id, Log_open_DT 
	from [OldBatchHistory].[dbo].[BatchIdLog]
	where Recipe_ID = \'' . odbc_result($dataBatchId, 9) . '\' and Train_ID = \'' . odbc_result($dataBatchId, 8) . '\' and Recipe_Version = ' . odbc_result($dataBatchId, 10) . ' and Log_open_DT > \'' . odbc_result($dataBatchId, 4) . '\') as temp2
	order by Log_open_DT asc) as temp4
	order by nav desc';
	$dataBatchNav = odbc_exec($bConn, $queryBatchNav);
	$sBatchNav .= '<div>Navigation:</div><ul>';
	while(odbc_fetch_row($dataBatchNav)) {
		$sBatchNav .= '<li><a href="batchsummary.php?' . $departmentURL . 'batch=' . odbc_result($dataBatchNav, 3) . '&dbname=' . odbc_result($dataBatchNav, 2) . '&mode=' . $mode . '">' . ucwords(odbc_result($dataBatchNav, 1)) . ' Batch</a></li>';
	};
	$sBatchNav .= '</ul>';
} else {
	$_SESSION['sqlMessage'] = 'Batch Detail not found!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
$hookReplace['contextmenu'] = $sBatchNav . '</div>';
switch ($mode) {
	case 'complete': 
	case 'phase':
		$queryBatch = 'select starttime, endtime, duration, operation_id, phase_id, temp1.Phase_Instance_ID, unitprocedure_id, unitorconnection, description, action_cd 
		from (select min(datetime) as starttime, max(datetime) as endtime, datediff(ss, min(datetime), max(datetime)) as duration, operation_id, phase_id, Phase_Instance_ID, unitprocedure_id, unitorconnection
		from BatchDetail
		where Batch_Log_ID = \'' . $_GET['batch'] . '\' and Phase_Instance_ID <> \'\'
		group by operation_id, phase_id, Phase_Instance_ID, unitorconnection, unitprocedure_id) as temp1';
		if ($mode == 'complete') {
			$queryBatch .= ' left';
		};
		$queryBatch .= ' join (select Phase_Instance_ID, action_cd, Description
		from (select ROW_NUMBER() over(partition by phase_instance_id order by datetime desc, action_cd desc) as phase_step, Phase_Instance_ID, action_cd
		from BatchDetail
		where Batch_Log_ID = \'' . $_GET['batch'] . '\' and Phase_Instance_ID <> \'\' and (Action_CD between 221 and 234';
		if ($mode == 'complete') {
			$queryBatch .= ' or Action_CD between 275 and 279';
		};
		$queryBatch .= ' or action_cd between 241 and 246)) as temp2
		join CodeTable on Action_CD = Code
		where phase_step = 1
		) as temp3 on temp3.phase_instance_id = temp1.phase_instance_id';
		if ($mode == 'complete') {
			$queryBatch .= ' union
			select DateTime as starttime, null, null, null, null, null, null, null, null, null
			from BatchDetail
			where Batch_Log_ID = \'' . $_GET['batch'] . '\' and Phase_Instance_ID = \'\'
			group by datetime';
		};
		$queryBatch .= ' order by starttime, endtime';
		//having datediff(ss, min(datetime), max(datetime)) >= 1
		$dataBatch = odbc_exec($bConn, $queryBatch);
		if (odbc_num_rows($dataBatch) >= 1) {
			$batchTable = fRecordSwap(['rename' => 'Batch Detail', 'table' => true, 'exclude' => ['batch']]) . '<div class="tablesorter-row-hider" id="record-table-rows"></div><table class="records" id="batch-sort"><thead><tr><th>Phase</th><th>Duration</th><th>Start Date/Time</th><th>End Date/Time</th><th>Unit</th><th>Unit Procedure</th><th>Operation</th><th>Description</th></tr></thead><tbody>';
			$iRowNo = 0;
			$iCount = 0;
			$aGantt = '';
			$aGanttY[] = 1;
			$bPie = array();
			while(odbc_fetch_row($dataBatch)) {
				if (substr(odbc_result($dataBatch, 9), 0, 8) == "Received") {
					$phaseDescription = trim(substr(odbc_result($dataBatch, 9), 8));
				} else {
					$phaseDescription = trim(odbc_result($dataBatch, 9));
				};
				if ($iRowNo % 2 == 0) {
					$batchTable .= '<tr class="oddRow">';
				} else {
					$batchTable .= '<tr class="evenRow">';
				};
				$iRowNo++;
				if (!isset($bFinishedBatch)) {
					if (!isset($batchEnd)) {
						$batchEnd = 0;
					};
					if ($batchEnd < strtotime(odbc_result($dataBatch, 2)) * 1000) {
						$batchEnd = strtotime(odbc_result($dataBatch, 2)) * 1000;
					};
				};
				$batchTable .= '<td>';
				if (odbc_result($dataBatch, 6)) {
					$batchTable .= '<a data-text="Compare Similar" href="batchphasecompare.php?' . $departmentURL . 'phase=' . urlencode(odbc_result($dataBatch, 5)) . '&recipe=' . odbc_result($dataBatchId, 9) . '"><span class="icon-link icon-hover-hint"></span></a> <a href="phase.php?' . $departmentURL . $equipmentUrl . 'phase=' . urlencode(odbc_result($dataBatch, 6)) . '&dbname=' . $_GET['dbname'] .  '" >' . odbc_result($dataBatch, 5) . '</a>';
				} else {
					$batchTable .= '<a href="phase.php?' . $departmentURL . $equipmentUrl . 'time=' . urlencode(odbc_result($dataBatch, 1)) . '&dbname=' . $_GET['dbname'] . '&batch=' . $_GET['batch'] .  '" >NULL</a>';
				};
				$batchTable .= '</td><td data-duration="';
				if (odbc_result($dataBatch, 3) == "") {
					$batchTable .= 0;
				} else {
					$batchTable .= odbc_result($dataBatch, 3);
				};
				$batchTable .= '">' . fToTime(odbc_result($dataBatch, 3)) . '</td><td>' . substr(odbc_result($dataBatch, 1), 0, -4) . '</td><td>' . substr(odbc_result($dataBatch, 2), 0, -4) . '</td><td><a href="batchunit.php?' . $departmentURL . 'equip=' . odbc_result($dataBatch, 8) . '">' . odbc_result($dataBatch, 8) . '</a></td><td>' . odbc_result($dataBatch, 7) . '</td><td>' . odbc_result($dataBatch, 4) . '</td><td>' . $phaseDescription . '</td></tr>';
				$bGantt = true;
				foreach ($aGanttY as $key => $value) {
					if ((strtotime(odbc_result($dataBatch, 1)) * 1000) >= $value) {
						$aGanttY[$key] = strtotime(odbc_result($dataBatch, 2)) * 1000;
						$bGantt = false;
						$iGanttPos = $key + 1;
						break;
					};
				};
				if ($bGantt) {
					$aGanttY[] = strtotime(odbc_result($dataBatch, 2)) * 1000;
					$iGanttPos = count($aGanttY);
				};
				if (odbc_result($dataBatch, 7) != ' ') {
					$phaseName = odbc_result($dataBatch, 7);
				} else {
					$phaseName = 'NoName';
				};
				if (odbc_result($dataBatch, 3) != 0) {
					$aGantt[$phaseName][] = '[' . (strtotime(odbc_result($dataBatch, 1)) * 1000) . ',' . (12 - $iGanttPos) . ',' . (strtotime(odbc_result($dataBatch, 2)) * 1000) . ',"' . odbc_result($dataBatch, 5) . '", "phase.php?' . $departmentURL . $equipmentUrl . 'phase=' . urlencode(odbc_result($dataBatch, 6)) . '&dbname=' . $_GET['dbname'] . '"]
					';
				};
				if (isset($bPie[$phaseDescription])) {
					$bPie[$phaseDescription]++;
				} else {
					$bPie[$phaseDescription] = 1;
				};
			};
			$pieData = Array();
			ksort($bPie);
			foreach ($bPie as $key => $value) {
				$pieData[] = '{ label: "' . $key . '" , data: ' . $value . '}';
			};
			$batchTable .= '</body>' . fTableFooter(['id' => 'batch-sort', 'cols' => 8, 'totals' => [[0 => "count"]]]) . '</table>';
		};
		break;
	case 'unit':
		$queryBatchMode = 'select DateTime, UnitOrConnection, Action_CD
		from BatchDetail
		join CodeTable on Action_CD = code
		where Batch_Log_ID = \'' . $_GET['batch'] . '\' and Action_CD between 210 and 211
		order by DateTime, action_cd desc';
		$return = fBatchMode($queryBatchMode, 210, 211);
		$batchTable = $return[0];
		$aGantt = $return[1];
		if (!isset($bFinishedBatch)) {
			if (!isset($batchEnd)) {
				$batchEnd = 0;
			};
			if ($batchEnd < $return[2]) {
				$batchEnd = $return[2];
			};
		};
		break;
	case 'procedure':
		$queryBatchMode = 'select DateTime, UnitProcedure_ID, action_cd
		from BatchDetail
		join CodeTable on Action_CD = code
		where Batch_Log_ID = \'' . $_GET['batch'] . '\' and action_cd between 500 and 501
		order by DateTime, action_cd desc';
		$return = fBatchMode($queryBatchMode, 500, 501);
		$batchTable = $return[0];
		$aGantt = $return[1];
		if (!isset($bFinishedBatch)) {
			if (!isset($batchEnd)) {
				$batchEnd = 0;
			};
			if ($batchEnd < $return[2]) {
				$batchEnd = $return[2];
			};
		};
		break;
	case 'operation':
		$queryBatchMode = 'select DateTime, Operation_ID, Action_CD
		from BatchDetail
		join CodeTable on Action_CD = code
		where Batch_Log_ID = \'' . $_GET['batch'] . '\' and action_cd between 502 and 503
		order by DateTime, action_cd desc';
		$return = fBatchMode($queryBatchMode, 502, 503);
		$batchTable = $return[0];
		$aGantt = $return[1];
		if (!isset($bFinishedBatch)) {
			if (!isset($batchEnd)) {
				$batchEnd = 0;
			};
			if ($batchEnd < $return[2]) {
				$batchEnd = $return[2];
			};
		};
		break;
	default:
};
$aData = array();
if (!empty($aGantt)) {
	foreach ($aGantt as $key => $array) {
		$aData[] = '{"label":"' . $key . '","data":
		[' . implode(',', $array) . ']}
		';
	};
};
$stdOut .= $batchTitle;
if ($mode != 'complete') {
	if (isset($batchNav['previous'])) {
		$stdOut .= $batchNav['previous'];
	};
		$stdOut .= '<div id="gantt"></div>';
	if (isset($batchNav['next'])) {
		$stdOut .= $batchNav['next'];
	};
};
if ($mode == 'phase') {
	$stdOut .= '<div id="minipiecontainer"><h3>Phase Status</h3><div id="minipielegend"></div><div id="minipie"></div></div>';
};
if ($mode == 'complete') {
	$stdOut .= '<script language="javascript">
	
	$(function() {
	
	fTableSorter({sorttable: "#batch-sort", 
			sortorder: [[2,0]],
			rowheaders: "record",
			headers: {1 : { sorter: "duration" },
					6 : { columnSelector: false}}
		});
	
	});
	</script>';
} elseif ($mode != 'complete') {
	$stdOut .= '
	<div><h3>';
	if ($mode == 'phase') {
		$stdOut .= 'Procedure';
	} else {
		$stdOut .= ucfirst($mode);
	};
	$stdOut .= 's</h3>
	<div id="legend"></div></div>
	<script language="javascript">
	var searchArray = new Array();
	$(function() {'; 
	
	if ($mode == 'phase') {
		$stdOut .= 'fTableSorter({sorttable: "#batch-sort", 
			sortorder: [[2,0]], 
			rowheaders: "record",
			headers: {1 : { sorter: "duration" },
					6 : { columnSelector: false}}
		});
		var tabletype = "large";
		';
	} else {
		$stdOut .= 'fTableSorter({sorttable: "#batch-sort", 
			sortorder: [[1,0]], 
			rowheaders: "record",
			headers: {3 : { sorter: "duration" }}
		});
		var tabletype = "small";
		';
	};
	
	
	
	$stdOut .= '
	
	function plotAccordingToChoice(container, data, plot) {
			var newData = [];
			var checkbox = new Array();
			container.find("input:checked").each(function () {
				checkbox.push("^" + data[this.name].label + "$");
				var key = $(this).attr("name");
				if (key && data[key]) {
					newData.push(data[key]);
				}
			});
			if (newData.length > 0) {
				var sCheckbox = checkbox.join("|");
				searchArray[7] = "/" + sCheckbox + "/i";
				//console.log(searchArray);
				$("#batch-sort").trigger(\'search\', [searchArray]);
				plot.getOptions().legend.container = null;
				plot.getOptions().legend.show = false;
				plot.setData(newData);
				plot.draw();
			};	
		};
	
	var data,o;
	data = [' . implode(',', $aData) . '];
	o = {
		colors: trendcolors,
		xaxis:{
			min: ' . $batchStart . ',
			max: ' . $batchEnd . ',
			mode:"time",
			timeformat: "%d/%m/%y<br />%H:%M:%S",
			ticks: 5,
			twelveHourClock:true,
			axisLabel: "Date/Time"
		},
		yaxis:{
			/*show:false,*/
			min:0,
			max:12,
			axisLabel: "' . ucfirst($mode) . 's",
			ticks: 0
		},
		series:{
			gantt:{
				active:true,
				show:true,
				barHeight:1
			}
		},
		selection: {
			color: trendselection,
			mode: "x"
		},
		grid:{
			hoverable:true,
			clickable:true
		},
		legend:{
			show:true,
			container:$("#legend"),
			noColumns:5
		}	
	}
	
	recordSwapTarget = $.plot($("#gantt"),data,o);
	
	$("#gantt").bind("plotclick", function(event,pos,item) {
		if (item) {
			window.location.href = data[item.seriesIndex].data[item.dataIndex][4];
		};
	});
	
	$("#gantt").bind("mouseout", function() {
		$("#tooltip").remove();
		$(this).data("previous-post", -1);
	});
	$("#gantt").bind("plothover", function(event, pos, item) {
		if (item) {
			if ($(this).data("previous-post") != item.seriesIndex) {
				$(this).data("previous-post", item.seriesIndex);
			}
			$("#tooltip").remove();
			showTooltip(pos.pageX, pos.pageY, item.series.data[item.dataIndex][3] + " (" + msToTime(item.series.data[item.dataIndex][2] - item.series.data[item.dataIndex][0]) + ")");
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
		o.legend.show = false;
		o.legend.container = false;
		choiceContainer.find("input").click(plotAccordingToChoices);
		function plotAccordingToChoices() {
			var checkbox = new Array();
			var newData = [];
			choiceContainer.find("input:checked").each(function () {
				checkbox.push("^" + data[this.name].label + "$");
				var key = $(this).attr("name");
				if (key && data[key]) {
					newData.push(data[key]);
				}
			});
			if (newData.length > 0) {
				var sCheckbox = checkbox.join("|");
				switch (tabletype) {
					case "large":
						searchArray[5] = "/" + sCheckbox + "/i";
						break;
					case "small":
						searchArray[0] = "/" + sCheckbox + "/i";
						break;
				};
				$("#batch-sort").trigger(\'search\', [searchArray]);
				recordSwapTarget = $.plot($("#gantt"),newData,o);
			}
		};';
		
		if ($mode == 'phase') {
			$stdOut .= 'var dataStatus = [' . implode(',', $pieData) . '];
			var minipie = $.plot("#minipie", dataStatus, {
				colors: trendcolors,
				series: {
					pie: {
						show: true,
						radius: 1,
						stroke: {
							width: 0
						},
						label: {
							show: false
						}
					}
				},
				grid: {
					hoverable: true
				},
				legend: {
					container:$("#minipielegend"),
					labelFormatter: legendFormatter
				}
			});
			
			var i = 0;
			$.each(dataStatus, function(key, val) {
				val.color = i;
				++i;
			});
			
			// insert checkboxes 
			var choiceContainerPie = $("#minipielegend");
			var legendChoiceContainerPie = choiceContainerPie.find(".legendColorBox");
			$.each(dataStatus, function(key, val) {
				iText = "<td><input type=\'checkbox\' name=\'" + key + "\' checked=\'checked\' id=\'id" + key + "\'></input></td>";
				legendChoiceContainerPie.eq(key).before(iText);
			});
			
			choiceContainerPie.find("input").click(function() { plotAccordingToChoice(choiceContainerPie, dataStatus, minipie); });
		
			function legendFormatter(label, series) {
				return label + \' [\' + series.data[0][1] + \'] (\' + Math.round(series.percent) + \'%)\';
			}

			$("#minipie").bind("plothover", function(event, pos, item) {
				if (item) {
					if ($(this).data("previous-post") != item.seriesIndex) {
						$(this).data("previous-post", item.seriesIndex);
					}
					$("#tooltip").remove();
					showTooltip(pos.pageX, pos.pageY, item.series.label + " [" + item.series.data[0][1] + "] (" + Math.round(item.series.percent) + "%)");
				} else {
					$("#tooltip").remove();
					previousPost = $(this).data("previous-post", -1);
				}
			});';
		};
	$stdOut .= '});</script>';
};
$queryBatchStatus = 'select datetime, description 
		from batchdetail 
		join codetable on action_cd = code
		where Batch_Log_ID = \'' . $_GET['batch'] . '\' and (action_cd = 201 or action_cd = 202 or action_cd = 203 or action_cd = 204 or action_cd = 205 or action_cd = 206 or action_cd = 403 or action_cd = 406) 
		order by datetime asc';
$dataBatchStatus = odbc_exec($bConn, $queryBatchStatus);
$statusTable = '<table class="overviewtable"><thead><tr><th>Date Time</th><th>Status</th></tr></thead><tbody>';
$iRowNo = 0;
$pos = 0;
$processedStatusArray = array();
while (odbc_fetch_row($dataBatchStatus)) {
	if ($iRowNo % 2 == 0) {
		$statusTable .= '<tr class="oddRow">';
	} else {
		$statusTable .= '<tr class="evenRow">';
	};
	$statusTable .= '<td>' . substr(odbc_result($dataBatchStatus, 1), 0, -4) . '</td><td>' . odbc_result($dataBatchStatus, 2) . '</td></tr>';
	if ($pos == 0 && odbc_result($dataBatchStatus, 1) > $batchStart) {
		$processedStatusArray['Waiting'][$pos]['start'] = $batchStart;
		$processedStatusArray['Waiting'][$pos]['end'] = strtotime(odbc_result($dataBatchStatus, 1)) * 1000;
		$pos++;
	}
	$processedStatusArray[odbc_result($dataBatchStatus, 2)][$pos]['start'] = strtotime(odbc_result($dataBatchStatus, 1)) * 1000;
	if (isset($lastRow)) {
		$processedStatusArray[$lastRow[0]][$pos - 1]['end'] = strtotime(odbc_result($dataBatchStatus, 1)) * 1000;
	};
	$lastRow = [odbc_result($dataBatchStatus, 2), strtotime(odbc_result($dataBatchStatus, 1)) * 1000];
	$iRowNo++;
	$pos++;
};
$processedStatusArray[$lastRow[0]][$iRowNo - 1]['end'] = $batchEnd;
$sStatusData = '';
$sepStat = '';
foreach ($processedStatusArray as $key => $array) {
	$sStatusData .= $sepStat . '{label:"' . $key . '", data: [';
	$arraySep = '';
	foreach ($array as $vals) {
		$sStatusData .= $arraySep . '[' . $vals['start'] . ', ' . 1 . ', ' . $vals['end'] . ', "' . $key . '"]';
		$arraySep = ', ';
	};
	$sStatusData .= ']}';
	$sepStat = ', ';
};
$statusTable .= '</tbody></table>';
$stdOut .= '<h3 class="clear">Batch Status</h3><div id="status"></div>
<script language="javascript">
$(function() {
	statusData = [ ' . $sStatusData . '];
	statusSettings = {
		colors: trendcolors,
		xaxis:{
			min: ' . $batchStart . ',
			max: ' . $batchEnd . ',
			mode:"time",
			timeformat: "%d/%m/%y<br />%H:%M:%S",
			ticks: 5,
			twelveHourClock:true,
			axisLabel: "Date/Time"
		},
		yaxis:{
			/*show:false,*/
			ticks: 0,
			min:0.5,
			max:1.5,
			axisLabel: "Status"
		},
		series:{
			gantt:{
				active:true,
				show:true,
				barHeight:1
			}
		},
		selection: {
			color: trendselection,
			mode: "x"
		},
		grid:{
			hoverable:true,
			clickable:true
		}
	};
	$.plot($("#status"),statusData,statusSettings);
	$("#status").bind("mouseout", function() {
		$("#tooltip").remove();
		$(this).data("previous-post", -1);
	});
	$("#status").bind("plothover", function(event, pos, item) {
		if (item) {
			if ($(this).data("previous-post") != item.seriesIndex) {
				$(this).data("previous-post", item.seriesIndex);
			}
			$("#tooltip").remove();
			showTooltip(pos.pageX, pos.pageY, item.series.data[item.dataIndex][3] + " (" + msToTime(item.series.data[item.dataIndex][2] - item.series.data[item.dataIndex][0]) + ")");
		} else {
			$("#tooltip").remove();
			previousPost = $(this).data("previous-post", -1);
		}
	});
});
</script>';
$stdOut .= $statusTable . $batchOverview . $batchTable;
odbc_close($bConn);
$hookReplace['help'] = '<a href="#">Context Sensitive "Briefcase" Menu</a><div>Click the context sensitive menu to;<ul><li>View raw Batch detail.</li><li>Change timeline mode. Default is phase</li><li>Goto previous or next batch ran on the currently selected recipe.</li><li>View batch reports.</li></div>' . $helptext['timelinehover'] . '<a href="#">Timeline Graph Clicking</a><div>Clicking a Bar in the Timeline Graph will take you to the selected phase.<br />Note: Only available in Phase mode</div>' . $helptext['graphtoggle'] . $helptext['piehover'] . $helptext['status'] . $helptext['recordswap'] . $helptext['dynrecordswap'] . $helptext['autohighlight'] . '<a href="#">Phase Comparison</a><div>Clicking the link icon in the phase column of the records will take you to the phase comparison page for the selected phase.<br />Note: Only available in Phase and Complete mode</div>' . $helptext['tablesorter'] . $helptext['tablesorterflot'] . $helptext['recordsetcolumns'];
require_once 'includes/footer.php'; ?>