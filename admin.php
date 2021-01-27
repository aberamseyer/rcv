<?php

$no_stats = true;
$admin = true;
require $_SERVER['DOCUMENT_ROOT']."/inc/init.php";

if (isset($_POST['logout'])) {
	unset($_SESSION['user']);
	redirect("/login");
}

session_write_close();

$title = "Admin";
$meta_description = "Admin portal";
$meta_canonical = "https://rcv.ramseyer.dev/admin";
require $_SERVER['DOCUMENT_ROOT']."/inc/head.php";

$begin_date = new DateTime("2000-01-01");
$end_date = new DateTime("3000-01-01");
if ($_GET['start_date'])
	$begin_date = new DateTime($_GET['start_date']) ?: $begin_date;
if ($_GET['end_date'])
	$end_date = new DateTime($_GET['end_date']) ?: $end_date;

?>
<h1><a href='/bible'>Admin</a></h1>
<form method="get">
	<p>
		Date Range: &nbsp;
		<input type='date' name='start_date'> through <input type="date" name='end_date'>
		<br>
		<button type="reset">Reset</button>
		<button type="submit">Filter</button>
	</p>
</form>
<?php
$views = [ ];

echo "<h6>Monthly Views</h6>";
$raw_views = $redis_client->KEYS("rcv.ramseyer.dev/stats/monthly-views/*");
natsort($raw_views);
foreach($raw_views as $key) {
	$date = date_create_from_format("Y-m", str_replace("rcv.ramseyer.dev/stats/monthly-views/", "", $key));
	if ($begin_date < $date && $date < $end_date)
		$views['monthly'][ $date->format("M Y") ] = $redis_client->get($key);
}
echo "<canvas id='monthly-views'></canvas>";

echo "<h6>Weekly Views</h6>";
$raw_views = $redis_client->KEYS("rcv.ramseyer.dev/stats/weekly-views/*");
natsort($raw_views);
foreach($raw_views as $key) {
	list($y, $_, $w) = explode('-', str_replace("rcv.ramseyer.dev/stats/weekly-views/", "", $key));
	$date = new DateTime();
	$date->setISODate($y,$w);
	if ($begin_date < $date && $date < $end_date)
		$views['weekly'][ "Week of ".$date->format("M j, Y") ] = $redis_client->get($key);
}
echo "<canvas id='weekly-views'></canvas>";

echo "<h6>Daily Views</h6>";
$raw_views = $redis_client->KEYS("rcv.ramseyer.dev/stats/daily-views/*");
natsort($raw_views);
foreach($raw_views as $key) {
	$date = date_create_from_format("Y-m-d", str_replace("rcv.ramseyer.dev/stats/daily-views/", "", $key));
	if ($begin_date < $date && $date < $end_date)
		$views['daily'][ $date->format("M j, Y") ] = $redis_client->get($key);
}
echo "<canvas id='daily-views'></canvas>";

?>
<form method='post'>
	<button type='submit' name='logout'>Logout</button>
</form>
<script src="https://cdn.jsdelivr.net/npm/chart.js@2.8.0"></script>
<script>
<?php foreach($views as $type => $data): ?>
var monthlyCtx = document.getElementById('<?= $type ?>-views').getContext('2d');
var chart = new Chart(monthlyCtx, {
    type: 'line',
    data: {
        labels: <?= json_encode(array_keys($data)) ?>,
        datasets: [{
            borderColor: 'rgb(81, 192, 191)',

            data: <?= json_encode(array_values($data)) ?>
        }]
    },
    options: {
    	legend: {
    		display: false
    	},
        scales: {
            yAxes: [{
                ticks: {
                    callback: value => value.toLocaleString()
                },
	        	gridLines: {
	        		color: 'rgb(74,74,74)'
	        	}
            }]
        }
    }
});
<?php endforeach; ?>
</script>
<?php
require $_SERVER['DOCUMENT_ROOT']."/inc/foot.php";