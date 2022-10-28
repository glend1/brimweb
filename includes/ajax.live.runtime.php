<?PHP
require_once 'functions.php';
if (!isset($_GET['tags'])) {
	$output['status'] = 'no Tags found.';
	$error = true;
};
if (!isset($error)) {
	$rConn = odbc_connect('DRIVER={SQL Server};Server=INSQL2;Database=runtime;', $dbUsername, $dbPassword);
	$sep = '';
	$sTag = '';
	foreach ($_GET['tags'] as $tag) {
		$sTag .= $sep . 'tagname = \'' . $tag . '\'';
		$sep = ' or ';
	};
	$sTags = implode(', ', $_GET['tags']);
	$queryTags = 'select datetime, tagname, value
	from history
	where datetime = current_timestamp and (' . $sTag . ')';
	$dataTags = odbc_exec($rConn, $queryTags);
	while(odbc_fetch_row($dataTags)) {
		$dataValues[odbc_result($dataTags, 2)] = [strtotime(odbc_result($dataTags, 1)) * 1000, (float) odbc_result($dataTags, 3)];
	};
	if (isset($dataValues)) {
		foreach ($dataValues as $key => $array) {
			$output['oreturn'][$key] = $array;
		};
		$output['status'] = 'complete';
	} else {
		$output['status'] = 'no Tagdata found.';
	};
	odbc_close($rConn);
};
print(json_encode($output));
?>