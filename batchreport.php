<?PHP 
$title = 'Cap 9/14 Production Batch Report';
require_once 'includes/header.php';
require_once 'includes/evalmath.class.php';
if (!fCanSeePublic(@$_SESSION['permissions']['page'][4] >= 100)) {
	$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
$aDE = fPermissionDE();
if (!(isset($_GET['batch']) && isset($_GET['dbname']))) {
	$_SESSION['sqlMessage'] = 'Select a Batch!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
require_once 'batchreportlibrary.php';

$batchDataSearch = fFind($aDataProcess, ['type' => 'phase', 'search' => 'batch_data']);
$batchDataReturn = fReturn($aDataProcess, $batchDataSearch, ['return' => ['Operator_ID', 'Seed_Batch_ID', 'Seed_Ag_PPM', 'Red_Batch_ID', 'Urea_Batch_ID', 'PAA_Batch_ID']]);
$stdOut .= fTable($batchDataReturn,	['PAA_Batch_ID' => ['round' => 'no', 'alias' => 'PAA Batch'], 'Operator_ID' => ['alias' => 'Operator'], 'Seed_Ag_PPM' => ['alias' => 'Seed Silver PPM'], 'Red_Batch_ID' => ['alias' => 'Reductant Batch'], 'Urea_Batch_ID' => ['alias' => 'Urea Batch'], 'Seed_Batch_ID' => ['alias' => 'Seed Batch']], 'Material Information', 'horizontal');

$phDataSearch = fFind($aDataProcess, ['type' => 'phase', 'search' => 'ph_data']);
$phDataReturn = fReturn($aDataProcess, $phDataSearch, ['return' => ['procedure', 'Mode', 'pH_PV']]);
$stdOut .= fTable($phDataReturn, ['procedure' => ['alias' => 'Procedure'], 'Mode' => ['alias' => 'Type'], 'pH_PV' => ['alias' => 'pH', 'round' => 1]], 'pH Data');

$residueDataSearch = fFind($aDataProcess, ['type' => 'phase', 'search' => 'res']);
$residueDataReturn = fReturn($aDataProcess, $residueDataSearch, ['return' => ['WEIGHT']]);
$stdOut .= fTable($residueDataReturn, ['WEIGHT' => ['alias' => 'Weight', 'array' => 'actual', 'round' => 2]], 'Residue Weight');

$decantTimingDecantSearch = fFind($aDataBatch, ['type' => 'procedure', 'search' => 'decant wash']);
$decantTimingH20HSearch = fFind($aDataBatch, ['type' => 'phase', 'search' => 'addh2oh']);
$decantTimingConfirmSearch = fFind($aDataBatch, ['type' => 'phase', 'search' => 'decant']);
$decantTimingSearchStart = fAndArrays([$decantTimingDecantSearch, $decantTimingH20HSearch]);
$decantTimingSearchEnd = fAndArrays([$decantTimingDecantSearch, $decantTimingConfirmSearch]);
$decantTimingsReturnStart = fReturn($aDataBatch, $decantTimingSearchStart, ['return' => ['procedure', 'starttime', 'durfromstart']]);
$decantTimingsReturnEnd = fReturn($aDataBatch, $decantTimingSearchEnd, ['return' => ['endtime']]);
$decantTimingsReturn = fArrayZip(['start' => $decantTimingsReturnStart, 'end' => $decantTimingsReturnEnd]);
$stdOut .= fTable($decantTimingsReturn,	['start_procedure' => ['alias' => 'Procedure'], 'start_starttime' => ['alias' => 'Start Date/Time'], 'start_durfromstart' => ['alias' => 'Duration from batch start'], 'end_endtime' => ['alias' => 'End Date/Time']], 'Decant Timings');

$motherLiqSearchStart = fFind($aDataBatch, ['type' => 'phase', 'search' => 'stopheat']);
$motherLiqReturnStart = fReturn($aDataBatch, $motherLiqSearchStart, ['end' => 1, 'return' => ['endtime']]);
fTagData($units, $motherLiqReturnStart, 'endtime', ['TT_1923_204']);
$motherLiqSearchEnd = fFind($aDataBatch, ['type' => 'phase', 'search' => 'decant']);
$motherLiqReturnEnd = fReturn($aDataBatch, $motherLiqSearchEnd, ['end' => 1, 'return' => ['starttime']]);
fTagData($units, $motherLiqReturnEnd, 'starttime', ['TT_1923_204']);
$motherLiqReturn = fArrayZip(['start' => $motherLiqReturnStart, 'end' => $motherLiqReturnEnd]);
fCustomData($motherLiqReturn, ['dur' => ['function' => 'customduration', 'start' => 'start_endtime', 'end' => 'end_starttime']]);
$stdOut .= fTable($motherLiqReturn, ['start_endtime' => ['alias' => 'Start Date/Time'], 'start_TT_1923_204' => ['alias' => 'Temp', 'array' => 1, 'round' => 1], 'end_starttime' => ['alias' => 'End Date/Time'], 'end_TT_1923_204' => ['alias' => 'Temp', 'array' => 1, 'round' => 1], 'dur' => ['alias' => 'Duration']], 'Mother Liquor Transfer');

$doseSearch = fFind($aDataBatch, ['type' => 'phase', 'search' => 'dose']);
$doseReturn = fReturn($aDataBatch, $doseSearch, ['return' => ['starttime', 'endtime']]);
fTagData($units, $doseReturn, ['starttime', 'endtime'], ['FT_1923_2_VF_PV', 'FT_1923_2_SG_PV', 'FT_1923_3_VF_PV', 'FT_1923_1_AgMF_Tot', 'FT_1923_3_VF_Tot']);
$stdOut .= fTable($doseReturn, ['starttime' => ['alias' => 'Start Date/Time'], 'endtime' => ['alias' => 'End Date/Time'], 'FT_1923_2_VF_PV' => ['visible' => 'no'], 'FT_1923_2_SG_PV' => ['visible' => 'no'], 'FT_1923_3_VF_PV' => ['visible' => 'no'], 'FT_1923_1_AgMF_Tot' => ['visible' => 'no'], 'FT_1923_3_VF_Tot' => ['visible' => 'no']], 'Dose phase times');

$batchTempSearchStart = fFind($aDataBatch, ['type' => 'phase', 'search' => 'confirm', 'end' => 1]);
$batchTempReturnStart = fReturn($aDataBatch, $batchTempSearchStart, ['return' => ['starttime']]);
$batchTempSearchEnd = fFind($aDataBatch, ['type' => 'phase', 'search' => 'xfer']);
$batchTempReturnEnd = fReturn($aDataBatch, $batchTempSearchEnd, ['return' => ['endtime']]);
$batchTempReturn = fArrayZip(['start' => $batchTempReturnStart, 'end' => $batchTempReturnEnd], true);
fTagData($units, $batchTempReturn, ['start_starttime', 'end_endtime'], ['TT_1923_204']);
$stdOut .= fPlotChart($batchTempReturn, $chartNumber, 'Batch Temperature', ['TT_1923_204' => 'Temperature']);

$stdOut .= fPlotChart($doseReturn, $chartNumber, 'Total Flow through Dosing phase', ['FT_1923_3_VF_Tot' => 'Reductant Volume', 'FT_1923_1_AgMF_Tot' => 'Silver Mass']);
$stdOut .= fPlotChart($doseReturn, $chartNumber, 'Flow through Dosing phase', ['FT_1923_3_VF_PV' => 'Reductant Flow Rate', 'FT_1923_2_SG_PV' => 'Silver Nitrate', 'FT_1923_2_VF_PV' => 'Silver Nitrate Flow Rate']);

odbc_close($bConn);
odbc_close($rConn);
require_once 'includes/footer.php'; ?>