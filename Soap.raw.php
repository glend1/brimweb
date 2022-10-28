<?php
$title = 'Raw Methods';
require_once 'includes/header.php';
require_once 'includes/SOAPincludes.php';
$devices = getData("j_device", ["key" => "id", "sort" => "name"]);
$stdOut .= '<form class="wmo oddRow" action="Soap.raw.php" method="get">
		<h3>Read I/O' . soapDescription('This method eads a PLC Address and returns the result', ['device', 'address']) . '</h3>
		<div class="soaptask">Doesn\'t work straight away when creating a new instance.</div>
		<input type="hidden" name="method" value="readIo"/>
		Device: ' . createSelect($devices, 0) . '
		Address: <input type="text" name="soap[arg1]" />
		<input ' . $disabled . 'type="submit" value="Get I/O!" />
	</form>';
$stdOut .= '<form class="wmo evenRow" action="Soap.raw.php" method="get">
		<h3>Set I/O' . soapDescription('This method sets a PLC Address', ['device', 'address', 'io value']) . '</h3>
		<div class="soaptask">Doesn\'t work because "Object" aren\'t handled properly</div>
		<input type="hidden" name="method" value="writeIo"/>
		Device: ' . createSelect($devices, 0) . '
		Address: <input type="text" name="soap[arg1]" />
		Value: <input type="text" name="soap[arg2]" />
		<input ' . $disabled . 'type="submit" value="Set I/O!" />
	</form>';
$stdOut .= '<form class="wmo oddRow" action="Soap.raw.php" method="get">
		<h3>SMS Send' . soapDescription('This method sends a SMS message', ['phone number', 'sms message']) . '</h3>
		<div class="soaptask">Seems to work first time but then doesn\'t pick up anymore requests</div>
		<input type="hidden" name="method" value="smsSend"/>
		# Number: <input type="text" name="soap[arg0][]"/>
		Message: <input type="text" name="soap[arg1]"/>
		<input ' . $disabled . 'type="submit" value="Send SMS!"/>
	</form>';
$stdOut .= '<form class="wmo evenRow" action="Soap.raw.php" method="get">
		<h3>E-Mail Send' . soapDescription('This method sends an E-Mail', ['email', 'subject', 'email message']) . '</h3>
		<div class="soaptask">Doesn\'t work because "AddressStructure" isn\'t handled</div>
		<input type="hidden" name="method" value="sendMail"/>
		E-Mail: <input type="text" name="soap[arg0]"/>
		Subject: <input type="text" name="soap[arg1]"/>
		Message: <input type="text" name="soap[arg2]"/>
		<input ' . $disabled . 'type="submit" value="Send E-Mail!"/>
	</form>';
$stdOut .= '<form class="wmo oddRow" action="Soap.raw.php" method="get">
		<h3>Print CSV' . soapDescription('This method takes a SQL Query and prints it to a File in CSV format on BrimWeb', ['table', 'query', 'path', 'filename', 'durations']) . '</h3>
		<div class="soaptask">Doesn\'t work because "TimeManipulation" isn\'t handled</div>
		<input type="hidden" name="method" value="sqlFileStatic"/>
		Table: <input type="text" name="soap[arg0]"/>
		Query: <input type="text" name="soap[arg1]"/>
		Path: <input type="text" name="soap[arg2]"/>
		Filename: <input type="text" name="soap[arg3]"/>
		Durations: <input type="text" name="soap[arg4]"/>
		<input ' . $disabled . 'type="submit" value="Print CSV!"/>
	</form>';
require_once 'includes/footer.php'; ?>