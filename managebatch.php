<?PHP 
$title = 'Batch Maintenance';
require_once 'includes/header.php';
if (!fCanSee(@$_SESSION['permissions']['page'][4] >= 300)) {
	$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
$sDate = new DateTime(($localTime));
$sDate->sub(new DateInterval('P15D'));
$sDate = $sDate->format('Y-m-d H:i:s');
$bConn = odbc_connect('DRIVER={SQL Server};Server=INSQL2;Database=BatchHistory;', $dbUsername, $dbPassword);
$queryBatchTidy = 'select batchidlog.Batch_Log_ID, Campaign_ID, Lot_ID, Batch_ID, Log_Open_DT, Product_ID, Recipe_ID, Recipe_Version, train_id, code
from BatchIdLog
left join 
		(select Batch_Log_ID, Action_CD
			from (
				select Batch_Log_ID, action_cd, ROW_NUMBER() over (partition by batch_log_id order by datetime desc, action_cd asc) as number 
				from batchdetail
				where (action_cd = 205 or action_cd = 209 or action_cd = 400 or action_cd = 401 or action_cd = 402 or action_cd = 404 or action_cd = 405) 
				) as statustable
			where number = 1
		) as statustable on BatchIdLog.Batch_Log_ID = statustable.Batch_Log_ID
	left join CodeTable on Action_CD = Code
left join [plantavail].[dbo].[train] on train_id = train 
where Log_Close_DT is null and log_open_dt < \'' . $sDate . '\'';
if (@$_SESSION['id'] != 1 && isset($_SESSION['id'])) {
	$aQueryBatch[] = 'DepartmentFK is null';
	$queryBatches .= ' and ' . fOrThemReturn($_SESSION['permissions']['department'], 300, 'departmentfk', $aQueryBatch);
};
$queryBatchTidy .= ' order by log_open_dt';
$batchTable = '<h2>Showing active Batches from before ' . $sDate . '</h2>
<table class="records"><thead><tr><th>Batch</th><th>Start Date/Time</th><th>Product</th><th>Recipe</th><th>Version</th><th>Train</th><th>Status</th><th>Action</th></tr></thead><tbody>';
$dataBatchTidy = odbc_exec($bConn, $queryBatchTidy);
$row = 0;
while(odbc_fetch_row($dataBatchTidy)) {
	$batchFound = true;
	if ($row % 2 == 0) {
		$rowHeader = 'oddRow';
	} else {
		$rowHeader = 'evenRow';
	};
	$row++;
	$batchTable .= '<tr class="' . $rowHeader . '"><td>' . odbc_result($dataBatchTidy, 2) . '/' . odbc_result($dataBatchTidy, 3) . '/' . odbc_result($dataBatchTidy, 4) . '</td><td>' . substr(odbc_result($dataBatchTidy, 5), 0, -4) . '</td><td>' . odbc_result($dataBatchTidy, 6) . '</td><td>' . odbc_result($dataBatchTidy, 7) . '</td><td>' . odbc_result($dataBatchTidy, 8) . '</td><td>' . odbc_result($dataBatchTidy, 9) . '</td><td>';
	if (odbc_result($dataBatchTidy, 10)) {
		$batchTable .= odbc_result($dataBatchTidy, 10);
	} else {
		$batchTable .= 'None';
	};
	$batchTable .= '</td><td><form action="includes/maintenancebatch.php" method="post"><input type="hidden" name="id" value="' . odbc_result($dataBatchTidy, 1) . '" /><input type="submit" value="Close!"/></form></td></tr>';
};
$batchTable .= '</tbody></table>';
if (isset($batchFound)) {
	$stdOut .= $batchTable;
} else {
	$stdOut .= '<h2>No open batches from before ' . $sDate . '</h2>';
};
odbc_close($bConn);
require_once 'includes/footer.php'; ?>