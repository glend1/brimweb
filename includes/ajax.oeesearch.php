<?PHP 
require_once 'functions.php';
if (!fCanSeePublic(@$_SESSION['permissions']['page'][1] >= 100)) {
	$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
$page = 1;
if (isset($_GET['page'])) {
	$page += $_GET['page'];
};
$items = 20;
$queryOee = 'select * from (select top ' . $items . ' * 
from (
SELECT TOP ' . ($page * $items) . 'StartDateTime, OEECategory.Name as cat, Discipline.name as disc, DepartmentEquipment.name as equip, oeename.name as name, area.name as area, department.name as department
from records
left join type on type.id = records.typefk
left join OEECategory on OEECategoryFK = oeecategory.id
left join discipline on disciplinefk = discipline.id
left join departmentequipment on departmentequipmentfk = departmentequipment.id
left join oeename on oeenamefk = oeename.id
left join area on areafk = area.id
left join department on departmentfk = department.id ';
if (@$_SESSION['id'] != 1 && isset($_SESSION['id'])) {
	$aqueryOee[] = 'Department.id is null';
	$queryOee .= ' where ' . fOrThemReturn($_SESSION['permissions']['department'], 100, 'department.id', $aqueryOee);
	$sep = 'and';
} else {
	$sep = 'where';
};
if (isset($_GET['filter'])) {
	if (!empty($_GET['filter'])) {
		foreach ($_GET['filter'] as $key => $value) {
			switch ($key) {
				case 1:
					$queryOee .= ' ' . $sep . ' oeecategory.name like \'%' . $value . '%\'';
					break;
				case 2:
					$queryOee .= ' ' . $sep . ' discipline.name like \'%' . $value . '%\'';
					break;
				case 3:
					$queryOee .= ' ' . $sep . ' departmentequipment.name like \'%' . $value . '%\'';
					break;
				case 4:
					$queryOee .= ' ' . $sep . ' oeename.name like \'%' . $value . '%\'';
					break;
				case 5:
					$queryOee .= ' ' . $sep . ' area.name like \'%' . $value . '%\'';
					break;
				case 6:
					$queryOee .= ' ' . $sep . ' department.name like \'%' . $value . '%\'';
					break;
			};
			$sep = 'and';
		};
	};
};
$queryOee .= ' order by startdatetime desc) as temp
order by startdatetime asc) as temp2
order by startdatetime desc';
$dataOee = odbc_exec($conn, $queryOee);
//$out['headers'] = ['Start Date/Time', 'End Date/Time', 'Campaign', 'Lot', 'Batch', 'Product', 'Recipe', 'Train'];
while(odbc_fetch_row($dataOee)) {
	$out['rows'][] = [substr(odbc_result($dataOee, 1), 0, -4), odbc_result($dataOee, 2), odbc_result($dataOee, 3), odbc_result($dataOee, 4),  odbc_result($dataOee, 5), odbc_result($dataOee, 6), odbc_result($dataOee, 7)];
};
$queryOeeTot = 'select count(*) 
from records
left join type on type.id = records.typefk
left join OEECategory on OEECategoryFK = oeecategory.id
left join discipline on disciplinefk = discipline.id
left join departmentequipment on departmentequipmentfk = departmentequipment.id
left join oeename on oeenamefk = oeename.id
left join area on areafk = area.id
left join department on departmentfk = department.id ';
if (@$_SESSION['id'] != 1 && isset($_SESSION['id'])) {
	$aqueryOeeTot[] = 'Department.id is null';
	$queryOeeTot .= ' where ' . fOrThemReturn($_SESSION['permissions']['department'], 100, 'department.id', $aqueryOeeTot);
	$sep = 'and';
} else {
	$sep = 'where';
};
if (isset($_GET['filter'])) {
	if (!empty($_GET['filter'])) {
		foreach ($_GET['filter'] as $key => $value) {
			switch ($key) {
				case 1:
					$queryOeeTot .= ' ' . $sep . ' oeecategory.name like \'%' . $value . '%\'';
					break;
				case 2:
					$queryOeeTot .= ' ' . $sep . ' discipline.name like \'%' . $value . '%\'';
					break;
				case 3:
					$queryOeeTot .= ' ' . $sep . ' departmentequipment.name like \'%' . $value . '%\'';
					break;
				case 4:
					$queryOeeTot .= ' ' . $sep . ' oeename.name like \'%' . $value . '%\'';
					break;
				case 5:
					$queryOeeTot .= ' ' . $sep . ' area.name like \'%' . $value . '%\'';
					break;
				case 6:
					$queryOeeTot .= ' ' . $sep . ' department.name like \'%' . $value . '%\'';
					break;
			};
			$sep = 'and';
		};
	};
};
$dataOeeTot = odbc_exec($conn, $queryOeeTot);
if (odbc_fetch_row($dataOeeTot)) {
	$out['total_rows'] = odbc_result($dataOeeTot, 1);
};
print(json_encode($out));
?>