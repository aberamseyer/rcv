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

$begin_date = new DateTime("2000-01-01");
$end_date = new DateTime("3000-01-01");
if ($_GET['start_date'])
	$begin_date = new DateTime($_GET['start_date']) ?: $begin_date;
if ($_GET['end_date'])
	$end_date = new DateTime($_GET['end_date']) ?: $end_date;

// ajax get data
if (isset($_GET['get_views'])) {
	print_json([
		'views' => $redis_client->get("rcv.ramseyer.dev/stats/daily-views/".date('Y-m-d'))
	]);
}

if (isset($_GET['individual_views'])) {
	$page_views = $redis_client->hgetall("rcv.ramseyer.dev/page-views");
	arsort($page_views); // sort by value high -> low
	$total_page_views = array_sum($page_views);

	foreach($page_views as $url => $value): ?>
		<tr>
			<td><?= str_replace('bible-', '', str_replace('/', '-', $url)) ?: "&nbsp;" ?></td>
			<td><?= number_format($value) ?></td>
			<td><?= number_format($value / $total_page_views * 100, 2) ?>%</td>
		</tr>
	<?php endforeach;
	die;
}

if (isset($_GET['total_views'])) {
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
	print_json($views);
}

if (isset($_GET['unique_visitors'])) {
	$views = [ ];
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

	print_json($views);
}

if (isset($_GET['map'])) {
	$visitors_today = $redis_client->hgetall("rcv.ramseyer.dev/stats/daily-unique/".date('Y-m-d'));
	arsort($visitors_today); // sort by visits descending
	$ips = array_keys($visitors_today);

	// fetch from freegeoip.app in parallel
	$curls = [ ];
	$results = [ ];
	$mh = curl_multi_init();
	foreach($ips as $ip) {
		$curls[ $ip ] = curl_init();
		curl_setopt($curls[ $ip ], CURLOPT_URL, "https://freegeoip.app/json/$ip");
		curl_setopt($curls[ $ip ], CURLOPT_HEADER, 0);
		curl_setopt($curls[ $ip ], CURLOPT_RETURNTRANSFER, 1);
		curl_multi_add_handle($mh, $curls[ $ip ]);
	}
	$active = null;
	do {
	   curl_multi_select($mh);
		$mrc = curl_multi_exec($mh, $active);
	} while ($mrc == CURLM_CALL_MULTI_PERFORM);
	
	// this bit here is mainly for php-related awkwardness and bugs
	while ($active && $mrc == CURLM_OK) {
        if (curl_multi_select($mh) != -1) {
            do {
                $mrc = curl_multi_exec($mh, $active);
            } while ($mrc == CURLM_CALL_MULTI_PERFORM);
        }
    }
    
	// get results
    foreach($curls as $ip => $ch) {
    	$results[ $ip ] = @json_decode(curl_multi_getcontent($ch), true);
    	curl_multi_remove_handle($mh, $ch);
    	curl_close($ch);
    }
    curl_multi_close($mh);

    ob_start();
	$locations = [];
	foreach($results as $ip => $info):
		if (!$info['city']) {
			$info = row("SELECT * FROM (
		       SELECT country_name, city_name city, region_name, latitude, longitude, ip_to, ip_from
		       FROM ip2location.ip2location_db11_ipv4
		       WHERE ip_to >= INET_ATON('$ip') LIMIT 1
	       ) AS tmp WHERE ip_from <= INET_ATON('$ip')");
		}

		$hits = number_format($redis_client->hget("rcv.ramseyer.dev/stats/daily-unique/".date('Y-m-d'), $ip));
		$locations[ $ip ] = [ $info['longitude'], $info['latitude'], $hits ]; ?>
		<tr>
			<td><?= $ip ?></td>
			<td><?= $info['country_name'] ?: '&nbsp;' ?></td>
      <td><?= $info['region_name'] ?: '&nbsp;' ?></td>
			<td><?= $info['city'] ?: '&nbsp;' ?></td>
			<td><?= $hits ?></td>
		</tr>
	<?php endforeach;

	print_json([
		'table' => ob_get_clean(),
		'locations' => $locations
	]);
	die;
}

// page contents

$title = "Admin";
$meta_description = "Admin portal";
$meta_canonical = "https://rcv.ramseyer.dev/admin";
require $_SERVER['DOCUMENT_ROOT']."/inc/head.php";

?>
<style>
	img {
		width: 30px;
		height: 30px;
	}
	body {
		max-width: 80vw; /* give the graphs some breathing room */
	}

	table {
	    width: 100%;
	}

	thead, tbody, tr, td, th { display: block; }

	tr:after {
	    content: ' ';
	    display: block;
	    visibility: hidden;
	    clear: both;
	}

	thead th {
	    height: 30px;
	}

	tbody {
	    height: 280px;
	    overflow-y: auto;
	}

	table tbody td, table thead th {
		float: left;
		overflow-x: scroll;
		font-size: 1.4rem;
	}
	table.width-30 tbody td, table.width-30 thead th {
		width: 30%;
	}
	table.width-15 tbody td, table.width-15 thead th {
		width: 15%;
	}

</style>
<script src="https://cdn.jsdelivr.net/npm/chart.js@2.8.0"></script>
<h1><a href='/bible'>Stats</a></h1>
<?php 
//
// page views table
//
$page_views = $redis_client->hgetall("rcv.ramseyer.dev/page-views");
arsort($page_views); // sort by value high -> low
$total_page_views = array_sum($page_views);
?>
<h6>Page Views Today: <span id='number'>0</span></h6>
<noscript>
	You're gonna want js for this page :/
