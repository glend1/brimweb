<?PHP 
$title = 'Suspicious OEE Data';
require_once 'includes/header.php';
if (!fCanSee(@$_SESSION['permissions']['page'][1] >= 300)) {
	$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
$queryOee = 'select top 20 rectemp.id, duration, startdatetime, enddatetime, oeecategory.name, discipline.name, departmentequipment.name, oeename.name
from (select *
from (select top 20 id, typefk, duration, startdatetime, enddatetime, \'a\' as sort
from records 
where duration < 0
order by duration asc) as temp1
union
select * 
from (select top 20 id, typefk, duration, startdatetime, enddatetime, \'b\' as sort
from records
order by duration desc) as temp2) as rectemp
join type on typefk = type.id
join departmentequipment on departmentequipmentfk = departmentequipment.id
join discipline on disciplinefk = discipline.id
join oeecategory on oeecategoryfk = oeecategory.id
join oeename on oeenamefk = oeename.id 
join area on areafk = area.id';
if (@$_SESSION['id'] != 1 && isset($_SESSION['id'])) {
	$aqueryOee[] = 'departmentequipment.departmentfk is null';
	$queryOee .= ' where ' . fOrThemReturn($_SESSION['permissions']['department'], 100, 'departmentequipment.departmentfk', $aqueryOee);
};
$queryOee .= ' order by sort asc, duration desc';
$dataOee = odbc_exec($conn, $queryOee);
$stdOut .= '<table class="records"><thead><th>Duration</th><th>Start Date/Time</th><th>Category</th><th>Discipline</th><th>Equipment</th><th>Reason</th><th>Action</th></thead><tbody>';
$row = 0;
while(odbc_fetch_row($dataOee)) {
	if ($row % 2 == 0) {
		$tr = 'oddRow';
	} else {
		$tr = 'evenRow';
	};
	$stdOut .= '<tr class="' . $tr . '"><td>' . fToTime(odbc_result($dataOee, 2)) . '</td><td>' . substr(odbc_result($dataOee, 4), 0 , -4) . '</td><td>' . odbc_result($dataOee, 5) . '</td><td>' . odbc_result($dataOee, 6) . '</td><td>' . odbc_result($dataOee, 7) . '</td><td>' . odbc_result($dataOee, 8) . '</td><td><form method="get" action="selectoeerecord.php"><input type="hidden" name="edit" value="' . odbc_result($dataOee, 1) . '" /><input type="submit" value="Edit!" /></form><form method="post" action="includes/changeoeerecord.php"><input type="hidden" name="action" value="delete"/><input type="hidden" name="id" value="' . odbc_result($dataOee, 1) . '" /><input type="submit" value="Delete!" /></form></td></tr>';
	$row++;
};
$stdOut .= '<tbody></table>';
//$hookReplace['help'] = $helptext['search'] . $helptext['update'] . $helptext['delete'] . $helptext['add'];
require_once 'includes/footer.php'; ?>