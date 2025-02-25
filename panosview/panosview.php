<?php
error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);

require_once 'vendor/autoload.php';

include './ChromePhp.php';

$uri = "http://<ip>/panosview/panos_xml_feed.xml";
$response = \Httpful\Request::get($uri)->withoutStrictSSL()->send();

echo $response . "\n";
ChromePhp::log('Hello console!');
?>

