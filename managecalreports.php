<?PHP 
$title = 'Manage Reports';
require_once 'includes/header.php';
if ($_SESSION['id'] != 1) {
	$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
if (isset($_GET['id'])) {
	$queryGet = 'select reportcalendartypefk, ReportCalendarTypeRepeatFK, value, name, startdate, enddate, reportcalendarfunctionnamefk, argjson
from ReportCalendarRange
left join ReportCalendarTypeRepeat on ReportCalendarTypeRepeatFK = ReportCalendarTypeRepeat.id
where ReportCalendarRange.id = ' . $_GET['id'];
	$dataGet = odbc_exec($conn, $queryGet);
	$aRow = odbc_fetch_array($dataGet, 1);
	$sRow = json_encode($aRow, true);
	if ($aRow['ReportCalendarTypeRepeatFK'] == 9) {
		$queryWeeklyDays = 'SELECT Value
		FROM ReportCalendarRangeValue
		where reportcalendarrangefk = ' . $_GET['id'];
		$dataWeeklyDays = odbc_exec($conn, $queryWeeklyDays);
		while (odbc_fetch_row($dataWeeklyDays)) {
			$days[odbc_result($dataWeeklyDays, 1)] = 'true';
		};
	};
};
$queryRepeatType = 'select ReportCalendarType.ID as typeid, ReportCalendarType.Name as typename, 
ReportCalendarTypeRepeat.ID as id, ReportCalendarRepeat.ID as repeatid, ReportCalendarRepeat.Name as repeatname
from ReportCalendarTypeRepeat
left join ReportCalendarType on ReportCalendarTypeFK = ReportCalendarType.ID
left join ReportCalendarRepeat on ReportCalendarRepeatFK = ReportCalendarRepeat.ID';
$dataRepeatType = odbc_exec($conn, $queryRepeatType);
while (odbc_fetch_row($dataRepeatType)) {
	$aRepeatType[odbc_result($dataRepeatType, 1)]['option'] = '<option ';
	$aRepeatType[odbc_result($dataRepeatType, 1)]['option'] .= 'data-id="' . odbc_result($dataRepeatType, 1) . '" value="' . odbc_result($dataRepeatType, 1) . '">' . odbc_result($dataRepeatType, 2) . '</option>';
	$aRepeatType[odbc_result($dataRepeatType, 1)]['select'][] = ['id' => odbc_result($dataRepeatType, 3), 'repeatid' => odbc_result($dataRepeatType, 4), 'option' => '<option value="' . odbc_result($dataRepeatType, 3) . '" data-id="' . odbc_result($dataRepeatType, 4) . '">' . odbc_result($dataRepeatType, 5) . '</option>'];
};
$sType = '<label for="caltype">Report Type: </label><select name="caltype" id="caltype"><option value="0">None</option>';
foreach ($aRepeatType as $key => $array) {
	$sType .= $array['option'];
	$aRepeat[$key] = '<select class="calrepeat" data-id="' . $key . '" name="typerepeat">';
	foreach ($array['select'] as $arrayArray) {
		$aRepeat[$key] .= $arrayArray['option'];
	};
	$aRepeat[$key] .= '</select>';
};
$sType .= '</select>';
$queryFunction = 'select id, name
from ReportCalendarFunctionName';
$dataFunction = odbc_exec($conn, $queryFunction);
$functionSelect = '<select id="fname" name="fname">';
while (odbc_fetch_row($dataFunction)) {
	$functionSelect .= '<option ';
	if (isset($aRow['reportcalendarfunctionnamefk'])) {
		if ($aRow['reportcalendarfunctionnamefk'] == odbc_result($dataFunction, 1)) {
			$functionSelect .= 'selected ';
		};
	};
	$functionSelect .= 'value="' . odbc_result($dataFunction, 1) . '">' . odbc_result($dataFunction, 2) . '</option>';
};
$functionSelect .= '</select>';
$localTime = date_create(date($localTime));
//print(date_format($localTime, 'Y-m-d H:i:s')); UTC // actually GMT/BST
date_timezone_set($localTime, timezone_open('Europe/London'));
//print(date_format($localTime, 'Y-m-d H:i:s')); GMT/BST // actually UTC
$stdOut .= '<div id="reportcalhidden">' . implode('', $aRepeat) . '</div><form method="post" action="includes/changecalreports.php">';
if (isset($_GET['id'])) {
	$stdOut .= '<input type="hidden" name="id" value="' . $_GET['id'] . '" />';
};
$stdOut .= $sType . '<span class="required"> * </span>
<ul id="reportcalform">
<li><label for="rname">Report Name: </label><input type="text" id="rname" name="rname" value="';
if (isset($aRow['name'])) {
	$stdOut .= $aRow['name'];
};
$stdOut .= '"/><span class="required"> * </span></li>
<li><label id="ftype">Frequency Type: </label><span class="required"> * </span></li>
<li><label for="frequency">Frequency: </label><input type="text" id="frequency" ';
if (isset($aRow['value'])) {
	$stdOut .= 'value="' . $aRow['value'] . '" ';
};
$stdOut .= 'name="everyfrequency"/><span class="required"> * </span></li>
<li id="weekfrequency">
Week Days:<span class="required"> * </span>
<ul>';
$dayArray = ['monday','tuesday','wednesday','thursday','friday','saturday','sunday'];
foreach ($dayArray as $key => $value) {
	$stdOut .= '<li><input type="checkbox" ';
	if (isset($days[$key])) {
		$stdOut .= 'checked ';
	};
	$stdOut .= 'name="weekfrequency[]" id="' . $value . '" value="' . $key . '"/><label for="' . $value . '">' . ucwords($value) . '</label></li>';
};
$stdOut .= '</ul>
</li>
<li><label for="reportstart">Report Start:</label><input type"text" value="';
if (isset($aRow['startdate'])) {
	$stdOut .= substr($aRow['startdate'], 0, 10);
} else {
	$stdOut .= date_format($localTime, 'Y-m-d');
};
$stdOut .= '" id="reportstart" name="reportstart"/><span class="required"> * </span></li>
<li>Report End:<span class="required"> * </span>
<ul>
<li><input type="radio" name="endtype" id="endnever" ';
if (!isset($aRow['enddate'])) {
	$stdOut .= 'checked '; 
};
$stdOut .= 'value="never"/><label for="endnever">Never</label></li>
<li><input type="radio" name="endtype" id="endafter" value="after"/><label for="endafter">After</label> <input type="text" id="afternumber" name="afternumber"/><span class="required"> * </span></li>
<li><input type="radio" name="endtype" ';
if (isset($aRow['enddate'])) {
	$stdOut .= 'checked '; 
};
$stdOut .= 'id="enddate" value="date"/><label for="enddate">Date</label><input type"text" ';
if (isset($aRow['enddate'])) {
	$stdOut .= 'value="' . substr($aRow['enddate'], 0, 10) . '" '; 
};
$stdOut .= 'id="reportend" name="reportend"/><span class="required"> * </span></li>
</ul>
<li><label for="fname">Function: </label>' . $functionSelect . '<span class="required"> * </span></li>
<li><label for="arg">Arguments:</label> <textarea class="basic" id="arg" name="arg">';
if (isset($aRow['argjson'])) {
	$stdOut .= $aRow['argjson'];
};
$stdOut .= '</textarea><span class="required"> * </span></li>
<li><input type="submit" class="validatetextbutton" value="Save!" /></li>
</ul>
</form><div class="requiredhint"><span class="required"> * </span>are required fields.</div>
<script type="text/javascript">
	$(function() {
		$("#reportcalform, #weekfrequency, ';
		if (!isset($aRow['enddate'])) {
			$stdOut .= '#reportend,';
		};
		$stdOut .= '#afternumber").hide();
		$("';
		if (!isset($aRow['enddate'])) {
			$stdOut .= '#reportend,';
		};
		$stdOut .= ' #afternumber").next().hide();
		function fGetEnd() {
			$("#afternumber, #reportend").hide();
			$("#reportend, #afternumber").next().hide();
			switch ($("input[name=\"endtype\"]:checked").val()) {
				case "after":
					$("#afternumber").show();
					$("#afternumber").next().show();
					break;
				case "date":
					$("#reportend").show();
					$("#reportend").next().show();
					break;
			};
		};
		function fGetRepeatType() {
			$("#reportcalform").show();
			$("#weekfrequency").hide();
			$("#frequency").parent().show();
			$("#reportstart").parent().show();
			$("#endnever").parent().parent().parent().show();
			switch ($("#reportcalform .calrepeat option:selected").data("id")) {
				case 0:
					$("#reportcalform").hide();
				case 3:
					$("#weekfrequency").show();
					break;
				case 5:
					$("#reportstart").parent().hide();
				case 4:
					$("#endnever").parent().parent().parent().hide();
					$("#frequency").parent().hide();
					break;
			};
		};
		$("#caltype").bind("change", function() {
			$("#reportcalform .calrepeat").remove();
			if ($(this).val() == 0) {
				$("#reportcalform").hide();
			} else {
				$(".calrepeat[data-id=\"" + $(this).val() + "\"]").clone().insertAfter("#ftype");
				fGetRepeatType();
				$("#reportcalform .calrepeat").bind("change", function() {
					fGetRepeatType();
				});
			};
		});
		$("input[name=\"endtype\"]").bind("change",	function() {
			fGetEnd();
		});
		$("#reportstart").datepicker({
			showOtherMonths: true,
			selectOtherMonths: true,
			changeMonth: true,
			changeYear: true,
			dateFormat: "yy-mm-dd",
			//minDate: date,
			//maxDate: endDate,
			showWeek: 1,
			firstDay: 1
		});
		$("#reportend").datepicker({
			showOtherMonths: true,
			selectOtherMonths: true,
			changeMonth: true,
			changeYear: true,
			dateFormat: "yy-mm-dd",
			//minDate: date,
			//maxDate: endDate,
			showWeek: 1,
			firstDay: 1
		});
		
		$(".validatetextbutton").click(function() {
			$("input").removeClass( "ui-state-error" );
			$("select").removeClass( "ui-state-error" );
			var retValue;
			var bValid = true;
			var fields = $("#reportcalform").find("li").not("li li").filter(function () {
				return $(this).css("display") != "none";
			}).find("input, select, textarea").not(".validatetextbutton").filter(function () {
				return $(this).css("display") != "none";
			});
			var checkedfields = fields.filter(function() {
				if ($(this).is(":checked")) {
					return $(this);
				};
			});
			if ($("#caltype").val() == 0) {
				updateTips("You must select a valid Report Type!");
				return false;
			};
			bValid = bValid && checkLength( fields.filter(function () {
				if ($(this).is("#rname")) {
					return $(this);
				};
			}), "report name", 1, 60 );
			fields.filter(function () {
				if ($(this).is("#frequency")) {
					bValid = bValid && checkNumber($(this), "Frequency is not a valid Number");
				};
			});
			var bTempValid = true;
			fields.filter(function () {
				if ($(this).is("[name=\"weekfrequency[]\"]")) {
					bTempValid = false;
					return $(this);
				};
			}).filter(function() {
				bTempValid = bTempValid || $(this).is(":checked");
			});
			if (bTempValid == false) {
				fields.filter(function () {
					if ($(this).is("[name=\"weekfrequency[]\"]")) {
						return $(this);
					};
				}).addClass( "ui-state-error" );
				updateTips("You must select a Day");
				bValid = bValid && false;
			};
			fields.filter(function() {
				if ($(this).is("#reportstart")) {
					bValid = bValid && checkRegexp($(this), /^\d{4}-\d{2}-\d{2}$/i, "Start date is not valid.");
				}
			});
			fields.filter(function () {
				if ($(this).is("#afternumber")) {
					bValid = bValid && checkNumber($(this), "After is not a valid Number");
				};
			});
			fields.filter(function() {
				if ($(this).is("#reportend")) {
					bValid = bValid && checkRegexp($(this), /^\d{4}-\d{2}-\d{2}$/i, "End date is not valid.");
				}
			});
			bValid = bValid && checkLength( fields.filter(function () {
				if ($(this).is("#fname")) {
					return $(this);
				};
			}), "function name", 1, 60 );
			if (bValid == false) {
				return false;
			};
			//return false;
		});';
		if (isset($sRow)) {
			$stdOut .= ' var selection = ' . $sRow . ';
			$("#caltype").val(selection.reportcalendartypefk).trigger("change");
			$("#reportcalform .calrepeat").val(selection.ReportCalendarTypeRepeatFK).trigger("change")';
		};
	$stdOut .= '});
</script>';
$hookReplace['help'] = '
		<a href="#">Report Type Options</a>
		<div>You have a number of options available to you. The combination of selected options determine how often the report occurs;
		<ul>
			<li>Weekly<ul>
				<li>Every</li>
				<li>Week Day</li>
			</ul></li>
			<li>Monthly<ul>
				<li>On Day</li>
				<li>Every</li>
			</ul></li>
			<li>Yearly<ul>
				<li>Every</li>
			</ul></li>
			<li>Single</li>
			<li>Daily</li>
		</ul>
		The initial selection determines which time frame you intend to work with, the second selection deals with frequency within that selection;
		<ul>
			<li>Every - This selection works on the basis that a report will be available every X. Example, Every 2 Days will be every other day from the Report Start Date til the End Date.</li>
			<li>Week Day - This selection allows you to select the day name you wish a report to be available for. Example, Every Friday from the Start Date til the End Date.</li>
			<li>On Day - This selection allows you to choose a specific date in the given range. If the number is out of range then the last available valid date is used. Example, On the 23rd of every month.</li>
		</ul>
		</div>
		<a href="#">Report Frequency</a>
		<div>This value modifies the selection of the selected "Report Type Options", It is also possible to be presented with a list of Days. The Day list works together with the frequency.</div>
		<a href="#">Report Start</a>
		<div>This value, when clicked, will allow you to pick a date from a calendar and is used as the start for the report date generation.</div>
		<a href="#">Report End</a>
		<div>
		<ul>
		<li>Never - This allows you to select no expiry date for the report.</li>
		<li>After - Selecting this option allows you to define how many times you want the report to run, when submitted, using the the selected frequency an End Date will be determined.</li>
		<li>Date - The value here is used to stop report date generation. Reports after this date will not be selectable.</li>
		</ul>
		</div>
		<a href="#">Function & Arguments</a>
		<div>The function is used to determine how the report is show to the user, as time goes on more will be added. The arguments are a formatted JSON(JavaScript Object Notation) string passed into the function for further customization beyond the functions inherent parameters.
		<ul><li>fDirPdf - Updates an &lt;iframe&gt; in the center of the window showing the selected PDF file.
			<ul>
				<li>Folder - Allows you to configure what folder on the network share "\\\\brimweb.brimcontrols.local\static" you wish reports to be pulled from.</li>
				<li>Filename - Allows you to configure the filename, with date string formatting.</li>
			<ul>
		</ul></li>
		</ul></div>
		<a href="#">JSON Example</a>
<div>{"menu": {
	<div class="indent">"id": "file", "value": "File", "popup": {<br />
		<div class="indent">"menuitem": [
			<div class="indent">{"value": "New", "onclick": "CreateNewDoc()"},<br />
			{"value": "Open", "onclick": "OpenDoc()"},<br />
			{"value": "Close", "onclick": "CloseDoc()"}</div>
		]</div>
	}</div>
}}</div>
<a href="#">Date Format</a>
	<div><ul>
		<li>yy = short year</li>
		<li>yyyy = long year</li>
		<li>M = month (1-12)</li>
		<li>MM = month (01-12)</li>
		<li>MMM = month abbreviation (Jan, Feb … Dec)</li>
		<li>MMMM = long month (January, February … December)</li>
		<li>d = day (1 - 31)</li>
		<li>dd = day (01 - 31)</li>
		<li>ddd = day of the week in words (Monday, Tuesday … Sunday)</li>
		<li>E = short day of the week in words (Mon, Tue … Sun)</li>
		<li>D - Ordinal day (1st, 2nd, 3rd, 21st, 22nd, 23rd, 31st, 4th…)</li>
		<li>h = hour in am/pm (0-12)</li>
		<li>hh = hour in am/pm (00-12)</li>
		<li>H = hour in day (0-23)</li>
		<li>HH = hour in day (00-23)</li>
		<li>mm = minute</li>
		<li>ss = second</li>
		<li>SSS = milliseconds</li>
		<li>a = AM/PM marker</li>
		<li>p = a.m./p.m. marker</li>
	</ul>
	You can also escape the date format function by enclosing the String characters in Single Quote (\') characters.</div>';
require_once 'includes/footer.php'; ?>