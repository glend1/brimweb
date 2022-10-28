<?PHP 
$title = 'Test';
require_once 'includes/header.php';
if (!fCanSee(isset($_SESSION['id']))) {
	$_SESSION['sqlMessage'] = 'You must be logged in to use this page!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
if ($_SESSION['id'] != 1) {
	$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
$queryTrend = 'select id, json
		from ProcessTrend';
$dataTrend = odbc_exec($conn, $queryTrend);
while (odbc_fetch_row($dataTrend)) {
	$json = json_decode(odbc_result($dataTrend, 2), true);
	foreach ($json as $aTag) {
		if ($aTag['max'] < $aTag['min']) {
			$stdOut .= odbc_result($dataTrend, 1) . " ";
		};
	};
};
require_once 'includes/footer.php'; ?>