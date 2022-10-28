<?PHP 
$title = 'Full Batch Data';
require_once 'includes/header.php';
if (!fCanSeePublic(@$_SESSION['permissions']['page'][4] >= 100)) {
	$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
$aDE = fPermissionDE();
if (!isset($_GET['batch']) && !isset($_GET['dbname'])) {
	$_SESSION['sqlMessage'] = 'Select a Batch!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
$bConn = odbc_connect('DRIVER={SQL Server};Server=INSQL2;Database=' . $_GET['dbname'] . ';', $dbUsername, $dbPassword);
$queryBatch = 'select dbname,datetime,unitorconnection,unitprocedure_id,operation_id,phase_id,material_parameter,phase_label,doneby_user_id,parameter_id,checkby_user_id,old_target_value,new_target_value,unitofmeasure,transition_id,transition_label,seqnumex,expression_text,transition_desc,question,answer,material_id,material_name,mtrl_campaign_id,mtrl_lot_id,mtrl_batch_id,actual_qty,target_qty,seqnumin,instruction,actual_value,target_value,action_cd,description
from (select \'detail\' as dbname, Batch_Log_ID, DateTime, cast(UnitOrConnection as varchar) as unitorconnection, 
cast(UnitProcedure_ID as varchar) as unitprocedure_id, cast(Operation_ID as varchar) as operation_id, 
cast(Phase_ID as varchar) as phase_id, cast(Phase_Instance_ID as varchar) as Phase_Instance_ID,
null as Material_Parameter, cast(Phase_Label as varchar) as Phase_Label, cast(DoneBy_User_ID as varchar) as DoneBy_User_ID, null as Parameter_ID,
cast(CheckBy_User_ID as varchar) as CheckBy_User_ID, null as Old_Target_Value, null as New_Target_Value, null as UnitOfMeasure, 
null as Transition_ID, null as Transition_Instance_ID, null as Transition_Label, null as SeqNumEx, null as Expression_Text, null as Transition_Desc, null as Question, null as answer, null as material_id, null as material_instance_id, null as material_name, null as Mtrl_campaign_id, null as Mtrl_lot_id, null as Mtrl_batch_id, null as actual_qty, 
null as target_qty, null as seqnumIn, null as instruction, null as actual_value, null as target_value, cast(Action_CD as varchar) as action_cd
from BatchDetail
where Batch_Log_ID = \'' . $_GET['batch'] . '\'
union
select \'question\', batch_log_id, datetime, null, null, null, null, null, null, null,
CAST(doneby_user_id as varchar), null, CAST(checkby_User_id as varchar), null, null,
null, null, null, null, null, null, null,
cast(question as varchar), CAST(answer as varchar), null, null, null, null, null, 
null, null, null, null, null, null, null, null
from BatchQuestion
where Batch_Log_ID = \'' . $_GET['batch'] . '\'
union
select \'material\', Batch_Log_ID, DateTime, cast(UnitOrConnection as varchar), 
cast(UnitProcedure_ID as varchar), cast(Operation_ID as varchar), 
cast(Phase_ID as varchar), cast(Phase_Instance_ID as varchar), 
cast(Material_Parameter as varchar), cast(Phase_Label as varchar), null, null, null,
null, null, cast(UnitOfMeasure as varchar), null,
null, null, null, null, null, null, null, cast(Material_ID as varchar), 
cast(Material_Instance_ID as varchar), cast(Material_Name as varchar), 
cast(Mtrl_Campaign_ID as varchar), cast(Mtrl_Lot_ID as varchar), 
cast(Mtrl_Batch_ID as varchar), cast(Actual_Qty as varchar), 
cast(Target_Qty as varchar), null, null, null, null, null
from MaterialInput
where Batch_Log_ID = \'' . $_GET['batch'] . '\'
union
select \'phase\', Batch_Log_ID, DateTime, cast(UnitOrConnection as varchar), 
cast(UnitProcedure_ID as varchar), cast(Operation_ID as varchar), 
cast(Phase_ID as varchar), cast(Phase_Instance_ID as varchar), null,
CAST(phase_label as varchar), null, null, null, null, null, null, null, null, null, null, null, null, null,
null, null, null, null, null, null, null, null, null, cast(SeqNum as varchar),
cast(Instruction as varchar), null, null, null
from PhaseInstruction
where Batch_Log_ID = \'' . $_GET['batch'] . '\'
union
select \'processvariable\', Batch_Log_ID, DateTime, cast(UnitOrConnection as varchar), 
cast(UnitProcedure_ID as varchar), cast(Operation_ID as varchar), 
cast(Phase_ID as varchar), cast(Phase_Instance_ID as varchar), null,
cast(Phase_Label as varchar), null, cast(Parameter_ID as varchar), null, null, null,
cast(UnitOfMeasure as varchar), null, null, null,
null, null, null, null, null, null, null, null, null, null, null, null, null, null,
null, cast(Actual_Value as varchar), cast(Target_Value as varchar), null
from ProcessVar
where Batch_Log_ID = \'' . $_GET['batch'] . '\'
union
select \'processvariablechange\', Batch_Log_ID, DateTime, cast(UnitOrConnection as varchar), 
cast(UnitProcedure_ID as varchar), cast(Operation_ID as varchar), cast(Phase_ID as varchar), 
cast(Phase_Instance_ID as varchar), null, cast(Phase_Label as varchar), 
cast(DoneBy_User_ID as varchar), cast(Parameter_ID as varchar), 
cast(CheckBy_User_ID as varchar), cast(Old_Target_Value as varchar), 
cast(New_Target_Value as varchar), cast(UnitOfMeasure as varchar), null, null, null, null, null, null, null, null, null, null, null, null, null, null,
null, null, null, null, null, null, null
from ProcessVarChange
where Batch_Log_ID = \'' . $_GET['batch'] . '\'
union
select \'transition\', Batch_Log_ID, DateTime, null, cast(UnitProcedure_ID  as varchar), 
cast(Operation_ID as varchar), null, null, null, null, null, null, null, null, null,
null, cast(Transition_ID as varchar), 
cast(Transition_Instance_ID as varchar), cast(Transition_Label as varchar), null, null,
cast(Transition_Desc as varchar), null, null, null, null, null, null, null, null, null,
null, null, null, null, null, null
from Transition
where Batch_Log_ID = \'' . $_GET['batch'] . '\'
union
select \'transitionexpression\', Batch_Log_ID, DateTime, null, cast(UnitProcedure_ID as varchar),
cast(Operation_ID as varchar), null, null, null, null, null, null, null, null, null,
null, cast(Transition_ID as varchar), 
cast(Transition_Instance_ID as varchar), cast(Transition_Label as varchar), 
cast(SeqNum as varchar), cast(Expression_Text as varchar), null, null, null, null,
null, null, null, null, null, null, null, null, null, null, null, null
from TransitionExpression
where Batch_Log_ID = \'' . $_GET['batch'] . '\'
union
select \'materialinputchange\', Batch_Log_ID, DateTime, 
cast(UnitOrConnection as varchar), cast(UnitProcedure_ID as varchar), 
cast(Operation_ID as varchar), cast(Phase_ID as varchar), 
cast(Phase_Instance_ID as varchar), cast(Material_Parameter as varchar), 
cast(Phase_Label as varchar), cast(DoneBy_User_ID as varchar), null, 
cast(CheckBy_User_ID as varchar), cast(Old_Target_Qty as varchar), 
cast(New_Target_Qty as varchar), null, null, null, null, null, null, null, 
null, null, cast(Material_ID as varchar), null, null, null, null, null, null, null, 
null, null, null, null, null
from MaterialInputChange
where Batch_Log_ID = \'' . $_GET['batch'] . '\'
union
select \'material output\', Batch_Log_ID, DateTime, cast(UnitOrConnection as varchar),
cast(UnitProcedure_ID as varchar), cast(Operation_ID as varchar),
cast(Phase_ID as varchar), cast(Phase_Instance_ID as varchar),
cast(Material_Parameter as varchar), cast(Phase_Label as varchar), null, null, null, 
null, null, cast(UnitOfMeasure as varchar), null, null, null, null, null, null,
null, null, cast(Material_ID as varchar), null, cast(Material_Name as varchar), 
null, null, null, cast(Actual_Qty as varchar), cast(Target_Qty as varchar), null, 
null, null, null, null
from MaterialOutput
where Batch_Log_ID = \'' . $_GET['batch'] . '\') as batchrealdetail
left join codetable on action_cd = code
order by DateTime';
$dataBatch = odbc_exec($bConn, $queryBatch);
$cols = odbc_num_fields($dataBatch);
$stdOut .= '<h3>Records <a data-text="Table Headers" href="none.php" class="table-row-button" data-id="batch"><span class="icon-list icon-hover-hint"></span></a></h3><table id="batch-full" class="records"><thead>';
for ($i = 1; $i <= $cols; $i++) {
	$stdOut .= '<th>' . odbc_field_name($dataBatch, $i) . '</th>';
};
$stdOut .= '</thead><tbody>';
while(odbc_fetch_row($dataBatch)) {
	$stdOut .=  '<tr>';
	for ($i = 1; $i <= $cols; $i++) {
		$stdOut .= '<td>' . odbc_result($dataBatch, $i) . '</td>';
	};
	$stdOut .=  '</tr>';
};
$stdOut .= '</tbody>' . fTableFooter(['id' => 'batch-full', 'cols' => 34]) . '</table><div id="batch-table-rows" class="tablesorter-row-hider"></div>
<script type="text/javascript">

	$(function() {
	
		fTableSorter({sorttable: "#batch-full", 
			sortorder: [[2,0]],
			rowheaders: "batch",
			headers: {
				0 : { columnSelector: false},
				6 : { columnSelector: false},
				7 : { columnSelector: false},
				8 : { columnSelector: false},
				9 : { columnSelector: false},
				10 : { columnSelector: false},
				11 : { columnSelector: false},
				12 : { columnSelector: false},
				13 : { columnSelector: false},
				14 : { columnSelector: false},
				15 : { columnSelector: false},
				16 : { columnSelector: false},
				17 : { columnSelector: false},
				18 : { columnSelector: false},
				19 : { columnSelector: false},
				20 : { columnSelector: false},
				21 : { columnSelector: false},
				22 : { columnSelector: false},
				23 : { columnSelector: false},
				24 : { columnSelector: false},
				25 : { columnSelector: false},
				26 : { columnSelector: false},
				27 : { columnSelector: false},
				28 : { columnSelector: false},
				29 : { columnSelector: false},
				30 : { columnSelector: false},
				31 : { columnSelector: false},
				32 : { columnSelector: false}
			}
		});
	});
</script>';
odbc_close($bConn);
require_once 'includes/footer.php'; ?>