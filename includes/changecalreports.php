<?PHP
require_once 'functions.php';
if ($_SESSION['id'] != 1) {
	$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
function fTestForm(&$out, $test, $table = 'unknown', $col = 'unknown', $function = 'unknown') {
	if (isset($out['count'])) {
		$out['count']++;
	} else {
		$out['count'] = 1;
	};
	$break = false;
	if (isset($_POST[$test])) {
		if (empty($_POST[$test])) {
			$break = true;
		} else {
			switch ($function) {
				case 'text':
					if ($valid = fTextDatabase($_POST[$test], 60)) {
						$out[$table][$col] = '\'' . $valid . '\'';
					} else {
						$break = true;
					};
					break;
				case 'int':
					if (fValidNumber($_POST[$test])) {
						$out[$table][$col] = $_POST[$test];
					} else {
						$break = true;
					};
					break;
				case 'array':
					if (fValidArray($_POST[$test])) {
						$out[$table][$col] = $_POST[$test];
					} else {
						$break = true;
					};
					break;
				case 'date':
					if ($valid = fValidDate($_POST[$test])) {
						$out[$table][$col] = '\'' . $_POST[$test] . '\'';
					} else {
						$break = true;
					};
					break;
				default:
					$out[$table][$col] = $_POST[$test];
					break;
			};
		};
	} else {
		$break = true;
	};
	if ($break == true) {
		$_SESSION['sqlMessage'] = 'Form not complete or invalid on call #' . $out['count'] . '!';
		$_SESSION['uiState'] = 'error';
		fRedirect();
	};
};
if (isset($_POST['id'])) {
	if (!empty($_POST['id'])) {
		$form['id'] = $_POST['id'];
	};
};
if (isset($_POST['delete'])) {
	$deleteRange = 'delete from ReportCalendarRange where id = ' . $_POST['delete'];
	odbc_exec($conn, $deleteRange);
	$deleteValue = 'delete from ReportCalendarRangeValue where reportcalendarrangefk = ' . $_POST['delete'];
	odbc_exec($conn, $deleteValue);
	$_SESSION['sqlMessage'] = 'Report Deleted';
	$_SESSION['uiState'] = 'active';
	fRedirect();
} else {
	fTestForm($blank, 'caltype', 'caltype', 'id', 'int');
	if (isset($blank['caltype']['id'])) {
		fTestForm($form, 'rname', 'ReportCalendarRange', 'name', 'text');
		fTestForm($form, 'typerepeat', 'ReportCalendarRange', 'reportcalendartyperepeatfk', 'int');
		if (isset($form['ReportCalendarRange']['reportcalendartyperepeatfk'])) {
			switch ($form['ReportCalendarRange']['reportcalendartyperepeatfk']) {
				//week day
				case 9:
					fTestForm($form, 'weekfrequency', 'ReportCalendarRangeValue', 'value', 'array');
					//break;
				//on day
				case 2:
					//break;
				//every
				case 1:
				case 3:
				case 4:
				case 7:
				default:
					fTestForm($form, 'everyfrequency', 'ReportCalendarRange', 'value', 'int');
					fTestForm($blank, 'endtype');
					if (isset($blank['unknown']['unknown'])) {
						switch ($blank['unknown']['unknown']) {
							case 'after';
								fTestForm($form, 'afternumber', 'ReportCalendarRange', 'endcalc', 'int');
								break;
							case 'date';
								fTestForm($form, 'reportend', 'ReportCalendarRange', 'enddate', 'date');
								break;
							case 'never';
							default:
								break;
						};
					};
					//break;
				//single
				case 5:
					fTestForm($form, 'reportstart', 'ReportCalendarRange', 'startdate', 'date');
					//break;
				//other
				case 6:
					fTestForm($form, 'fname', 'ReportCalendarRange', 'reportcalendarfunctionnamefk', 'int');
					if (isset($_POST['arg'])) {
						if (!empty($_POST['arg'])) {
							if ($valid = fValidJson($_POST['arg'])) {
								$form['ReportCalendarRange']['argjson'] = '\'' . str_replace("'", "''", $valid) . '\'';
							} else {
								$_SESSION['sqlMessage'] = 'Form not complete or invalid!';
								$_SESSION['uiState'] = 'error';
								fRedirect();
							};
						};
					};
					break;
			};
		};
	};
	unset($form['count']);
	if (isset($form['ReportCalendarRange']['endcalc'])) {
		$caltypes = [1 => ['s' => 'D', 'i' => '7'], 2 => 'M', 3 => 'Y', 6 => 'D'];
		if (is_array($caltypes[$blank['caltype']['id']])) {
			$interval = ($caltypes[$blank['caltype']['id']]['i'] * $form['ReportCalendarRange']['endcalc'] * $form['ReportCalendarRange']['value']) . $caltypes[$blank['caltype']['id']]['s'];
		} else {
			$interval = ($form['ReportCalendarRange']['value'] * $form['ReportCalendarRange']['endcalc']) . $caltypes[$form['ReportCalendarRange']['endcalc']];
		};
		unset($form['ReportCalendarRange']['endcalc']);
		$reportEnd = new DateTime(strtotime($form['ReportCalendarRange']['startdate']));
		$reportEnd->add(new DateInterval('P' . $interval));
		$form['ReportCalendarRange']['enddate'] = $reportEnd->format('\'Y-m-d\'');
	};
	if (isset($_POST['id'])) {
		$queryId = 0;
		if (isset($form['ReportCalendarRange'])) {
			$queryUpdate[$queryId] = 'update ReportCalendarRange';
			$sep = ' set ';
			foreach ($form['ReportCalendarRange'] as $col => $value) {
				$queryUpdate[$queryId] .= $sep . $col . ' = ' . $value;
				$sep = ', ';
			};
			$queryUpdate[$queryId++] .= ' where id = ' . $_POST['id'];
			$queryUpdate[$queryId++] = 'delete from ReportCalendarRangeValue where ReportCalendarRangefk = ' . $_POST['id'];
			if ($form['ReportCalendarRange']['reportcalendartyperepeatfk'] == 9) {
				$queryUpdate[$queryId] = 'insert into ReportCalendarRangeValue (ReportCalendarRangeFK, value) ';
				$sepDays = 'VALUES (';
				foreach ($form['ReportCalendarRangeValue']['value'] as $value) {
					$queryUpdate[$queryId] .= $sepDays . $_POST['id'] . ', ' . $value;
					$sepDays = '), (';
				};
				$queryUpdate[$queryId++] .= ')';
			};
			foreach ($queryUpdate as $update) {
				print($update . '<br />');
				odbc_exec($conn, $update);
			};
			$_SESSION['sqlMessage'] = 'Report configuration updated!';
			$_SESSION['uiState'] = 'active';
		};
	} else {
		odbc_autocommit($conn,false);
		foreach ($form as $table => $array) {
			$query = 'insert into ' . $table;
			$sep = '';
			$cols = ' (';
			$vals = ' VALUES (';
			foreach($array as $col => $value) {
				$cols .= $sep . $col;
				if (is_array($value)) {
					$cols .= ', reportcalendarrangefk';
					foreach ($value as $key => $valueValue) {
						if (!isset($colArray[$key])) {
							$colArray[$key] = '';
							$arraySep = '';
						};
						$colArray[$key] .= $arraySep . $valueValue . ', ' . $sqlID;
						$arraySep = ',';
					};
					$multiSep = '';
					foreach ($colArray as $valueValueValue) {
						$vals .= $multiSep . $valueValueValue;
						$multiSep = '),(';
					};	
				} else {
					$vals .= $sep . $value;
				};
				$sep = ',';
			};
			$cols .= ')';
			$vals .= ')';
			print($query . $cols . $vals . '<br />');
			odbc_exec($conn, $query . $cols . $vals);
			$sqlID = fGetId();
		};
		odbc_commit($conn);
		odbc_autocommit($conn,true);
		$_SESSION['sqlMessage'] = 'Report configured!';
		$_SESSION['uiState'] = 'active';
	};
};
fRedirect(); ?>