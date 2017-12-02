<?php

if($data_ini == $data_fin) {
	$datas = "LIKE '".$data_ini."%'";
}

$data1 = $data_ini;
$data2 = $data_fin;

$unix_data1 = strtotime($data1);
$unix_data2 = strtotime($data2);

$interval = ($unix_data2 - $unix_data1) / 86400;
$datas = "BETWEEN '".$data_ini." 00:00:00' AND '".$data_fin." 23:59:59'";
$arr_months = array();

if($interval <= "31") {
	
	$queryd = "
	SELECT DISTINCT   DATE_FORMAT(date, '%b-%d') AS day_l,  COUNT(id) AS nb, DATE_FORMAT(date, '%Y-%m-%d') AS day
	FROM glpi_tickets
	WHERE glpi_tickets.is_deleted = '0'
	AND date ".$datas."
	GROUP BY day
	ORDER BY day ";

	$resultd = $DB->query($queryd) or die('erro');

	$arr_days = array();
	
	while ($row_result = $DB->fetch_assoc($resultd))
	{
		$v_row_result = $row_result['day'];
		$arr_days[$v_row_result] = 0;		
	}

	$days = array_keys($arr_days) ;
	$quantd = array_values($arr_days) ;
}

else {
	
	$queryd = "
	SELECT DISTINCT DATE_FORMAT(date, '%b-%Y') AS day_l,  COUNT(id) AS nb, DATE_FORMAT(date, '%Y-%m') AS day
	FROM glpi_tickets
	WHERE glpi_tickets.is_deleted = '0'
	AND date ".$datas."
	GROUP BY day
	ORDER BY day ";

	$resultd = $DB->query($queryd) or die('erro');
	
	while ($row_result = $DB->fetch_assoc($resultd))
	{
		$v_row_result = $row_result['day'];
		$arr_months[$v_row_result] = 0;		
	}

	$months = array_keys($arr_months) ;
	$monthsq = array_values($arr_months) ;
}

//chamados mensais
$arr_grfm = array();
$arr_opened = array();

if($interval >= "31") {

		$DB->data_seek($resultd, 0);
		while ($row_result = $DB->fetch_assoc($resultd))
		{
			$querym = "
			SELECT DISTINCT DATE_FORMAT(glpi_tickets.date, '%b-%Y') as day_l,  COUNT(glpi_tickets.id) as nb, DATE_FORMAT(glpi_tickets.date, '%Y-%m') as day
			FROM glpi_tickets, glpi_groups_tickets
			WHERE glpi_tickets.is_deleted = '0'
			AND DATE_FORMAT(glpi_tickets.date, '%Y-%m' ) = '".$row_result['day']."'
			AND glpi_tickets.id = glpi_groups_tickets.tickets_id
			AND glpi_groups_tickets.groups_id = ".$id_grp."
			GROUP BY day
			ORDER BY day ";

			$resultm = $DB->query($querym) or die('erro m');
			$row_result2 = $DB->fetch_assoc($resultm);

			$v_row_result = $row_result['day'];
			if($row_result2['nb'] != '') {
				$arr_grfm[$v_row_result] = $row_result2['nb'];
			}
			else {
				$arr_grfm[$v_row_result] = 0;
			}
		}

		$arr_opened = $arr_grfm;
}

else {
		$DB->data_seek($resultd, 0);
		while ($row_result = $DB->fetch_assoc($resultd))
		{
			$querym = "
			SELECT DISTINCT DATE_FORMAT(glpi_tickets.date, '%b-%d') as day_l,  COUNT(glpi_tickets.id) as nb, DATE_FORMAT(glpi_tickets.date, '%Y-%m-%d') as day
			FROM glpi_tickets, glpi_groups_tickets
			WHERE glpi_tickets.is_deleted = '0'
			AND DATE_FORMAT(glpi_tickets.date, '%Y-%m-%d' ) = '".$row_result['day']."'
			AND glpi_tickets.id = glpi_groups_tickets.tickets_id
			AND glpi_groups_tickets.groups_id = ".$id_grp."

			GROUP BY day
			ORDER BY day ";

			$resultm = $DB->query($querym) or die('erro m');
			$row_result2 = $DB->fetch_assoc($resultm);

			$v_row_result = $row_result['day'];
			if($row_result2['nb'] != '') {
				$arr_grfm[$v_row_result] = $row_result2['nb'];
			}
			else {
				$arr_grfm[$v_row_result] = 0;
			}
		}

		$arr_opened = $arr_grfm;
}


/*$resultm = $DB->query($querym) or die('errol');
$contador = $DB->numrows($resultm);
$arr_grfm = array();

while ($row_result = $DB->fetch_assoc($resultm)){
	$v_row_result = $row_result['day_l'];
	$arr_grfm[$v_row_result] = $row_result['nb'];
}
*/

