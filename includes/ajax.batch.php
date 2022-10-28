<?PHP
require_once 'functions.php';
$output = array();
$aDE = fPermissionDE();
$departmentURL = '';
$departmentData = '';
if (isset($aDE['department'])) {
	$departmentData .= 'data-department="' . $aDE['department'] . '" ';
	$departmentURL = 'department=' . $aDE['department'] . '&';
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
	$startDate = date('Y-m-d H:i:s', (($_GET['startdate'] / 1000)));
	$endDate = date('Y-m-d H:i:s', (($_GET['enddate'] / 1000) + 1));
	$output['status'] = 'complete';
	if (isset($aDE['department'])) {
		$queryTrains = 'select distinct train from train where departmentfk = ' . $aDE['department'];
		if (isset($aDE['equipment'])) {
			$queryTrains .= ' and (departmentequipmentfk = ' . $aDE['equipment'] . ' or departmentequipmentfk is null)';
		};
	} else {
		$queryTrains = 'select train_id 
		from train
		right join (select distinct train_id from [batchhistory].[dbo].[batchidlog]) as trains on trains.train_id = train
		where DepartmentFK is NULL';
	};
	$dataTrains = odbc_exec($conn, $queryTrains);
	while(odbc_fetch_row($dataTrains)) {
		$deptFound = true;
		$aDepartments[] = 'train_id = \'' . odbc_result($dataTrains, 1) . '\'';
	};
	if (isset($deptFound)) {
		$sTrains = 'where ' . implode(' or ', $aDepartments);
		$bConn = odbc_connect('DRIVER={SQL Server};Server=INSQL2;Database=batchhistory;', $dbUsername, $dbPassword);
		$queryBatches = 'select distinct Campaign_ID, Lot_ID, Batch_ID, Batch_Log_ID, dbname, duration, product_id, recipe_id, log_open_dt, log_close_dt, int_recipe, Description, train_id, departmentequipmentfk
		from (
			select Campaign_ID, Lot_ID, Batch_ID, [oldBatchHistory].[dbo].[BatchIdLog].[Batch_Log_ID], \'oldbatchhistory\' as dbname, datediff(ss, log_open_dt, log_close_dt) as duration, product_id, recipe_id, log_open_dt, log_close_dt, cast(recipe_version as int) int_recipe, CodeTable.Description, train_id 
			from [oldBatchHistory].[dbo].[BatchIdLog]
			left join 
				(select Batch_Log_ID, Action_CD
					from (
						select Batch_Log_ID, action_cd, ROW_NUMBER() over (partition by batch_log_id order by datetime desc, action_cd asc) as number 
						from [oldBatchHistory].[dbo].[batchdetail]
						where (action_cd = 205 or action_cd = 209 or action_cd = 400 or action_cd = 401 or action_cd = 402 or action_cd = 404 or action_cd = 405)
						) as statustable
					where number = 1
				) as statustable on [oldBatchHistory].[dbo].[BatchIdLog].[Batch_Log_ID] = statustable.Batch_Log_ID
			left join CodeTable on Action_CD = Code
			' . $sTrains . '
			union
			select Campaign_ID, Lot_ID, Batch_ID, [BatchHistory].[dbo].[BatchIdLog].[Batch_Log_ID], \'batchhistory\' as dbname, datediff(ss, log_open_dt, log_close_dt) as duration, product_id, recipe_id, log_open_dt, log_close_dt, cast(recipe_version as int) int_recipe, CodeTable.Description, train_id 
			from [BatchHistory].[dbo].[BatchIdLog]
			left join 
				(select Batch_Log_ID, Action_CD
					from (
						select Batch_Log_ID, action_cd, ROW_NUMBER() over (partition by batch_log_id order by datetime desc, action_cd asc) as number 
						from [BatchHistory].[dbo].[batchdetail]
						where (action_cd = 205 or action_cd = 209 or action_cd = 400 or action_cd = 401 or action_cd = 402 or action_cd = 404 or action_cd = 405)
						) as statustable
					where number = 1
				) as statustable on [BatchHistory].[dbo].[BatchIdLog].[Batch_Log_ID] = statustable.Batch_Log_ID
			left join CodeTable on Action_CD = Code
			' . $sTrains . '
		) as batchtable
		left join [plantavail].[dbo].[train] on train_id = train 
		where 
		((Log_Open_DT between \'' . $startDate . '\' and \'' . date('Y-m-d H:i:s', strtotime($endDate) + 1) . '\'
		or Log_Close_DT between \'' . $startDate . '\' and \'' . date('Y-m-d H:i:s', strtotime($endDate) + 1) . '\')
		or ((Log_Open_DT < \'' . $startDate . '\' and Log_Open_DT < \'' . date('Y-m-d H:i:s', strtotime($endDate) + 1) . '\')
		and ((Log_Close_DT > \'' . $startDate . '\' and Log_Close_DT > \'' . date('Y-m-d H:i:s', strtotime($endDate) + 1) . '\') or Log_Close_DT is null)))
		order by log_open_dt, train_id';
		$dataBatches = odbc_exec($bConn, $queryBatches);
		$output['oreturn'] = '<table id="ajax-batch" class="records ajax"><thead><tr><th>Batch ID</th><th>Duration</th><th>Start Time</th><th>End Time</th><th>Product</th><th>Recipe</th><th>Version</th><th>Train</th><th>Status</th></tr></thead><tbody>';
		$i = 1;
		$dataDuration = array();
		$dataStatus = array();
		while(odbc_fetch_row($dataBatches)) {
			$batchStatus = trim(substr(odbc_result($dataBatches, 12), 10));
			if ($i % 2 == 0) {
				$output['oreturn'] .= '<tr class="oddRow">';
			} else {
				$output['oreturn'] .= '<tr class="evenRow">';
			};
			$i++;
			$output['oreturn'] .= '<td><a data-text="Show On Chart" href="#top"><span class="icon-bar-chart icon-hover-hint" data-start="' . (strtotime(odbc_result($dataBatches, 9)) * 1000) . '" data-end="' . (strtotime(odbc_result($dataBatches, 10)) * 1000) . '"></span></a> <a data-text="Open Table" href="#top"><span class="icon-table icon-hover-hint" data-dbname="' . odbc_result($dataBatches, 5) . '" data-batch="' . odbc_result($dataBatches, 4) . '" data-url="includes/ajax.batch.phase.php" data-id="ajax-batch-phase" data-name="' . odbc_result($dataBatches, 1) . '/' . odbc_result($dataBatches, 2) . '/' . odbc_result($dataBatches, 3) . '" ' . $departmentData;
			$equipmentUrl = '';
			if (odbc_result($dataBatches, 14)) {
				$output['oreturn'] .= 'data-equipment="' . odbc_result($dataBatches, 14) . '" ';
				$equipmentUrl = 'equipment=' . odbc_result($dataBatches, 14) . '&';
			};
			$output['oreturn'] .= '></span></a> <a href="batchsummary.php?' . $departmentURL . $equipmentUrl . 'batch=' . urlencode(odbc_result($dataBatches, 4)) . '&dbname=' . urlencode(odbc_result($dataBatches, 5)) . '" >' . odbc_result($dataBatches, 1) . '/' . odbc_result($dataBatches, 2) . '/' . odbc_result($dataBatches, 3) . '</a></td><td data-duration="';
			if (odbc_result($dataBatches, 6) == "") {
				$output['oreturn'] .= 0;
			} else {
				$output['oreturn'] .= odbc_result($dataBatches, 6);
			};
			$output['oreturn'] .= '">' . fToTime(odbc_result($dataBatches, 6)) . '</td><td>' . substr(odbc_result($dataBatches, 9), 0, -4) . '</td><td>' . substr(odbc_result($dataBatches, 10), 0, -4) . '</td><td>' . odbc_result($dataBatches, 7) . '</td><td>' . odbc_result($dataBatches, 8) . '</td><td>' . odbc_result($dataBatches, 11) . '</td><td>' . odbc_result($dataBatches, 13) . '</td><td>' . $batchStatus . '</td></tr>';
		};
		$output['oreturn'] .= '</tbody>' . fTableFooter(['id' => 'ajax-batch', 'cols' => 9, 'totals' => [[0 => "count", 1 => 'time-mean'], [1 => 'time-sum']]]) . '</table>';
		odbc_close($bConn);
	} else {
		$output['status'] = 'not found in database.';
	};
};
print(json_encode($output));
?>