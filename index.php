<?php require_once 'XmlParser.php' ?>
<?php require_once 'FacturaParser.php' ?>
<?php require_once 'NotaCreditoParser.php' ?>
<?php 

try {
	// 0509201901179206376147120020052050754120054076917	
	$clave_acc = "0509201901179242984600120020052050754120054076917";
	$clave_acc = "0809201901099133185900120230150009498921357246816";
	$clave_acc = "2608201904179037150600120020210000994710009947118";


 // 	$input = readline("Ingrese clave de acceso a consultar: ");

	// if(!$input)
	// 	die("Debe ingresar una clave de acceso");
	// if(strlen($input) !== 49)
	// 	die("Clave de acceso invalida");

	$timeStart = time();
	// $clave_acc = $input;

	$url = "https://cel.sri.gob.ec/comprobantes-electronicos-ws/AutorizacionComprobantesOffline?wsdl";
	
	$client = new SoapClient($url, array(
		"soap_version" => SOAP_1_1,"trace" => 1)
	);

	// Parametros SOAP
	$user_param = array (
	  'claveAccesoComprobante' => $clave_acc
	);

	// Peticion al metodo expuesto
	$response = $client->__soapCall(
       "autorizacionComprobante",
       array($user_param)
   	);

	if(!$response){
		throw new Exception("No se ha podido conectar con el servicio del SRI. Intente mas tarde");	
	}

	// echo $client->__getLastRequest();
	$autorizacionComprobante = $response->RespuestaAutorizacionComprobante;

	if(!$autorizacionComprobante->numeroComprobantes){
		throw new Exception("No hay comprobantes para estos datos");	
	}

	$comprobante = $autorizacionComprobante->autorizaciones->autorizacion->comprobante;
	$dom = new DOMDocument();
	$dom->loadXML($comprobante);

	$parser = new XmlParser($dom);
	$infoTributaria = $parser->getInfoTributaria();

	echo "Info Tributaria <br>";
	print_r($infoTributaria);
	echo "<hr>";
	
	if($infoTributaria["codDoc"] == '07'){

		$facturaParser = new FacturaParser($dom);

		echo "Info Factura <br>";
		print_r($facturaParser->getInfoFactura());
		echo "<hr>";

		echo "Detalles <br>";
		print_r($facturaParser->getDetalles());
		echo "<hr>";

	}else{
		$notaCreditoParser = new NotaCreditoParser($dom);
		echo "Info Tributaria <br>";
		print_r($notaCreditoParser->getInfoNotaCredito());
		echo "<hr>";

		// print_r($response);
		// die("FIN");
	}

	echo "Tiempo de ejecucion de consulta: " . (time() - $timeStart) . " segundos";

} catch (Exception $e) {
	die($e->getMessage());
}

function getInfoNotaCredito($dom){
	$infoFacturaContent = [
		'fechaEmision', 'dirEstablecimiento', 
		'tipoIdentificacionComprador', 'razonSocialComprador', 
		'identificacionComprador', 'contribuyenteEspecial', 
		'obligadoContabilidad', 'codDocModificado', 
		'numDocModificado', 'fechaEmisionDocSustento', 
		'totalSinImpuestos', 'valorModificacion', 
		'moneda', 'motivo', 
		'totalConImpuestos'
	];
	$infoFactura = getNode($dom, 'infoNotaCredito', 0);
	$infoFacturaData = [];
	foreach ($infoFacturaContent as $content) {
		$infoFacturaData[$content] = getNodeData($infoFactura, $content, 0);			
	}
	// $infoFacturaData['totalConImpuestos'] = getInfoFacturaImpuestos();
	// $infoFacturaData['pagos'] = getInfoFacturaPagos();
	return $infoFacturaData;
}

function getNodes($parent, $child){
	return $parent->getElementsByTagName($child);
}

function getNode($parent, $child, $position){
	return getNodes($parent,$child)->item($position);
}

function getNodeData($parent, $child, $position){
	$node = getNode($parent,$child, $position);
	return $node ?  $node->nodeValue: "";
}