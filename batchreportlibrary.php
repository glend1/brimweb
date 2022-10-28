<?PHP 
require_once 'includes/header.php';
require_once 'includes/evalmath.class.php';
if (!fCanSeePublic(@$_SESSION['permissions']['page'][4] >= 100)) {
	$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
$bConn = odbc_connect('DRIVER={SQL Server};Server=INSQL2;Database=' . $_GET['dbname'] . ';', $dbUsername, $dbPassword);
$rConn = odbc_connect('DRIVER={SQL Server};Server=INSQL2;Database=runtime;', $dbUsername, $dbPassword);
$chartCount = 0;
$units = array();
function fGetUnits(&$input, $tags) {
	if (!empty($tags)) {
		GLOBAL $rConn;
		foreach ($tags as $key => $value) {
			$tags[$key] = 'tagname = \'' . $value . '\'';
		};
		$queryUnits = 'select tagname, unit 
		from  AnalogTag
		left join EngineeringUnit on AnalogTag.eukey = EngineeringUnit.eukey
		where ' . implode(' or ', $tags);
		$dataUnits = odbc_exec($rConn, $queryUnits);
		while(odbc_fetch_row($dataUnits)) {
			$input[odbc_result($dataUnits, 1)] = odbc_result($dataUnits, 2);
		};
	};
};
function fPattern($start, $end, $expression = '(n)') {
	$loop = true;
	$m = new EvalMath;
	$n = 1;
	$out = array();
	$lastResult = 0;
	while($loop) {
		$result = $m->evaluate(str_replace('(n)', $n, $expression));
		if ($result > $end || $lastResult >= $result) {
			$loop = false;
			break;
		};
		$lastResult = $result;
		if ($result >= $start && $result <= $end) {
			$out[] = $result;
		};
		$n++;
	};
	return $out;
};
function fFind($data, $array) {
	if (empty($array)) {
		return false;
	};
	$arrayFind = array();
	$arrayReturn = array();
	if(isset($array['start'])) {
		$start = $array['start'];
	} else {
		$start = 1;
	};
	if(isset($array['end'])) {
		$end = $array['end'];
	} else {
		$end = count($data);
	};
	if (isset($array['expression'])) {
		$match = fPattern($start, $end, $array['expression']);
	} else {
		$match = fPattern($start, $end);
	};
	$i = 1;
	foreach ($match as $value) {
		if (stristr($data[$value][$array['type']], $array['search'])) {
			$arrayFind[$i] = $value;
			$i++;
		};
	};
	return $arrayFind;
};
function fAndArrays ($aAndArray) {
	if (empty($aAndArray)) {
		return false;
	};
	$result = array();
	$resultProxy = array_shift($aAndArray);
	foreach ($aAndArray as $filter) {
		$resultProxy = array_intersect($resultProxy, $filter);
	};
	$j = 1;
	foreach ($resultProxy as $value) {
		$result[$j] = $value;
		$j++;
	};
	return $result;
};
function fOrArrays ($aOrArray) {
	if (empty($aOrArray)) {
		return false;
	};
	$result = array();
	foreach ($aOrArray as $key => $array) {
		foreach ($array as $keyKey => $value) {
			if (!in_array($value, $result)) {
				$result[] = $value;
			};
		};
	};
	asort($result, SORT_NUMERIC);
	$sortedResult = array();
	$i = 1;
	foreach ($result as $key => $value) {
		$sortedResult[$i++] = $value;
	};
	return $sortedResult;
};
function fNotArrays ($aNotArray) {
	if (empty($aNotArray)) {
		return false;
	};
	$result = array();
	$or = fOrArrays ($aNotArray);
	$and = fAndArrays ($aNotArray);
	if (!empty($or) && !empty($and)) {
		$resultProxy = array_diff($or, $and);
		$j = 1;
		foreach ($resultProxy as $value) {
			$result[$j] = $value;
			$j++;
		};
	};
	return $result;
};
function fReturn ($data, $result, $array = NULL) {
	if (empty($data)) {
		return false;
	};
	$arrayReturn = array();
	if(isset($array['start'])) {
		$resultStart = $array['start'];
	} else {
		$resultStart = 1;
	};
	if(isset($array['end'])) {
		$resultEnd = $array['end'];
	} else {
		$resultEnd = count($result);
	};
	if (isset($array['expression'])) {
		$match = fPattern($resultStart, $resultEnd, $array['expression']);
	} else {
		$match = fPattern($resultStart, $resultEnd);
	};
	$i = 1;
	foreach ($match as $value) {
		$return = array();
		if (empty($array['return'])) {
			foreach ($data[$result[$value]] as $key => $valueValue) {
				$return[] = $key;
			};
		} else {
			$return = $array['return'];
		};
		foreach ($return as $key => $valueValue) {
			if (isset($data[$result[$value]][$valueValue])) {
				$arrayReturn[$i][$valueValue] = $data[$result[$value]][$valueValue];
			};
		};
		$i++;
	};
	return $arrayReturn;
};
function fArrayZip ($array, $match = false) {
	if (empty($array)) {
		return false;
	};
	$out = array();
	if ($match) {
		$sizes = array();
		foreach ($array as $dupename => $arrayArray) {
			$sizes[] = count($arrayArray);
		};
		$min = min($sizes);
	};
	foreach ($array as $dupename => $arrayArray) {
		foreach ($arrayArray as $pos => $arrayArrayArray) {
			if (isset($min)) {
				if ($pos > $min) {
					break;
				};
			};
			foreach($arrayArrayArray as $name => $value) {
				$out[$pos][$dupename . '_' . $name] = $value;
			};
		};
	};
	return $out;
};
function fTagData(&$units, &$array, $between, $tags, $datapoints = 35) {
	if (empty($array)) {
		return false;
	};
	GLOBAL $rConn;
	$unknownTags = array();
	foreach ($tags as $key => $value) {
		if (!isset($units[$value])) {
			$unknownTags[] = $value;
		};
		$tags[$key] = 'tagname = \'' . $value . '\'';
	};
	fGetUnits($units, $unknownTags);
	$tagQuery = '(' . implode(' or ', $tags) . ')';
	foreach ($array as $key => $arrayArray) {
		if (is_array($between)) {
			$dates = Array();
			$timestamp = strtotime($arrayArray[$between[0]]);
			$interval = floor((strtotime($arrayArray[$between[1]]) - $timestamp) / $datapoints);
			for ($i = 1; $i <= $datapoints; $i++) {
				$dates[] = 'select tagname, datetime, value from history where ' . $tagQuery . ' and datetime = \'' . date('Y-m-d H:i:s' ,$interval + $timestamp) . '\'';
				$timestamp = $interval + $timestamp;
			};
			$historyQuery = implode(' union ', $dates) . 'order by datetime';
			
		} else {
			$historyQuery = 'select tagname, datetime, value from history where ' . $tagQuery . ' and datetime = \'' . $arrayArray[$between] . '\'';
		};
		$historyData = odbc_exec($rConn, $historyQuery);
		while(odbc_fetch_row($historyData)) {
			if (!isset($array[$key][odbc_result($historyData, 1)]['unit'])) {
				$array[$key][odbc_result($historyData, 1)]['unit'] = $units[odbc_result($historyData, 1)];
			};
			$array[$key][odbc_result($historyData, 1)][odbc_result($historyData, 2)] = odbc_result($historyData, 3);
		};
	};
	return $array;
};
function fPlotChart ($data, &$chartNumber, $title = null, $alias = null) {
	if (empty($data)) {
		return false;
	};
	$out = '';
	foreach ($data as $key => $array) {
		$aFormattedData = Array();
		if (!is_array($alias)) {
			foreach ($array as $keyKey => $arrayArray) {
				if (is_array($arrayArray)) {
					$alias[$keyKey] = $keyKey;
				};
			};
		};
		foreach ($alias as $name => $fakeName) {
			if (is_array($array[$name])) {
				if (isset($alias[$name])) {
					$label = $alias[$name];
				} else {
					$label = $name;
				};
				if (isset($array[$name]['unit'])) {
					$label .= ' ' . $array[$name]['unit'];
					unset($array[$name]['unit']);
				}
				$aFormattedData[$name] = '{label:\'' . $label . '\', data:[';
				$sep = '';
				foreach ($array[$name] as $timestamp => $value) {
					$aFormattedData[$name] .= $sep . '[' . (strtotime($timestamp) * 1000) . ', "' . $value . '"]';
					$sep = ', ';
				};
				$aFormattedData[$name] .= ']}';
			};
		};
		if (!empty($aFormattedData)) {
			$chartNumber++;
			$plotName = 'plot' . $chartNumber;
			$dataName = 'data' . $chartNumber;
			$out .= '<script type="text/javascript">
				$(function() {
					var ' . $dataName . ' = [' . implode(', ', $aFormattedData) . ']; 
					var options = {
						colors: trendcolors,
						series: {
							points: {
								show: true
							},
							lines: {
								show: true
							}
						},
						xaxis: {
							mode: "time",
							timeformat: "%d/%m/%y<br />%H:%M:%S",
							ticks: 5,
							tickLength: 0
						}
					};
					$.plot("#' . $plotName . '", ' . $dataName . ', options);
				});
			</script>';
			if (!empty($title)) {
				$out .= '<div class="printsafe batchreportchart">';
				if (is_array($title)) {
					$headerTitle = str_replace('{var}', $array[$title['variable']], $title['string']);
				} else {
					$headerTitle = $title;
				};
				$out .= '<h3>' . $headerTitle . '</h3>';
			};
			$out .= '<div class="batchreportgraph" id="' . $plotName . '"></div></div>';
		};
	};
	return $out;
};
function fRoundSafe($value, $round = 0) {
	if (preg_match('/^[\d\.]+$/', $value) && $round !== 'no') {
		$out = number_format($value, $round, '.', ',');
	} else {
		$out = $value;
	};
	return $out;
};
function fCustomData(&$data, $options) {
	if (empty($data)) {
		return false;
	};
	foreach ($data as $number => $dataset) {
		foreach ($options as $header => $parameters) {
			if (isset($parameters['function'])) {
				switch ($parameters['function']) {
					case 'customduration':
						if (isset($parameters['end']) && isset($parameters['start'])) {
							$data[$number][$header] = fToTime(strtotime($dataset[$parameters['end']]) - strtotime($dataset[$parameters['start']]));
						};
						break;
					default;
				};
			};
		};
	};
};
function fTable($data, $options, $title = NULL, $direction = 'vertical') {
	if (empty($data)) {
		return false;
	};
	$aFormattedTable = array();
	$out = '';
	$headers = array();
	$tableHeaders = array();
	$subHeaders = array();
	foreach ($options as $name => $optionsArray) {
		if (isset($options[$name]['visible'])) {
			$visible = $options[$name]['visible'];
		} else {
			$visible = 'yes';
		};
		if ($visible == 'yes') {
			if (isset($options[$name]['alias'])) {
				$headers[$name] = $options[$name]['alias'];
				$tableHeaders[$name] = $options[$name]['alias'];
			} else {
				$headers[$name] = $name;
				$tableHeaders[$name] = $name;
			};
		};
	};
	foreach ($data as $key => $dataArray) {
		foreach ($dataArray as $header => $value) {
			if (isset($options[$header]['visible'])) {
				$visible = $options[$header]['visible'];
			} else {
				$visible = 'yes';
			};
			if ($visible == 'yes') {
				if (!isset($headers[$header])) {
					$headers[$header] = $header;
					$tableHeaders[$header] = $header;
				};
				if (is_array($value)) {
					$i = 1;
					foreach ($value as $headerHeader => $valueValue) {
						if ($headerHeader != 'unit') {
							if (!isset($tableHeaders[$header . ' ' . $headerHeader])) {
								$tableHeaders[$header . ' ' . $headerHeader] = $header . ' ' . $headerHeader;
							};
							if (!isset($subHeaders[$header][$headerHeader])) {
								$subHeaders[$header][$headerHeader] = $header . ' ' . $headerHeader;
								$subHeaders[$header][$i++] = $headerHeader;
							};
						};
					};
				};
			};
		};
	};
	foreach ($data as $key => $dataArray) {
		foreach ($headers as $header => $subName) {
			if (isset($dataArray[$header])) {
				if (is_array($dataArray[$header])) {
					if (isset($dataArray[$header]['unit'])) {
						$unit = ' ' . $dataArray[$header]['unit'];
						unset($dataArray[$header]['unit']);
					} else {
						$unit = '';
					};
					if (isset($options[$header]['array'])) {
						$arrayOption = $options[$header]['array'];
					} else {
						$arrayOption = '{all}';
					};
					if ($arrayOption == '{all}') {
						foreach ($dataArray[$header] as $headerHeader => $valueValue) {
							$aFormattedTable[$key][$header . ' ' . $headerHeader] = '<td>';
							if (isset($options[$header]['round'])) {
								$aFormattedTable[$key][$header . ' ' . $headerHeader] .= fRoundSafe($valueValue, $options[$header]['round']);
							} else {
								$aFormattedTable[$key][$header . ' ' . $headerHeader] .= fRoundSafe($valueValue);
							};
							$aFormattedTable[$key][$header . ' ' . $headerHeader] .= $unit . '</td>';
						};
						unset($tableHeaders[$header]);
					} else {
						if (isset($dataArray[$header][$arrayOption])) {
							$aFormattedTable[$key][$header] = '<td>';
							if (isset($options[$header]['round'])) {
								$aFormattedTable[$key][$header] .= fRoundSafe($dataArray[$header][$arrayOption], $options[$header]['round']);
							} else {
								$aFormattedTable[$key][$header] .= fRoundSafe($dataArray[$header][$arrayOption]);
							};
							$aFormattedTable[$key][$header] .= $unit . '</td>';
							foreach ($subHeaders[$header] as $subHeader => $subHeadName) {
								if (isset($tableHeaders[$header . ' ' . $subHeader])) {
									unset($tableHeaders[$header . ' ' . $subHeader]);
								};
							};
						} elseif (isset($dataArray[$header][$subHeaders[$header][$arrayOption]])) {
							$aFormattedTable[$key][$header] = '<td>';
							if (isset($options[$header]['round'])) {
								$aFormattedTable[$key][$header] .= fRoundSafe($dataArray[$header][$subHeaders[$header][$arrayOption]], $options[$header]['round']);
							} else {
								$aFormattedTable[$key][$header] .= fRoundSafe($dataArray[$header][$subHeaders[$header][$arrayOption]]);
							};
							$aFormattedTable[$key][$header] .= $unit . '</td>';
							foreach ($subHeaders[$header] as $subHeader => $subHeadName) {
								if (isset($tableHeaders[$header . ' ' . $subHeader])) {
									unset($tableHeaders[$header . ' ' . $subHeader]);
								};
							};
						};
					};
				} else {
					$aFormattedTable[$key][$header] = '<td>';
					if (isset($options[$header]['round'])) {
						$aFormattedTable[$key][$header] .= fRoundSafe($dataArray[$header], $options[$header]['round']);
					} else {
						$aFormattedTable[$key][$header] .= fRoundSafe($dataArray[$header]);
					};
					$aFormattedTable[$key][$header] .= '</td>';
				};
			};
		};
	};
	$out = '<div class="printsafe batchreporttable">';
	if (!empty($title)) {
		$out .= '<h3>' . $title . '</h3>';
	};
	$out .= '<table class="records">';
	switch ($direction) {
		case 'vertical':
			$out .= '<thead><tr><th>' . implode('</th><th>', $tableHeaders) . '</th></tr></thead>';
			$out .= '<tbody>';
			$i = 0;
			foreach ($aFormattedTable as $key => $array) {
				if ($i % 2 == 0) {
					$out .= '<tr class="oddRow">';
				} else {
					$out .= '<tr class="evenRow">';
				};
				$i++;
				foreach ($tableHeaders as $header => $headerName) {
					if (isset($array[$header])) {
						$out .= $array[$header];
					} else {
						$out .= '<td></td>';
					};
				};
				$out .= '</tr>';
			};
			break;
		case 'horizontal':
			$out .= '<tbody>';
			$i = 0;
			foreach ($tableHeaders as $header => $headerName) {
				if ($i % 2 == 0) {
					$out .= '<tr class="oddRow">';
				} else {
					$out .= '<tr class="evenRow">';
				};
				$i++;
				$out .= '<td class="tableheader">' . $headerName . '</td>';
				foreach ($aFormattedTable as $key => $array) {
					if (isset($array[$header])) {
						$out .= $array[$header];
					} else {
						$out .= '<td></td>';
					};
				};
				$out .= '</tr>';
			};
			break;
		default:
	};
	$out .= '</tbody></table></div>';
	return $out;
};
$queryBatch = 'select phase_id, unitprocedure_id, min(datetime) as starttime, 
datediff(ss, (select Log_Open_DT from BatchIdLog where Batch_Log_ID = \'' . $_GET['batch'] . '\'), min(datetime)) as durfromstart, 
max(datetime) as endtime, 
datediff(ss, (select Log_Open_DT from BatchIdLog where Batch_Log_ID = \'' . $_GET['batch'] . '\'), max(datetime)) as durfromend, 
datediff(ss, min(datetime), max(datetime)) as durphase,
Phase_Instance_ID 
from BatchDetail
where Batch_Log_ID = \'' . $_GET['batch'] . '\' and Phase_Instance_ID <> \'\'
group by phase_id, Phase_Instance_ID, unitorconnection, unitprocedure_id
order by starttime, endtime';
$dataBatch = odbc_exec($bConn, $queryBatch);
$iBatchRow = 1;
while(odbc_fetch_row($dataBatch)) {
	$aDataBatch[$iBatchRow] = ['phase' => odbc_result($dataBatch, 1), 'procedure' => odbc_result($dataBatch, 2), 'starttime' => substr(odbc_result($dataBatch, 3), 0, -4), 'durfromstart' => fToTime(odbc_result($dataBatch, 4)), 'endtime' => substr(odbc_result($dataBatch, 5), 0, -4), 'durfromend' => fToTime(odbc_result($dataBatch, 6)), 'durphase' => fToTime(odbc_result($dataBatch, 7)), 'id' => odbc_result($dataBatch, 8)];
	$iBatchRow++;
};
$queryProcessVariables = 'select parameter_id, actual_value, target_value, unitofmeasure, unitprocedure_id, phase_id, phase_instance_id, datetime from ProcessVar where batch_log_id = \'' . $_GET['batch'] . '\' order by datetime';
$dataProcessVariables = odbc_exec($bConn, $queryProcessVariables);
$iProcessPhase = 0;
$currentPhase = '';
while(odbc_fetch_row($dataProcessVariables)) {
	if (odbc_result($dataProcessVariables, 1) != 'Units') {
		if (odbc_result($dataProcessVariables, 7) != $currentPhase) {
			$currentPhase = odbc_result($dataProcessVariables, 7);
			$iProcessPhase++;
			$processPhase[$currentPhase] = $iProcessPhase;
		};
		$aDataProcess[$processPhase[odbc_result($dataProcessVariables, 7)]]['procedure'] = odbc_result($dataProcessVariables, 5);
		$aDataProcess[$processPhase[odbc_result($dataProcessVariables, 7)]]['phase'] = odbc_result($dataProcessVariables, 6);
		$aDataProcess[$processPhase[odbc_result($dataProcessVariables, 7)]]['datetime'] = substr(odbc_result($dataProcessVariables, 8), 0, -4);
		$actual = trim(odbc_result($dataProcessVariables, 2));
		if (!empty($actual) && $actual != '0.0') {
			$aDataProcess[$processPhase[odbc_result($dataProcessVariables, 7)]][odbc_result($dataProcessVariables, 1)]['actual'] = odbc_result($dataProcessVariables, 2);
		};
		$target = trim(odbc_result($dataProcessVariables, 3));
		if (!empty($target) && $target != '0.0') {
			$aDataProcess[$processPhase[odbc_result($dataProcessVariables, 7)]][odbc_result($dataProcessVariables, 1)]['target'] = odbc_result($dataProcessVariables, 3);
		};
		if (isset($aDataProcess[$processPhase[odbc_result($dataProcessVariables, 7)]][odbc_result($dataProcessVariables, 1)]['actual']) || isset($aDataProcess[$processPhase[odbc_result($dataProcessVariables, 7)]][odbc_result($dataProcessVariables, 1)]['target'])) {
			$unit = trim(odbc_result($dataProcessVariables, 4));
			if (!empty($unit) && $unit != '0.0') {
				$aDataProcess[$processPhase[odbc_result($dataProcessVariables, 7)]][odbc_result($dataProcessVariables, 1)]['unit'] = odbc_result($dataProcessVariables, 4);
			};
		};
		if (isset($aDataProcess[$processPhase[odbc_result($dataProcessVariables, 7)]][odbc_result($dataProcessVariables, 1)])) {
			if (count($aDataProcess[$processPhase[odbc_result($dataProcessVariables, 7)]][odbc_result($dataProcessVariables, 1)]) == 1) {
				foreach ($aDataProcess[$processPhase[odbc_result($dataProcessVariables, 7)]][odbc_result($dataProcessVariables, 1)] as $key => $value) {
					$aDataProcess[$processPhase[odbc_result($dataProcessVariables, 7)]][odbc_result($dataProcessVariables, 1)] = $value;
				};
			};
		};
	};
};
$queryReportBatch = 'select Campaign_ID, Lot_ID, Batch_ID, Log_Open_DT, Log_Close_DT, datediff(ss, Log_Open_DT, Log_Close_DT) as duration, status.name, train_id, recipe_id, recipe_version, product_id
	from BatchIdLog
	join (select id, name
	from (select id, max(number) as number
	from (select Batch_Log_ID as id, Description as name, row_number() over (order by batch_log_id) as number
	from batchdetail
	join CodeTable on Code = Action_CD
	where Description like \'%batch set%\' and Action_CD <> 206 and batch_log_id = \'' . $_GET['batch'] . '\') as temp
	group by id) as temp
	join (select Description as name, row_number() over (order by batch_log_id) as number
	from batchdetail
	join CodeTable on Code = Action_CD
	where Description like \'%batch set%\' and Action_CD <> 206 and batch_log_id = \'' . $_GET['batch'] . '\') as temp2 on temp.number = temp2.number) as status on status.id = batch_log_id
	where Batch_Log_ID = \'' . $_GET['batch'] . '\'';
$dataReportBatch = odbc_exec($bConn, $queryReportBatch);
while(odbc_fetch_row($dataReportBatch)) {
	$stdOut .= '<div class="printsafe batchreporttable"><h3>Batch Information</h3><table class="records"><thead><tr><th>Batch ID</th><th>Duration</th><th>Start Time</th><th>End Time</th><th>Product</th><th>Recipe</th><th>Version</th><th>Train</th><th>Status</th></tr></thead>
	<tbody><tr class="oddRow"><td>' . odbc_result($dataReportBatch, 1) . '/' . odbc_result($dataReportBatch, 2) . '/' . odbc_result($dataReportBatch, 3) . '</td><td>' . fToTime(odbc_result($dataReportBatch, 6)) . '</td><td>' . substr(odbc_result($dataReportBatch, 4), 0, -4) . '</td><td>' . substr(odbc_result($dataReportBatch, 5), 0, -4) . '</td><td>' . odbc_result($dataReportBatch, 11) . '</td><td>' . odbc_result($dataReportBatch, 9) . '</td><td>' . odbc_result($dataReportBatch, 10) . '</td><td>' . odbc_result($dataReportBatch, 8) . '</td><td>' . substr(odbc_result($dataReportBatch, 7), 10) . '</td></tr><tbody></table></div>';
	if (substr(odbc_result($dataReportBatch, 7), 10) != 'Done') {
		$_SESSION['sqlMessage'] = 'Cannot show report. The selected batch is not complete or has failed!';
		$_SESSION['uiState'] = 'error';
		fRedirect();
	};
};
?>