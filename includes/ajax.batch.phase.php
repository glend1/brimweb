<?PHP
require_once 'functions.php';
$output = array();
$aDE = fPermissionDE();
$departmentURL = '';
if (isset($aDE['department'])) {
	$departmentURL = 'department=' . $aDE['department'] . '&';
};
$equipmentUrl = '';
if (isset($aDE['equipment'])) {
	$equipmentUrl = 'equipment=' . $aDE['equipment'] . '&';
};
if (!isset($_GET['dbname'])) {
	$output['status'] = 'database name not found.';
	$error = true;
};
if (!isset($_GET['batch'])) {
	$output['status'] = 'batch not found.';
	$error = true;
};
if (!isset($_GET['startdate'])) {
	$output['status'] = 'start date not found.';
	$error = true;
};
if (!isset($_GET['enddate'])) {
	$output['status'] = 'end date not found.';
	$error = true;
};
if (!isset($error)) {
	$bConn = odbc_connect('DRIVER={SQL Server};Server=INSQL2;Database=' . $_GET['dbname'] . ';', $dbUsername, $dbPassword);
	$startDate = date('Y-m-d H:i:s', (($_GET['startdate'] / 1000)));
	$endDate = date('Y-m-d H:i:s', (($_GET['enddate'] / 1000) + 1));
	$output['status'] = 'complete';		
	$queryBatch = 'select starttime, endtime, duration, operation_id, phase_id, temp1.Phase_Instance_ID, unitprocedure_id, unitorconnection, description, action_cd 
	from (select min(datetime) as starttime, max(datetime) as endtime, datediff(ss, min(datetime), max(datetime)) as duration, operation_id, phase_id, Phase_Instance_ID, unitprocedure_id, unitorconnection
	from BatchDetail
	where Batch_Log_ID = \'' . $_GET['batch'] . '\' and Phase_Instance_ID <> \'\'
	group by operation_id, phase_id, Phase_Instance_ID, unitorconnection, unitprocedure_id) as temp1
	join (select Phase_Instance_ID, action_cd, Description
	from (select ROW_NUMBER() over(partition by phase_instance_id order by datetime desc, action_cd desc) as phase_step, Phase_Instance_ID, action_cd
	from BatchDetail
	where Batch_Log_ID = \'' . $_GET['batch'] . '\' and Phase_Instance_ID <> \'\' and (Action_CD between 221 and 234
	or action_cd between 241 and 246)) as temp2
	join CodeTable on Action_CD = Code
	where phase_step = 1
	) as temp3 on temp3.phase_instance_id = temp1.phase_instance_id
	where 
	((starttime between \'' . $startDate . '\' and \'' . date('Y-m-d H:i:s', strtotime($endDate) + 1) . '\'
	or endtime between \'' . $startDate . '\' and \'' . date('Y-m-d H:i:s', strtotime($endDate) + 1) . '\')
	or ((starttime < \'' . $startDate . '\' and starttime < \'' . date('Y-m-d H:i:s', strtotime($endDate) + 1) . '\')
	and ((endtime > \'' . $startDate . '\' and endtime > \'' . date('Y-m-d H:i:s', strtotime($endDate) + 1) . '\') or endtime is null)))
	order by starttime, endtime';
	$dataBatch = odbc_exec($bConn, $queryBatch);
	if (odbc_num_rows($dataBatch) >= 1) {
		$output['oreturn'] = '<table id="ajax-batch-phase" class="records ajax"><thead><tr><th>Phase</th><th>Duration</th><th>Start Date/Time</th><th>End Date/Time</th><th>Unit</th><th>Unit Procedure</th><th>Operation</th><th>Description</th></tr></thead><tbody>';
		$iRowNo = 0;
		$iCount = 0;
		while(odbc_fetch_row($dataBatch)) {
			if (substr(odbc_result($dataBatch, 9), 0, 8) == "Received") {
				$phaseDescription = trim(substr(odbc_result($dataBatch, 9), 8));
			} else {
				$phaseDescription = trim(odbc_result($dataBatch, 9));
			};
			if ($iRowNo % 2 == 0) {
				$output['oreturn'] .= '<tr class="oddRow">';
			} else {
				$output['oreturn'] .= '<tr class="evenRow">';
			};
			$iRowNo++;
			$output['oreturn'] .= '<td>';
			if (odbc_result($dataBatch, 6)) {
				$output['oreturn'] .= '<a data-text="Show On Chart" href="#top"><span class="icon-bar-chart icon-hover-hint" data-start="' . (strtotime(odbc_result($dataBatch, 1)) * 1000) . '" data-end="' . (strtotime(odbc_result($dataBatch, 2)) * 1000) . '"></span></a> <a href="phase.php?' . $departmentURL . $equipmentUrl . 'phase=' . urlencode(odbc_result($dataBatch, 6)) . '&dbname=' . $_GET['dbname'] .  '" >' . odbc_result($dataBatch, 5) . '</a>';
			} else {
				$output['oreturn'] .= '<a href="phase.php?' . $departmentURL . $equipmentUrl . 'time=' . urlencode(odbc_result($dataBatch, 1)) . '&dbname=' . $_GET['dbname'] . '&batch=' . $_GET['batch'] .  '" >NULL</a>';
			};
			$output['oreturn'] .= '</td><td data-duration="';
			if (odbc_result($dataBatch, 3) == "") {
				$output['oreturn'] .= 0;
			} else {
				$output['oreturn'] .= odbc_result($dataBatch, 3);
			};
			$output['oreturn'] .= '">' . fToTime(odbc_result($dataBatch, 3)) . '</td><td>' . substr(odbc_result($dataBatch, 1), 0, -4) . '</td><td>' . substr(odbc_result($dataBatch, 2), 0, -4) . '</td><td><a href="batchunit.php?' . $departmentURL . 'equip=' . odbc_result($dataBatch, 8) . '">' . odbc_result($dataBatch, 8) . '</a></td><td>' . odbc_result($dataBatch, 7) . '</td><td>' . odbc_result($dataBatch, 4) . '</td><td>' . $phaseDescription . '</td></tr>';
		};
		$output['oreturn'] .= '</body>' . fTableFooter(['id' => 'ajax-batch-phase', 'cols' => 8, 'totals' => [[0 => "count"]]]) . '</table>';
	};
	odbc_close($bConn);
};
print(json_encode($output));
?>