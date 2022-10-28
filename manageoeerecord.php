<?PHP $title = 'Manage OEE Records';
require_once 'includes/header.php';
if (!fCanSee(@$_SESSION['permissions']['page'][1] >= 100)) {
	$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
if (!isset($_GET['type'])) {
	$_SESSION['sqlMessage'] = 'You must complete the form!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
$hookReplace['calicon'] = '<a data-text="Date/Time Picker" href="#" class="menucontext"><span class="icon-calendar-empty icon-hover-hint icon-large"></span></a>';
$hookReplace['calform'] = fStandardCal('manageoeerecord', '[2014, 01, 27]');
fSetDates($startDate, $endDate, 7);
$queryMeta = 'select top 1 discipline.name, area.name, department.name, oeecategory.name, department.id, oeename.name, departmentequipment.name
from type
join Discipline on Discipline.ID = disciplinefk
join departmentequipment on departmentequipment.ID = departmentequipmentfk
join department on department.ID = departmentfk
join area on area.ID = AreaFK
join oeename on oeename.ID = oeenamefk
join oeecategory on oeecategory.ID = oeecategoryfk
where type.id = ' . $_GET['type'];
$dataMeta = odbc_exec($conn, $queryMeta);
odbc_fetch_row($dataMeta);
if (!fCanSee(@$_SESSION['permissions']['department'][odbc_result($dataMeta, 5)] >= 100)) {
	$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
$oeeRecords = '<h2>Viewing ' . odbc_result($dataMeta, 6) . ' records between ' . $startDate . ' and ' . $endDate . '</h2><h3>Reason Properties</h3>
<table class="records"><thead><tr><th>Discipline</th><th>Area</th><th>Department</th><th>Equipment</th><th>Category</th></tr></thead><tbody><tr class="evenRow"><td>' . odbc_result($dataMeta, 1) . '</td><td>' .  odbc_result($dataMeta, 2) . '</td><td>' .  odbc_result($dataMeta, 3) . '</td><td>' . odbc_result($dataMeta, 7) . '</td><td>' . odbc_result($dataMeta, 4) . '</td></tr></tbody></table>';
$queryRecords = 'SELECT oeename.Name, StartDateTime, duration, enddatetime, Comment, records.ID
FROM records
join Type on Type.ID = typefk
join oeename on oeename.ID = oeenamefk
where ((startdatetime between \'' . $startDate . '\' and \'' . $endDate . '\' or enddatetime between \'' . $startDate . '\' and \'' . $endDate . '\') or ((startdatetime < \'' . $startDate . '\' and startdatetime < \'' . date('Y-m-d H:i:s', strtotime($endDate) + 1) . '\') 
and (enddatetime > \'' . $startDate . '\' and enddatetime > \'' . date('Y-m-d H:i:s', strtotime($endDate) + 1) . '\')))
and type.id = ' . $_GET['type'] . ' order by startdatetime asc, enddatetime asc';
$dataRecords = odbc_exec($conn, $queryRecords);
$row = 0;
$cols = 5;
$oeeRecords .= '<h3>Records</h3><table class="records"><thead><th>Reason</th><th>Start Date/Time</th><th>Duration</th><th>End Date/Time</th><th>Comment</th>'; 
if (fCanSee(@$_SESSION['permissions']['department'][odbc_result($dataMeta, 5)] >= 200)) {
	$oeeRecords .= '<th>Edit</th>';
	$cols++;
};
if (fCanSee(@$_SESSION['permissions']['department'][odbc_result($dataMeta, 5)] >= 300)) {
	$oeeRecords .= '<th>Delete</th>';
	$cols++;
};
$oeeRecords .= '</thead><tbody>';
while (odbc_fetch_row($dataRecords)) {
	if ($row % 2 == 0) {
		$tr = 'oddRow';
	} else {
		$tr = 'evenRow';
	};
	$row++;
	$oeeRecords .= '<tr class="' . $tr . '"><td>' . odbc_result($dataRecords, 1) . '</td><td>' . substr(odbc_result($dataRecords, 2), 0 , -4) . '</td><td>' . fToTime(odbc_result($dataRecords, 3)) . '</td><td>' . substr(odbc_result($dataRecords, 4), 0 , -4) . '</td>';
	if (odbc_result($dataRecords, 5)) {
		$oeeRecords .= '<td><a href="#" class="toggle-table-sorter">Show</a></td>';
	} else {
		$oeeRecords .= '<td class="emptyCell">None</td>';
	};
	if (fCanSee(@$_SESSION['permissions']['department'][odbc_result($dataMeta, 5)] >= 200)) {
		$oeeRecords .= '<td><form method="get" action="selectoeerecord.php"><input type="hidden" name="edit" value="' . odbc_result($dataRecords, 6) . '" /><input type="submit" value="Edit!" /></form></td>';
	};
	if (fCanSee(@$_SESSION['permissions']['department'][odbc_result($dataMeta, 5)] >= 300)) {
		$oeeRecords .= '<td><form method="post" action="includes/changeoeerecord.php"><input type="hidden" name="action" value="delete"/><input type="hidden" name="id" value="' . odbc_result($dataRecords, 6) . '" /><input type="submit" value="Delete!" /></form></td>';
	};
	$oeeRecords .= '</tr>';
	if (odbc_result($dataRecords, 5)) {
		$oeeRecords .= '<tr class="' . $tr . ' hiddenrow"><td colspan="' . $cols . '">' . odbc_result($dataRecords, 5) . '</td></tr>';
	};
};
if ($row % 2 == 0) {
	$tr = 'oddRow';
} else {
	$tr = 'evenRow';
};
if ($row >= 1) {
	$stdOut .= $oeeRecords;
} else {
	$stdOut .= '<h2>No data available</h2>';
};
$stdOut .= '</tbody></table><script type="text/javascript">showDisc(".records");</script>';
$hookReplace['help'] .= $helptext['default7'] . '<a href="#">Editing Records</a><div>Editing records returns you to the record selection page with all of the data automatically populated.</div><a href="#">Deleting Records</a><div>Deleting records removes the record fro mthe database, it is non-reversable.</div>';
require_once 'includes/footer.php'; ?>