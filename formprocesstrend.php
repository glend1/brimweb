<?PHP 
$title = 'Process Trend';
require_once 'includes/header.php';
if (!fCanSeePublic(@$_SESSION['permissions']['page'][10] >= 100)) {
	$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
$name = '';
$publicBool = 0;
$aDE['department'] = 0;
$aDE['equipment'] = 0;
$taglist = '';
if (isset($_GET['id'])) {
	if ($_SESSION['id'] != 1) {
		$joinWhere = 'where userfk = ' . $_SESSION['id'] . ' or ' . fOrThemReturn($_SESSION['permissions']['group'], 100, 'groupfk');
	} else {
		$joinWhere = 'where userfk = 0';
	};
	$queryTrend = 'select top 1 json, name, publicbool, departmentfk, departmentequipmentfk, userfk, share from ProcessTrend 
	left join (select distinct processtrendfk, 1 as share from ProcessTrendShare ' . $joinWhere . ') as shares on ProcessTrend.id = shares.ProcessTrendFK
	where id = ' . $_GET['id'];
	$dataTrend = odbc_exec($conn, $queryTrend);
	if (odbc_fetch_row($dataTrend)) {
		if (odbc_result($dataTrend, 6) == $_SESSION['id'] || odbc_result($dataTrend, 7) || @$_SESSION['id'] == 1 || odbc_result($dataTrend, 3)) {
			$trendPref = json_decode(odbc_result($dataTrend, 1), true);
			ksort($trendPref);
			$name = odbc_result($dataTrend, 2);
			$publicBool = odbc_result($dataTrend, 3);
			$aDE['department'] = odbc_result($dataTrend, 4);
			$aDE['equipment'] = odbc_result($dataTrend, 5);
			$edit = true;
			foreach ($trendPref as $key => $array) {
				$taglist .= '<li><b>' . $key . '</b><a href=\'#\'><span class=\'icon-trash\'></span></a><div>Min: <input value="' . $array['min'] . '" name=\'data[' . $key . '][min]\' type=\'text\' /><span class="required"> * </span> Max: <input value="' . $array['max'] . '" name=\'data[' . $key . '][max]\' type=\'text\' /><span class="required"> * </span> DP: <select name=\'data[' . $key . '][dp]\'>';
				for ($i = 0; $i < 5; $i++) {
					$taglist .= '<option ';
					if ($i == $array['dp']) {
						$taglist .= 'selected ';
					};
					$taglist .= 'value=\'' . $i . '\'>' . $i . '</option>';
				};
				$taglist .= '</select><br />Type: <select name=\'data[' . $key . '][type]\'>';
				$typeList = ['smooth', 'step'];
				foreach ($typeList as $value) {
					$taglist .= '<option ';
					if ($value == $array['type']) {
						$taglist .= 'selected ';
					}
					$taglist .= 'value=\'' . $value . '\'>' . ucwords($value) . '</option>';
				};
				$taglist .= '</select> Invert: <select name=\'data[' . $key . '][invert]\'>';
				$invertList = ['false', 'true'];
				foreach ($invertList as $value) {
					$taglist .= '<option ';
					if ($value == $array['invert']) {
						$taglist .= 'selected ';
					}
					$taglist .= 'value=\'' . $value . '\'>' . ucwords($value) . '</option>';
				};
				$taglist .= '</select></div></li>';
			};
		} else {
			$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
			$_SESSION['uiState'] = 'error';
			fRedirect();
		};
	} else {
		$_SESSION['sqlMessage'] = 'Trend not found!';
		$_SESSION['uiState'] = 'error';
		fRedirect();
	};
};
$stdOut .= '<div class="tagnameclass">
<h3>Available Tags</h3>
<ul id="filteredtags">
</ul>
<form action="includes/ajax.taglist.php" method="get">
<input type="text" name="filter" />
<input type="submit" value="Filter!" id="filtersubmit" />
</form>
</div>
<div class="tagnameclass">
<form action="includes/changeprocesstrend.php" id="trendsave" method="post">
<h3>Trend Properties</h3>
<div class="trenddetails"><label for="trendtitle">Title: </label><input value="' . $name . '" type="text" name="title" id="trendtitle" /><span class="required"> * </span> <label for="public">Public:</label><input id="public" type="checkbox" ';
if ($publicBool == 1) {
	$stdOut .= 'checked';
};
$stdOut .= ' value="true" name="public"/>
<label for="department">Department: </label><select name="department" id="department"><option value="none">None</option>';
$queryDepartment = 'select department.id, department.name, departmentequipment.id, departmentequipment.name 
from department
left join DepartmentEquipment on departmentfk = department.id ';
if (@$_SESSION['id'] != 1 && isset($_SESSION['id'])) {
	$queryDepartment .= ' where ' . fOrThemReturn(@$_SESSION['permissions']['department'], 100, 'department.id');
};  
$queryDepartment .= ' order by department.name asc, departmentequipment.name asc';
$dataDepartment = odbc_exec($conn, $queryDepartment);
while(odbc_fetch_row($dataDepartment)) {
	$departmentLookup[odbc_result($dataDepartment, 1)] = odbc_result($dataDepartment, 2);
	$equipmentLookup[odbc_result($dataDepartment, 3)] = odbc_result($dataDepartment, 4);
	$departmentEquipment[odbc_result($dataDepartment, 1)][] = odbc_result($dataDepartment, 3);
};
$equipmentOption[0] = '<option value="none">None</option>';
foreach ($departmentEquipment as $departmentKey => $equipmentArray) {
	$equipmentOption[$departmentKey] = '<option value="none">None</option>';
	foreach($equipmentArray as $equipmentKey) {
		if ($equipmentKey) {
			$selected = '';
			if (isset($aDE['equipment'])) {
				if ($aDE['department'] == $departmentKey && $aDE['equipment'] == $equipmentKey) {
					$selected = 'selected';
				};
			};
			$equipmentOption[$departmentKey] .= '<option ' . $selected . ' value="' . $equipmentKey . '">' . $equipmentLookup[$equipmentKey] . '</option>';
		};
	};
	$stdOut .= '<option ';
	if ($aDE['department'] == $departmentKey) {
		$stdOut .= 'selected ';
	};
	$stdOut .= 'value="' . $departmentKey . '">' . $departmentLookup[$departmentKey] . "</option>";
};
$stdOut .= '</select><span class="required"> * </span> <label for="equipment">Equipment: </label><select name="equipment" id="equipment">' . $equipmentOption[$aDE['department']] . '</select></div><h3>Current Tags</h3><ul id="currenttags">' . $taglist . '</ul>';
if (isset($_GET['id'])) {
	$stdOut .= '<input type="hidden" value="' . $_GET['id'] . '" name="id" />';
};
$stdOut .= '<input type="submit" id="preview" value="Preview!" /><input type="submit" id="save" value="Save!" /></form></div><div class="requiredhint"><span class="required"> * </span>are required fields.</div>
<script type="text/javascript">
	$(function() {
		var loading = $(\'<div id="loading"><img src="images/loading.gif" alt="Loading" title="Loading" /></div>\');
		var ajaxnotification = $("#ajax-notification");
		var currentTags = $("#currenttags");
		$("#filtersubmit").click(function () {
			var tagSearch = $(this).prev().val();
			var excludeTags = new Array();
			$("#currenttags li b").each(function() {
				excludeTags.push($(this).text());	
			});
			//console.log(excludeTags);
			$.ajax({
				// the URL for the request
				url: "includes/ajax.taglist.php",
				// the data to send (will be converted to a query string)
				data: { filter: tagSearch, exclude: excludeTags },
				// whether this is a POST or GET request
				type: "GET",
				// the type of data we expect back
				dataType : "json",
				beforeSend: function() {
						$("#filteredtags").html(loading);
						updateNotifications("something", "complete", ajaxnotification);
					},
				// code to run if the request succeeds;
				// the response is passed to the function
				success: function( json ) {
						updateNotifications("Action failed, " + json.status, json.status, ajaxnotification);
						$("#filteredtags").html(json.oreturn);
						$("#filteredtags li a").click(function () {
							var jthis = $(this).parent();
							var tagname = jthis.find(".tagname").text();
							var movedElem = $("<li><b>" + tagname + "</b><a href=\'#\'><span class=\'icon-trash\'></span></a><div>Min: <input name=\'data[" + tagname + "][min]\' type=\'text\' /><span class=\'required\'> * </span> Max: <input name=\'data[" + tagname + "][max]\' type=\'text\' /><span class=\'required\'> * </span> DP: <select name=\'data[" + tagname + "][dp]\'><option value=\'0\'>0</option><option value=\'1\'>1</option><option value=\'2\'>2</option><option value=\'3\'>3</option><option value=\'4\'>4</option></select><br />Type: <select name=\'data[" + tagname + "][type]\'><option value=\'smooth\'>Smooth</option><option value=\'step\'>Step</option></select> Invert: <select name=\'data[" + tagname + "][invert]\'><option value=\'false\'>False</option><option value=\'true\'>True</option></select></div></li>");
							movedElem.appendTo(currentTags);
							movedElem.find("a").click(function() {
								$(this).parent().remove();
								return false;
							});
							jthis.remove();
							return false;
						});
					},
				// code to run if the request fails; the raw request and
				// status codes are passed to the function
				error: function( xhr, status, errorThrown ) {
						updateNotifications( xhr.status + ": " + xhr.statusText, xhr.status, ajaxnotification);
					},
				// code to run regardless of success or failure
				complete: function( xhr, status ) {
						loading.remove();
					}
			});
			return false;
		});
		$("#currenttags a").click( function() {
			$(this).parent().remove();
		});
		function fTestForm() {
			var bValid = true;
			var selectDepartment = $("#department");
			var selectEquipment = $("#equipment");
			var currentTags = $("#currenttags input");
			$("input, #department, #equipment").removeClass( "ui-state-error" );
			if (currentTags.length < 1) {
				bValid = false;
				updateTips("No Tagnames found!");
				return false;
			};
			currentTags.each(function() {
				bValid = bValid && checkNumber(  $(this), "Trend preferences must be Numeric." );
			});
			if (bValid == false) {
				return false;
			};
			bValid = bValid && checkLength( $("#trendtitle"), "Title", 1, 60 );
			if (bValid == false) {
				return false;
			};
			if (selectDepartment.val() + selectEquipment.val() == "nonenone") {
				updateTips("you must set a Department or Equipment!");
				selectDepartment.addClass("ui-state-error");
				selectEquipment.addClass("ui-state-error");
				bValid = false;
				return false;
			};
			if (bValid == false) {
				return false;
			};
			return true;
		};
		$("#preview").click(function () {
			if (fTestForm() == false) {
				return false;	
			}
			//console.log($("#trendsave").prop("ACTION"));
			$("#trendsave").prop("action", "processtrend.php");
			$("#trendsave").prop("method", "get");
			//console.log($("#trendsave").prop("ACTION"));
		});
		$("#save").click(function () {';
		if (isset($_SESSION['id'])) {
			$stdOut .= 'if (fTestForm() == false) {
				return false;	
			}';
		} else {
			$stdOut .= 'alert("You must be logged in to perform this action!");
			return false;';
		};
		$stdOut .= '});
		var equipmentOption = ' . json_encode($equipmentOption) . ';
		$("#department").change(function() {
			$("#equipment").html(equipmentOption[this.options[this.selectedIndex].value]);
		});
	});
</script>';
$hookReplace['help'] = '<a href="#">Adding Tags</a><div>First you must Filter on tagname or description for a list of Available Tags, once filtered clicking the plus icon in the left most list will move them to the list on the right hand side, this signifies that the Tag has been added.</div><a href="#">Removing Tags</a><div>Clicking the Trash Bin in the right most list removes that tag from the list of Current Tags list, this signifies that the Tag has been removed.</div>' . $helptext['trendsave'] . $helptext['departmentequip'];
require_once 'includes/footer.php'; ?>