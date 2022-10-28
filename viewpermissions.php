<?PHP 
	$title = 'View Permissions';
	require_once 'includes/header.php';
	if (!isset($_SESSION['id'])) {
		$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
		$_SESSION['uiState'] = 'error';
		fRedirect();
	};
	function fGetData($database, &$input) {
		global $conn;
		$query = 'select id, name from ' . $database;
		$data = odbc_exec($conn, $query);
		while (odbc_fetch_row($data)) {
			$input[odbc_result($data, 1)] = odbc_result($data, 2);
		};
	};
	fGetData('area', $aArea);
	fGetData('department', $aDepartment);
	fGetData('pages', $aPage);
	fGetData('grouptable', $aGroup);
	fGetData('discipline', $aDiscipline);
		
	$sViewPermissions = '<div>';
	foreach($_SESSION as $sessionKey => $aSession) {
		if (is_array($aSession)) {
			foreach($aSession as $keyKey => $aPermission) {
				if (is_array($aPermission)) {
					$sViewPermissions .= '<h3>' . ucwords($keyKey) . '</h3>';
					foreach($aPermission as $keyKeyKey => $value) {
						switch ($value) {
							case 300;
								$sVal = 'Admin';
								break;
							case 200;
								$sVal = 'Edit';
								break;
							case 100;
								$sVal = 'View';
								break;
							case 0;
								$sVal = 'None';
								break;
							default:
								$sVal = $value;
								break;
						};
						switch ($keyKey) {
							case 'area':
								$sViewPermissions .= $aArea[$keyKeyKey];
								break;
							case 'department':
								$sViewPermissions .= $aDepartment[$keyKeyKey];
								break;
							case 'page':
								$sViewPermissions .= $aPage[$keyKeyKey];
								break;
							case 'group':
								$sViewPermissions .= $aGroup[$keyKeyKey];
								break;
							case 'discipline':
								$sViewPermissions .= $aDiscipline[$keyKeyKey];
								break;
							default:
								$sViewPermissions .= $keyKeyKey;
								break;
						};
						$sViewPermissions .= ' is ' . $sVal . '<br />';
					};
				};
			};
		};
	};
	$sViewPermissions .= '</div>';
	$stdOut .= $sViewPermissions;
	require_once 'includes/footer.php'; ?>