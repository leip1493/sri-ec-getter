<?php

$url = "http://ws.cdyne.com/ip2geo/ip2geo.asmx?wsdl";

try {
	$client = new SoapClient($url, [ "trace" => 1 ] );
	$result = $client->ResolveIP( [ "ipAddress" => $argv[1], "licenseKey" => "0" ] );

	print_r($result);
} catch ( SoapFault $e ) {
	echo $e->getMessage();
}

echo PHP_EOL;