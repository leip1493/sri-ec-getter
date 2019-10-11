<?php require_once 'XmlParser.php' ?>
<?php require_once 'FacturaParser.php' ?>
<?php require_once 'NotaCreditoParser.php' ?>
<?php 

try {
	$clave_acc = "0509201901179242984600120020052050754120054076917"; // FACTURA
	// $clave_acc = "0809201901099133185900120230150009498921357246816"; // FACTURA
	$clave_acc = "2608201901019038904900120010020000019611234567816"; // FACTURA
	// $clave_acc = "2608201904179037150600120020210000994710009947118"; // NOTA DE CREDITO	
	// $clave_acc = "2608201904099236486600120020020000004490000044918"; // NOTA DE CREDITO


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
	print_r($response);

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

		die("EL ARCHIVO ES UN COMPROBANTE DE RETENCION");
	}
	else{
		print_r($response);
		die("FIN");
	}

	print_r($response);

	echo "Tiempo de ejecucion de consulta: " . (time() - $timeStart) . " segundos";

} catch (Exception $e) {
	die($e->getMessage());
}
//////////////////////////
// METODOS NOTA CREDITO //
//////////////////////////
// ///PUBLIC
// function getInfoNotaCredito($dom){
// 	$infoFacturaContent = [
// 		'fechaEmision', 'dirEstablecimiento', 
// 		'tipoIdentificacionComprador', 'razonSocialComprador', 
// 		'identificacionComprador', 'contribuyenteEspecial', 
// 		'obligadoContabilidad', 'codDocModificado', 
// 		'numDocModificado', 'fechaEmisionDocSustento', 
// 		'totalSinImpuestos', 'valorModificacion', 
// 		'moneda', 'motivo', 
// 		'totalConImpuestos'
// 	];
// 	$infoFactura = getNode($dom, 'infoNotaCredito', 0);
// 	$infoFacturaData = [];
// 	foreach ($infoFacturaContent as $content) {
// 		$infoFacturaData[$content] = getNodeData($infoFactura, $content, 0);			
// 	}
// 	$infoFacturaData['totalConImpuestos'] = getInfoNotaCreditoImpuestos($dom);
// 	// $infoFacturaData['pagos'] = getInfoFacturaPagos();
// 	return $infoFacturaData;
// }
// function getDetalles($dom){
// 	$detalles = getNode($dom, 'detalles', 0);
// 	$detalle = getNodes($dom, 'detalle');
// 	$detalleHeaders = [
// 		'codigoInterno', 'descripcion', 'cantidad', 'precioUnitario', 'descuento', 'precioTotalSinImpuesto', 'impuestos',
// 	];
// 	$detallesContent = [];	
// 	foreach ($detalle as $index => $d) {
// 		$rowDetalle = [];
// 		foreach ($detalleHeaders as $header) {
// 			$rowDetalle[$header] = getNodeData($d, $header, 0);
// 		}
// 		$rowDetalle['impuestos'] = getDetallesImpuestos($dom, $index);
// 		$detallesContent[$index] = $rowDetalle;
// 	}
// 	return $detallesContent;
// }
// function getInfoAdicional($dom){
// 	$infoAdicional = getNode($dom, 'infoAdicional', 0);
// 	$campoAdicional = getNodes($dom, 'campoAdicional');
// 	$detallesContent = [];	
// 	foreach ($campoAdicional as $index => $campo) {		
// 		$detallesContent[$index] = [
// 			'name' 	=> $campo->getAttribute('nombre'),
// 			'value' => $campo->nodeValue

// 		];
// 	}
// 	return $detallesContent;
// }
// // PRIVATE
// function getInfoNotaCreditoImpuestos($dom){
// 	$totalConImpuestos = getNode($dom, 'totalConImpuestos', 0);
// 	$totalImpuestos = getNodes($dom, 'totalImpuesto');
// 	$totalImpuestoHeaders = [
// 		'codigo', 'codigoPorcentaje', 'baseImponible', 'valor'
// 	];
// 	$totalImpuestoContent = [];	
// 	foreach ($totalImpuestos as $index => $totalImpuesto) {
// 		$rowImpuesto = [];
// 		foreach ($totalImpuestoHeaders as $header) {
// 			$rowImpuesto[$header] = getNodeData($totalImpuesto, $header, 0);
// 		}
// 		$totalImpuestoContent[$index] = $rowImpuesto;
// 	}

// 	return $totalImpuestoContent;
// }
// function getDetallesImpuestos($dom, $position){
// 	$impuestos = getNode($dom, 'impuestos', $position);
// 	$impuesto = getNodes($dom, 'impuesto');
// 	$impuestoHeaders = [
// 		'codigo', 'codigoPorcentaje', 'tarifa', 'baseImponible', 'valor'
// 	];
// 	$impuestoContent = [];	
// 	foreach ($impuesto as $index => $i) {
// 		$rowImpuesto = [];
// 		foreach ($impuestoHeaders as $header) {
// 			$rowImpuesto[$header] = getNodeData($i, $header, 0);
// 		}
// 		$impuestoContent[$index] = $rowImpuesto;
// 	}
// 	return $impuestoContent;
// }

/////////////////
// DOM GETTERs //
/////////////////
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