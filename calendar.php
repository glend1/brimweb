<?PHP
$title = 'Calibration Calendar';
require_once 'includes/header.php';
if (!fCanSeePublic(@$_SESSION['permissions']['page'][7] >= 100)) {
	$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
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
$date = strtotime("1-" . $intMonth . "-" .$intYear);
$nextMonth =  strtotime("+1 month", $date);
$previousMonth =  strtotime("-1 month", $date);
$previousMonthMonth = date("n", $previousMonth);
$previousMonthYear = date("Y", $previousMonth);
$nextMonthMonth = date("n", $nextMonth);
$nextMonthYear = date("Y", $nextMonth);
$stdOut .= '<h2>
<a id="cal-left" href="' . $_SERVER['PHP_SELF'] . '?month=' . $previousMonthMonth . '&year=' .  $previousMonthYear . '">&lt;&lt;</a><a href="month.php">' . $strMonthName . ' ' . $intYear . '</a><a id="cal-right" href="' . $_SERVER['PHP_SELF'] . '?month=' . $nextMonthMonth . '&year=' .  $nextMonthYear . '">&gt;&gt;</a>
</h2>
<table class="records cal"><thead><tr><th>Week</th><th>Monday</th><th>Tuesday</th><th>Wednesday</th><th>Thursday</th><th>Friday</th><th>Saturday</th><th>Sunday</th></tr></thead><tbody>';
	$j=1;
	$k=1;
	$l=1;
	while ($k<$intMonthDay+$intFirstDay) {
		$stdOut .= '<tr><td class="week"> ' . $l++ . '</td>';
		for ($i=1; $i<=7; $i++) {
			if ($k < $intFirstDay ) { 
				$stdOut .= '<td class="nocell">' . (date("t", $previousMonth) +  (1 + $k - $intFirstDay)) . '</td>';
			} elseif ($j > $intMonthDay) {
				$stdOut .= '<td class="nocell">' . ($j - $intMonthDay) . '</td>';
				$j++;
			} else {
				if (date("j") == $j && date("n") == $intMonth && date("Y") == $intYear) {
					$strToday = ' class="today"';
				} else {
					$strToday = "";
				};
				$stdOut .= '<td ' . $strToday . '>' . $j . '</td>';
				$j++;
			};
			$k++;
		};
		$stdOut .= '</tr>';
	};
$stdOut .= '</tbody></table>
<form  id="quickJump" method="get" action="index.php"><b>Quick Jump:</b>&nbsp;<label>Month:</label><input type="text" name="month" value="' . $intMonth . '"/>&nbsp;<label>Year:</label><input type="text" name="year" value="' . $intYear . '"/>&nbsp;<input type="submit" value="Submit!"/></form>
<script type="text/javascript">
	$(function() {
		$(".cal td:not(.week, .nocell)").click(function () {
			//console.log("day");
			return false;
		});
		$(".cal th").click(function () {
			//console.log("month");
			return false;
		});
		$(".cal .week").click(function () {
			//console.log("week");
			return false;
		});
	});
</script>';
require_once 'includes/footer.php'; ?>