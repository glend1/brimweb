<?php
$title = 'Database I/O Methods';
require_once 'includes/header.php';
require_once 'includes/SOAPincludes.php';
$devices = getData("j_device", ["key" => "id", "sort" => "name"]);
$servers = getData("j_serverio", ["key" => "id", "sort" => "name"]);
$datas = getData("j_data", ["key" => "id", "sort" => "address"]);
$structureEvents = getData("j_structureevent", ["key" => "id", "sort" => "operator", "condition"]);
$operators = ['==' => 'Equals', '!=' => 'NOT Equals','>' => 'Greater Than', '<' => 'Less Than', '<=' => 'Less Than OR Equal', '>=' => 'Greater Than OR Equal'];
$stdOut .= '<form class="wmo oddRow" action="soap.io.php" method="get">
		<h3>I/O Add' . soapDescription('This method adds a server and host to BrimJava\'s internal memory creating anything if required', ['server', 'host']) . '</h3>
		<input type="hidden" name="method" value="loadIoAdd"/>
		Server: <input type="text" name="soap[arg0]"/>
		Host: <input type="text" name="soap[arg1]"/>
		<input ' . $disabled . 'type="submit" value="Add I/O!"/>
	</form>';
$stdOut .= '<div class="wmo evenRow"><h3>I/O Update</h3>';
$stdOut .= 'Not complete</div>';
$stdOut .= '<form class="wmo oddRow" action="soap.io.php" method="get">
		<h3>I/O Remove' . soapDescription('This method remove a server and host from BrimJava\'s internal memory cleaning up after itself as required', ['server', 'host']) . '</h3>
		<input type="hidden" name="method" value="loadIoRemove"/>
		Server: <input type="text" name="soap[arg0]"/>
		Host: <input type="text" name="soap[arg1]"/>
		<input ' . $disabled . 'type="submit" value="Remove I/O!"/>
	</form>';
$stdOut .= '<form class="wmo evenRow" action="soap.io.php" method="get">
		<h3>Add Data' . soapDescription('This method creates an Address inside the Data structure', ['server', 'device', 'address']) . '</h3>
		<div class="soaptask">Unsure how hidden true will behave</div>
		<input type="hidden" name="method" value="loadDataAdd"/>
		Server: ' . createSelect($servers, 0) . '
		Device: ' . createSelect($devices, 1) . '
		Address: <input type="text" name="soap[arg2]"/>
		<input type="hidden" name="soap[arg3]" value="true"/>
		<input ' . $disabled . 'type="submit" value="Add Data!"/>
	</form>';
$stdOut .= '<div class="wmo oddRow"><h3>Update Data</h3>';
$stdOut .= 'Not complete</div>';
$stdOut .= '<form class="wmo evenRow" action="soap.io.php" method="get">
		<h3>Remove Data' . soapDescription('This method removes an Address from the Data structure and then cleans up after itself as required', ['server', 'device', 'address']) . '</h3>
		<input type="hidden" name="method" value="loadDataRemove"/>
		Server: ' . createSelect($servers, 0) . '
		Device: ' . createSelect($devices, 1) . '
		Address: <input type="text" name="soap[arg2]"/>
		<input ' . $disabled . 'type="submit" value="Remove Data!"/>
	</form>';
$stdOut .= '<form class="wmo oddRow" action="soap.io.php" method="get">
		<h3>Add Structure' . soapDescription('This method creates a trigger inside a Structure', ['server', 'device', 'address', 'condition', 'operator']) . '</h3>
		<input type="hidden" name="method" value="loadStructureAdd"/>
		Server: ' . createSelect($servers, 0) . '
		Device: ' . createSelect($devices, 1) . '
		Address: <input type="text" name="soap[arg2]"/>
		Condition: <input type="text" name="soap[arg3]"/>
		Operator: ' . createSelect($operators, 4) . '
		<input ' . $disabled . 'type="submit" value="Add Structure!"/>
	</form>';
$stdOut .= '<div class="wmo evenRow"><h3>Update Structure</h3>';
$stdOut .= 'Not complete</div>';
$stdOut .= '<form class="wmo oddRow" action="soap.io.php" method="get">
		<h3>Remove Structure' . soapDescription('This method removes a Trigger from a Structure an then cleans up after itself', ['data', 'structure event']) . '</h3>
		<div class="soaptask">This could be refactored to only need "StructureEvent"</div>
		<input type="hidden" name="method" value="loadStructureRemove"/>
		Data: ' . createSelect($datas, 0) . '
		StructureEvent: ' . createSelect($structureEvents, 1) . '
		<input ' . $disabled . 'type="submit" value="Remove Structure!"/>
	</form>';
require_once 'includes/footer.php'; ?>