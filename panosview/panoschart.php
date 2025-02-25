<?php
error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);
date_default_timezone_set('America/New_York');

//use Ghunti\HighchartsPHP\Highchart;
//use Ghunti\HighchartsPHP\HighchartJsExpr;
use \src\HighchartsPHP\Highchart;
use \src\HighchartsPHP\HighchartsJsExpr;
require_once 'vendor/autoload.php';

//$uri = "/app/panos_last_7_days.xml";
$uri = "http://<ip>:8080/panos_last_7_days.xml";
$response = \Httpful\Request::get($uri)->withoutStrictSSL()->sendIt();

//echo $response;

$panosjson = json_encode(simplexml_load_string($response),JSON_NUMERIC_CHECK);
$panosarray = json_decode($panosjson,TRUE);

$dort = array();
foreach($panosarray["report"]["result"]["entry"] as $w){
	$dort[$w['day-of-receive_time']][] = $w;
}
//print("<pre>".print_r($dort,true)."</pre>");

// Comparison function
function cmp($a, $b) {
	$tmp = explode(',', $a[0]['day-of-receive_time']);
    $a1 = next($tmp);
	$tmp = explode(',', $b[0]['day-of-receive_time']);
    $b1 = next($tmp);
    if ($a1 == $b1) {
        return 0;
    }
    return ($a1 < $b1) ? -1 : 1;
}

uasort($dort, 'cmp');

//Array
//(
//    [Thu, Apr 10, 2014] => Array
//        (
//            [0] => Array
//                (
//                    [day-of-receive_time] => Thu, Apr 10, 2014
//                    [app] => ms-ds-smb
//                    [bytes] => 6716561637
//                )
//
//            [1] => Array
//                (
//                    [day-of-receive_time] => Thu, Apr 10, 2014
//                    [app] => ssl
//                    [bytes] => 1733067419
//                )

//print("<pre>".print_r($dort,true)."</pre>");

$days = array();
$days['name'] = 'Top 10 Application Bandwith Use';
//$days['data'] = array_keys($dort);

//if (is_array($dort)){
//	foreach ($dort as $childIDX => $arrayItem) {
//		if (is_array($arrayItem)) {
//			foreach($arrayItem as $itemIDX => $deleteCandidate) {
//				$arrayItemToDelete = array($dort[$childIDX][$itemIDX]);
//				unset($dort[$childIDX][$itemIDX]['day-of-receive_time']);
				// Re-sequence the lowest-level array key values...
//				$dort[$childIDX] = array_values($dort[$childIDX]);
//				$dort[$childIDX][$itemIDX]['bytes'] = $dort[$childIDX][$itemIDX]['bytes']/1048576;
//			}
//		}
//	}
//}

$apps_unstruct = array_values($dort);
//$apps = array();
$dayCount = 0;
$appCount = 0;

$apps[$dayCount] = array();
$apps[$dayCount]['name'] = 'Dates';
$apps[$dayCount]['data'] = array();

$app_temp = array();

if (is_array($apps_unstruct)){
	foreach ($apps_unstruct as $appIDX => $appArrItem) {	
		if (is_array($appArrItem)) {
			foreach($appArrItem as $itemIDX => $appIdx) {
				$apps[0]['data'][$dayCount] = 
				$apps_unstruct[$appIDX][$itemIDX]['day-of-receive_time'];
				
				$appName = $apps_unstruct[$appIDX][$itemIDX]['app'];
				
				// Is app already known?  If so, create array
				
				if (!in_array($apps_unstruct[$appIDX][$itemIDX]['app'],$app_temp)){
					array_push($app_temp,$appName);
					$appCount++;
					$apps[$appName] = array();
					$apps[$appName]['name'] = $appName;
					$apps[$appName]['data'] = array();
					array_push($apps[$appName]['data'],0,0,0,0,0,0,0,0);
				}
				
				$apps[$appName]['data'][$dayCount] = 
				$apps_unstruct[$appIDX][$itemIDX]['bytes'];
				//$apps_unstruct[$appIDX][$itemIDX]['bytes']/1048576;		
				//print("<pre>megabytes: ".$apps_unstruct[$appIDX][$itemIDX]['bytes']."</pre>");	
			}
		}	
		$dayCount++;

		//print("<pre>".print_r($appArrItem,true)."</pre>");
		//print("end array item<br/>");
	}
}

function removeKeys( array $array )
{
  $array = array_values( $array );
  foreach ( $array as &$value )
  {
    if ( is_array( $value ) )
    {
      $value = removeKeys( $value );
    }
  }
  return $array;
}

$days = array_shift($apps);
$apps = array_values($apps);

$result = array();
$result['title'] = '7-Day Top Bandwidth Consumers';
array_push($result,$days);
array_push($result,$apps);
//print("<pre>".print_r($result,true)."</pre>"); 

print json_encode($result, JSON_NUMERIC_CHECK);
?>
