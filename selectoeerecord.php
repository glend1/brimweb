<?PHP 
$title = 'OEE Records';
require_once 'includes/header.php';
if (!fCanSee(@$_SESSION['permissions']['page'][1] >= 100)) {
	$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
$startdateoee = $localTime;
$enddateoee = $startdateoee;
$stdOut .= '<form id="oeeselect" action="includes/changeoeerecord.php" method="post"><div class="oeeform">';
if (isset($_GET['edit'])) {
	$queryRecord = 'select top 1 Comment, StartDateTime, enddatetime, oeecategoryfk, DisciplineFK, DepartmentequipmentFK, Type.id, departmentfk
	from Records
	join Type on TypeFK = Type.ID
	join departmentequipment on DepartmentEquipmentFK = departmentequipment.id
	where records.ID = ' . $_GET['edit'];
	$dataRecord = odbc_exec($conn, $queryRecord);
	if (!odbc_fetch_row($dataRecord)) {
		$_SESSION['sqlMessage'] = 'Record not found!';
		$_SESSION['uiState'] = 'error';
		fRedirect();
	};
	if (!fCanSee(@$_SESSION['permissions']['department'][odbc_result($dataRecord, 8)] >= 200)) {
		$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
		$_SESSION['uiState'] = 'error';
		fRedirect();
	};
	$startdateoee = substr(odbc_result($dataRecord, 2), 0, -4);
	$enddateoee = substr(odbc_result($dataRecord, 3), 0, -4);
	$stdOut .= '<input type="hidden" name="action" value="edit" /><input type="hidden" name="id" value="' . $_GET['edit'] . '" />';
} else {
	$stdOut .= '<h3>Action</h3>
	<input type="radio" name="action" checked value="" class="hiddenobject" />
	<input type="radio" name="action" value="add" id="add" /><label for="add">Add Record</label>
	<input type="radio" name="action" value="find" id="find" /><label for="find">Find Record</label>';
};
$stdOut .= '<h3>Date/Time:</h3>
<table><thead><tr><th><label for="startdate">Start Date/Time:</label></th>
<th><label for="enddate">End Date/Time:</label></th><th></th></tr></thead>
<tbody><tr><td><input type="text" name="startdate" value="' . $startdateoee . '" id="startdateoee" /></td>
<td><input type="text" name="enddate" id="enddateoee" value="' . $enddateoee . '" /></td>
<td><a href="#" id="datetimeswitcher" data-mode="advanced">Basic</a></td>
</tr></tbody></table>
<h3>Department Equipment:</h3><select name="departmentequipment" ';
/*if (!isset($_GET['edit'])) {
	$stdOut .= 'disabled ';
};*/
$stdOut .= 'size="8">';
	$queryDepartmentEquipments = 'select distinct departmentequipment.ID, departmentequipment.name from type
	join departmentequipment on DepartmentEquipmentFK = departmentequipment.id';
	if ($_SESSION['id'] != 1) {
		fOrThem($_SESSION['permissions']['department'], 100, 'departmentequipment.departmentfk', $aDeptEquip);
		fGenerateWhere($queryDepartmentEquipments, $aDeptEquip);
	};
	$queryDepartmentEquipments .= ' order by departmentequipment.name';
	$dataDepartmentEquipments = odbc_exec($conn, $queryDepartmentEquipments);
	while (odbc_fetch_row($dataDepartmentEquipments)) {
		$stdOut .= '<option value="' . odbc_result($dataDepartmentEquipments, 1) . '"';
		if (isset($_GET['edit'])) {
			if (odbc_result($dataDepartmentEquipments, 1) == odbc_result($dataRecord, 6)) {
				$stdOut .= ' selected';
			};
		};
		$stdOut .= '>' . odbc_result($dataDepartmentEquipments, 2) . '</option>';
	};
$stdOut .= '</select><h3>Category:</h3>
<select name="category" ';
if (!isset($_GET['edit'])) {
	$stdOut .= 'disabled ';
};
$stdOut .= 'size="8">';
if (isset($_GET['edit'])) {
	$queryCategory = 'select distinct oeecategory.ID, oeecategory.name from type
	join oeecategory on oeecategoryfk = oeecategory.id';
	$dataCategory = odbc_exec($conn, $queryCategory);
	while (odbc_fetch_row($dataCategory)) {
		$stdOut .= '<option value="' . odbc_result($dataCategory, 1) . '"';
		if (isset($_GET['edit'])) {
			if (odbc_result($dataCategory, 1) == odbc_result($dataRecord, 4)) {
				$stdOut .= ' selected';
			};
		};
		$stdOut .= '>' . odbc_result($dataCategory, 2) . '</option>';
	};
};
$stdOut .= '</select><h3>Discipline:</h3><select name="discipline" ';
if (!isset($_GET['edit'])) {
	$stdOut .= 'disabled ';
}
$stdOut .= 'size="8">';
if (isset($_GET['edit'])) {
	$queryDisciplines = 'select distinct discipline.id, discipline.name from type 
	join discipline on disciplinefk = discipline.id
	where departmentequipmentfk = ' . odbc_result($dataRecord, 6) . ' and oeecategoryfk = ' . odbc_result($dataRecord, 4) . ' 
	order by name asc';
	$dataDisciplines = odbc_exec($conn, $queryDisciplines);
	while(odbc_fetch_row($dataDisciplines)) {
		$stdOut .= '<option value="' . odbc_result($dataDisciplines, 1) . '" ';
		if (odbc_result($dataDisciplines, 1) == odbc_result($dataRecord, 5)) {
			$stdOut .= ' selected';
		};
		$stdOut .= '>' . odbc_result($dataDisciplines, 2) . '</option>';
	};
};
$stdOut .= '</select>';
if (!isset($_GET['edit'])) {
	$stdOut .= '<div id="oeeadd">';
};
$stdOut .= '<h3><label for="oeecomment" >Comment:</label></h3>
<input type="text" maxlength="131" name="comment" id="oeecomment" ';
if (isset($_GET['edit'])) {
	$stdOut .= 'value="' . odbc_result($dataRecord, 1) . '"';
};
$stdOut .= '/>';
if (!isset($_GET['edit'])) {
	$stdOut .= '</div>';
};
$stdOut .= '</div>
<div class="oeeform"><h3>Reason:</h3><select name="type" id="type" size="50" ';
if (!isset($_GET['edit'])) {
	$stdOut .= 'disabled ';
};
$stdOut .= '>';
if (isset($_GET['edit'])) {
	$queryTypes = 'select type.id, oeename.name from type 
	join oeename on oeenamefk = oeename.id
	where departmentequipmentfk = ' . odbc_result($dataRecord, 6) . ' and disciplinefk = ' . odbc_result($dataRecord, 5) . ' and oeecategoryfk = ' . odbc_result($dataRecord, 4) . ' order by name asc';
	$dataTypes = odbc_exec($conn, $queryTypes);
	while(odbc_fetch_row($dataTypes)) {
		$stdOut .= '<option value="' . odbc_result($dataTypes, 1) . '"';
		if (odbc_result($dataTypes, 1) == odbc_result($dataRecord, 7)) {
			$stdOut .= ' selected';
		};
		$stdOut .= '>' . odbc_result($dataTypes, 2) . '</option>';
	};
};
$stdOut .= '</select>
</div><div class="oeesubmit"><input id="oeesubmit" type="submit" value="Go!" ';
if (!isset($_GET['edit'])) {
	$stdOut .= 'disabled ';
};
$stdOut .= '/></div>
<script type="text/javascript">
	function fPopUpCal(id, dates, mode) {
		$(id).datetimepicker("destroy");
		switch (mode) {
			case "advanced":
				$(id).datetimepicker({
					showOtherMonths: true,
					selectOtherMonths: true,
					changeMonth: true,
					changeYear: true,
					minDate: dates[0],
					maxDate: dates[1],
					//altField: field,
					dateFormat: "yy-mm-dd",
					showWeek: 1,
					firstDay: 1,
					showButtonPanel: false,
					timeFormat: "HH:mm:ss",
					//altFieldTimeOnly: false,
					pickerTimeFormat: "hh:mm:ss TT",
					addSliderAccess: true,
					sliderAccessArgs: { touchonly: false }
				});
				break;
			case "basic":
				$(id).datetimepicker({
					showOtherMonths: true,
					selectOtherMonths: true,
					changeMonth: true,
					changeYear: true,
					minDate: dates[0],
					maxDate: dates[1],
					//altField: field,
					dateFormat: "yy-mm-dd",
					showWeek: 1,
					firstDay: 1,
					showButtonPanel: false,
					timeFormat: "HH:mm:ss",
					//altFieldTimeOnly: false,
					showSecond: false,
					stepMinute: 5,
					pickerTimeFormat: "hh:mm:ss TT",
					addSliderAccess: true,
					sliderAccessArgs: { touchonly: false }
				});
				var newDate = $(id).datetimepicker("getDate");
				newDate.setSeconds(00);
				$(id).datetimepicker("setDate", newDate);
				break;
		};
	};
	now = new Date();
	var dates = [new Date( 2014, 0, 27), new Date(now.getFullYear() + 1, now.getMonth(), now.getDate(), now.getHours(), now.getMinutes(), now.getSeconds())];
	$(function() {
		fPopUpCal("#startdateoee", dates, "advanced");
		fPopUpCal("#enddateoee", dates, "advanced");
		$("#datetimeswitcher").click(function() {
			var jThis = $(this);
			switch (jThis.data("mode")) {
				case "advanced":
					jThis.data("mode", "basic");
					jThis.text("Advanced");
					fPopUpCal("#startdateoee", dates, "basic");
					fPopUpCal("#enddateoee", dates, "basic");
					break;
				case "basic":
					jThis.data("mode", "advanced");
					jThis.text("Basic");
					fPopUpCal("#startdateoee", dates, "advanced");
					fPopUpCal("#enddateoee", dates, "advanced");
					break;
			};
			return false;
		});
		$("#oeesubmit").click(function() {
			var sdtValOee = new Date($("#startdateoee").val().split(" ").join("T"));
			var edtValOee = new Date($("#enddateoee").val().split(" ").join("T"));
			if (sdtValOee.getTime() > edtValOee.getTime()) {
				updateTips("Invalid date time range selected!");
				return false;
			};
		});
		var oee = $("#oeeselect input, #oeeselect select");
		var optioncategory = $("[name=category]");
		var optiondiscipline = $("[name=discipline]");
		var optiontype = $("#type");
		var oeedatepicker = $("#startdateoee, #enddateoee");
		var oeeadd = $("#oeeadd");
		var oeedepartmentequipment = $("[name=\'departmentequipment\']");
		var oeediscipline = $("[name=\'departmentequipment\'], [name=\'category\']");
		var go = $("input[type=\'submit\']");
		$("input[name=\'action\']").change(function() {
			var actionval = $(this).val();
			switch (actionval) {
				case "find":
					oeeadd.css("display", "none");
					oeedepartmentequipment.prop("disabled", false);
					break;
				case "add":
					oeeadd.css("display", "block");
					oeedepartmentequipment.prop("disabled", false);
					break;
				default:
					break;
			};
		});
		oeedepartmentequipment.change(function() {
			var ajaxnotification = $("#ajax-notification");
			var oeeArray = $(oee).serializeArray();
			var oeeSerial = $(oee).serialize();
			if (this.name == "departmentequipment") {
				$.ajax({
					// the URL for the request
					url: "includes/ajax.getcategory.php",
					// the data to send (will be converted to a query string)
					data: oeeSerial,
					// whether this is a POST or GET request
					type: "GET",
					// the type of data we expect back
					dataType : "json",
					beforeSend: function() {
							updateNotifications("something", "complete", ajaxnotification);
							optioncategory.html("<option value=\'none\'>Loading...</option>");
							optiondiscipline.prop("disabled", true);
							optiondiscipline.html("");
							optioncategory.prop("disabled", true);

						},
					// code to run if the request succeeds;
					// the response is passed to the function
					success: function( json ) {
							optioncategory.html("");
							optioncategory.html(json.oreturn);
							optioncategory.prop("disabled", false);
							if (!json.oreturn) {
								updateNotifications("Action failed, " + json.status, json.status, ajaxnotification);
								optioncategory.prop("disabled", true);
							};
						},
					// code to run if the request fails; the raw request and
					// status codes are passed to the function
					error: function( xhr, status, errorThrown ) {
							updateNotifications( xhr.status + ": " + xhr.statusText, xhr.status, ajaxnotification);
							optioncategory.html("");						
						},
					// code to run regardless of success or failure
					complete: function( xhr, status ) {
							
						}
				});
			};
		});
		oeediscipline.change(function() {
			var ajaxnotification = $("#ajax-notification");
			var oeeArray = $(oee).serializeArray();
			var oeeSerial = $(oee).serialize();
			var oeeValid = 0;
			$.each(oeeArray, function( i, obj ) {
				if(obj.name == "departmentequipment" || obj.name == "category") {
					oeeValid++;
				};
			});
			if (oeeValid == 2) {
				$.ajax({
					// the URL for the request
					url: "includes/ajax.getdisciplines.php",
					// the data to send (will be converted to a query string)
					data: oeeSerial,
					// whether this is a POST or GET request
					type: "GET",
					// the type of data we expect back
					dataType : "json",
					beforeSend: function() {
							updateNotifications("something", "complete", ajaxnotification);
							optiondiscipline.html("<option value=\'none\'>Loading...</option>");
							optiondiscipline.prop("disabled", true);
						},
					// code to run if the request succeeds;
					// the response is passed to the function
					success: function( json ) {
							optiondiscipline.html("");
							optiondiscipline.html(json.oreturn);
							optiondiscipline.prop("disabled", false);
							if (!json.oreturn) {
								updateNotifications("Action failed, " + json.status, json.status, ajaxnotification);
								optiondiscipline.prop("disabled", true);
							};
						},
					// code to run if the request fails; the raw request and
					// status codes are passed to the function
					error: function( xhr, status, errorThrown ) {
							updateNotifications( xhr.status + ": " + xhr.statusText, xhr.status, ajaxnotification);
							optiondiscipline.html("");
							
						},
					// code to run regardless of success or failure
					complete: function( xhr, status ) {
							
						}
				});
			};
		});
		$("[name=\'departmentequipment\'], [name=\'category\'], [name=\'discipline\']").change(function() {
			var ajaxnotification = $("#ajax-notification");
			var oeeArray = $(oee).serializeArray();
			var oeeSerial = $(oee).serialize();
			var oeeValid = 0;
			$.each(oeeArray, function( i, obj ) {
				if(obj.name == "departmentequipment" || obj.name == "discipline" || obj.name == "category") {
					oeeValid++;
				};
			});
			if (oeeValid == 3) {
				$.ajax({
					// the URL for the request
					url: "includes/ajax.gettypes.php",
					// the data to send (will be converted to a query string)
					data: oeeSerial,
					// whether this is a POST or GET request
					type: "GET",
					// the type of data we expect back
					dataType : "json",
					beforeSend: function() {
							updateNotifications("something", "complete", ajaxnotification);
							optiontype.html("<option value=\'none\'>Loading...</option>");
							optiontype.prop("disabled", true);
						},
					// code to run if the request succeeds;
					// the response is passed to the function
					success: function( json ) {
							optiontype.html("");
							optiontype.html(json.oreturn);
							optiontype.prop("disabled", false);
							if (!json.oreturn) {
								updateNotifications("Action failed, " + json.status, json.status, ajaxnotification);
								optiontype.prop("disabled", true);
							};
						},
					// code to run if the request fails; the raw request and
					// status codes are passed to the function
					error: function( xhr, status, errorThrown ) {
							updateNotifications( xhr.status + ": " + xhr.statusText, xhr.status, ajaxnotification);
							optiontype.html("");
							optiontype.prop("disabled", true);
						},
					// code to run regardless of success or failure
					complete: function( xhr, status ) {
							
						}
				});
			} else {
				optiontype.html("");
				optiontype.prop("disabled", true);
			};
			go.prop("disabled", true);
		});
		optiontype.change(function() {
				if ($(this).val()) {
					go.prop("disabled", false);
				} else {
					go.prop("disabled", true);
				};
		});
		$("#oeesubmit").click(function () {
			var optionaction = $("[name=action]:checked").val();
			if (optionaction === "") {
				updateTips("Please select a valid action!");
				return false;
			};
			var sdtVal = new Date($("#startdateoee").val().split(" ").join("T"));
			var edtVal = new Date($("#enddateoee").val().split(" ").join("T"));
			console.log([sdtVal, edtVal]);
			if (sdtVal.getTime() >= edtVal.getTime()) {
				updateTips("Invalid date time range selected!");
				return false;
			};
		});		
	});
</script>';
$hookReplace['help'] = '<a href="#">Selection Inputs</a><div>Most form inputs are disabled until enough information is given;
<ul><li>the "add" action will be preselected for you, this option is not available when editing records.</li>
<li>A date time must be selected.</li>
<li>Department Equipment must be selected first, this enables Category.</li>
<li>Category must be selected second, this enables Discipline.</li>
<li>Then finally select a Reason.</li></ul>
After the final selection is made the submit button will become enabled. if a selection is removed the process is reset uptil that point.</div>';
require_once 'includes/footer.php'; ?>