</noscript>
<script>
	const el = document.getElementById('number');
	function updateViews() {
		function animateViews(from, to) {
			el.innerText = from;
			let number = from;
			let interval = setInterval(() => {
		        el.innerText = number;
		        if (number >= to) clearInterval(interval);
		        number += Math.ceil((to - number)/(to / 50));
		    }, 30);
		}
	    fetch(`/admin${window.location.search || `?`}&get_views`)
		    .then(rsp => rsp.json())
		    .then(json => animateViews(parseInt(el.innerText), parseInt(json.views)));
	}
</script>
<h2>Individual Page Views</h2>
<table class='width-30'>
	<thead>
		<tr>
			<th>Location</th>
			<th>Visits</th>
			<th>%</th>
		</tr>
	</thead>
	<tbody id='individual-views-table'>
		<tr><td><img src='/res/img/spin.gif'></td></tr>
	</tbody>
</table>
<script>
	function updateIndividualViews() {
		fetch(`/admin${window.location.search || `?`}&individual_views`)
		.then(rsp => rsp.text())
		.then(text => document.getElementById('individual-views-table').innerHTML = text);
	}
</script>
<?php
//
// line charts
//
?>
<hr style="margin: 5rem 2rem;">
<form method="get">
	<p>
		Date Range: &nbsp;
		<input type='date' name='start_date'> through <input type="date" name='end_date'>
		<button type="reset">Reset</button>
		<button type="submit">Filter</button>
	</p>
</form>
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
function updateTotalViews() {
	fetch(`/admin${window.location.search || `?`}&total_views`)
		.then(rsp => rsp.json())
		.then(json => {
			Object.entries(json).forEach(([ type, data ]) => {
				new Chart(document.getElementById(type).getContext('2d'), {
				    type: 'line',
				    data: {
				        labels: Object.keys(data),
				        datasets: [{
				            borderColor: 'rgb(81, 192, 191)',
				            data: Object.values(data)
				        }]
				    },
				    options
				});
			});
		});
}

function updateUniqueVisitors() {
	fetch(`/admin${window.location.search || `?`}&unique_visitors`)
		.then(rsp => rsp.json())
		.then(json => {
			Object.entries(json).forEach(([ type, data ]) => {
				new Chart(document.getElementById(type).getContext('2d'), {
				    type: 'line',
				    data: {
				        labels: Object.keys(data),
				        datasets: [{
				            borderColor: 'rgb(81, 192, 191)',
				            data: Object.values(data)
				        }]
				    },
				    options
				});
			});
		});
}
</script>
<table class='width-15'>
	<thead>
		<tr>
			<th>IP</th>
			<th>Country</th>
      <th>Region</th>
			<th>City</th>
			<th>Visits</th>
		</tr>
	</thead>
	<tbody id='map-table'>
		<tr><td><img src='/res/img/spin.gif'></td></tr>
	</tbody>
</table>

<!-- mapbox embed -->
<script src="https://api.mapbox.com/mapbox-gl-js/v2.0.1/mapbox-gl.js"></script>
<link href="https://api.mapbox.com/mapbox-gl-js/v2.0.1/mapbox-gl.css" rel="stylesheet" />
<div id='map' style="height: 400px; margin-top: 2rem;"></div>
<script>
	function updateMap() {
		fetch(`/admin${window.location.search || `?`}&map`)
			.then(rsp => rsp.json())
			.then(json => {
				document.getElementById('map-table').innerHTML = json.table;

				mapboxgl.accessToken = 'pk.eyJ1IjoiYWJlcmFtc2V5ZXIiLCJhIjoiY2trbjI3dmY3MGtmZzJ3czMxZGZsM241ZSJ9.0YYBE5Yj5UVae8WPQQ1weQ';
				const map = new mapboxgl.Map({
					container: 'map',
					style: 'mapbox://styles/mapbox/streets-v11',
					zoom: 1
				});
				Object.entries(json.locations)
				.forEach(([ ip, [ long, lat, hits ] ]) => {
						if (parseInt(long) && parseInt(lat)) {
							const label = new mapboxgl.Popup({ closeButton: false })
						      .setText(`${ip} - ${hits} views`)
						      .addTo(map);
							new mapboxgl.Marker()
								.setLngLat([ long, lat ])
								.setPopup(label)
								.addTo(map);
						}
					});
			});
	}
	
</script>
 
<form method='post' style="margin: 5rem 2rem;">
	<button type='submit' name='logout'>Logout</button>
</form>
<script>
	setTimeout(updateViews, 200);
	setTimeout(updateIndividualViews, 400);
	setTimeout(updateTotalViews, 6200);
	setTimeout(updateUniqueVisitors, 800);
	setTimeout(updateMap, 1200);
	setInterval(updateViews, 1000 * 60 * 5);
	setInterval(updateIndividualViews, 1000 * 60 * 5);
	setInterval(updateTotalViews, 1000 * 50 * 5);
	setInterval(updateUniqueVisitors, 1000 * 55 * 5);
	setInterval(updateMap, 1000 * 60 * 10);
</script>
<?php
echo "<hr>".nav_line();
require $_SERVER['DOCUMENT_ROOT']."/inc/foot.php";
