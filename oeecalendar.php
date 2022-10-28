<?PHP
$title = 'OEE Calendar';
require_once 'includes/header.php';
if (!fCanSeePublic(@$_SESSION['permissions']['page'][7] >= 100)) {
	$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
if (!isset($_GET['mode']) || !isset($_GET['id'])) {
	$_SESSION['sqlMessage'] = 'You must select a Department or Equipment!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
//date_default_timezone_set("utc");
if (isset($_GET["month"]) && isset($_GET["year"])) { 
	$intMonth = $_GET["month"];
	$intYear = $_GET["year"];
	$intMonthDay = date("t", strtotime("1-" . $intMonth . "-" . $intYear));
	$strMonthName = date("F", strtotime("1-" . $intMonth . "-" . $intYear));
	$intFirstDay = date("N", strtotime("1-" . $intMonth . "-" . $intYear));
} else {
	$strDate = getdate(); 
	$intMonth = $strDate["mon"];
	$intYear =  $strDate["year"]; 
	$intMonthDay = date("t", strtotime("1-" . $intMonth . "-" . $intYear));
	$strMonthName = date("F", strtotime("1-" . $intMonth . "-" . $intYear));
	$intFirstDay = date("N", strtotime("1-" . $intMonth . "-" .$intYear));
};
switch ($_GET['mode']) {
	case 'department':
		$mode = 'department';
		break;
	case 'equipment':
		$mode = 'departmentequipment';
		break;
	default:
		$_SESSION['sqlMessage'] = 'Not a valid view type!';
		$_SESSION['uiState'] = 'error';
		fRedirect();
		break;
};
$hookReplace['contexticon'] = '<a href="#" data-text="Context Sensitive Menu"  class="menucontext"><span class="icon-briefcase icon-hover-hint icon-large"></span></a>';
$stdOut .= '<form id="subnav" action="oeecalendar.php" method="get"><div>Quick Jump</div><ul><li><label for="month">Month:</label><input type="text" id="month" name="month" value="' . $intMonth . '"/></li><li><label for="year">Year:</label><input id="year" type="text" name="year" value="' . $intYear . '"/></li></ul><input type="submit" value="Submit!"/></form>';
$date = strtotime("1-" . $intMonth . "-" .$intYear);
$firstMonth = date("Y-m-d 00:00:00", strtotime($intYear . "-" . $intMonth . "-01"));
$lastMonth = date("Y-m-d 00:00:00", strtotime("+1 month", strtotime($firstMonth)));
$nextMonth =  strtotime("+1 month", $date);
$previousMonth =  strtotime("-1 month", $date);
$previousMonthMonth = date("n", $previousMonth);
$previousMonthYear = date("Y", $previousMonth);
$nextMonthMonth = date("n", $nextMonth);
$nextMonthYear = date("Y", $nextMonth);
$stdOut .= '<h2>
<a id="cal-left" href="oeecalendar.php' . fQueryString(['include' => ['month' => $previousMonthMonth, 'year' => $previousMonthYear]]) . '">&lt;&lt;</a>
<a href="oeedowntime.php?startdate=' . $firstMonth . '&enddate=' . $lastMonth . '&top=5&step=oeename&mode=' . $_GET['mode'] . '&' . $mode . '=' . $_GET['id'] . '">' . $strMonthName . ' ' . $intYear . '</a>
<a id="cal-right" href="oeecalendar.php' . fQueryString(['include' => ['month' => $nextMonthMonth, 'year' => $nextMonthYear]]) . '">&gt;&gt;</a>
</h2>
<table class="records cal"><thead><tr><th data-start="' . $firstMonth . '" data-end="' . $lastMonth . '">Week</th><th data-start="' . $firstMonth . '" data-end="' . $lastMonth . '">Monday</th><th data-start="' . $firstMonth . '" data-end="' . $lastMonth . '">Tuesday</th><th data-start="' . $firstMonth . '" data-end="' . $lastMonth . '">Wednesday</th><th data-start="' . $firstMonth . '" data-end="' . $lastMonth . '">Thursday</th><th data-start="' . $firstMonth . '" data-end="' . $lastMonth . '">Friday</th><th data-start="' . $firstMonth . '" data-end="' . $lastMonth . '">Saturday</th><th data-start="' . $firstMonth . '" data-end="' . $lastMonth . '">Sunday</th></tr></thead><tbody>';
	$j=1;
	$k=1;
	$l=0;
	while ($k<$intMonthDay+$intFirstDay) {
		$wArray[++$l]['row'] = '';
		$wArray[$l]['end'] = '';
		$start = date("Y-m-", $date) . str_pad($j, 2, "0", STR_PAD_LEFT) . ' 00:00:00';
		$end = date("Y-m-", $date) . str_pad($j, 2, "0", STR_PAD_LEFT) . ' 00:00:00';
		for ($i=1; $i<=7; $i++) {
			if ($k < $intFirstDay ) {
				if (!isset($wArray[$l]['start'])) {
					$wArray[$l]['start'] = date("Y-m-", $previousMonth) . str_pad(date("t", $previousMonth) +  (1 + $k - $intFirstDay), 2, "0", STR_PAD_LEFT) . ' 00:00:00';
				};
				$wArray[$l]['row'] .= '<td class="nocell">' . str_pad(date("t", $previousMonth) +  (1 + $k - $intFirstDay), 2, "0", STR_PAD_LEFT) . '</td>';
			} elseif ($j > $intMonthDay) {
				$wArray[$l]['end'] = date("Y-m-", $nextMonth) . str_pad(($j - $intMonthDay), 2, "0", STR_PAD_LEFT) . ' 00:00:00';
				$wArray[$l]['row'] .= '<td class="nocell">' . ($j - $intMonthDay) . '</td>';
				$j++;
			} else {
				if (date("j") == $j && date("n") == $intMonth && date("Y") == $intYear) {
					$strToday = ' class="today"';
				} else {
					$strToday = "";
				};
				if (!isset($wArray[$l]['start'])) {
					$wArray[$l]['start'] = date("Y-m-", $date) . str_pad($j, 2, "0", STR_PAD_LEFT) . ' 00:00:00';
				};
				$thisSDay = date("Y-m-", $date) . str_pad($j, 2, "0", STR_PAD_LEFT) . ' 00:00:00';
				$thisEDay = date("Y-m-d 00:00:00", strtotime("+1 day", strtotime($thisSDay)));
				$wArray[$l]['row'] .= '<td ' . $strToday . ' data-start="' . $thisSDay . '" data-end="' . $thisEDay . '">' . $j . '</td>';
				$wArray[$l]['end'] = date("Y-m-", $date) . str_pad($j, 2, "0", STR_PAD_LEFT) . ' 00:00:00';
				$j++;
			};
			$wArray[$l]['end'] = date("Y-m-d 00:00:00", strtotime("+1 day", strtotime($wArray[$l]['end'])));
			$k++;
		};
	};
	foreach ($wArray as $key => $row) {
		$stdOut .= '<tr><td class="week" data-start="' . $row['start'] . '" data-end="' . $row['end'] . '"> ' . $key . '</td>' . $row['row'] . '</tr>';
	};
$stdOut .= '</tbody></table>
<script type="text/javascript">
	$(function() {
	
		function fLinky(jThis) {
			window.location.href = "oeedowntime.php?startdate=" + $(jThis).data("start") + "&enddate=" + $(jThis).data("end") + "&top=5&step=oeename&mode=' . $_GET['mode'] . '&' . $mode . '=' . $_GET['id'] . '";
		};
	
		$(".cal td:not(.week, .nocell)").click(function () {
			fLinky(this);
			return false;
		});
		$(".cal th").click(function () {
			fLinky(this);
			return false;
		});
		$(".cal .week").click(function () {
			fLinky(this);
			return false;
		});
	});
</script>';
$hookReplace['help'] = '<a href="#">Basic Usage</a><div>Hovering over the calendars Headers, Weeks or Days will highlight an area of the calendar this reflects the date selected for the Downtime top reasons. Clicking the month name will take you to the Downtime top reasons for the month. Clicking the Double Chevrons (<<) will take you to the previous or following Months calendar view.</div><a href="#">Context Sensitive "Briefcase" Menu</a><div>This menu allows you to change the calendar page view by year and month.</div>';
require_once 'includes/footer.php'; ?>