<?php
/**
 * Created by Sublime Text 3.
 * User: user
 * Date: 2021-01-27
 * Time: 13:23
 */

$no_stats = true;
$admin = true;
require $_SERVER['DOCUMENT_ROOT']."/inc/init.php";

if (isset($_POST['logout']) || !$_SESSION['user']) {
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
<style>
	body {
		max-width: 80vw; /* give the graphs some breathing room */
	}
</style>
<script src="https://cdn.jsdelivr.net/npm/chart.js@2.8.0"></script>
<h1><a href='/bible'>Stats</a></h1>
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
//
// page views radar chart
//
$page_views = $redis_client->hgetall("rcv.ramseyer.dev/page-views");
foreach($page_views as $k => &$view) {
	if (strpos($k, "bible") !== 0 || strlen($k) <= 5) // starts with '/bible' and isn't just '/bible'
		unset($page_views[ $k ]);
}
unset($view);

arsort($page_views); // sort by value high -> low
//$page_views = array_slice($page_views, 0, 50, true); // pull top 50 from list
ksort($page_views, SORT_NATURAL); // sort by key low -> high
?>
<h2>Individual Page Views</h2>
<canvas id='page-hits'></canvas>
<script>
new Chart(document.getElementById('page-hits').getContext('2d'), {
	type: 'radar',
	data: {
        labels: <?= json_encode(array_keys($page_views)) ?>,
        datasets: [{
            borderColor: 'rgb(81, 192, 191)',
            data: <?= json_encode(array_values($page_views)) ?>
        }]
    },
    options: {
    	legend: { display: false },
    	scale: {
    		gridLines: { color: 'rgb(74,74,74)' },
    		ticks: { min: 0 }
    	}
    }
});
</script>
<?php
//
// line charts
//
$views = [ ];

// monthly views
$raw_views = $redis_client->keys("rcv.ramseyer.dev/stats/monthly-views/*");
natsort($raw_views);
foreach($raw_views as $key) {
	$date = date_create_from_format("Y-m", str_replace("rcv.ramseyer.dev/stats/monthly-views/", "", $key));
	if ($begin_date < $date && $date < $end_date)
		$views['monthly-views'][ $date->format("M Y") ] = $redis_client->get($key);
}

// weekly views
$raw_views = $redis_client->keys("rcv.ramseyer.dev/stats/weekly-views/*");
natsort($raw_views);
foreach($raw_views as $key) {
	list($y, $_, $w) = explode('-', str_replace("rcv.ramseyer.dev/stats/weekly-views/", "", $key));
	$date = new DateTime();
	$date->setISODate($y,$w);
	if ($begin_date < $date && $date < $end_date)
		$views['weekly-views'][ "Week of ".$date->format("M j, Y") ] = $redis_client->get($key);
}

// daily views
$raw_views = $redis_client->keys("rcv.ramseyer.dev/stats/daily-views/*");
natsort($raw_views);
foreach($raw_views as $key) {
	$date = date_create_from_format("Y-m-d", str_replace("rcv.ramseyer.dev/stats/daily-views/", "", $key));
	if ($begin_date < $date && $date < $end_date)
		$views['daily-views'][ $date->format("M j, Y") ] = $redis_client->get($key);
}

// monthly unique
$raw_visitors = $redis_client->keys("rcv.ramseyer.dev/stats/monthly-unique/*");
natsort($raw_visitors);
foreach($raw_visitors as $key) {
	$date = date_create_from_format("Y-m", str_replace("rcv.ramseyer.dev/stats/monthly-unique/", "", $key));
	if ($begin_date < $date && $date < $end_date)
		$views['monthly-unique'][ $date->format("M Y") ] = count($redis_client->hkeys($key));
}

// weekly views
$raw_visitors = $redis_client->keys("rcv.ramseyer.dev/stats/weekly-unique/*");
natsort($raw_visitors);
foreach($raw_visitors as $key) {
	list($y, $_, $w) = explode('-', str_replace("rcv.ramseyer.dev/stats/weekly-unique/", "", $key));
	$date = new DateTime();
	$date->setISODate($y,$w);
	if ($begin_date < $date && $date < $end_date)
		$views['weekly-unique'][ "Week of ".$date->format("M j, Y") ] = count($redis_client->hkeys($key));
}

// daily views
$raw_visitors = $redis_client->keys("rcv.ramseyer.dev/stats/daily-unique/*");
natsort($raw_visitors);
foreach($raw_visitors as $key) {
	$date = date_create_from_format("Y-m-d", str_replace("rcv.ramseyer.dev/stats/daily-unique/", "", $key));
	if ($begin_date < $date && $date < $end_date)
		$views['daily-unique'][ $date->format("M j, Y") ] = count($redis_client->hkeys($key));
}

?>
<h2>Total Views</h2>
<h6>Monthly</h6>
<canvas id='monthly-views'></canvas>
<h6>Weekly</h6>
<canvas id='weekly-views'></canvas>
<h6>Daily</h6>
<canvas id='daily-views'></canvas>

<h2>Unique Visitors</h2>
<h6>Monthly</h6>
<canvas id='monthly-unique'></canvas>
<h6>Weekly</h6>
<canvas id='weekly-unique'></canvas>
<h6>Daily</h6>
<canvas id='daily-unique'></canvas>

<form method='post'>
	<button type='submit' name='logout'>Logout</button>
</form>
<script>
const options = {
	legend: { display: false },
    scales: {
        yAxes: [{
            ticks: {
            	callback: value => value.toLocaleString(),
            	min: 0
            },
        	gridLines: { color: 'rgb(74,74,74)' }
        }]
    }
};
<?php foreach($views as $type => $data): ?>
new Chart(document.getElementById('<?= $type ?>').getContext('2d'), {
    type: 'line',
    data: {
        labels: <?= json_encode(array_keys($data)) ?>,
        datasets: [{
            borderColor: 'rgb(81, 192, 191)',
            data: <?= json_encode(array_values($data)) ?>
        }]
    },
    options
});
<?php endforeach; ?>
</script>
<?php
echo "<hr>".nav_line();
require $_SERVER['DOCUMENT_ROOT']."/inc/foot.php";