$grfm = array_keys($arr_opened) ;
$grfm3 = json_encode($grfm);

$quantm = array_values($arr_opened) ;
$quantm2 = implode(',',$quantm);

$opened = array_sum($quantm);

// closed
$status = "('5','6')";
$arr_grff = array();

// fechados mensais
if($interval >= "31") {

	// fechados mensais
	$queryf = "
	SELECT DISTINCT DATE_FORMAT( glpi_tickets.closedate, '%b-%Y' ) AS day_l, DATE_FORMAT( glpi_tickets.closedate, '%Y-%m' ) AS day, COUNT(glpi_tickets.id) AS nb
	FROM glpi_tickets, glpi_groups_tickets
	WHERE glpi_tickets.closedate ".$datas."
	AND glpi_tickets.status = 6	
	AND glpi_tickets.id = glpi_groups_tickets.tickets_id
	AND glpi_groups_tickets.groups_id = ".$id_grp."
	GROUP BY day
	ORDER BY day";
	
	$resultf = $DB->query($queryf) or die('erro f');
	
	while ($row_result = $DB->fetch_assoc($resultf)) {
	
		$v_row_result = $row_result['day'];
		if($row_result['nb'] != '') {
			$arr_grff[$v_row_result] = $row_result['nb'];
		}
		else {
			$arr_grff[$v_row_result] = 0;
		}
	}
	
	$arr_closed = array_unique(array_merge($arr_months,$arr_grff));

 }

else {

		$DB->data_seek($resultd, 0);
		while ($row_result = $DB->fetch_assoc($resultd))
		{
			$queryf = "
			SELECT DISTINCT DATE_FORMAT(glpi_tickets.closedate, '%b-%d') as day_l,  COUNT(glpi_tickets.id) as nb, DATE_FORMAT(glpi_tickets.closedate, '%Y-%m-%d') as day
			FROM glpi_tickets, glpi_groups_tickets
			WHERE glpi_tickets.is_deleted = '0'
			AND DATE_FORMAT(glpi_tickets.closedate, '%Y-%m-%d' ) = '".$row_result['day']."'
			AND glpi_tickets.id = glpi_groups_tickets.tickets_id
			AND glpi_groups_tickets.groups_id = ".$id_grp."

			GROUP BY day
			ORDER BY day ";

			$resultf = $DB->query($queryf) or die('erro f');
			$row_result2 = $DB->fetch_assoc($resultf);

			$v_row_result = $row_result['day'];
			
			if($row_result2['nb'] != '') {
				$arr_grff[$v_row_result] = $row_result2['nb'];
			}
			else {
				$arr_grff[$v_row_result] = 0;
			}
		}
		$arr_closed = $arr_grff;
}


$grff = array_keys($arr_closed) ;
$grff3 = json_encode($grff);

$quantf = array_values($arr_closed) ;
$quantf2 = implode(',',$quantf);

$closed = array_sum($quantf);

/*var_dump($arr_months);
var_dump($arr_grfm);
var_dump($arr_grff);
var_dump($arr_opened);
var_dump($arr_closed);*/

echo "
<script type='text/javascript'>
$(function () {

        $('#graf_linhas').highcharts({
            chart: {
	       		type: 'column',
           		height: 460

            },
            title: {
                text: '".__('Tickets','dashboard')."'
            },
            legend: {
                layout: 'horizontal',
                align: 'center',
                verticalAlign: 'bottom',
                x: 0,
                y: 0,
                //floating: true,
                borderWidth: 0,
                //backgroundColor: '#FFFFFF',
                adjustChartSize: true
            },
            xAxis: {
                categories: $grfm3,
                labels: {
                    rotation: -55,
                    align: 'right',
                    style: {
                        fontSize: '11px',
                        fontFamily: 'Verdana, sans-serif'
                    }
                }

            },
          		 
     		yAxis: {
	 						minPadding: 0,
   	 					maxPadding: 0,
    						min: 0,
    						//max:1,
   						showLastLabel:false,
    						//tickInterval:1,

                title: { // Primary yAxis
                    text: '".__('Tickets','dashboard')."'
                }
             },  
      
				plotOptions: {
                column: {
                    fillOpacity: 0.5,
                    borderWidth: 1,
                	  borderColor: 'white',
                	  shadow:true,
                    dataLabels: {
	                 	enabled: true
	                 },
                },
            },

            tooltip: {
                shared: true
            },
            credits: {
                enabled: false
            },

        series: [
          		 {
                name: '".__('Opened','dashboard')." (".$opened.")',

                 dataLabels: {
                    enabled: true,
    
                    },
                data: [$quantm2]
                },

                {
                name: '".__('Closed','dashboard')." (".$closed.")',
                dataLabels: {
                    enabled: true,
                    //color: '#000'
                    },
                data: [$quantf2]
                },
                ]
        });
    });
  </script>
";

?>
