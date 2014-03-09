<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

// Spin up composer stuffs
include 'vendor/autoload.php';

// Spin up Silex
$app = new Silex\Application();

// enable debug
$app['debug'] = true;

// We're wiring up a REST service, so there should be no HTML output, not even for errors.  New error handler.
$app->error(function (\Exception $e, $code) use ($app) {
    $response = array(
        'status'    => 400,
        'message'   => $e->getMessage()
    );
    return $app->json($response);
});

// Wire up the magic!
$app->get('/{type}.svg', function(Request $Request, $type) use ($app) {
	$iWidth = 1000;
	$iHeight = 1000;
	switch($type) {
		case 'line':
			$chart = new ezcGraphLineChart();
			break;
		case 'pie':
			$chart = new ezcGraphPieChart();
			break;
		case 'vbar':
		case 'bar':
			$chart = new ezcGraphBarChart();
			break;
		case 'hbar':
			$chart = new ezcGraphHorizontalBarChart();
			break;
		case 'odometer':
			$chart = new ezcGraphOdometerChart();
			break;
		case 'radar':
			$chart = new ezcGraphRadarChart();
			break;
		default:
			throw new \InvalidArgumentException("{$type} is not a chart type we know how to handle!");
	}
	
	// Let's look at everything on the request
	$datasetArray = array();
	foreach ($Request->query->all() as $key => $value) {
		switch($key) {
			case 'title':
				$chart->title = $value;
				break;
			case 'width':
				$iWidth = $value;
				break;
			case 'height':
				$iHeight = $value;
				break;
			default:
				$datasetArray[$key] = (float)$value;
				break;
		}	
	}
	$chart->data['data'] = new ezcGraphArrayDataSet($datasetArray);

	// Run with the chart
	return new Response($chart->renderToOutput( $iWidth, $iHeight ), 200, array('Content-Type' => 'image/svg+xml'));
});


// And off we go...
$app->run();