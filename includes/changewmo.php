<?PHP
require_once 'functions.php';
if (isset($_POST['comment'])) {
	$strippedBody = trim(strip_tags($_POST['comment']));
	if (!empty($strippedBody)) {
		$aSql['comment'] = fTextDatabase($_POST['comment'], 'na', true);
	};
};
if (isset($_POST['json'])) {
	if (!empty($_POST['json'])) {
		$aSql['json'] = fTextDatabase($_POST['json'], 'na', true);
	};
};
if (isset($_POST['version'])) {
	$aSql['version'] = $_POST['version'];
};
if (isset($_POST['script'])) {
	$aSql['PermissionAbstractFK'] = $_POST['script'];
};
if (isset($_POST['ForeignWMO'])) {
		$aSql['ForeignWMOFK'] = $_POST['ForeignWMO'];
};
if (isset($_POST['id'])) {
		$aSql['id'] = $_POST['id'];
};
if (isset($_POST['SameAs'])) {
	if (!empty($_POST['SameAs'])) {
		if (fValidNumber($_POST['SameAs'])) {
			$aSql['SameAsFK'] = $_POST['SameAs'];
		} else {
			$_SESSION['sqlMessage'] = 'Parent WMO must be numeric!';
			$_SESSION['uiState'] = 'error';
			fRedirect();
		};
	};
};
if (isset($_POST['priority'])) {
	$aSql['PriorityFK'] = $_POST['priority'];
};
if (!isset($aSql)) {
	$_SESSION['sqlMessage'] = 'You must complete the form!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
if (isset($_POST['add'])) {
	$formOption = true;
	if (!isset($_SESSION['id'])) {
		$aSql['submitteduserfk'] = 0;
	} else {
		$aSql['submitteduserfk'] = $_SESSION['id'];
	};
	if (!isset($aSql['PermissionAbstractFK'])) {
		$aSql['PermissionAbstractFK'] = 999;
	};
	if (!isset($aSql['version']) || !isset($aSql['PriorityFK']) || !isset($aSql['comment'])) {
		$_SESSION['sqlMessage'] = 'WMO creation failed, you must complete the form!';
		$_SESSION['uiState'] = 'error';
		fRedirect();
	} else {
		$timeUTC = date_create($localTime);
		date_timezone_set($timeUTC, timezone_open('UTC'));
		$datetimestamp = date_format($timeUTC, 'Y-m-d H:i:s');
		odbc_autocommit($conn,false);
		$queryAddInsert = 'insert into WMO (SubmittedUserFK, Version, OpenDateTime, PermissionAbstractFK, ForeignWMOFK, StatusFK, PriorityFK)
		values (' . $aSql['submitteduserfk'] . ', ' . $aSql['version'] . ', \'' . $datetimestamp . '\', ' . $aSql['PermissionAbstractFK'] . ', \'' . $aSql['ForeignWMOFK'] . '\', 3, ' . $aSql['PriorityFK'] . ')';
		odbc_exec($conn, $queryAddInsert);
		$sqlID = fGetId();
		odbc_commit($conn);
		odbc_autocommit($conn,true);
		$queryAddComment = 'insert into wmocomment (wmofk, submitteddatetime, userfk, comment)
		values (' . $sqlID . ', \'' . $datetimestamp . '\', ' . $aSql['submitteduserfk'] . ', \'' . $aSql['comment'] . '\')';
		odbc_exec($conn, $queryAddComment);
		$queryAddVotes = 'insert into wmovotes (wmofk, userfk, rating)
		values (' . $sqlID . ', ' . $aSql['submitteduserfk'] . ', 1)';
		odbc_exec($conn, $queryAddVotes);
		if (isset($aSql['json'])) {
			$queryAddJson = 'insert into wmojson (wmofk, json)
			values (' . $sqlID . ', \'' . $aSql['json'] . '\')';
			odbc_exec($conn, $queryAddJson);
		} else {
			$aSql['json'] = false;
		};
			//subscribe check
			if (isset($_POST['subscribe'])) {
				$querySubscribe = 'insert into subscriptions (subscriptiontypefk, subscriptionfk, userfk)
				values (1, ' . $sqlID . ', ' . $aSql['submitteduserfk'] . ')';
				$dataSubscribe = odbc_exec($conn, $querySubscribe);
			};
			//email go!
			$queryEmailUser = 'select top 1 name, email, mobile from users where id = ' . $aSql['submitteduserfk'];
			$dataEmailUser = odbc_exec($conn, $queryEmailUser);
			odbc_fetch_row($dataEmailUser);
			$queryEmailPage = 'select top 1 abstractname from permissionabstract where id = ' . $aSql['PermissionAbstractFK'];
			$dataEmailPage = odbc_exec($conn, $queryEmailPage);
			odbc_fetch_row($dataEmailPage);
			$queryEmailPri = 'select top 1 WMOPriorityCode.Description, WMOType.Description from WMOPriorityCode 
			join wmotype on typefk = wmotype.id
			where WMOPriorityCode.id = ' . $aSql['PriorityFK'];
			$dataEmailPri = odbc_exec($conn, $queryEmailPri);
			odbc_fetch_row($dataEmailPri);
			$mail = new PHPMailer();
			$mail->isSMTP();
			$mail->SMTPDebug = 0;
			$mail->Host = "172.30.4.15";
			$mail->Port = 25;
			$mail->SMTPAuth = false;
			$mail->setFrom('brimweb@matthey.com', 'Brimweb');
			$mail->addAddress('glen@cogentautomation.co.uk', 'Glen Dovey');
			$mail->Subject = 'New WMO';
			$emailCont = '<p>' . odbc_result($dataEmailUser, 1) . ' (' . odbc_result($dataEmailUser, 2) . '/' . odbc_result($dataEmailUser, 3) . ') created a new ' . odbc_result($dataEmailPri, 2) . ' because ' . odbc_result($dataEmailPri, 1) . ' on page ' . odbc_result($dataEmailPage, 1) . '</p><h3>Comment</h3> ' . $aSql['comment'] . '<h3>JSON</h3> ' . fPrintList(json_decode($aSql['json'], true));
			$mail->msgHTML($emailCont, dirname(__FILE__));
			if (!$mail->send()) {
				$_SESSION['sqlMessage'] = "Mailer Error: " . $mail->ErrorInfo;
				$_SESSION['uiState'] = 'error';
			} else {
				$_SESSION['sqlMessage'] = 'WMO Created!';
				$_SESSION['uiState'] = 'active';
			};
	};
};
if (isset($_POST['update'])) {
	unset($aSql['id']);
	$formOption = true;
	if ($_SESSION['id'] != 1) {
		$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
		$_SESSION['uiState'] = 'error';
		fRedirect();
	};
	if (!isset($_POST['id'])) {
		$_SESSION['sqlMessage'] = 'You must complete the form!';
		$_SESSION['uiState'] = 'error';
		fRedirect();
	};
	$sUpdate = '';
	$sep = '';
	foreach ($aSql as $key => $value) {
		if ($value === '') {
			$newValue = 'NULL';
		} else {
			$newValue = '\'' . $value . '\'';
		};
		$sUpdate .= $sep . $key . ' = ' . $newValue;
		$sep = ', ';
	};
	$queryUpdate = 'update wmo set ' . $sUpdate . ' where id = ' . $_POST['id'];
	odbc_exec($conn, $queryUpdate);
	odbc_commit($conn);
	$_SESSION['sqlMessage'] = 'WMO Updated!';
	$_SESSION['uiState'] = 'active';
};
if (isset($_POST['delete'])) {
	$formOption = true;
	if ($_SESSION['id'] != 1) {
		$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
		$_SESSION['uiState'] = 'error';
		fRedirect();
	};
	$queryDelete = 'delete from wmo where id = ' . $_POST['id'];
	odbc_exec($conn, $queryDelete);
	odbc_commit($conn);
	$_SESSION['sqlMessage'] = 'WMO Deleted!';
	$_SESSION['uiState'] = 'active';
};
if (!isset($formOption)) {
	$_SESSION['sqlMessage'] = 'You must complete the form!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
odbc_close($conn);
fRedirect();
?>