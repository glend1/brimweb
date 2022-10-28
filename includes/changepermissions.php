<?PHP
require_once 'functions.php';
if (!fCanSee($_SESSION['permissions']['group'][$_POST['id']] >= 200)) {
		$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
		$_SESSION['uiState'] = 'error';
		fRedirect();
};
foreach ($_POST as $key => $aValue) {
	if (is_array($aValue)) {
		foreach($aValue as $keyKey => $value) {
			if ($value == 'none') {
				unset($GLOBALS['_POST'][$key][$keyKey]);
			};
		};
	};
};
$iQueryStatus = 0;
function fPermissionQueries ($valueTestKey) {
	//print('e' . $valueTestKey);
	global $valueTest, $currentGroupID, $conn, $iQueryStatus;
	$tableFK = $valueTestKey . 'fk';
	$formID = $valueTestKey . 'check';
	$formLevel = 'accesslevel' . $valueTestKey;
	if (!empty($valueTest[$valueTestKey])) {
		if (isset($_POST[$formID][$valueTest[$valueTestKey]])) {
			if (isset($_POST[$formLevel][$valueTest[$valueTestKey]])) {
				if ($_POST[$formLevel][$valueTest[$valueTestKey]] == $valueTest['level']) {
					//print('found in database and value is the same, so do nothing<br/ >');
					unset($GLOBALS['_POST'][$formID][$valueTest[$valueTestKey]]);
				} else {
					//print('found in database, so update<br/ >');
					/*if (!fCanSee($_SESSION['permissions'][$valueTestKey][$valueTest[$valueTestKey]] >= 200)) {
						$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
						$_SESSION['uiState'] = 'error';
						fRedirect();
					};*/
					$queryUpdate = 'update permissions set level = ' . $_POST[$formLevel][$valueTest[$valueTestKey]] . ' where groupfk = ' . $_POST['id'] . ' and ' . $tableFK . ' = ' . $valueTest[$valueTestKey];
					odbc_exec($conn, $queryUpdate);
					//print($queryUpdate . '<br />');
					unset($GLOBALS['_POST'][$formID][$valueTest[$valueTestKey]]);
				};
			} else {
				$iQueryStatus++;
				//print('form incomplete1<br />');
			};
		} else {
			/*if (!fCanSee($_SESSION['permissions'][$valueTestKey][$valueTest[$valueTestKey]] >= 300)) {
				$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
				$_SESSION['uiState'] = 'error';
				fRedirect();
			};*/
			//print('delete<br />');
			$queryDelete = 'delete from permissions where groupfk = ' . $_POST['id'] . ' and ' . $tableFK . ' = ' . $valueTest[$valueTestKey];
			//print($queryDelete . '<br />');
			odbc_exec($conn, $queryDelete);
		};
	};
};
function fPermissionQueriesInsert ($valueTestKey) {
	global $valueTest, $currentGroupID, $conn, $iQueryStatus;
	$tableFK = $valueTestKey . 'fk';
	$formID = $valueTestKey . 'check';
	$formLevel = 'accesslevel' . $valueTestKey;
	if (isset($_POST[$formID])) {
		foreach ($_POST[$formID] as $key => $value) {
			if (isset($_POST[$formLevel][$key])) {
				/*if (!fCanSee($_SESSION['permissions'][$valueTestKey][$valueTest[$valueTestKey]] >= 300)) {
					$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
					$_SESSION['uiState'] = 'error';
					fRedirect();
				};*/
				$queryInsert = 'insert into permissions (' . $tableFK . ', level, groupfk) values (' . $key . ', ' . $_POST[$formLevel][$key] . ', ' . $_POST['id'] . ')';
				//print($queryInsert . '<br />');
				odbc_exec($conn, $queryInsert);
			} else {
				$iQueryStatus++;
				//print('form incomplete2<br />');
			};
		};
	} else {
		//$iQueryStatus++;
		//print('form incomplete<br />');
	};
};
function fErrorTest ($name) {
	global $iQueryStatus;
	$formID = $name . 'check';
	$formLevel = 'accesslevel' . $name;
	//print($_POST[$formID][$key]);
	foreach ($_POST[$formLevel] as $key => $value) {
		if (!isset($_POST[$formID][$key])) {
			$iQueryStatus++;
			//print('form incomplete3<br />');
		};
	};
};
fErrorTest ('area');
fErrorTest ('department');
fErrorTest ('page');
fErrorTest ('discipline');
fErrorTest ('groupadmin');
$queryLevel = 'select areafk, departmentfk, pagefk, id, level, groupfk, groupadminfk, disciplinefk from permissions where groupfk = ' . $_POST['id'];
$dataLevel = odbc_exec($conn, $queryLevel);
while (odbc_fetch_row($dataLevel)) {
	//because empty() cant handle functions *sigh*
	$valueTest = ['area' => odbc_result($dataLevel, 1), 'department' => odbc_result($dataLevel, 2), 'page' => odbc_result($dataLevel, 3), 'level' => odbc_result($dataLevel, 5), 'groupadmin' => odbc_result($dataLevel, 7), 'discipline' => odbc_result($dataLevel, 8)];
	$currentGroupID = odbc_result($dataLevel, 6);
	fPermissionQueries ('area');
	fPermissionQueries ('department');
	fPermissionQueries ('page');
	fPermissionQueries ('discipline');
	fPermissionQueries ('groupadmin');
};
fPermissionQueriesInsert ('area');
fPermissionQueriesInsert ('department');
fPermissionQueriesInsert ('page');
fPermissionQueriesInsert ('discipline');
fPermissionQueriesInsert ('groupadmin');
if ($iQueryStatus < 1) {
	$_SESSION['sqlMessage'] = 'Permissions updated!';
	$_SESSION['uiState'] = 'active';
} else {
	$_SESSION['sqlMessage'] = 'Permissions updated with ' . $iQueryStatus . ' error(s)!';
	$_SESSION['uiState'] = 'error';
};
odbc_close($conn);
fRedirect(); ?>