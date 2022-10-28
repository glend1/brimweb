<?PHP
require_once 'functions.php';
if (!(isset($_GET['tags']) || isset($_GET['trend']))) {
	$output['status'] = 'no Tags found.';
	$error = true;
};
if (!isset($_GET['interval'])) {
	$output['status'] = 'no Interval found.';
	$error = true;
} else {
	if ($_GET['interval'] < 1 || $_GET['interval'] > 2146999999) {
		$output['status'] = 'Trend out of range.';
		$error = true;
	};
};
if (!isset($_GET['startdate'])) {
	$output['status'] = 'no Start Date/Time found.';
	$error = true;
} else {
	$startdate = date('Y-m-d H:i:s', floor($_GET['startdate'] / 1000));
};
if (!isset($_GET['enddate'])) {
	$output['status'] = 'no End Date/Time found.';
	$error = true;
} else {
	$enddate = date('Y-m-d H:i:s', floor($_GET['enddate'] / 1000));
};
if (!isset($error)) {
	$rConn = odbc_connect('DRIVER={SQL Server};Server=INSQL2;Database=runtime;', $dbUsername, $dbPassword);
	$conn = odbc_connect('DRIVER={SQL Server};Server=INSQL2;Database=PlantAvail;', $dbUsername, $dbPassword);
	if (isset($_GET['tags'])) {
		$aTags = $_GET['tags'];
	} else {
		$queryTrend = 'select top 1 json from processtrend where id = ' . $_GET['trend'];
		$dataTrend = odbc_exec($conn, $queryTrend);
		if (odbc_fetch_row($dataTrend)) {
			$json = json_decode(odbc_result($dataTrend, 1), true);
			$output['trendprefs'] = $json;
			$queryWhere = '';
			$sep = '';
			foreach ($json as $key => $value) {
				$aTags[] = $key;
				$queryWhere .= $sep . 'tagname = \'' . $key . '\'';
				$sep = ' or ';
			};
		} else {
			$output['status'] = 'no Trend found.';
			$error = true;
		};
		$queryDesc = 'select TagName, tag.Description
		from tag
		join IOServer on IOServer.IOServerKey = Tag.IOServerKey
		join Topic on Topic.TopicKey = Tag.TopicKey
		join TagType on TagType.TagTypeKey = Tag.TagType
		where ' . $queryWhere;
		$dataDesc = odbc_exec($rConn, $queryDesc);
		while(odbc_fetch_row($dataDesc)) {
			if (isset($output['trendprefs'][odbc_result($dataDesc, 1)])) {
				$output['trendprefs'][odbc_result($dataDesc, 1)]['desc'] = odbc_result($dataDesc, 2);
			};
		};
	};
	$sTags = implode(', ', $aTags);
	$mode = "cyclic"; 
	if (isset($_GET['mode'])) {
		if ($_GET['mode'] == "true") {
			$mode = "bestfit";
		};
	};
	$queryTags = 'select datetime, ' . $sTags . ' from openquery(insql,\'select datetime, ' . $sTags . ' from widehistory where datetime >= "' . $startdate . '" and datetime <= "' . $enddate . '" and wwretrievalmode = "' . $mode . '" and wwresolution = ' . $_GET['interval'] . '\')';
	$dataTags = odbc_exec($rConn, $queryTags);
	$output['query'] = $queryTags;
	$i = 0;
	while(odbc_fetch_row($dataTags)) {
		foreach ($aTags as $key => $value) {
			$i++;
			$dataValues[$value][] = [strtotime(odbc_result($dataTags, 1)) * 1000, (float) odbc_result($dataTags, $key + 2)];
		};
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
	odbc_close($conn);
};
print(json_encode($output));
?>