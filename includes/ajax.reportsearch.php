<?PHP 
require_once 'functions.php';
if (!fCanSeePublic(@$_SESSION['permissions']['page'][8] >= 100)) {
	$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
$page = 1;
if (isset($_GET['page'])) {
	$page += $_GET['page'];
};
$items = 20;
$sep = ' where ';
$strSearchParam = '';
if (isset($_GET['filter'])) {
	if (!empty($_GET['filter'])) {
		$searchParam = explode(',', $_GET['filter'][0]);
		foreach ($searchParam as $value) {
			if ($trim = trim($value)) {
				$strSearchParam .= $sep . 'ReportCalendarRange.name like \'%' . $trim . '%\'';
				$sep = ' or ';
			};
		};
	};
};
$querySearch = 'select top ' . $items . ' * 
from (
SELECT TOP ' . ($page * $items) . ' ReportCalendarRange.id, ReportCalendarRange.name as range, StartDate, EndDate, ReportCalendarType.name as type, ReportCalendarRepeat.name as repeat, value, cast(stuff((select \',\' + cast(value as char(1)) from reportcalendarrangevalue where ReportCalendarRangeFK = ReportCalendarRange.id for xml path(\'\')),1,1,\'\') as varchar(max)) as days
from ReportCalendarRange 
join ReportCalendarTypeRepeat on ReportCalendarTypeRepeat.id = ReportCalendarTypeRepeatfk 
join ReportCalendarType on ReportCalendarType.ID = ReportCalendarTypeFK
join reportcalendarrepeat on ReportCalendarRepeat.id = reportcalendarrepeatfk ' . $strSearchParam . ' 
order by ReportCalendarRange.name desc) as temp
order by range asc';
$dataSearch = odbc_exec($conn, $querySearch);
while(odbc_fetch_row($dataSearch)) {
	if (odbc_result($dataSearch, 4)) {
		$end = substr(odbc_result($dataSearch, 4), 0, 10);
	} else {
		$end = 'Never';
	};
	$out['rows'][] = ['<a href="viewreport.php?id=' . odbc_result($dataSearch, 1) . '">' . odbc_result($dataSearch, 2) . '</a>', substr(odbc_result($dataSearch, 3), 0, 10), $end, fFrequencyTranslate (odbc_result($dataSearch, 5), odbc_result($dataSearch, 6), odbc_result($dataSearch, 7), odbc_result($dataSearch, 8))];
};
$querySearchTot = 'select count(*)
from ReportCalendarRange ' . $strSearchParam;
$dataSearchTot = odbc_exec($conn, $querySearchTot);
if (odbc_fetch_row($dataSearchTot)) {
	$out['total_rows'] = odbc_result($dataSearchTot, 1);
};
odbc_close($conn);
print(json_encode($out));
?>