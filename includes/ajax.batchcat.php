<?PHP 
require_once 'functions.php';
if (!fCanSeePublic(@$_SESSION['permissions']['page'][4] >= 100)) {
	$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
fSetDates($startDate, $endDate, 30);
if (isset($_GET)) {
	if (isset($_GET['filter'])) {
		if (count($_GET['filter']) == 7) {
			foreach ($_GET['filter'] as $key => $value) {
				if ($value != '') {
					switch ($key) {
						case 1:
							$where[$key] = 'campaign_id like ';
							break;
						case 2:
							$where[$key] = 'lot_id like ';
							break;
						case 3:
							$where[$key] = 'batch_id like ';
							break;
						case 4:
							$where[$key] = 'product_id like ';
							break;
						case 5:
							$where[$key] = 'recipe_id like ';
							break;
						case 6:
							$where[$key] = 'train_id like ';
							break;
						case 7:
							$where[$key] = 'department.name like ';
							break;
						case 8:
							$where[$key] = 'departmentequipment.name like ';
							break;
						case 9:
							$where[$key] = 'description like ';
							break;
						default:
							//error
							break;
					};
					$where[$key] .= '\'%' . $value . '%\'';
				};
			};
		} else {
			//error
		};
	} else {
		//error
	};
	if (!isset($_GET['column'])) {
		//error
	} else {
		switch ($_GET['column']) {
			case 1:
				$column = 'campaign_id';
				break;
			case 2:
				$column = 'lot_id';
				break;
			case 3:
				$column = 'batch_id';
				break;
			case 4:
				$column = 'product_id';
				break;
			case 5:
				$column = 'recipe_id';
				break;
			case 6:
				$column = 'train_id';
				break;
			case 7:
				$column = 'department.name';
				break;
			case 8:
				$column = 'departmentequipment.name';
				break;
			case 9:
				$column = 'description';
				break;
			default:
				//error
				break;
		};
	};
} else {
	//error
};
$bConn = odbc_connect('DRIVER={SQL Server};Server=INSQL2;Database=BatchHistory;', $dbUsername, $dbPassword);
$query = 'select distinct ' . $column . '
from (
select Campaign_ID, Lot_ID, Batch_ID, Product_ID, Recipe_ID , Train_ID, statustable.batch_log_id, log_open_dt, Log_Close_DT, CodeTable.Description from [oldbatchhistory].[dbo].[batchidlog]
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
select Campaign_ID, Lot_ID, Batch_ID, Product_ID, Recipe_ID , Train_ID, statustable.batch_log_id, log_open_dt, Log_Close_DT, CodeTable.Description from [batchhistory].[dbo].[batchidlog]
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
	$aQuery[] = 'DepartmentFK is null';
	$query .= ' where ' . fOrThemReturn($_SESSION['permissions']['department'], 100, 'departmentfk', $aQuery);
	$sep = ' and ';
} else {
	$sep = 'where ';
};
if (isset($where)) {
	foreach ($where as $value) {
		$query .= $sep . $value; 
		$sep = ' and ';
	};
};
$query .= $sep . '((Log_Open_DT between \'' . $startDate . '\' and \'' . date('Y-m-d H:i:s', strtotime($endDate) + 1) . '\'
or Log_Close_DT between \'' . $startDate . '\' and \'' . date('Y-m-d H:i:s', strtotime($endDate) + 1) . '\')
or ((Log_Open_DT < \'' . $startDate . '\' and Log_Open_DT < \'' . date('Y-m-d H:i:s', strtotime($endDate) + 1) . '\')
and ((Log_Close_DT > \'' . $startDate . '\' and Log_Close_DT > \'' . date('Y-m-d H:i:s', strtotime($endDate) + 1) . '\') or Log_Close_DT is null)))';
$query .= 'order by ' . $column . ' asc';
$data = odbc_exec($bConn, $query);
$out = '';
$sep = '';
while(odbc_fetch_row($data)) {
	$out .= $sep . '"' . odbc_result($data, 1) . '"';
	$sep = ', ';
};
print('[' . $out . ']');
?>