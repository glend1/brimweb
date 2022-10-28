<?PHP
require_once 'functions.php';
$output = array();
$aDE = fPermissionDE();
if (!$aDE) {
	$output['status'] = 'you do not have permission.';
	$error = true;
};
if (!isset($aDE['department'])) {
	$output['status'] = 'department not found.';
	$error = true;
};
if (!isset($_GET['startdate'])) {
	$output['status'] = 'start date not found.';
	$error = true;
};
if (!isset($_GET['enddate'])) {
	$output['status'] = 'end date not found.';
	$error = true;
};
if (!isset($error)) {
	if ($out = fGetTrends($_GET['startdate'], $_GET['enddate'], 'external')) {	
		$output['oreturn'] = $out;
		$output['status'] = 'complete';
	} else {
		$output['status'] = 'not found in database.';
	};
};
print(json_encode($output));
?>