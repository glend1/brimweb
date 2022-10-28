<?PHP
require_once 'functions.php';
if (!fCanSee(@$_SESSION['permissions']['page'][4] >= 300)) {
	$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
if (!isset($_POST['id'])) {
	$_SESSION['sqlMessage'] = 'No Batch Identified!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
$bConn = odbc_connect('DRIVER={SQL Server};Server=INSQL2;Database=BatchHistory;', $dbUsername, $dbPassword);
$queryPermission = 'select top 1 log_open_dt, bend, log_close_dt
from BatchIdLog
left join (select batch_log_id, max(datetime) as bend from batchdetail group by Batch_Log_ID) as bend on bend.Batch_Log_ID = BatchIdLog.Batch_Log_ID
left join [plantavail].[dbo].[train] on train_id = train 
where batchidlog.Batch_Log_ID = \'' . $_POST['id'] . '\'';
if (@$_SESSION['id'] != 1 && isset($_SESSION['id'])) {
	$aQueryBatch[] = 'DepartmentFK is null';
	$queryPermission .= ' and ' . fOrThemReturn($_SESSION['permissions']['department'], 300, 'departmentfk', $aQueryBatch);
};
$dataPermission = odbc_exec($bConn, $queryPermission);
if (odbc_fetch_row($dataPermission)) {
	if (!odbc_result($dataPermission, 3)) {
		if (odbc_result($dataPermission, 2)) {
			$bEnd = odbc_result($dataPermission, 2);
		} elseif (odbc_result($dataPermission, 1)) {
			$bEnd = odbc_result($dataPermission, 1);
		};
		$updateBatch = 'update batchidlog set Log_Close_DT = \'' . $bEnd . '\' where batch_log_id = \'' . $_POST['id'] . '\'';
		odbc_exec($bConn, $updateBatch);
		$_SESSION['sqlMessage'] = 'Batch Closed!';
		$_SESSION['uiState'] = 'active';
		fRedirect();
	} else {
		$_SESSION['sqlMessage'] = 'Batch Already Closed!';
		$_SESSION['uiState'] = 'error';
		fRedirect();
	};
} else {
	$_SESSION['sqlMessage'] = 'Alarm not found!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
odbc_close($bConn); ?>