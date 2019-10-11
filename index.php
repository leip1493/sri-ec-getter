<?php require_once 'XmlParser.php' ?>
<?php require_once 'FacturaParser.php' ?>
<?php require_once 'NotaCreditoParser.php' ?>
<?php require_once 'ComprobanteRetencionParser.php' ?>
<?php 

try {
	$clave_acc = "0509201901179242984600120020052050754120054076917"; // FACTURA
	// $clave_acc = "0809201901099133185900120230150009498921357246816"; // FACTURA
	// $clave_acc = "2608201901019038904900120010020000019611234567816"; // FACTURA
	// $clave_acc = "2608201904179037150600120020210000994710009947118"; // NOTA DE CREDITO	
	$clave_acc = "2608201904099236486600120020020000004490000044918"; // NOTA DE CREDITO
	// $clave_acc = "2608201907179009835400120010050030421340000000111"; // COMPROBANTE DE RETENCION
	// $clave_acc = "2608201907179009835400120010050030410160000000111"; // COMPROBANTE DE RETENCION


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
	
	if($infoTributaria["codDoc"] == XmlParser::FACTURA){ // Factura

		$facturaParser = new FacturaParser($dom);

		echo "Info Factura <br>";
		print_r($facturaParser->getInfoFactura());
		echo "<hr>";

		echo "Detalles <br>";
		print_r($facturaParser->getDetalles());
		echo "<hr>";


	}
	elseif ($infoTributaria["codDoc"] == XmlParser::NOTA_CREDITO) { // Nota de credito

		$notaCreditoParser = new NotaCreditoParser($dom);

		echo "Info Nota Credito <br>";
		print_r($notaCreditoParser->getInfoNotaCredito());
		echo "<hr>";

		echo "Detalles <br>";
		print_r($notaCreditoParser->getDetalles());
		echo "<hr>";

		echo "InfoAdicional <br>";
		print_r($notaCreditoParser->getInfoAdicional());
		echo "<hr>";
	}
	elseif ($infoTributaria["codDoc"] == XmlParser::COMPROBANTE_RETENCION) { // Comprobante de retencion
		
		$comprobanteRetencon = new ComprobanteRetencionParser($dom);

		echo "Info Comprobante retencion<br>";
		print_r($comprobanteRetencon->getInfoCompRetencion());
		echo "<hr>";

		echo "Impuestos <br>";
		print_r($comprobanteRetencon->getImpuestos());
		echo "<hr>";

		echo "Info adicional <br>";
		print_r($comprobanteRetencon->getInfoAdicional());
		echo "<hr>";

	}
	else{
		print_r($response);
		die("Tipo de documento no reconocido: " . $infoTributaria['codDoc']);
	}

	echo "Tiempo de ejecucion de consulta: " . (time() - $timeStart) . " segundos";

} catch (Exception $e) {
	die($e->getMessage());
}
