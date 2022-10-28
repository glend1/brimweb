<?PHP 
require_once 'functions.php';
if (!fCanSeePublic(@$_SESSION['permissions']['page'][1] >= 100)) {
	$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
if (isset($_GET)) {
	if (isset($_GET['filter'])) {
		if (count($_GET['filter']) == 8) {
			foreach ($_GET['filter'] as $key => $value) {
				if ($value != '') {
					switch ($key) {
						case 1:
							$where[$key] = 'oeecategory.name like ';
							break;
						case 2:
							$where[$key] = 'discipline.name like ';
							break;
						case 3:
							$where[$key] = 'departmentequipment.name like ';
							break;
						case 4:
							$where[$key] = 'oeename.name like ';
							break;
						case 5:
							$where[$key] = 'area.name like ';
							break;
						case 6:
							$where[$key] = 'department.name like ';
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
				$column = 'oeecategory.name';
				break;
			case 2:
				$column = 'discipline.name';
				break;
			case 3:
				$column = 'departmentequipment.name';
				break;
			case 4:
				$column = 'oeename.name';
				break;
			case 5:
				$column = 'area.name';
				break;
			case 6:
				$column = 'department.name';
				break;
			default:
				//error
				break;
		};
	};
} else {
	//error
};
$query = 'select distinct ' . $column . '
from records
left join type on type.id = records.typefk
left join OEECategory on OEECategoryFK = oeecategory.id
left join discipline on disciplinefk = discipline.id
left join departmentequipment on departmentequipmentfk = departmentequipment.id
left join oeename on oeenamefk = oeename.id
left join area on areafk = area.id
left join department on departmentfk = department.id ';
if (@$_SESSION['id'] != 1 && isset($_SESSION['id'])) {
	$aQuery[] = 'Department.id is null';
	$query .= ' where ' . fOrThemReturn($_SESSION['permissions']['department'], 100, 'department.id', $aQuery);
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
$query .= 'order by ' . $column . ' asc';
$data = odbc_exec($conn, $query);
$out = '';
$sep = '';
while(odbc_fetch_row($data)) {
	$out .= $sep . '"' . trim(odbc_result($data, 1)) . '"';
	$sep = ', ';
};
print('[' . $out . ']');
?>