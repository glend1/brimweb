<?PHP
	date_default_timezone_set("Europe/London");
	$localTime = date('Y-m-d H:i:s');
	date_default_timezone_set("UTC");
	require_once 'PHP-Mailer/class.phpmailer.php';
	require_once 'PHP-Mailer/class.smtp.php';
	require_once 'secrets.php'
	session_start();
	if ($_SERVER['SERVER_NAME'] != 'brimweb.brimcontrols.local' && !isset($_COOKIE['dev'])) {
		header('Location:http://brimweb.brimcontrols.local');
	};
	$urlPath = 'http://' . $_SERVER['SERVER_NAME'];
	if ($_SERVER['SERVER_NAME'] == 'glen-pc.brimcontrols.local') {
		$urlPath .= '/brimweb/';
	} else {
		$urlPath .= '/';
	};
	function fFrequencyTranslate ($sType, $sRepeat, $sValue, $sDay) {
		$freq = '';
		$days = explode(',', $sDay);
		$daySep = '';
		foreach ($days as $day) {
			if (strlen($day) > 0) {
				switch ($day) {
					case 0:
						$freq .= $daySep . 'Monday';
						break;
					case 1:
						$freq .= $daySep . 'Tuesday';
						break;
					case 2:
						$freq .= $daySep . 'Wednesday';
						break;
					case 3:
						$freq .= $daySep . 'Thursday';
						break;
					case 4:
						$freq .= $daySep . 'Friday';
						break;
					case 5:
						$freq .= $daySep . 'Saturday';
						break;
					case 6:
						$freq .= $daySep . 'Sunday';
						break;
				};
				$daySep = ', ';
			};
		};
		if ($sRepeat != 'Every' && $sRepeat != 'On Day') {
			$freq .= ' ' . $sRepeat . '. Every ';
		} else {
			$freq .= ' ' . $sRepeat;
		};
		$freq .= ' ' . $sValue . ', ' . $sType;
		switch (trim($freq)) {
			case 'Every 1, Daily';
				$freq = 'Daily';
				break;
			default:
				break;
		};
		return $freq;
	};
	function fMailNav($currentPage = 'nothing') {
		$options = ['folder' => ['inbox', 'outbox'], 'action' => ['compose']];
		$out = '';
		foreach($options as $header => $array) {
			if (!(count($array) == 1 && $array[0] == $currentPage)) {
				$out .= '<div>' . ucwords($header) . '</div><ul>';
				foreach($array as $option) {
					if ($currentPage != $option) {
						$out .= '<li><a href="' . $option . '.php">' . ucwords($option) . '</a></li>';
					};
				};
				$out .= '</ul>';
			};
		};
		return '<div id="subnav">' . $out . '</div>';
	};
	function fSendMessages($to, $type, $message) {
		GLOBAL $conn;
		GLOBAL $localTime;
		$timeUTC = date_create($localTime);
		date_timezone_set($timeUTC, timezone_open('UTC'));
		$datetimestamp = date_format($timeUTC, 'Y-m-d H:i:s');
		$sep = '';
		$queryMessage = '';
		switch ($type) {
			case 1:
				$queryMessage = 'insert into message (timestamp, toid, subject, message, readbit)
				VALUES ';
				$subject = 'NO: New Tracker Notification';
				break;
			default:
				break;
		};
		foreach ($to as $id) {
			if ($id != $_SESSION['id']) {
				$queryMessage .= $sep . '(\'' . $datetimestamp . '\', ' . $id . ', \'' . $subject . '\', \'' . $message . '\', 0)';
				$sep = ', ';
				$queryComplete = true;
			};
		};
		if ($queryComplete);
		odbc_exec($conn, $queryMessage);
	};
	function fGetId() {
		GLOBAL $conn;
		$queryID = 'select top 1 @@identity';
		$dataID = odbc_exec($conn, $queryID);
		odbc_fetch_row($dataID);
		return odbc_result($dataID, 1);
	};
	function fTableFooter($options = Array()) {
		if (empty($options)) {
			return false;
		};
		$out = '<tfoot>';
		/*if (isset($options['totals'])) {
			foreach ($options['totals'] as $row) {
				$out .= '<tr>';
				for ($i = 0; $i < $options['cols']; $i++) {
						$out .= '<td ';
						foreach ($row as $k => $type) {
							if ($i == $k) {
								$out .= 'data-math="col-' . $type . '"';
							};
						};
						$out .= '></td>';
				};
				$out .= '</tr>';
			};
		};*/
		$out .= '<tr><td class="table-pager" id="' . $options['id'] . '-pager" colspan="' . $options['cols'] . '">
			<span class="page-left"><button class="first"><span class="icon-caret-left"></span><span class="icon-caret-left"></span></button>
			<button class="prev"><span class="icon-caret-left"></span></button>
			</span>
			<span class="pagedisplay"></span>
			<span class="page-right">
			<button class="next"><span class="icon-caret-right"></span></button>
			<button class="last"><span class="icon-caret-right"></span><span class="icon-caret-right"></span></button>
			</span>
		</td></tr></tfoot>';
		return $out;
	};
	function fRandomString() {
		return substr(str_shuffle('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGJIJKLOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyzABCDEFGJIJKLOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyzABCDEFGJIJKLOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyzABCDEFGJIJKLOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyzABCDEFGJIJKLOPQRSTUVWXYZ'),0, 60);
	};
	function fWmoVote($rating, $id, $user) {
		if ($user != $_SESSION['id']) {
			$out = '<div class="vote">Vote:';
			if ($rating <= 0 || $rating == NULL) {
				$out .= '<a data-text="Vote Up" href="includes/wmovote.php?id='. $id . '&action=up"><span class="icon-chevron-sign-up icon-hover-hint"></span></a>';
			} else {
				$out .= '<span class="icon-chevron-sign-up icon-hover-hint"></span>';
			};	
			if ($rating) {
				$out .= '<a data-text="Vote Neutral" href="includes/wmovote.php?id='. $id . '&action=minus"><span class="icon-minus-sign icon-hover-hint"></span></a>';
			} else {
				$out .= '<span class="icon-minus-sign icon-hover-hint"></span>';
			};	
			if ($rating >= 0 || $rating == NULL) {
				$out .= '<a data-text="Vote Down" href="includes/wmovote.php?id='. $id . '&action=down"><span class="icon-chevron-sign-down icon-hover-hint"></span></a>';
			} else {
				$out .= '<span class="icon-chevron-sign-down icon-hover-hint"></span>';
			};	
			$out .= '</div>';
			return $out;
		};
	};
	function fPrintList($array, $class = NULL) {
		$out = '<ul';
		if ($class) {
			$out .= ' class="' . $class . '"';
		};
		$out .= '>';
		if (is_array($array)) {
			foreach ($array as $key => $value) {
				$out .= '<li>' . $key . ' <a href="#"><span class="icon-caret-right icon-large"></span><span class="icon-caret-down icon-large"></span></a>' . fPrintList($value) . '</li>';
			};
		} else {
			$out .= '<li>' . $array . '</li>';
		};
		$out .= '</ul>';
		return $out;
	};
	function fRecordSwap($custom = array()) {
		global $conn;
		global $aDE;
		$default = ['rename' => 'Records', 'table' => false, 'exclude' => array()];
		$used = $custom + $default;
		$i = 0;
		$aOut[$i] = '<li><a href="#" class="ajax-records" data-id="ajax-records">' . $used['rename'] . '</a>'; 
		//if ($used['table']) {
			$aOut[$i] .= ' <a data-text="Table Headers" data-id="record" class="table-row-button" href="none.php"><span class="icon-list icon-hover-hint"></span></a></li>';
		//};
		$i++;
		if (!in_array('alarm', $used['exclude']) && fCanSeePublic(@$_SESSION['permissions']['page'][12] >= 100)) {
			if (isset($aDE['department'])) {
				$queryValidAlarmDept = 'select top 1 id from alarmgroup where DepartmentFK = ' . $aDE['department'];
				if (isset($aDE['equipment'])) {
					$queryValidAlarmDept .= ' and (departmentequipmentfk = ' . $aDE['equipment'] . ' or departmentequipmentfk is null)';
				} else {;
					$queryValidAlarmDept .= ' and departmentequipmentfk is null';
				};
			} else {
				$queryValidAlarmDept = 'select top 1 groupname 
				from alarmgroup
				right join (select distinct groupname from [wwalmdb].[dbo].[AlarmMaster]) as alarms on alarms.groupname = alarmgroup
				where DepartmentFK is NULL';
			};
			$dataValidAlarmDept = odbc_exec($conn, $queryValidAlarmDept);
			while (odbc_fetch_row($dataValidAlarmDept)) {
				$aOut[$i] = '<li><a href="#" class="ajax-records" data-url="includes/ajax.alarm.php" data-id="ajax-alarm" ';
				if (isset($aDE['department'])) {
					$aOut[$i] .= 'data-department="' . $aDE['department'] . '"';
				};
				if (isset($aDE['equipment'])) {
					$aOut[$i] .= ' data-equipment="' . $aDE['equipment'] . '"';
				};
				$aOut[$i] .= '>Alarms</a> <a data-text="Table Headers" data-id="ajax-alarm" class="table-row-button" href="none.php"><span class="icon-list icon-hover-hint"></span></a> <a data-text="Refresh" href="#" class="ajax-records" data-url="includes/ajax.alarm.php" data-id="ajax-alarm" ';
				if (isset($aDE['department'])) {
					$aOut[$i] .= 'data-department="' . $aDE['department'] . '"';
				};
				if (isset($aDE['equipment'])) {
					$aOut[$i] .= ' data-equipment="' . $aDE['equipment'] . '"';
				};
				$aOut[$i] .= '><span class="icon-refresh icon-hover-hint"></span></a></li>';
				$i++;
			};
		};
		if (!in_array('batch', $used['exclude']) && fCanSeePublic(@$_SESSION['permissions']['page'][4] >= 100)) {
			if (isset($aDE['department'])) {
				$queryValidBatchDept = 'select top 1 id from train where DepartmentFK = ' . $aDE['department'];
				if (isset($aDE['equipment'])) {
					$queryValidBatchDept .= ' and (departmentequipmentfk = ' . $aDE['equipment'] . ' or departmentequipmentfk is null)';
				};
			} else {
				$queryValidBatchDept = 'select top 1 train_id 
				from train
				right join (select distinct train_id from [batchhistory].[dbo].[batchidlog]) as trains on trains.train_id = train
				where DepartmentFK is NULL';
			};
			$dataValidBatchDept = odbc_exec($conn, $queryValidBatchDept);
			while (odbc_fetch_row($dataValidBatchDept)) {
				$aOut[$i] = '<li><a href="#" class="ajax-records" data-url="includes/ajax.batch.php" data-id="ajax-batch" ';
				if (isset($aDE['department'])) {
					$aOut[$i] .= 'data-department="' . $aDE['department'] . '"';
				};
				if (isset($aDE['equipment'])) {
					$aOut[$i] .= ' data-equipment="' . $aDE['equipment'] . '"';
				};
				$aOut[$i] .= '>Batches</a> <a data-text="Table Headers" data-id="ajax-batch" class="table-row-button" href="none.php"><span class="icon-hover-hint icon-list"></span></a> <a data-text="Refresh" href="#" class="ajax-records" data-url="includes/ajax.batch.php" data-id="ajax-batch" ';
				if (isset($aDE['department'])) {
					$aOut[$i] .= 'data-department="' . $aDE['department'] . '"';
				};
				if (isset($aDE['equipment'])) {
					$aOut[$i] .= ' data-equipment="' . $aDE['equipment'] . '"';
				};
				$aOut[$i] .= '><span class="icon-refresh icon-hover-hint"></span></a></li>';
				$i++;
			};
		};
		if (!in_array('trend', $used['exclude']) && fCanSeePublic(@$_SESSION['permissions']['page'][10] >= 100)) {
			$joinWhere = 'where userfk = 0';
			if (isset($_SESSION['id'])) {
				if ($_SESSION['id'] != 1) {
					$joinWhere = 'where userfk = ' . $_SESSION['id'] . ' or ' . fOrThemReturn($_SESSION['permissions']['group'], 100, 'groupfk');
				};
			};
			$queryValidTrendDept = 'select top 1 realdepartmentfk
			from (select processtrend.userfk, processtrend.ID, processtrend.name as trendname, publicbool, users.name as username, share, case 
					when processtrend.DepartmentEquipmentFK IS NOT NULL 
						then departmentequipment.DepartmentFK 
						else processtrend.DepartmentFK end as realdepartmentfk,
				DepartmentEquipmentFK, DepartmentEquipment.Name as departmentequipmentname
				from ProcessTrend 
				left join (select distinct processtrendfk, 1 as share from ProcessTrendShare ' . $joinWhere . ') as shares on ProcessTrend.id = shares.ProcessTrendFK
				left join departmentequipment on processtrend.departmentequipmentfk = departmentequipment.ID
				join users on users.id = processtrend.userfk) as temp1
			left join department on realdepartmentfk = department.id';
			if (isset($_SESSION['id'])) {
				if ($_SESSION['id'] > 1) {
					$queryValidTrendDept .= ' where (publicbool = 1 or temp1.userfk = ' . $_SESSION['id'] . ') and realdepartmentfk = ' . $aDE['department'];
				} else {
					if (isset($aDE['department'])) {
						$queryValidTrendDept .= ' where realdepartmentfk = ' . $aDE['department'];
						if (isset($aDE['equipment'])) {
							$queryValidTrendDept .= ' and (departmentequipmentfk = ' . $aDE['equipment'] . ' or departmentequipmentfk is null)';
						};
					} else {
						$queryValidTrendDept .= ' where realdepartmentfk is NULL';
					};
				};
			} else {
				if (isset($aDE['department'])) {
					$queryValidTrendDept .= ' where realdepartmentfk = ' . $aDE['department'];
					if (isset($aDE['equipment'])) {
						$queryValidTrendDept .= ' and (departmentequipmentfk = ' . $aDE['equipment'] . ' or departmentequipmentfk is null)';
					};
				} else {
					$queryValidTrendDept .= ' where realdepartmentfk is NULL';
				};
			};
			$dataValidTrendDept = odbc_exec($conn, $queryValidTrendDept);
			if (odbc_fetch_row($dataValidTrendDept)) {
				$aOut[$i] = '<li><a href="#" class="ajax-records" data-url="includes/ajax.trend.php" data-id="ajax-trend" ';
				if (isset($aDE['department'])) {
					$aOut[$i] .= 'data-department="' . $aDE['department'] . '"';
				};
				if (isset($aDE['equipment'])) {
					$aOut[$i] .= ' data-equipment="' . $aDE['equipment'] . '"';
				};
				$aOut[$i] .= '>Trends</a> <a data-text="Refresh" href="#" class="ajax-records" data-url="includes/ajax.trend.php" data-id="ajax-trend" ';
				if (isset($aDE['department'])) {
					$aOut[$i] .= 'data-department="' . $aDE['department'] . '"';
				};
				if (isset($aDE['equipment'])) {
					$aOut[$i] .= ' data-equipment="' . $aDE['equipment'] . '"';
				};
				$aOut[$i] .= '><span class="icon-refresh icon-hover-hint"></span></a></li>';
				$i++;
			};
		};
		if (count($aOut) <= 1) {
			$out = '<h3>' . $used['rename'] . ' <a data-text="Table Headers" data-id="record" class="table-row-button" href="none.php"><span class="icon-hover-hint icon-list"></span></a></h3>';
		} else {
			$out = '<h3 id="recordswap"><ul>' . implode('', $aOut) . '</ul></h3>';
		};
		return $out;
	};
	function fGetScriptName() {
		preg_match('/\/(?:.(?!\/))+.php/i', $_SERVER['SCRIPT_FILENAME'], $scriptMatch);
		return substr($scriptMatch[0], 1);
	};
	function fSetDates(&$startDate, &$endDate, $dur) {
		global $localTime;
		if (isset($_GET['startdate']) && isset($_GET['enddate'])) {
			$startDate = $_GET['startdate'];
			$endDate = $_GET['enddate'];
			if ($startDate > $endDate) {
				$_SESSION['sqlMessage'] = 'Invalid date time range selected!';
				$_SESSION['uiState'] = 'error';
				fRedirect();
			};
		} else {;
			$endDate = $localTime;
			$startDate = new DateTime(($endDate));
			$startDate->sub(new DateInterval('P' . $dur . 'D'));
			$startDate = $startDate->format('Y-m-d H:i:s');
		};
	};
	function array_to_input($array, $prefix='') {
		# https://gist.github.com/eric1234/5802030
		$out = '';
		if( (bool)count(array_filter(array_keys($array), 'is_string')) ) {
			foreach($array as $key => $value) {
				if( empty($prefix) ) {
					$name = $key;
				} else {
					$name = $prefix. '[' .$key. ']';
				};
				if( is_array($value) ) {
					$out .= array_to_input($value, $name);
				} else {
					$out .= '<input type="hidden" value="' . $value . '" name="' . $name . '">';
				};
			};
		} else {
			foreach($array as $item) {
				if( is_array($item) ) {
					$out .= array_to_input($item, $prefix . '[]');
				} else {
					$out .= '<input type="hidden" name="' . $prefix . '[]" value="' . $item . '">';
				};
			};
		};
		return $out;
	};
	function fQueryString($array = array()) {
		$defaults = ['include' => array(), 'exclusive' => array(), 'exclude' => array(), 'output' => 'url'];
		$getProxy = array();
		$out = '';
		$array += $defaults;
		if (empty($array['exclusive'])) {
			$getProxy = $_GET;
		} else {
			foreach ($array['exclusive'] as $exclusive) {
				if (isset($_GET[$exclusive])) {
					$getProxy[$exclusive] = $_GET[$exclusive];
				};
			};
		};
		$aOut = $array['include'] + $getProxy;
		foreach ($array['exclude'] as $value) {
			unset($aOut[$value]);
		};
		switch ($array['output']) {
			case 'url':
				$sep = '?';
				foreach ($aOut as $key => $value) {
					if ($value !== NULL) {
						if (!is_array($value)) {
							$out .= $sep . urlencode($key) . '=' . urlencode($value);
							$sep = '&';
						} else {
							foreach($value as $arrayKey => $arrayValue) {
								$out .= $sep . urlencode($key) . '[]=' . urlencode($arrayValue);
								$sep = '&';
							};
						};
					};
				};		
				break;
			case 'hidden':
				$out .= array_to_input($aOut);
				break;
		};
		return $out;
	};
	function fPermissionDE($array = array()) {
		GLOBAL $conn;
		$out = array();
		if (isset($array['department'])) {
			$department = $array['department'];
		} elseif (isset($_POST['department'])) {
			$department = $_POST['department'];
		} elseif (isset($_GET['department'])) {
			$department = $_GET['department'];
		};
		if (isset($department)) {
			if ($department === 'none' || $department === 0) {
				unset($department);
			};
		};
		if (isset($department)) {
			if (!fCanSeePublic(@$_SESSION['permissions']['department'][$department] >= 100)) {
				$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
				$_SESSION['uiState'] = 'error';
				fRedirect();
				return false;
			} else {
				$out['department'] = $department;
			};
		};
		if (isset($array['equipment'])) {
			$equipment = $array['equipment'];
		} elseif (isset($_POST['equipment'])) {
			$equipment = $_POST['equipment'];
		} elseif (isset($_GET['equipment'])) {
			$equipment = $_GET['equipment'];
		};
		if (isset($equipment)) {
			if ($equipment === 'none' || $equipment === 0) {
				unset($equipment);
			};
		};
		if (isset($equipment) && isset($department)) {
			$queryEquipmentCheck = 'select top 1 id from DepartmentEquipment where id = ' . $equipment . ' and departmentfk = ' . $department;
			$dataEquipmentCheck = odbc_exec($conn, $queryEquipmentCheck);
			if (!odbc_fetch_row($dataEquipmentCheck)) {
				$_SESSION['sqlMessage'] = 'Department and Equipment have no connection!';
				$_SESSION['uiState'] = 'error';
				fRedirect();
				return false;
			} else {
				$out['equipment'] = $equipment;
			};
		};
		return $out;
	};
	function in_array_regex_reverse($string, $array, $mode = 'value') {
		if (is_array($array)) {
			foreach ($array as $key => $value) {
				$match = preg_match('/' . ${$mode} . '/i', $string);
				if ($match === 1) {
					return $string;
				};
			};
		};
		return false;
	};
	
	function in_array_regex($string, $array, $mode = 'value') {
		if (is_array($array)) {
			foreach ($array as $key => $value) {
				$match = preg_match('/' . $string . '/i', ${$mode});
				if ($match === 1) {
					return ${$mode};
				};
			};
		};
		return false;
	};
	function fMakeRandom($type, $id) {
		GLOBAL $conn;
		GLOBAL $localTime;
		$queryRandom = 'select id, random from random where type = ' . $type . ' and id = ' . $id;
		$dataRandom = odbc_exec($conn, $queryRandom);
		while (odbc_fetch_row($dataRandom)) {
			$updateRandom = 'update random set timestamp = \'' . $localTime . '\' where id = ' . odbc_result($dataRandom, 1);
			if (odbc_exec($conn, $updateRandom)) {
				return odbc_result($dataRandom, 2);
			} else {
				return false;
			};
		};
		$string = fRandomString();
		$insertRandom = 'insert into Random (type, id, timestamp, random)
		VALUES (' . $type . ', ' . $id . ', \'' . $localTime . '\', \'' . $string . '\')';
		if (odbc_exec($conn, $insertRandom)) {
			return $string;
		} else {
			return false;
		};
	};
	function fCheckRandom($type, $id, $random, $expire = false) {
		GLOBAL $conn;
		GLOBAL $localTime;
		$queryRandom = 'select random, timestamp from random where type = ' . $type . ' and id = ' . $id;
		$dataQuery = odbc_exec($conn, $queryRandom);
		$cTimeStamp = strtotime($localTime);
		while (odbc_fetch_row($dataQuery)) {
			if (odbc_result($dataQuery, 1) == $random) {
				$timeStamp = strtotime(odbc_result($dataQuery, 2));
				if ($expire) {
					if ($expire >= ($cTimeStamp - $timeStamp)) {
						return true;
					} else {
						fDeleteRandom($type, $id);
						return false;
					};
				} else {
					return true;
				};
			} else {
				return false;
			};
		};
	};
	function fDeleteRandom($type, $id) {
		GLOBAL $conn;
		$deleteRandom = 'delete from random where id = ' . $id . ' and type = ' . $type;
		odbc_exec($conn, $deleteRandom);
	};
	function fConfirmEmail($to, $name, $id) {
		GLOBAL $conn;
		$random = fMakeRandom(1, $id);
		$mail = new PHPMailer();
		$mail->isSMTP();
		$mail->SMTPDebug = 0;
		$mail->Host = "172.30.4.15";
		$mail->Port = 25;
		$mail->SMTPAuth = false;
		$mail->setFrom('brimweb@matthey.com', 'Brimweb');
		$mail->addAddress($to, $name);
		$mail->Subject = 'Welcome to Brimweb.';
		$mail->msgHTML('<p>Hi, ' . $name . '</p>
		<p>Please follow the link to activate your account. <a href="http://brimweb.brimcontrols.local/activate.php?user=' . $id . '&code=' . $random . '">http://brimweb.brimcontrols.local/activate.php?user=' . $id . '&code=' . $random . '</a></p>
		<p>Do not reply to this message.</p>', dirname(__FILE__));
		if (!$mail->send()) {
			$_SESSION['sqlMessage'] = 'Email Error: ' . $mail->ErrorInfo;
			$_SESSION['uiState'] = 'active';
		} else {
			$_SESSION['sqlMessage'] = 'Email Sent!';
			$_SESSION['uiState'] = 'active';
		};
	};
	function fResetPasswordEmail($to, $name, $id) {
		GLOBAL $conn;
		$random = fMakeRandom(2, $id);
		$mail = new PHPMailer();
		$mail->isSMTP();
		$mail->SMTPDebug = 0;
		$mail->Host = "172.30.4.15";
		$mail->Port = 25;
		$mail->SMTPAuth = false;
		$mail->setFrom('brimweb@matthey.com', 'Brimweb');
		$mail->addAddress($to, $name);
		$mail->Subject = 'Brimweb password reset.';
		$mail->msgHTML('<p>Hi, ' . $name . '</p>
		<p>Your Password can be reset for the next hour, follow the link for instructions. <a href="http://brimweb.brimcontrols.local/resetpassword.php?user=' . $id . '&code=' . $random . '">http://brimweb.brimcontrols.local/resetpassword.php?user=' . $id . '&code=' . $random . '</a></p>
		<p>Do not reply to this message.</p>', dirname(__FILE__));
		if (!$mail->send()) {
			$_SESSION['sqlMessage'] = 'Email Error: ' . $mail->ErrorInfo;
			$_SESSION['uiState'] = 'active';
		} else {
			$_SESSION['sqlMessage'] = 'Email Sent!';
			$_SESSION['uiState'] = 'active';
		};
	};
	$hookSearch = array();
	$hookReplace = array();
	function fHookInsert($hookID) {
		GLOBAL $hookSearch;
		GLOBAL $hookReplace;
		$hookSearch[$hookID] = '%hook' . $hookID . '%';
		if (!isset($hookReplace[$hookID])) {
			$hookReplace[$hookID] = '';
		};
		return '%hook' . $hookID . '%';
	};
	function fStandardCal($calFile, $calFirstDate, $mode = 'datetime') {
		$out = '<form id="datetimepicker" action="' . $calFile . '.php" method="get">
		<input type="submit" id="go" value="Use Range" />' . fQueryString(['exclude' => ['startdate', 'enddate'], 'output' => 'hidden']) . '
		<input value="defaultdate" type="hidden" id="startdatefield" name="startdate" />
		<div id="startdate"></div>
		<input value="defaultdate" type="hidden" id="enddatefield" name="enddate" />
		<div id="enddate"></div>
		</form>
		<script type="text/javascript">
			fDatePicker ("#startdate", "#startdatefield", ' . $calFirstDate . ', "' . $mode . '");
			fDatePicker ("#enddate", "#enddatefield", ' . $calFirstDate . ', "' . $mode . '");
			$("#go").click(function () {
				var sdtVal = new Date($("#startdatefield").val().split(" ").join("T"));
				var edtVal = new Date($("#enddatefield").val().split(" ").join("T"));
				if (sdtVal.getTime() > edtVal.getTime()) {
					updateTips("Invalid date time range selected!");
					return false;
				};
			});
		</script>';
		return $out;
	};
	function fGenerateWhere(&$query, $aWhere, $type = 'and') {
		$i = 0;
		if (is_array($aWhere)) {
			foreach ($aWhere as $value) {
				if ($i==0) {
					$where = ' where ';
				} else {
					$where = ' ' . $type . ' ';
				};
				$i++;
				$query .= $where . $value;
			};
		};
	};
	function fToTime($seconds) {
		$output = Array();
		$neg = '';
		if ($seconds < 0) {
			$neg = '-';
			$seconds = $seconds * -1;
		};
		$days = floor($seconds / 60 / 60 / 24);
		$seconds =  $seconds - (24 * 60 * 60 * $days);
		if ($days >= 1) {
			$output[] = $days . 'd';
		};
		$hours = floor($seconds / 60 / 60);
		$seconds =  $seconds - (60 * 60 * $hours);
		if ($hours >= 1) {
			$output[] = $hours . 'h';
		};
		$minutes = floor($seconds / 60);
		$seconds =  $seconds - (60 * $minutes);
		if ($minutes >= 1) {
			$output[] = $minutes . 'm';
		};
		if ($seconds >= 1) {
			$output[] = round($seconds) . 's';
		};
		if (count($output) >= 1) {
			return $neg . implode(' ' , $output);
		} else {
			return $seconds . 's';
		};
	};
	function fOrThem($sDatabase, $iPermissionValue, $sColumnName, &$aWhere) {
		$i = 0;
		$sWhereConditions = '';
		foreach ($sDatabase as $key => $value) {
			if ($value >= $iPermissionValue) {
				if ($i != 0) {
					$sWhereConditions .= ' or ';
				};
				$i++;
				$sWhereConditions .= $sColumnName . ' = ' . $key;
			};
		};
		if (!empty($sWhereConditions)) {
			$aWhere[] = '(' . $sWhereConditions . ')';
		};
	};
	function fOrThemReturn($sDatabase, $iPermissionValue, $sColumnName, &$aWhere = '') {
		$i = 0;
		$sWhereConditions = '';
		if (isset($sDatabase)) {
			foreach ($sDatabase as $key => $value) {
				if ($value >= $iPermissionValue) {
					if ($i != 0) {
						$sWhereConditions .= ' or ';
					};
					$i++;
					$sWhereConditions .= $sColumnName . ' = ' . $key;
				};
			};
		};
		if (!empty($aWhere)) {
			foreach ($aWhere as $key => $value) {
				if ($i != 0) {
					$sWhereConditions .= ' or ';
				};
				$i++;
				$sWhereConditions .= $value;
			};
		};
		if (!empty($sWhereConditions)) {
			return '(' . $sWhereConditions . ')';
		};
	};
	function fPassTest ($password) {
		preg_match('/^[0-9a-zA-Z\@\#\$\%\^\&\*\(\)\_\+\!]+$/', $password, $passValid);
		if (!isset($passValid[0]) && strlen(password) < 5 && strlen($password) > 16) {
			$_SESSION['sqlMessage'] = 'Username and/or password were invalid!';
			$_SESSION['uiState'] = 'error';
			fRedirect();
			return false;
		};
		return true;
	};
	function fTextDatabase ($text, $size, $nomax = false) {
		$len = strlen($text);
		if ($nomax) {
			if ($len > 0) {
				return str_replace('\'', '\'\'', $text);
			};
		} else {
			if ($len > 0 && $len < $size) {
				return str_replace('\'', '\'\'', $text);
			};
		};
	};
	function fValidEmail ($input) {
		preg_match('/^[a-z0-9\.]+@[a-z0-9\.]+[\.](com|co[\.]uk)$/i', $input, $valid);
		if (isset($valid[0])) {	
			return $valid[0];
		};
		return false;
	};
	function fValidNumber ($input) {
		preg_match('/^[0-9\-]+$/i', $input, $valid);
		if (isset($valid[0]) || $input === "0") {	
			return true;
		};
		return false;
	};
	function fValidFloat ($input) {
		return is_numeric($input);
	};
	function fValidMobile ($input) {
		preg_match('/^07[0-9]{9}$/', $input, $valid);
		if (isset($valid[0])) {	
			return $valid[0];
		};
		return false;
	};
	function fValidDate ($input) {
		preg_match('/^\d{4}-\d{2}-\d{2}$/i', $input, $valid);
		if (isset($valid[0])) {	
			return $valid[0];
		};
		return false;
	};
	function fValidArray ($input) {
		if (is_array($input)) {
			return true;
		} else {
			return false;
		};
	};
	function fValidJson ($input) {
		json_decode($input);
		switch (json_last_error()) {
			case JSON_ERROR_NONE:
				return $input;
			break;
			default:
				return false;
			break;
		};
	};
	function fCanSee($condition) {
		if (!isset($condition)) {
			$condition = true;
		};
		if (isset($_SESSION['id'])) {
			global $_SESSION;
			$loggedIn = true;
			if ($_SESSION['id'] == 1) {
				$isAdmin = true;
			} else {
				$isAdmin = false;
			};
		} else {
			$loggedIn = false;
			$isAdmin = false;
		};
		if ($condition) {
			$canSee = true;
		} else {
			$canSee = false;
		};
		if ($loggedIn) {
			if ($canSee || $isAdmin) {
				return true;
			} else {
				return false;
			};
		} else {
			return false;
		};
	};
	function fCanSeePublic($condition) {
		if ($condition) {
			return true;
		} elseif (@$_SESSION['id'] == 1) {
			return true;
		} elseif (!isset($_SESSION['id'])) {
			return true;
		} else {
			return false;
		};
	};
	function fSetVar(&$var) {
		if (!isset($var)) {
			$var = false;
		};
	};
	function fRedirect() {
		if (isset($_SERVER['HTTP_REFERER'])) {
			if (strpos($_SERVER['HTTP_REFERER'], $_SERVER['PHP_SELF'])) {
				header('Location:index.php');
			} else {
				header('Location:' . $_SERVER['HTTP_REFERER']);
			};
		} else {
			header('Location:index.php');
		};
		exit();
	};
	function fGroupSelect() {
		global $conn;
		$sGroupSelectAdmin = '<select id="groupadmin" name="groupadmin"><option value="none">Self/None</option>';
		$queryGroupSelectAdmin = 'select id, name from grouptable';
		if ($_SESSION['id'] != 1) {
			fOrThem($_SESSION['permissions']['group'], 300, 'id', $aQuerySelectGroupAdmin);
			fGenerateWhere($queryGroupSelectAdmin, $aQuerySelectGroupAdmin);
		};
		$dataGroupSelectAdmin = odbc_exec($conn, $queryGroupSelectAdmin);
		while (odbc_fetch_row($dataGroupSelectAdmin)) {
			$sGroupSelectAdmin .= '<option value="' . odbc_result($dataGroupSelectAdmin, 1) . '" >' . odbc_result($dataGroupSelectAdmin, 2) . '</option>';
		};
		$sGroupSelectAdmin .= '</select>';
		return $sGroupSelectAdmin;
	};
	function fCheckEquip($departmentfk, $equip) {
		global $conn;
		$query1 = 'select top 1 id from equip where departmentfk = ' . $departmentfk . ' and equip = \'' . $equip . '\'';
		$data1 = odbc_exec($conn, $query1);
		while(odbc_fetch_row($data1)) {
			$set = true;
		};
		$query2 = 'select top 1 id from eequip where departmentfk = ' . $departmentfk . ' and eequip = \'' . $equip . '\'';
		$data2 = odbc_exec($conn, $query2);
		while(odbc_fetch_row($data2)) {
			$set = true;
		};
		if (isset($set)) {
			return true;
		} else {
			return false;
		};
	};
	$conn = odbc_connect('DRIVER={SQL Server};Server=INSQL2;Database=PlantAvail;', $dbUsername, $dbPassword);
	function fMenu($menu) {
		$item = array();
		if (is_array($menu)) {
			foreach ($menu as $key => $array) {
				//if (isset($array['name'])) {
				if (@$_SESSION['id'] == 1 || (isset($array['name']) && @$_SESSION['permissions']['page'][$array['id']] >= 100)) {
					$item[] = '<li><a href="' . $array['path'];
					if ($array['path'] != '#') {
						$item[] .= fQueryString(['exclusive' => ['startdate', 'enddate']]);
					};
					$item[] .= '">' . $array['name'] . '</a>';
					if (isset($array['children'])) {
						$item[] .= fMenu($array['children']);
					};
					$item[] .= '</li>';
				};
			};
		};
		return '<ul>' . implode($item) . '</ul>';
	};
	$queryScript = 'select top 1 id, PermissionFK from PermissionAbstract where AbstractName = \'' . fGetScriptName() . '\'';
	$dataScript = odbc_exec($conn, $queryScript);
	odbc_fetch_row($dataScript);
	$scriptProp = ['id' => odbc_result($dataScript, 1), 'permission' => odbc_result($dataScript, 2)];
	$queryPages = 'select id, Name, path, parentid, "order", defaultpermissions from Pages order by "order"';
	$dataPages = odbc_exec($conn, $queryPages);
	$navRefs = array();
	$navList = array();
	$defaultPermissions = array();
	while (odbc_fetch_row($dataPages)) {
		$thisref = &$navRefs[ odbc_result($dataPages, 1) ];
		$thisref['parent_id'] = odbc_result($dataPages, 4);
		$thisref['name'] = odbc_result($dataPages, 2);
		$thisref['path'] = odbc_result($dataPages, 3);
		$thisref['order'] = odbc_result($dataPages, 5);
		$thisref['id'] = odbc_result($dataPages, 1);
		$defaultPermissions[odbc_result($dataPages, 1)] = odbc_result($dataPages, 6);
		if (odbc_result($dataPages, 4) == 0) {
			$navList[ odbc_result($dataPages, 1) ] = &$thisref;
		} else {
			$navRefs[ odbc_result($dataPages, 4) ]['children'][ odbc_result($dataPages, 1) ] = &$thisref;
		};
	};
	
	function fGroupPermissions($in, &$out) {
		global $conn;
		if (count($in) >= 1) {
			foreach ($in as $key => $value) {
				$in[$key] = 'groupfk = ' . $value;
			};
			$queryGetNewGroups = 'select groupadminfk, groupfk, level from Permissions where GroupAdminFK is not null and (' . implode(' or ', $in) . ')';
			$dataGetNewGroups = odbc_exec($conn, $queryGetNewGroups);
			$in = array();
			while(odbc_fetch_row($dataGetNewGroups)) {
				$out[] = odbc_result($dataGetNewGroups, 1);
				$in[] = odbc_result($dataGetNewGroups, 1);
			};
			$in = array_diff($in, $out);
			fGroupPermissions($in, $out);
		};
	};
	if (fCanSee(isset($_SESSION['id']))) {
		$queryGetInitialGroups = 'select groupid from groupjunction where userid = ' . $_SESSION['id'];
		$dataGetInitialGroups = odbc_exec($conn, $queryGetInitialGroups);
		$aGroupIDS = array();
		while (odbc_fetch_row($dataGetInitialGroups)) {
			$aGroupIDS[] = odbc_result($dataGetInitialGroups, 1);
		};
		fGroupPermissions($aGroupIDS, $aGroupIDS);
		$aGroupIDS = array_unique($aGroupIDS);
	};
	function fGetChildren($in, &$out) {
		if (isset($in['id'])) {
			$out[] = $in['id'];
		};
		foreach ($in as $key => $array) {
			if (is_array($array)) {
				fGetChildren($array, $out);
			};
		};
	};
	function fGetParents($in, $id, &$out) {
		$out[] = $in[$id]['id'];
		if ($in[$id]['parent_id'] != 0) {
			fGetParents($in, $in[$id]['parent_id'], $out);
		};
	};
	function fGetHeritage($in, $id, $direction) {
		$out = array();
		if (isset($in[$id])) {
			if ($direction == 'down' || $direction == 'both') {
				fGetChildren($in[$id], $out);
			};
			if ($direction == 'up' || $direction == 'both') {
				fGetParents($in, $id, $out);
			};
		};
		return array_unique($out);
	};
	function fGetPermissions() {
		global $aGroupIDS;
		global $navRefs;
		global $conn;
		if (isset($_SESSION['id']) && !empty($aGroupIDS)) {
			if ($_SESSION['id'] != 1) {
				foreach ($aGroupIDS as $key => $value) {
					$aGroupIDS[$key] = 'groupfk = ' . $value;
				};
				$queryPermissionsGet = 'select areafk, departmentfk, pagefk, groupadminfk, max(level) as level, disciplinefk
				from permissions
				where ' . implode(' or ', $aGroupIDS) . '
				group by AreaFK, DepartmentFK, PageFK, GroupAdminFK, disciplinefk';
				$queryPermissions = odbc_exec($conn, $queryPermissionsGet);
				while (odbc_fetch_row($queryPermissions)) {
					if (odbc_result($queryPermissions, 1)) {
						$aPermissions['area'][odbc_result($queryPermissions, 1)][] = odbc_result($queryPermissions, 5);
						if (odbc_result($queryPermissions, 5) >= 300) {
							$aAdmins['areaadmin'] = true;
						};
						if (odbc_result($queryPermissions, 5) >= 200) {
							$aEdit['areaedit'] = true;
						};
						if (odbc_result($queryPermissions, 5) >= 100) {
							$aView['areaview'] = true;
						};
					};
					if (odbc_result($queryPermissions, 2)) {
						$aPermissions['department'][odbc_result($queryPermissions, 2)][] = odbc_result($queryPermissions, 5);
						if (odbc_result($queryPermissions, 5) >= 300) {
							$aAdmins['departmentadmin'] = true;
						};
						if (odbc_result($queryPermissions, 5) >= 200) {
							$aEdit['departmentedit'] = true;
						};
						if (odbc_result($queryPermissions, 5) >= 100) {
							$aView['departmentview'] = true;
						};
					};
					if (odbc_result($queryPermissions, 3)) {
						foreach (fGetHeritage($navRefs, odbc_result($queryPermissions, 3), 'down') as $key => $value) {
							$aPermissions['page'][$value][] = odbc_result($queryPermissions, 5);
						};
						if (odbc_result($queryPermissions, 5) >= 1) {
							foreach (fGetHeritage($navRefs, odbc_result($queryPermissions, 3), 'up') as $key => $value) {
								$aPermissions['page'][$value][] = odbc_result($queryPermissions, 5);
							};
						};
						if (odbc_result($queryPermissions, 5) >= 300) {
							$aAdmins['pageadmin'] = true;
						};
						if (odbc_result($queryPermissions, 5) >= 200) {
							$aEdit['pageedit'] = true;
						};
						if (odbc_result($queryPermissions, 5) >= 100) {
							$aView['pageview'] = true;
						};
					};
					if (odbc_result($queryPermissions, 6)) {
						$aPermissions['discipline'][odbc_result($queryPermissions, 6)][] = odbc_result($queryPermissions, 5);
						if (odbc_result($queryPermissions, 5) >= 300) {
							$aAdmins['disciplineadmin'] = true;
						};
						if (odbc_result($queryPermissions, 5) >= 200) {
							$aEdit['disciplineedit'] = true;
						};
						if (odbc_result($queryPermissions, 5) >= 100) {
							$aView['disciplineview'] = true;
						};
					};
					if (odbc_result($queryPermissions, 4)) {
						$aPermissions['group'][odbc_result($queryPermissions, 4)][] = odbc_result($queryPermissions, 5);
						if (odbc_result($queryPermissions, 5) >= 300) {
							$aAdmins['groupadmin'] = true;
						};
						if (odbc_result($queryPermissions, 5) >= 200) {
							$aEdit['groupedit'] = true;
						};
						if (odbc_result($queryPermissions, 5) >= 100) {
							$aView['groupview'] = true;
						};
					};
				};
				if (isset($aPermissions)) {
					foreach ($aPermissions as $typeKey => $type) {
						foreach ($type as $idKey => $id) {
							if ($typeKey == 'permissions' && in_array(0, $aPermissions[$typeKey][$idKey])) {
								$aPermissions[$typeKey][$idKey] = 0;
							} else {
								$aPermissions[$typeKey][$idKey] = max($aPermissions[$typeKey][$idKey]);
							};
						};
					};
				};
				if (!empty($aPermissions['area']) || !empty($aPermissions['department']) || !empty($aPermissions['page'])) {
					session_id($_COOKIE['id']);
					setcookie('id', session_id(), time() + (60 * 60 * 24 * 14), '/');
					$_SESSION['permissions'] = $aPermissions;
					if (isset($aAdmins)) {
						$_SESSION['admin'] = $aAdmins;
					} else {
						$_SESSION['admin'] = '';
					};
					if (isset($aEdit)) {
						$_SESSION['edit'] = $aEdit;
					} else {
						$_SESSION['edit'] = '';
					};
					if (isset($aView)) {
						$_SESSION['view'] = $aView;
					} else {
						$_SESSION['view'] = '';
					};
				} else {
					$_SESSION['sqlMessage'] = 'You do not have any Permissions.';
					$_SESSION['uiState'] = 'error';
					//header('Location:index.php');
				};
				if (empty($aPermissions['group'])) {
					$_SESSION['sqlMessage'] = 'You are not a member of any group.';
					$_SESSION['uiState'] = 'error';
					//header('Location:index.php');
				};
			} else {
				session_id($_COOKIE['id']);
				setcookie('id', session_id(), time() + (60 * 60 * 24 * 14), '/');
			};
		};
	};
	fGetPermissions();
	if (@$_SESSION['id'] != 1) {
		foreach ($defaultPermissions as $key => $value) {
			if (!isset($_SESSION['permissions']['page'][$key]) || !isset($_SESSION['id'])) {
				$_SESSION['permissions']['page'][$key] = $value;
			};
		};
	};
	function fGetTrends($start, $end, $linkType = 'external', $exclude = array()) {
		global $conn;
		global $aDE;
		$joinWhere = 'where userfk = 0';
		if (isset($_SESSION['id'])) {
			if ($_SESSION['id'] != 1) {
				$joinWhere = 'where userfk = ' . $_SESSION['id'] . ' or ' . fOrThemReturn($_SESSION['permissions']['group'], 100, 'groupfk');
			};
		};
		$queryTrends = 'select temp1.id, trendname, publicbool, username, share, realdepartmentfk, departmentequipmentfk, departmentequipmentname, department.name
		from (select processtrend.userfk, processtrend.ID, processtrend.name as trendname, publicbool, users.name as username, share, case 
				when processtrend.DepartmentEquipmentFK IS NOT NULL 
					then departmentequipment.DepartmentFK 
					else processtrend.DepartmentFK end as realdepartmentfk,
			DepartmentEquipmentFK, DepartmentEquipment.Name as departmentequipmentname
			from ProcessTrend 
			left join (select distinct processtrendfk, 1 as share from ProcessTrendShare ' . $joinWhere . ') as shares on ProcessTrend.id = shares.ProcessTrendFK
			left join departmentequipment on processtrend.departmentequipmentfk = departmentequipment.ID
			join users on users.id = processtrend.userfk) as temp1
		left join department on realdepartmentfk = department.id';
		if (isset($_SESSION['id'])) {
			if ($_SESSION['id'] > 1) {
				if (isset($aDE['department'])) {
					$queryTrends .= ' where (publicbool = 1 or temp1.userfk = ' . $_SESSION['id'] . ') and realdepartmentfk = ' . $aDE['department'];
				} else {
					$queryTrends .= ' where (publicbool = 1 or temp1.userfk = ' . $_SESSION['id'] . ') and realdepartmentfk is NULL';
				};
			} else {
				if (isset($aDE['department'])) {
					$queryTrends .= ' where realdepartmentfk = ' . $aDE['department'];
					if (isset($aDE['equipment'])) {
						$queryTrends .= ' and (departmentequipmentfk = ' . $aDE['equipment'] . ' or departmentequipmentfk is null)';
					};
				} else {
					$queryTrends .= ' where realdepartmentfk is NULL';
				};
			};
		} else {
			if (isset($aDE['department'])) {
				$queryTrends .= ' where realdepartmentfk = ' . $aDE['department'];
				if (isset($aDE['equipment'])) {
					$queryTrends .= ' and (departmentequipmentfk = ' . $aDE['equipment'] . ' or departmentequipmentfk is null)';
				};
			} else {
				$queryTrends .= ' where realdepartmentfk is NULL';
			};
		};
		$queryTrends .= ' order by department.name asc, departmentequipmentfk asc, trendname asc';
		$dataTrends = odbc_exec($conn, $queryTrends);
		while (odbc_fetch_row($dataTrends)) {
			if (!isset($aTrends)) {
				$aTrends = ['My Trends' => array(), 'Shared Trends' => array(), 'Public Trends' => array()];
			};
			$lookup['e' . odbc_result($dataTrends, 7)] = odbc_result($dataTrends, 8);
			$row = [odbc_result($dataTrends, 1), odbc_result($dataTrends, 2), odbc_result($dataTrends, 4)];
			if (isset($_SESSION['id'])) {
				if (fCanSee(odbc_result($dataTrends, 4) == $_SESSION['user'])) {
					$aTrends['My Trends']['e' . odbc_result($dataTrends, 7)][] = $row;
				};
			};
			if (odbc_result($dataTrends, 3) == 1) {
				$aTrends['Public Trends']['e' . odbc_result($dataTrends, 7)][] = $row;
			};
			if (odbc_result($dataTrends, 5) == 1) {
				$aTrends['Shared Trends']['e' . odbc_result($dataTrends, 7)][] = $row;
			};
		};
		if (isset($aTrends)) {
			$out = '';
			if ($linkType == 'local') {
				$out .= fRecordSwap(['rename' => 'Trends', 'exclude' => $exclude]) . '<div class="records" id="ajax-trend">'; 
			} else {
				$out .= '<div class="records ajax" id="ajax-trend">'; 
			};
			$rowId = 1;
			foreach ($aTrends as $key => $array) {
				if (!empty($array)) {
					if ($rowId % 2 == 0) {
						$rowType = 'oddRow';
					} else {
						$rowType = 'evenRow';
					};
					$rowId++;
					$out .= '<div class="inlineclass ' . $rowType . '"><h3>' . $key . '</h3><ul>';
						foreach ($array as $equipKey => $equipArray) {
							if (isset($lookup[$equipKey])) {
								$out .= '<li><a class="listheader" href="#">' . $lookup[$equipKey] . ' <span class="icon-caret-right icon-large"></span><span class="icon-caret-down icon-large"></span></a><ul>';
							};
							foreach ($equipArray as $trend) {
								switch ($linkType) {
									case 'external':
										$startDate = date('Y-m-d H:i:s', round(($start / 1000)));
										$endDate = date('Y-m-d H:i:s', round(($end / 1000) + 1));
										$out .= '<li><a href="processtrend.php?id=' . $trend[0] . '&startdate=' . $startDate . '&enddate=' . $endDate . '">' . $trend[1] . '</a><div class="hinttext">Author:' . $trend[2] . '</div></li>';
										break;
									case 'local':
										$out .= '<li><a class="trendlink" href="#up" data-id="' . $trend[0] . '" data-start="' . $start . '" data-end="' . $end . '">' . $trend[1] . '</a><div class="hinttext">Author:' . $trend[2] . '</div></li>';
										break;
									case 'include':
										break;
								};
							};
							if (isset($lookup[$equipKey])) {
								$out .= '</ul></li>';
							};
						};
					$out .= '</ul></div>';
				};
			};
			return $out . '</div>';
		} else {
			return false;
		};
	};
?>