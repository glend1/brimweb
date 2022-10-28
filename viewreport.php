<?PHP
$title = 'Report';
require_once 'includes/header.php';
if (!fCanSeePublic(@$_SESSION['permissions']['page'][8] >= 100)) {
	$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
if (!isset($_GET['id'])) {
	$_SESSION['sqlMessage'] = 'No Report selected!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
} else {
	if (isset($_GET['date'])) {
		$date = $_GET['date'];
	};
};
$queryReportParam = 'SELECT top 1 ReportCalendarRange.name as range, StartDate, EndDate, ReportCalendarType.name as type, ReportCalendarType.id as typeid, ReportCalendarRepeat.name as repeat, ReportCalendarRepeat.id as repeatid, value, 
cast(stuff((select \',\' + cast(value as char(1)) from reportcalendarrangevalue where ReportCalendarRangeFK = ReportCalendarRange.id for xml path(\'\')),1,1,\'\') as varchar(max)) as days, reportcalendarfunctionname.name as func, argjson
from ReportCalendarRange 
join ReportCalendarTypeRepeat on ReportCalendarTypeRepeat.id = ReportCalendarTypeRepeatfk 
join ReportCalendarType on ReportCalendarType.ID = ReportCalendarTypeFK
join reportcalendarfunctionname on reportcalendarfunctionname.id = reportcalendarfunctionnamefk
join reportcalendarrepeat on ReportCalendarRepeat.id = reportcalendarrepeatfk
where ReportCalendarRange.id = ' . $_GET['id'];
$dataReportParam = odbc_exec($conn, $queryReportParam);
if (!($ReportParam = odbc_fetch_array($dataReportParam, 1))) {
	$_SESSION['sqlMessage'] = 'Report not found!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
} else {
	$translation = fFrequencyTranslate ($ReportParam['type'], $ReportParam['repeat'], $ReportParam['value'], $ReportParam['days']);
	if ($ReportParam['days']) {
		$days = explode(',', $ReportParam['days']);
		$aDays = '';
		foreach ($days as $day) {
			if (strlen($day) > 0) {
				$aDays[] = $day;
			};
		};
		$ReportParam['days'] = $aDays;
	};
	if ($ReportParam['argjson']) {
		$ReportParam['argjson'] = json_decode($ReportParam['argjson'], true);
	}
	$jsonReport = json_encode($ReportParam, true);
};
function fDirPdf() {
	$out = '
		//console.log(report);
		dates = report.split("-");
		thisDate = new Date(dates[0], dates[1] - 1, dates[2]);
		format = $.format.date(thisDate, json.argjson.filename);
		var url = "http://brimweb.brimcontrols.local/static/" + json.argjson.folder + "/" + format + ".pdf";
		$("iframe#reportviewer").prop("src", url);';
	return $out;
};
if (isset($_GET['date'])) {
	$selected = new DateTime(($_GET['date']));
} else {
	$selected = new DateTime(($localTime));
};
$selected = explode(',', $selected->format('Y,m,d'));
$hookReplace['calicon'] = '<a data-text="Date/Time Picker" href="#" class="menucontext"><span class="icon-calendar-empty icon-hover-hint icon-large"></span></a>';
$hookReplace['calform'] = '<form id="datetimepicker" action="viewreport.php" method="get">
		<input type="submit" id="go" value="Use Date" />' . fQueryString(['exclude' => ['date'], 'output' => 'hidden']) . '
		<input value="defaultdate" type="hidden" id="datefield" name="date" />
		<div id="date"></div><div id="definereport">' . $translation . '</div>
		</form>
		<script type="text/javascript">
			var sMonth, eMonth, availableDates = new Array(), json = ' . $jsonReport . ', startDate = new Date(json.StartDate.substring(0,4), json.StartDate.substring(5,7) - 1, json.StartDate.substring(8,10)), iMonth = new Date(json.StartDate.substring(0,4), json.StartDate.substring(5,7) - 1, json.StartDate.substring(8,10));
			function available(reportDate) {
				dmy = reportDate.getDate() + "-" + (reportDate.getMonth()+1) + "-" + reportDate.getFullYear();
				if ($.inArray(dmy, availableDates) != -1) {
					return [true,"cal-avail"];
				} else {
					return [false,"cal-unavail"];
				};
			};
			if (json.EndDate) {
				var endDate = new Date(json.EndDate.substring(0,4), json.EndDate.substring(5,7) - 1, json.EndDate.substring(8,10));
			} else {
				var endDate = new Date();
				var endDate = new Date(endDate.getFullYear(), endDate.getMonth(), endDate.getDate());
			};
			availableDates = new Array();
			iMonth = new Date(json.StartDate.substring(0,4), json.StartDate.substring(5,7) - 1, json.StartDate.substring(8,10));
			while (iMonth < endDate) {
				switch (json.repeatid - 0) {
					case 3:
						//console.log("week day");
						for (i = 0; i < json.days.length; i++) {
							interval = (json.days[i] - 0 + 1) - iMonth.getDay();
								if (interval < 0) {
									interval += 7;
								};
							iMonth.setDate(iMonth.getDate() + interval);
							current = iMonth.getFullYear() + "-" + (iMonth.getMonth() + 1) + "-" + iMonth.getDate();
							availableDates.push(iMonth.getDate() + "-" + (iMonth.getMonth() + 1) + "-" + iMonth.getFullYear());
						};
					case 2:
						//console.log("every");
						switch (json.typeid - 0) {
							case 1:
								//weekly
								current = iMonth.getFullYear() + "-" + (iMonth.getMonth() + 1) + "-" + iMonth.getDate();
								availableDates.push(iMonth.getDate() + "-" + (iMonth.getMonth() + 1) + "-" + iMonth.getFullYear());
								iMonth.setDate(iMonth.getDate() + (json.value * 7));
								break;
							case 2:
								//monthly
								current = iMonth.getFullYear() + "-" + (iMonth.getMonth() + 1) + "-" + iMonth.getDate();
								availableDates.push(iMonth.getDate() + "-" + (iMonth.getMonth() + 1) + "-" + iMonth.getFullYear());
								iMonth.setMonth(iMonth.getMonth() + (json.value - 0));
								break;
							case 3:
								//yearly
								current = iMonth.getFullYear() + "-" + (iMonth.getMonth() + 1) + "-" + iMonth.getDate();
								availableDates.push(iMonth.getDate() + "-" + (iMonth.getMonth() + 1) + "-" + iMonth.getFullYear());
								iMonth.setFullYear(iMonth.getFullYear() + (json.value - 0));
								break;
							case 6:
								//daily
								current = iMonth.getFullYear() + "-" + (iMonth.getMonth() + 1) + "-" + iMonth.getDate();
								availableDates.push(iMonth.getDate() + "-" + (iMonth.getMonth() + 1) + "-" + iMonth.getFullYear());
								iMonth.setDate(iMonth.getDate() + (json.value - 0));
								break;
							default:
								break;
						};
						break;
					case 6:
						//console.log("on day");
						iMonthDays = daysInMonth((iMonth.getMonth() + 1), iMonth.getFullYear());
						if (iMonthDays < json.value) {
							iMonth.setDate(iMonthDays);	
						} else {
							iMonth.setDate(json.value);
						};
						current = iMonth.getFullYear() + "-" + (iMonth.getMonth() + 1) + "-" + iMonth.getDate();
						availableDates.push(iMonth.getDate() + "-" + (iMonth.getMonth() + 1) + "-" + iMonth.getFullYear());
						iNextMonthDays = daysInMonth((iMonth.getMonth() + 2), iMonth.getFullYear());
						if (iNextMonthDays < json.value) {
							iMonth.setDate(iNextMonthDays);
						};
						iMonth.setMonth(iMonth.getMonth() + 1);
						break;
					case 4:
						//console.log("single");
						current = iMonth.getFullYear() + "-" + (iMonth.getMonth() + 1) + "-" + iMonth.getDate();
						availableDates.push((json.value - 0) + "-" + (iMonth.getMonth() + 1) + "-" + iMonth.getFullYear());
						iMonth = endDate;
						break;
					default:
						//console.log("huh?");
						iMonth.setDate(iMonth.getDate() + 1);
						current = iMonth.getFullYear() + "-" + (iMonth.getMonth() + 1) + "-" + iMonth.getDate();
						availableDates.push(iMonth.getDate() + "-" + (iMonth.getMonth() + 1) + "-" + iMonth.getFullYear());
						break;
				};
			};
			console.log(availableDates);
			$("#date").datepicker({
				showOtherMonths: true,
				selectOtherMonths: true,
				changeMonth: true,
				changeYear: true,
				altField: "#datefield",
				altFormat: \'yy-mm-dd\',
				minDate: startDate,
				maxDate: endDate,
				showWeek: 1,
				firstDay: 1,
				beforeShowDay: available
			});
			$("#go").click(function() {
				getReport($("#datefield").val());
				return false;
			});
		</script>';
if (isset($ReportParam['func'])) {
	$stdOut .= '<h2>' . $ReportParam['range'] . '</h2><iframe id="reportviewer" src="about:blank"></iframe><script type="text/javascript">
		function getReport(report) {' . call_user_func($ReportParam['func']) . ' };
		getReport(current);
		</script>';
} else {
	$stdOut .= '<h2>No Report Found</h2>';
};
$hookReplace['help'] = '';
require_once 'includes/footer.php'; ?>