<?PHP
require_once 'secrets.php';
if (isset($_GET['search'])) {
	$conn = odbc_connect('DRIVER={SQL Server};Server=INSQL2;Database=PlantAvail;', $dbUsername, $dbPassword);
	$query = 'select Name
	from Users
	where name like \'%' . $_GET['search'] . '%\'
	order by name';
	$out = '';
	$sep = '';
	$data = odbc_exec($conn, $query);
	while(odbc_fetch_row($data)) {
			$out .= $sep . '"' . odbc_result($data, 1) . '"';
			$sep = ', ';
	};
	odbc_close($conn);
	print('[' . $out . ']');
};
?>