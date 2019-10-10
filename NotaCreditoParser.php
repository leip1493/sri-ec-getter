<?php require_once 'XmlParser.php' ?>
<?php 

class NotaCreditoParser extends XmlParser{
	public function __construct(DOMDocument $dom)
    {
        parent::__construct($dom);
    }

	public function getInfoNotaCredito(){
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
		$infoFactura = $this->getNode($this->dom, 'infoNotaCredito', 0);
		$infoFacturaData = [];
		foreach ($infoFacturaContent as $content) {
			$infoFacturaData[$content] = $this->getNodeData($infoFactura, $content, 0);			
		}
		// $infoFacturaData['totalConImpuestos'] = getInfoFacturaImpuestos();
		// $infoFacturaData['pagos'] = getInfoFacturaPagos();
		return $infoFacturaData;
	}


}