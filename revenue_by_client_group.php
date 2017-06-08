<?php
/** 
 * WHMCS Report for Revenue Breakdown by Client Group
 * 
 * @author     Márcio Dias <marcio.dias@abale.com.br>
 * @link       https://abale.com.br
 */

if (!defined("WHMCS")){

	die("This file cannot be accessed directly");

}else{
	
	$reportdata['title'] = 'Revenue by Client Group for '.$year;
	$reportdata['description'] = 'This report shows the breakdown of revenue by Client Group in a given year and Avg Ticket by Clients/Month.';
	$reportdata['yearspagination'] = true;
	$reportdata["currencyselections"] = true;

	$reportdata['tableheadings'] = array(
	"Group", 
	"Total Clients", 
	"Avg. Customer Ticket", 
	"Avg. Customer Ticket per Month", 
	"Total Amount In"
	);

	$query = " SELECT Grupo.groupname, SUM(Invoices.total) as revenue, COUNT(DISTINCT Clients.id) as total_clientes FROM tblinvoices Invoices "

	. " LEFT JOIN tblclients Clients ON Invoices.userid = Clients.id "
	. " LEFT JOIN tblclientgroups Grupo ON Clients.groupid = Grupo.id "

	. " WHERE Invoices.status = 'Paid' "
	. " AND Clients.currency='".(int)$currencyid."' "
	. " AND Invoices.datepaid >= '".$currentyear."-01-01 00:00:00' "
	. " AND Invoices.datepaid <= '".$currentyear."-12-31 23:59:59' "
	. " GROUP BY Grupo.groupname "
	. " ORDER BY revenue DESC";

	$result = full_query($query);
	$total_rev = 0.00;
	
	while($data = mysql_fetch_row($result)) {
		
		if(!$data['0']){
			$data['0'] = "<i>None</i>";
		}
		
		$reportdata['tablevalues'][] = array(
		0 => $data['0'],
		1 => $data['2'],
		2 => formatCurrency($data['1'] / $data['2']), 
		3 => formatCurrency($data['1'] / 12), 
		4 => formatCurrency($data['1'])
		);
		
		$chartdata['rows'][] = array('c'=>array(array('v'=> strip_tags($data['0']." (".$data['2']." ".ngettext("client", "clients", $data['2']).")") ),array('v'=>($data['1']),'f'=>formatCurrency($data['1']))));
	
		$total_rev = bcadd($total_rev, $data[1], 2);
	
	}
	
	$reportdata['footertext'] = '<p align="center"><b>Total Revenue: ' . formatCurrency($total_rev) . '</b></p>';
	
	$chartdata['cols'][] = array('label'=>'Group','type'=>'string');
	$chartdata['cols'][] = array('label'=>'Income','type'=>'number');
	
	$args = array();
	$args['legendpos'] = 'right';
	
	$reportdata["footertext"] .= $chart->drawChart('Pie',$chartdata,$args,'400px');

}
?>