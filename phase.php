<?PHP 
$title = 'Batch Step Detail';
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
if (!((isset($_GET['phase']) || isset($_GET['time']) || isset($_GET['mode'])) && isset($_GET['dbname']))) {
	$_SESSION['sqlMessage'] = 'Select a Batch!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
function arrayFind($code, $array) {
	foreach ($array as $i) {
		if ($i == $code) {
			return true;
		};
	};
	return false;
};
$bConn = odbc_connect('DRIVER={SQL Server};Server=INSQL2;Database=' . $_GET['dbname'] . ';', $dbUsername, $dbPassword);
$startDate = '';
$endDate = '';
if (isset($_GET['phase'])) {
	$phaseTable = '';
	$phaseComments = '';
	$queryInstruction = 'select instruction, seqnum from PhaseInstruction where Phase_Instance_ID = \'' . $_GET['phase'] . '\' order by seqnum, datetime asc';
	$dataInstruction = odbc_exec($bConn, $queryInstruction);
	if (odbc_num_rows($dataInstruction) >= 1) {
		$phaseComments .= '<h3>Phase Instruction</h3>';
		$seqnum = 1;
		while(odbc_fetch_row($dataInstruction)) {
			if ($seqnum < odbc_result($dataInstruction, 2)) {
				$phaseComments .= '<br />';
			};
			$seqnum = odbc_result($dataInstruction, 1);
			$phaseComments .= odbc_result($dataInstruction, 1);
		};
	};
	$queryTransition = 'select Transition_Desc
	from Transition
	where Transition_Instance_ID = \'' . $_GET['phase'] . '\'';
	$dataTransition = odbc_exec($bConn, $queryTransition);
	if (odbc_num_rows($dataTransition) >= 1) {
		$phaseComments .= '<h3>Transition</h3>';
		while(odbc_fetch_row($dataTransition)) {
			$phaseComments .= odbc_result($dataTransition, 1);
		};
	};
	$queryPhase = 'select datetime, Description, phase_id, unitorconnection, unitprocedure_id, operation_id, code
	from BatchDetail	
	join CodeTable on Action_CD = Code
	where Phase_Instance_ID = \'' . $_GET['phase'] . '\'
	order by datetime asc, action_cd desc';
	$dataPhase = odbc_exec($bConn, $queryPhase);
	if (odbc_num_rows($dataPhase) >= 1) {
		$phaseTable .= '<h3>Phase Detail <a data-text="Table Headers" data-id="phase" class="table-row-button" href="none.php"><span class="icon-list icon-hover-hint"></span></a></h3><div class="tablesorter-row-hider" id="phase-table-rows"></div><table class="overviewtable" id="phase-table"><thead><tr><th>Date/Time</th><th>Status</th><th>Unit Or Connection</th><th>Procedure</th><th>Operation</th></tr></thead><tbody>';
		$i = 0;
		while(odbc_fetch_row($dataPhase)) {
			if ($i == 0) {
				$startDate = substr(odbc_result($dataPhase, 1), 0, -4);
			};
			$endDate = substr(odbc_result($dataPhase, 1), 0, -4);
			$phaseName = 'Phase: ' . odbc_result($dataPhase, 3);
			if ($i % 2 == 0) {
				$phaseTable .= '<tr class="oddRow">';
			} else {
				$phaseTable .= '<tr class="evenRow">';
			};
			$i++;
			$phaseTable .= '<td>' . substr(odbc_result($dataPhase, 1), 0, -4) . '</td><td>';
			if (arrayFind(odbc_result($dataPhase, 7), [222, 244, 225, 226, 228, 230, 233, 234, 242, 244, 246])) {
				$phaseTable .= '<span class="emphasis">' . odbc_result($dataPhase, 2) . '</span>';
				$statusData[] = ["timestamp" => strtotime(odbc_result($dataPhase, 1)) * 1000, "description" => odbc_result($dataPhase, 2)];
			} else {
				$phaseTable .= odbc_result($dataPhase, 2);
			};
			$phaseTable .=  '</td><td>' . odbc_result($dataPhase, 4) . '</td><td>' . odbc_result($dataPhase, 5) . '</td><td>' . odbc_result($dataPhase, 6) . '</td></tr>';
		};
		$phaseTable .= '</tbody>' . fTableFooter(['id' => 'phase-table', 'cols' => 5]) . '</table>';
	};
	$queryProcess = 'select Parameter_ID, Actual_Value, Target_Value, UnitOfMeasure
	from ProcessVar
	where Phase_Instance_ID = \'' . $_GET['phase'] . '\'
	order by datetime';
	$dataProcess = odbc_exec($bConn, $queryProcess);
	if (odbc_num_rows($dataProcess) >= 1) {
		$phaseTable .= '<h3>Process Variables <a data-text="Table Headers" data-id="var" class="table-row-button" href="none.php"><span class="icon-list icon-hover-hint"></span></a></h3><div class="tablesorter-row-hider" id="var-table-rows"></div><table class="overviewtable" id="var-table"><thead><tr><th>Parameter</th><th>Actual</th><th>Target</th><th>Unit</th></tr></thead><tbody>';
		$i = 0;
		while(odbc_fetch_row($dataProcess)) {
			if ($i % 2 == 0) {
				$phaseTable .= '<tr class="oddRow">';
			} else {
				$phaseTable .= '<tr class="evenRow">';
			};
			$i++;
			$phaseTable .= '<td>' . odbc_result($dataProcess, 1) . '</td><td>' . odbc_result($dataProcess, 2) . '</td><td>' . odbc_result($dataProcess, 3) . '</td><td>' . odbc_result($dataProcess, 4) . '</td></tr>';
		};
		$phaseTable .= '</tbody>' . fTableFooter(['id' => 'var-table', 'cols' => 4]) . '</table>';
	};
	$queryMaterial = 'select Material_Instance_ID, Material_Name, Material_ID, Material_Parameter, Actual_Qty, Target_Qty, UnitOfMeasure
	from MaterialInput
	where Phase_Instance_ID = \'' . $_GET['phase'] . '\'';
	$dataMaterial = odbc_exec($bConn, $queryMaterial);
	if (odbc_num_rows($dataMaterial) >= 1) {
		$phaseTable .= '<h3>Material Input <a data-text="Table Headers" data-id="material" class="table-row-button" href="none.php"><span class="icon-list icon-hover-hint"></span></a></h3><div class="tablesorter-row-hider" id="material-table-rows"></div><table class="overviewtable" id="material-table"><thead><tr><th>Instance</th><th>Name</th><th>ID</th><th>Parameter</th><th>Actual</th><th>Target</th><th>Unit</th></tr></thead><tbody>';
		$i = 0;
		while(odbc_fetch_row($dataMaterial)) {
			if ($i % 2 == 0) {
				$phaseTable .= '<tr class="oddRow">';
			} else {
				$phaseTable .= '<tr class="evenRow">';
			};
			$i++;
			$phaseTable .= '<td>' . odbc_result($dataMaterial, 1) . '</td><td>' . odbc_result($dataMaterial, 2) . '</td><td>' . odbc_result($dataMaterial, 3) . '</td><td>' . odbc_result($dataMaterial, 4) . '</td><td>' . odbc_result($dataMaterial, 5) . '</td><td>' . odbc_result($dataMaterial, 6) . '</td><td>' . odbc_result($dataMaterial, 7) . '</td></tr>';
		};
		$phaseTable .= '</tbody>' . fTableFooter(['id' => 'material-table', 'cols' => 7]) . '</table>';
	};
} elseif (isset($_GET['time'])) {
	if (!isset($_GET['batch'])) {
		$_SESSION['sqlMessage'] = 'Select a Batch!';
		$_SESSION['uiState'] = 'error';
		fRedirect();
	};
	$queryPhaseTime = 'select datetime, Description, phase_id, unitorconnection, unitprocedure_id, operation_id
	from BatchDetail	
	join CodeTable on Action_CD = Code
	where datetime = \'' . $_GET['time'] . '\' and Phase_Instance_ID = \'\' and batch_log_id = \'' . $_GET['batch'] . '\'
	order by datetime asc, action_cd desc';
	$phaseTable = '<h3>Step Detail <a data-text="Table Headers" data-id="step" class="table-row-button" href="none.php"><span class="icon-list icon-hover-hint"></span></a></h3><div class="tablesorter-row-hider" id="step-table-rows"></div><table class="overviewtable" id="step-table"><thead><tr><th>Date/Time</th><th>Status</th><th>Unit Or Connection</th><th>Procedure</th><th>Operation</th></tr></thead><tbody>';
	$dataPhaseTime = odbc_exec($bConn, $queryPhaseTime);
	$i = 0;
	while(odbc_fetch_row($dataPhaseTime)) {
		if ($i == 0) {
			$startDate = substr(odbc_result($dataPhaseTime, 1), 0, -4);
		};
		$endDate = substr(odbc_result($dataPhaseTime, 1), 0, -4);
		$phaseName = 'Psudophase';
		if ($i % 2 == 0) {
			$phaseTable .= '<tr class="oddRow">';
		} else {
			$phaseTable .= '<tr class="evenRow">';
		};
		$i++;
		$phaseTable .= '<td>' . substr(odbc_result($dataPhaseTime, 1), 0, -4) . '</td><td>' . odbc_result($dataPhaseTime, 2) . '</td><td>' . odbc_result($dataPhaseTime, 4) . '</td><td>' . odbc_result($dataPhaseTime, 5) . '</td><td>' . odbc_result($dataPhaseTime, 6) . '</td></tr>';
	};
	$phaseTable .= '</tbody>' . fTableFooter(['id' => 'step-table', 'cols' => 5]) . '</table>';
} elseif (isset($_GET['mode'])) {
	if (!(isset($_GET['startdate']) && isset($_GET['enddate']) && isset($_GET['name']) && isset($_GET['batch']))) {
		$_SESSION['sqlMessage'] = 'Invalid Selection!';
		$_SESSION['uiState'] = 'error';
		fRedirect();
	};
	switch ($_GET['mode']) {
		case 'unit':
			$mode = 'unitorconnection';
			$arrayCodes = [410, 411, 412, 413, 210, 211];
			break;
		case 'procedure':
			$mode = 'unitprocedure_id';
			$arrayCodes = [500, 501];
			break;
		case 'operation':
			$mode = 'operation_id';
			$arrayCodes = [502, 503];
			break;
		default:
			$_SESSION['sqlMessage'] = 'Invalid Mode!';
			$_SESSION['uiState'] = 'error';
			fRedirect();
	};
	$queryPhaseMode = 'select datetime, Description, phase_id, unitorconnection, unitprocedure_id, operation_id, phase_instance_id, code
	from BatchDetail	
	join CodeTable on Action_CD = Code
	where datetime between \'' . $_GET['startdate'] . '\' and \'' . $_GET['enddate'] . '\' and ' . $mode . '=\'' . $_GET['name'] . '\' and batch_log_id = \'' . $_GET['batch'] . '\'
	order by datetime asc, action_cd desc';
	$phaseTable = '<h3>' . ucwords($_GET['mode']) . ' Detail <a data-text="Table Headers" data-id="mode" class="table-row-button" href="none.php"><span class="icon-list icon-hover-hint"></span></a></h3><div class="tablesorter-row-hider" id="mode-table-rows"></div><table class="overviewtable" id="mode-table"><thead><tr><th>Date/Time</th><th>Status</th><th>Phase</th><th>Unit Or Connection</th><th>Procedure</th><th>Operation</th></tr></thead><tbody>';
	$dataPhaseMode = odbc_exec($bConn, $queryPhaseMode);
	$i = 0;
	while(odbc_fetch_row($dataPhaseMode)) {
		if ($i == 0) {
			$startDate = substr(odbc_result($dataPhaseMode, 1), 0, -4);
		};
		$endDate = substr(odbc_result($dataPhaseMode, 1), 0, -4);
		$phaseName = ucwords($_GET['mode']) . ': ' . $_GET['name'];
		if ($i % 2 == 0) {
			$phaseTable .= '<tr class="oddRow">';
		} else {
			$phaseTable .= '<tr class="evenRow">';
		};
		$i++;
		$phaseTable .= '<td>' . $endDate . '</td><td>';
		if (arrayFind(odbc_result($dataPhaseMode, 8), $arrayCodes)) {
			$phaseTable .= '<span class="emphasis">' . odbc_result($dataPhaseMode, 2) . '</span>';
		} else {
			$phaseTable .= odbc_result($dataPhaseMode, 2);
		};
		$phaseTable .=  '</td><td>';
		if (odbc_result($dataPhaseMode, 3)) {
			$phaseTable .= '<a href="phase.php?' . $departmentURL . 'dbname=' . $_GET['dbname'] . '&phase=' . odbc_result($dataPhaseMode, 7) . '">' . odbc_result($dataPhaseMode, 3) . '</a>';
		};
		$phaseTable .= '</td><td>' . odbc_result($dataPhaseMode, 4) . '</td><td>' . odbc_result($dataPhaseMode, 5) . '</td><td>' . odbc_result($dataPhaseMode, 6) . '</td></tr>';
	};
	$phaseTable .= '</tbody>' . fTableFooter(['id' => 'mode-table', 'cols' => 6]) . '</table>';
};
odbc_close($bConn);
if (isset($phaseName)) {
	$stdOut .= '<h2>' . $phaseName . '</h2>';
}
if (isset($phaseTable)) {
	$stdOut .= '<div id="quicktrendholder"><div id="sidebar"><h3>Tagnames</h3><div id="processlegend"></div><div id="tagdescription"></div></div><div id="quicktrend"></div><div id="datetimestampcontainer"><div id="datetimestamp"></div></div></div><h3 id="trendhint">Select a trend from the <a href="#trends">bottom</a> of this page to view Historical Data</h3>';
	if (isset($phaseComments)) {
		$stdOut .= $phaseComments;
	};
	if (isset($statusData)) {
		$sStatusData = '';
		$sep = '';
		$pos = 0;
		$processedArray = array();
		foreach ($statusData as $key => $array) {
			if ($pos == 0 && $array['timestamp'] > (strtotime($startDate) * 1000)) {
				$processedArray['Waiting'][$pos]['start'] = strtotime($startDate) * 1000;
				$processedArray['Waiting'][$pos]['end'] = $statusData[$key]['timestamp'];
				$pos++;
			}
			$processedArray[$array['description']][$pos]['start'] = $array['timestamp']; 
			if (isset($statusData[$key + 1])) {
				$processedArray[$array['description']][$pos]['end'] = $statusData[$key + 1]['timestamp'];
			} else {
				$processedArray[$array['description']][$pos]['end'] = strtotime($endDate) * 1000;
			}
			$pos++;
		};
		foreach ($processedArray as $key => $array) {
			$sStatusData .= $sep . '{label:"' . $key . '", data: [';
			$arraySep = '';
				foreach ($array as $vals) {
					$sStatusData .= $arraySep . '[' . $vals['start'] . ', ' . 1 . ', ' . $vals['end'] . ', "' . $key . '"]';
					$arraySep = ', ';
				};
			$sStatusData .= ']}';
			$sep = ', ';
		};
		$stdOut .= '<h3>Status</h3>
		<div id="status"></div>
		<script language="javascript">
			$(function() {
				statusData = [ ' . $sStatusData . '];
				statusSettings = {
					colors: trendcolors,
					xaxis:{
						min: ' . strtotime($startDate) * 1000 . ',
						max: ' . strtotime($endDate) * 1000 . ',
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
	};
	$stdOut .= $phaseTable . '<a name="trends"></a>' . fGetTrends(strtotime($startDate) * 1000, strtotime($endDate) * 1000, 'local', ['trend', 'batch']) . '
	<script type="text/javascript">
	
	fTableSorter({sorttable: "#mode-table", 
		rowheaders: "mode",
		sortorder: [[0,0]], 
		headers: {}
	});
	
	fTableSorter({sorttable: "#phase-table", 
		rowheaders: "phase",
		sortorder: [[0,0]], 
		headers: {4 : { columnSelector: false }}
	});
	
	fTableSorter({sorttable: "#step-table", 
		rowheaders: "step",
		sortorder: [[0,0]], 
		headers: {}
	});
	
	fTableSorter({sorttable: "#var-table", 
		rowheaders: "var",
		sortorder: [[0,0]], 
		headers: {3 : { columnSelector: false }}
	});
	
	fTableSorter({sorttable: "#material-table",
		rowheaders: "material",
		sortorder: [[0,0]], 
		headers: {6 : { columnSelector: false }}
	});
	
	
		var iDateStart = ' . (strtotime($startDate) * 1000) . ';
		var iDateEnd = ' . (strtotime($endDate) * 1000) . ';
	</script>
	<script language="javascript" type="text/javascript" src="js/quicktrend.js"></script>';
} else {
	$stdOut .= '<h2>No Data Available</h2>';
};
$hookReplace['help'] = $helptext['status'] . $helptext['linehoverany'] . $helptext['linemarkings'] . $helptext['recordswap']  . $helptext['dynrecordswap'] . $helptext['autohighlight'] . $helptext['tablesorter'] . $helptext['recordsetcolumns'];
require_once 'includes/footer.php'; ?>