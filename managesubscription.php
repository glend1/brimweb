<?PHP 
$title = 'My Subscriptions';
require_once 'includes/header.php';
if (!isset($_SESSION['id'])) {
	$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
$querySubscriptions = 'select subscriptiontypefk, subscriptionfk, id
from subscriptions
where userfk = ' . $_SESSION['id'];
$dataSubscriptions = odbc_exec($conn, $querySubscriptions);
$querySubscriptionsType = 'select distinct subscriptiontypefk, name
from subscriptions
join subscriptiontype on subscriptiontypefk = subscriptiontype.id
where userfk = ' . $_SESSION['id'];
$dataSubscriptionsType = odbc_exec($conn, $querySubscriptionsType);
while (odbc_fetch_row($dataSubscriptionsType)) {
	$subscriptionLookup[odbc_result($dataSubscriptionsType, 1)] = odbc_result($dataSubscriptionsType, 2);
};
while (odbc_fetch_row($dataSubscriptions)) {
	if (!isset($aStdOut[odbc_result($dataSubscriptions, 1)])) {
		$aStdOut[odbc_result($dataSubscriptions, 1)] = '';
	};
	switch (odbc_result($dataSubscriptions, 1)) {
		case 1:
			$aStdOut[odbc_result($dataSubscriptions, 1)] .= '<li><a href="viewwmo.php?id=' . odbc_result($dataSubscriptions, 2) . '">WMO #' . odbc_result($dataSubscriptions, 2) . '</a> <a data-text="Delete" href="includes/changesubscription.php?delete=' . odbc_result($dataSubscriptions, 3) . '"><span class="icon-trash icon-hover-hint"></span></a></li>';
			break;
		default:
			$aStdOut[odbc_result($dataSubscriptions, 1)] .= '<li>#' . odbc_result($dataSubscriptions, 2) . '<a data-text="Delete" href="includes/changesubscription.php?delete=' . odbc_result($dataSubscriptions, 3) . '"><span class="icon-trash icon-hover-hint"></span></a></li>';
			break;
	};
};
if (isset($aStdOut)) {
	foreach ($aStdOut as $type => $list) {
		$stdOut .= '<h3>' . $subscriptionLookup[$type] . '</h3><ul>' . $list . '</ul>';
	};
};
require_once 'includes/footer.php'; ?>