<?PHP 
require_once 'functions.php';
if (!fCanSeePublic(@$_SESSION['permissions']['page'][4] >= 100)) {
	$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
fSetDates($startDate, $endDate, 30);
$bConn = odbc_connect('DRIVER={SQL Server};Server=INSQL2;Database=BatchHistory;', $dbUsername, $dbPassword);
$page = 1;
if (isset($_GET['page'])) {
	$page += $_GET['page'];
};
$items = 100;
$queryBatch = 'select * from (select top ' . $items . ' * 
from (
SELECT TOP ' . ($page * $items) . ' Campaign_ID, Lot_ID, Batch_ID, Product_ID, Recipe_ID , Train_ID, departmentfk, batch_log_id, log_open_dt, dbname, departmentequipmentfk, department.name as departmentname, departmentequipment.name as equipmentname, datediff(ss, log_open_dt, log_close_dt) as duration, description
FROM (
select Campaign_ID, Lot_ID, Batch_ID, Product_ID, Recipe_ID , Train_ID, statustable.batch_log_id, log_open_dt, Log_Close_DT, CodeTable.Description, \'oldbatchhistory\' as dbname from [oldbatchhistory].[dbo].[batchidlog]
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
union 
select Campaign_ID, Lot_ID, Batch_ID, Product_ID, Recipe_ID , Train_ID, statustable.batch_log_id, log_open_dt, Log_Close_DT, CodeTable.Description, \'batchhistory\' as dbname from [batchhistory].[dbo].[batchidlog]
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
)as BatchIdLog
left join [plantavail].[dbo].[train] on train_id = train
left join [plantavail].[dbo].[department] as department on departmentfk = department.id
left join (select id, name from [plantavail].[dbo].[departmentequipment]) as departmentequipment on departmentequipmentfk = departmentequipment.id ';
if (@$_SESSION['id'] != 1 && isset($_SESSION['id'])) {
	$aQueryBatch[] = 'DepartmentFK is null';
	$queryBatch .= ' where ' . fOrThemReturn($_SESSION['permissions']['department'], 100, 'departmentfk', $aQueryBatch);
	$sep = ' and ';
} else {
	$sep = ' where ';
};
if (isset($_GET['filter'])) {
	if (!empty($_GET['filter'])) {
		foreach ($_GET['filter'] as $key => $value) {
			switch ($key) {
				case 1:
					$queryBatch .= ' ' . $sep . ' Campaign_ID like \'%' . $value . '%\'';
					break;
				case 2:
					$queryBatch .= ' ' . $sep . ' Lot_ID like \'%' . $value . '%\'';
					break;
				case 3:
					$queryBatch .= ' ' . $sep . ' Batch_ID like \'%' . $value . '%\'';
					break;
				case 4:
					$queryBatch .= ' ' . $sep . ' Product_ID like \'%' . $value . '%\'';
					break;
				case 5:
					$queryBatch .= ' ' . $sep . ' Recipe_ID like \'%' . $value . '%\'';
					break;
				case 6:
					$queryBatch .= ' ' . $sep . ' Train_ID like \'%' . $value . '%\'';
					break;
				case 7:
					$queryBatch .= ' ' . $sep . ' department.name like \'%' . $value . '%\'';
					break;
				case 8:
					$queryBatch .= ' ' . $sep . ' departmentequipment.name like \'%' . $value . '%\'';
					break;
				case 9:
					$queryBatch .= ' ' . $sep . ' description like \'%' . $value . '%\'';
					break;
			};
			$sep = ' and ';
		};
	};
};
$queryBatch .= $sep . '((Log_Open_DT between \'' . $startDate . '\' and \'' . date('Y-m-d H:i:s', strtotime($endDate) + 1) . '\'
or Log_Close_DT between \'' . $startDate . '\' and \'' . date('Y-m-d H:i:s', strtotime($endDate) + 1) . '\')
or ((Log_Open_DT < \'' . $startDate . '\' and Log_Open_DT < \'' . date('Y-m-d H:i:s', strtotime($endDate) + 1) . '\')
and ((Log_Close_DT > \'' . $startDate . '\' and Log_Close_DT > \'' . date('Y-m-d H:i:s', strtotime($endDate) + 1) . '\') or Log_Close_DT is null)))';
$queryBatch .= ' order by log_open_dt desc) as temp
order by log_open_dt asc) as temp2
order by log_open_dt desc';
$dataBatch = odbc_exec($bConn, $queryBatch);
//$out['headers'] = ['Start Date/Time', 'End Date/Time', 'Campaign', 'Lot', 'Batch', 'Product', 'Recipe', 'Train'];
while(odbc_fetch_row($dataBatch)) {
	$departmentURL = '';
	if (odbc_result($dataBatch, 7)) {
		$departmentURL = 'department=' . odbc_result($dataBatch, 7) . '&';
	};
	$equipmentUrl = '';
	if (odbc_result($dataBatch, 11)) {
		$equipmentUrl = 'equipment=' . odbc_result($dataBatch, 11) . '&';
	};
	if (odbc_result($dataBatch, 13)) {
		$equipment = odbc_result($dataBatch, 13);
	} else {
		$equipment = '';
	};
	$out['rows'][] = [substr(odbc_result($dataBatch, 9), 0, -4),
		//substr(odbc_result($dataBatch, 2), 0, -4),
		odbc_result($dataBatch, 1),
		odbc_result($dataBatch, 2),
		'<a href="batchsummary.php?' . $equipmentUrl . $departmentURL . 'dbname=' . odbc_result($dataBatch, 10) . '&batch=' . odbc_result($dataBatch, 8) . '">' . odbc_result($dataBatch, 3) . '</a>',
		odbc_result($dataBatch, 4), 
		'<a href="batch.php?' . $equipmentUrl . $departmentURL . 'recipe=' . urlencode(odbc_result($dataBatch, 5)) . '">' . odbc_result($dataBatch, 5) . '</a>',
		'<a href="batchrecipe.php?' . $equipmentUrl . $departmentURL . '">' . odbc_result($dataBatch, 6) . '</a>',
		odbc_result($dataBatch, 12),
		$equipment,
		odbc_result($dataBatch, 15)];
	$out['data'][] = [strtotime(odbc_result($dataBatch, 9)) * 1000, odbc_result($dataBatch, 14) + 0, odbc_result($dataBatch, 1) . '/' . odbc_result($dataBatch, 2) . '/' . odbc_result($dataBatch, 3) . ' (' . fToTime(odbc_result($dataBatch, 14)) . ')', 'batchsummary.php?' . $equipmentUrl . $departmentURL . 'dbname=' . odbc_result($dataBatch, 10) . '&batch=' . odbc_result($dataBatch, 8)];
};
$queryBatchTot = 'select count(*) 
from (select Campaign_ID, Lot_ID, Batch_ID, Product_ID, Recipe_ID , Train_ID, statustable.batch_log_id, log_open_dt, log_close_dt, CodeTable.Description from [oldbatchhistory].[dbo].[batchidlog]
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
union 
select Campaign_ID, Lot_ID, Batch_ID, Product_ID, Recipe_ID , Train_ID, statustable.batch_log_id, log_open_dt, log_close_dt, CodeTable.Description from [batchhistory].[dbo].[batchidlog]
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
)as BatchIdLog
left join [plantavail].[dbo].[train] on train_id = train
left join [plantavail].[dbo].[department] as department on departmentfk = department.id
left join (select id, name from [plantavail].[dbo].[departmentequipment]) as departmentequipment on departmentequipmentfk = departmentequipment.id ';
if (@$_SESSION['id'] != 1 && isset($_SESSION['id'])) {
	$aQueryBatchTot[] = 'DepartmentFK is null';
	$queryBatchTot .= ' where ' . fOrThemReturn($_SESSION['permissions']['department'], 100, 'departmentfk', $aQueryBatchTot);
	$sep = ' and ';
} else {
	$sep = ' where ';
};
if (isset($_GET['filter'])) {
	if (!empty($_GET['filter'])) {
		foreach ($_GET['filter'] as $key => $value) {
			switch ($key) {
				case 1:
					$queryBatchTot .= ' ' . $sep . ' Campaign_ID like \'%' . $value . '%\'';
					break;
				case 2:
					$queryBatchTot .= ' ' . $sep . ' Lot_ID like \'%' . $value . '%\'';
					break;
				case 3:
					$queryBatchTot .= ' ' . $sep . ' Batch_ID like \'%' . $value . '%\'';
					break;
				case 4:
					$queryBatchTot .= ' ' . $sep . ' Product_ID like \'%' . $value . '%\'';
					break;
				case 5:
					$queryBatchTot .= ' ' . $sep . ' Recipe_ID like \'%' . $value . '%\'';
					break;
				case 6:
					$queryBatchTot .= ' ' . $sep . ' Train_ID like \'%' . $value . '%\'';
					break;
				case 7:
					$queryBatchTot .= ' ' . $sep . ' department.name like \'%' . $value . '%\'';
					break;
				case 8:
					$queryBatchTot .= ' ' . $sep . ' departmentequipment.name like \'%' . $value . '%\'';
					break;
				case 9:
					$queryBatchTot .= ' ' . $sep . ' description like \'%' . $value . '%\'';
					break;
			};
			$sep = ' and ';
		};
	};
};
$queryBatchTot .= $sep . '((Log_Open_DT between \'' . $startDate . '\' and \'' . date('Y-m-d H:i:s', strtotime($endDate) + 1) . '\'
or Log_Close_DT between \'' . $startDate . '\' and \'' . date('Y-m-d H:i:s', strtotime($endDate) + 1) . '\')
or ((Log_Open_DT < \'' . $startDate . '\' and Log_Open_DT < \'' . date('Y-m-d H:i:s', strtotime($endDate) + 1) . '\')
and ((Log_Close_DT > \'' . $startDate . '\' and Log_Close_DT > \'' . date('Y-m-d H:i:s', strtotime($endDate) + 1) . '\') or Log_Close_DT is null)))';
$out['query'] = $queryBatch;
$dataBatchTot = odbc_exec($bConn, $queryBatchTot);
if (odbc_fetch_row($dataBatchTot)) {
	$out['total_rows'] = odbc_result($dataBatchTot, 1);
};
print(json_encode($out));
?>