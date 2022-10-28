<?PHP 
$title = 'Batch Report Documentation';
require_once 'includes/header.php';
require_once 'includes/evalmath.class.php';
if (!fCanSeePublic(@$_SESSION['permissions']['page'][4] >= 100)) {
	$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
$stdOut .= '<h2>Data</h2>
<h3>$aDataBatch</h3>
Output:
<pre>[1] => Array
	(
		[phase] => (string) 
		[procedure] => (string)
		[starttime] => (datetime)
		[durfromstart] => "fToTime"(string)
		[endtime] => (datetime)
		[durfromend] => "fToTime"(string)
		[durphase] => "fToTime"(string)
		[id] => (string) [UNIQUE]
	)
[2] => ...</pre>
<table><thead><tr><th colspan="8">BatchDetail</th></tr><tr><th>phase</th><th>procedure</th><th>starttime</th><th>durfromstart</th><th>endtime</th><th>durfromend</th><th>durphase</th><th>id</th></thead>
<tbody><td>phase_id</td><td>unitprocedure_id</td><td>min(datetime)</td><td>datediff(ss, (*), min(datetime))</td><td>max(datetime)</td><td>datediff(ss, (*), max(datetime))</td><td>datediff(ss, min(datetime), max(datetime))</td><td>Phase_Instance_ID</td></tr>
<tr><td>Phase Name</td><td>Procedure ID/Name</td><td>Start time of Phase</td><td>Duration from Batch Start till Phase Start</td><td>End time of Phase</td><td>Duration from Batch Start till Phase End</td><td>Duration of Phase</td><td>Phase ID</td></tbody></table>
* = select Log_Open_DT from BatchIdLog where Batch_Log_ID = \'batchid\'
<h3>$aDataProcess</h3>
Output:
<pre>[1] => Array
	(
		[procedure] => (string) 
		[phase] => (string) 
		[datetime] => (datetime)
		[{parameter1}] => Array
			(
				[actual] => (string)
				[target] => (string)
				[unit] => (string)
			)
		[{parameter2}] => Array
			(
				[actual] => (string)
				[target] => (string)
				[unit] => (string)
			)
		[{parameter3}] => ...
	)
[2] => ...</pre>
<table><thead><tr><th colspan="8">ProcessVar</th></tr><tr><th>{parameter1}</th><th>actual</th><th>target</th><th>unit</th><th>procedure</th><th>phase</th><th>*phase_instance_id</th><th>*datetime</th></tr></thead>
<tbody><tr><td>parameter_id</td><td>actual_value</td><td>target_value</td><td>unitofmeasure</td><td>unitprocedure_id</td><td>phase_id</td><td>phase_instance_id</td><td>datetime</td></tr>
<tr><td>Parameter</td><td>Actual Value</td><td>Target Value</td><td>Measurement Unit</td><td>Procedure ID/Name</td><td>Phase Name</td><td>Phase ID</td><td>Date/Time Stamp</td></tr></body></table>
* = not used
<h2>Search</h2>
<h3>fFind()</h3>
Usage:
<pre>$findObj = fFind(
	$data, 
	[
		\'start\' => (integer),
		\'end\' => (integer),
		\'expression\' => (string) \'{n}*2\',
		\'type\' => partial(string),
		\'search\' => partial(string)
	]
);</pre>
This function finds data in an array. Returns an array with the positional elements.
<table><thead><tr><th>Value</th><th>Req</th><th>Default</th><th>Definition</th></tr></thead>
<tbody><tr><td>findObj</td><td>Req</td><td></td><td>Return value, acceptable by fNotArrays(), fOrArrays(), fAndArrays() or fReturn()</td></tr>
<tr><td>data</td><td>Req</td><td></td><td>Array to Search, must equal $aDataBatch or $aDataProcess, can be used in fReturn()</td></tr>
<tr><td>start</td><td>Opt</td><td>1</td><td>An additional method for filtering results, the startpoint for findObj. if start and end are the same then 1 result is returned at the position specified</td></tr>
<tr><td>end</td><td>Opt</td><td>count($findObj)</td><td>An additional method for filtering results, the end for findObj. if start and end are the same then 1 result is returned at the position specified</td></tr>
<tr><td>expression</td><td>Opt</td><td>NULL</td><td>An additional method for filtering results, used to return results based on the specified pattern (\'{n}*2\' will return every even dataset) if {n} is not specified the patern will evaluate the identically for each iteration and will return the position of the row specified</td></tr>
<tr><td>{n}</td><td>Opt</td><td>NULL</td><td>an internally used variable and iterator</td></tr>
<tr><td>type</td><td>Req</td><td></td><td>Top level array element to search in. Will return partial matches</td></tr>
<tr><td>search</td><td>Req</td><td></td><td>String to search for within type. Will return partial matches</td></tr></tbody></table>
Output:
<pre>Array
(
    [1] => 48
    [2] => 49
    [3] => 50
    [4] => 51
    ...
)</pre>
<h3>fNotArrays()</h3>
Usage:
<pre>$findObj = fNotArrays(
	[
		$findObj,
		$findObj,
		...
	]
);</pre>
This function returns an array where array elements are not common between array arguments.
<table><thead><tr><th>Value</th><th>Req</th><th>Definition</th></tr></thead>
<tbody><tr><td>findObj</td><td>3 (2 arg)</td><td>Return value and arguments, acceptable by  fOrArrays(), fAndArrays() or fReturn()</td></tr>
</tbody></table>
Output:
<pre>Array
(
    [1] => 48
    [2] => 49
    [3] => 50
    [4] => 51
    ...
)</pre>
<h3>fOrArrays()</h3>
Usage:
<pre>$findObj = fOrArrays(
	[
		$findObj,
		$findObj,
		...
	]
);</pre>
This function returns an array where array elements exist in any of the array arguments.
<table><thead><tr><th>Value</th><th>Req</th><th>Definition</th></tr></thead>
<tbody><tr><td>findObj</td><td>3 (2 arg)</td><td>Return value and arguments, acceptable by  fNotArrays(), fAndArrays() or fReturn()</td></tr>
</tbody></table>
Output:
<pre>Array
(
    [1] => 48
    [2] => 49
    [3] => 50
    [4] => 51
    ...
)</pre>
<h3>fAndArrays()</h3>
Usage:
<pre>$findObj = fAndArrays(
	[
		$findObj,
		$findObj,
		...
	]
);</pre>
This function returns an array where elements are common between array arguments
<table><thead><tr><th>Value</th><th>Req</th><th>Definition</th></tr></thead>
<tbody><tr><td>findObj</td><td>3 (2 arg)</td><td>Return value and arguments, acceptable by  fNotArrays(), fOrArrays() or fReturn()</td></tr>
</tbody></table>
Output:
<pre>Array
(
    [1] => 48
    [2] => 49
    [3] => 50
    [4] => 51
    ...
)</pre>
<h2>Get</h2>
<h3>fReturn()</h3>
Usage:
<pre>$returnArray = fReturn(
	$data,
	$findObj,
	[
		\'start\' => (integer),
		\'end\' => (integer),
		\'expression\' => (string) \'{n}*2\',
		\'return\' =>
		[
			\'dataKey\',
			\'dataKey\',
			...
		]
	]
);</pre>
This function returns an array using a $findObj array for the specified $data array
<table><thead><tr><th>Value</th><th>Req</th><th>Default</th><th>Definition</th></tr></thead>
<tbody><tr><td>returnArray</td><td>Req</td><td></td><td>Function output, used in fArrayZip(), fPlotChart() and fTagData()</td></tr>
<tr><td>data</td><td>Req</td><td></td><td>Array type used to return, used in fFind()</td></tr>
<tr><td>findObj</td><td>Req</td><td></td><td>Array type used to return, acceptable by  fNotArrays(), fOrArrays() or fReturn(), used in fFind()</td></tr>
<tr><td>start</td><td>Opt</td><td>1</td><td>An additional method for filtering results, the startpoint for findObj. if start and end are the same then 1 result is returned at the position specified</td></tr>
<tr><td>end</td><td>Opt</td><td>count($findObj)</td><td>An additional method for filtering results, the end for findObj. if start and end are the same then 1 result is returned at the position specified</td></tr>
<tr><td>expression</td><td>Opt</td><td>NULL</td><td>An additional method for filtering results, used to return results based on the specified pattern (\'{n}*2\' will return every even dataset) if {n} is not specified the patern will evaluate the identically for each iteration and will return the position of the row specified</td></tr>
<tr><td>{n}</td><td>Opt</td><td>NULL</td><td>an internally used variable and iterator</td></tr>
<tr><td>return</td><td>Opt</td><td>All</td><td>an array of elements to return</td></tr>
<tr><td>dataKey</td><td>Opt</td><td>All</td><td>identical case-sensitive key values of $data to return</td></tr>
</tbody></table>
Output:
<pre>[1] => Array
	(
		[procedure] => Check Level
		[phase] => CHECKLEV
		[datetime] => 2014-03-18 17:21:30.000
		[Wait_Above] => 1
		[Level] => Array
			(
				[actual] => 1016.0840
				[target] => 980.00000
			)

		[Level_Log] => 1016.0840
	)
[2] => ...</pre>
<h3>fArrayZip()</h3>
Usage:
<pre>$returnArray = fArrayZip(
	[
		\'prefix\' => $returnArray,
		\'prefix\' => $returnArray,
		...
	],
	match
);</pre>
This function adds $returnArray contents together
<table><thead><tr><th>Value</th><th>Req</th><th>Default</th><th>Definition</th></tr></thead>
<tbody><tr><td>returnArray</td><td>3 (2 Arg)</td><td></td><td>Inputs and outputs used for creating the compound array, used in fReturn(), fPlotChart() and fTagData()</td></tr>
<tr><td>prefix</td><td>Req</td><td></td><td>Value added to the begining of each array key in the result set</td></tr>
<tr><td>match</td><td>Opt</td><td>FALSE</td><td>Boolean, by default returns results that that dont have equivalent results</td></tr>
</tbody></table>
Output:
<pre>[1] => Array
	(
		[procedure] => Check Level
		[phase] => CHECKLEV
		[datetime] => 2014-03-18 17:21:30.000
		[Wait_Above] => 1
		[Level] => Array
			(
				[actual] => 1016.0840
				[target] => 980.00000
			)

		[Level_Log] => 1016.0840
	)
[2] => ...</pre>
<h3>fTagData()</h3>
Usage:
<pre>fTagData(
	$units, 
	$returnArray, 
	\'snapshot\' OR [
		\'starttime\', 
		\'endtime\'
	], 
	[
		\'tagname\', 
		...
	],
	datapoints
);</pre>
This function adds historian tag data to a data array
<table><thead><tr><th>Value</th><th>Req</th><th>Default</th><th>Definition</th></tr></thead>
<tbody><tr><td>units</td><td>Req</td><td></td><td>Constant. Holds units for tags used</td></tr>
<tr><td>returnArray</td><td>Req</td><td></td><td>Output and Input of function, used in fReturn(), fPlotChart() and fArrayZip()</td></tr>
<tr><td>snapshot</td><td>OR starttime AND endtime</td><td></td><td>Date/Time Stamp in $returnArray. Will append the returnArray with a single datapoint</td></tr>
<tr><td>starttime</td><td>AND endtime OR snapshot</td><td></td><td>Date/Time Stamp in $returnArray. Will append the returnArray with X datapoints using this as a starting point</td></tr>
<tr><td>endtime</td><td>AND starttime OR snapshot</td><td></td><td>Date/Time Stamp in $returnArray. Will append the returnArray with X datapoints using this as a end point</td></tr>
<tr><td>tagname</td><td>1</td><td></td><td>Case sensitive. Tagnames to be be appended</td></tr>
<tr><td>datapoints</td><td></td><td>35</td><td>Datapoints to return</td></tr>
</tbody></table>
Output:
<pre>[1] => Array
	(
		[phase] => CHECKTMP
		[procedure] => Load
		[starttime] => 2014-03-18 20:19:55.000
		[durfromstart] => 3h 1m 11s
		[endtime] => 2014-03-18 21:21:23.000
		[durfromend] => 4h 2m 39s
		[durphase] => 1h 1m 28s
		[id] => 3ZGJM7KEX6
		[{tagname1}] => Array
			(
				[2014-03-18 20:21:40.000] => 0.99666666984558105
				[2014-03-18 20:23:25.000] => 0.99500000476837158
				...
			)
		[{tagname2}] => Array
			(
				[2014-03-18 20:21:40.000] => 40.7236328125
				[2014-03-18 20:23:25.000] => 40.179134368896484
				...
			)
		[{tagname3}] => ...
	)
[2] => Array
	(
		[phase] => CHECKTMP
		[procedure] => Decant Wash 1
		[starttime] => 2014-03-19 17:31:56.000
		[durfromstart] => 1d 13m 12s
		[endtime] => 2014-03-19 17:49:36.000
		[durfromend] => 1d 30m 52s
		[durphase] => 17m 40s
		[id] => 3ZGK5PSCM9
		[{tagname1}] => Array
			(
				[2014-03-19 17:32:26.000] => 0.98833334445953369
				[2014-03-19 17:32:56.000] => 0.99333333969116211
				...
			)

		[{tagname2}] => Array
			(
				[2014-03-19 17:32:26.000] => 55.756580352783203
				[2014-03-19 17:32:56.000] => 55.510696411132813
				...
			)
		[{tagname3}] => ...
	)
...</pre>
<h2>Display</h2>
<h3>Batch Information</h3>
<table><thead><tr><th colspan="8">BatchIdLog</th></tr>
<tr><th colspan="3">Batch ID</th><th>Start Date/Time</th><th>End Date/Time</th><th>fToTime(Duration)</th><th>Recipe Id</th><th>Recipe Version</th></tr>
</thead><tbody>
<tr><th>Campaign_ID</td><td>Lot_ID</td><td>Batch_ID</td><td>Log_Open_DT</td><td>Log_Close_DT</td><td>datediff(ss, Log_Open_DT, Log_Close_DT)</td><td>recipe_id</td><td>recipe_version</td></tr>
</tbody></table>
Constant. Always output first. No functions can be performed.
<h3>fPlotChart()</h3>
Usage:
<pre>$stdOut .= fPlotChart(
	$returnArray, 
	$chartNumber, 
	\'string\' OR [
		\'variable\' => (string), 
		\'string\' => (string) \'test {var}\'
	], 
	[
		\'tagname\' => \'name\', 
		...
	]
);</pre>
This function returns a flot HTML graph
<table><thead><tr><th>Value</th><th>Req</th><th>Default</th><th>Definition</th></tr></thead>
<tbody>
<tr><td>stdOut</td><td>Req</td><td></td><td>Display string</td></tr>
<tr><td>returnArray</td><td>Req</td><td></td><td>Data array to use for the graph</td></tr>
<tr><td>chartNumber</td><td>Req</td><td></td><td>Constant. Internal counter</td></tr>
<tr><td>variable</td><td>Opt</td><td>NULL</td><td>name of the variable you want to substitute into the headline</td></tr>
<tr><td>string</td><td>Opt</td><td>NULL</td><td>string template, optionally with {var} to substitute</td></tr>
<tr><td>{var}</td><td>Opt</td><td>NULL</td><td>where variable should be substituted in</td></tr>
<tr><td>tagname</td><td>Opt</td><td>Array()</td><td>Case sensitive tagname previously obtained with fTagData()</td></tr>
<tr><td>name</td><td>Opt</td><td>tagname</td><td>Display name for graph legend</td></tr>
</tbody></table>
<h3>fTable()</h3>
Usage:
<pre>$stdOut .= fTable(
	$returnArray,
	[
		header =>
			[
				round => (integer),
				array => (string),
				alias => (string),
				visible => (string)
			],
		header => ...
	],
	title,
	direction
);</pre>
This function returns a HTML table
<table><thead><tr><th>Value</th><th>Req</th><th>Default</th><th>Definition</th></tr></thead>
<tbody>
<tr><td>stdOut</td><td>Req</td><td></td><td>Display string</td></tr>
<tr><td>returnArray</td><td>Req</td><td></td><td>Data array to use for the table</td></tr>
<tr><td>header</td><td>Opt</td><td>All</td><td>Array key from $returnArray</td></tr>
<tr><td>round</td><td>Opt</td><td>2</td><td>Number of decimal places to show if it is a number. Use \'no\' to force no rounding</td></tr>
<tr><td>array</td><td>Opt</td><td>{all}</td><td>Subkey from header or index to use</td></tr>
<tr><td>visible</td><td>Opt</td><td>yes</td><td>Visibility for table column/row toggle, yes or no</td></tr>
<tr><td>title</td><td>Opt</td><td></td><td>Table headline</td></tr>
<tr><td>direction</td><td>Opt</td><td>vertical</td><td>determines how the table headers should be shown, vertical or horizontal</td></tr>
</tbody></table>';
require_once 'includes/footer.php'; ?>