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
		$infoFacturaData['totalConImpuestos'] = $this->getInfoNotaCreditoImpuestos();
		return $infoFacturaData;
	}

	public function getDetalles(){
		$detalles = $this->getNode($this->dom, 'detalles', 0);
		$detalle = $this->getNodes($this->dom, 'detalle');
		$detalleHeaders = [
			'codigoInterno', 'descripcion', 'cantidad', 'precioUnitario', 'descuento', 'precioTotalSinImpuesto', 'impuestos',
		];
		$detallesContent = [];	
		foreach ($detalle as $index => $d) {
			$rowDetalle = [];
			foreach ($detalleHeaders as $header) {
				$rowDetalle[$header] = $this->getNodeData($d, $header, 0);
			}
			$rowDetalle['impuestos'] = $this->getDetallesImpuestos($index);
			$detallesContent[$index] = $rowDetalle;
		}
		return $detallesContent;
	}
	public function getInfoAdicional(){
		$infoAdicional = $this->getNode($this->dom, 'infoAdicional', 0);
		$campoAdicional = $this->getNodes($this->dom, 'campoAdicional');
		$detallesContent = [];	
		foreach ($campoAdicional as $index => $campo) {		
			$detallesContent[$index] = [
				'name' 	=> $campo->getAttribute('nombre'),
				'value' => $campo->nodeValue
			];
		}
		return $detallesContent;
	}
	// PRIVATE
	private function getInfoNotaCreditoImpuestos(){
		$totalConImpuestos = $this->getNode($this->dom, 'totalConImpuestos', 0);
		$totalImpuestos = $this->getNodes($this->dom, 'totalImpuesto');
		$totalImpuestoHeaders = [
			'codigo', 'codigoPorcentaje', 'baseImponible', 'valor'
		];
		$totalImpuestoContent = [];	
		foreach ($totalImpuestos as $index => $totalImpuesto) {
			$rowImpuesto = [];
			foreach ($totalImpuestoHeaders as $header) {
				$rowImpuesto[$header] = $this->getNodeData($totalImpuesto, $header, 0);
			}
			$totalImpuestoContent[$index] = $rowImpuesto;
		}

		return $totalImpuestoContent;
	}
	private function getDetallesImpuestos($position){
		$impuestos = $this->getNode($this->dom, 'impuestos', $position);
		$impuesto = $this->getNodes($this->dom, 'impuesto');
		$impuestoHeaders = [
			'codigo', 'codigoPorcentaje', 'tarifa', 'baseImponible', 'valor'
		];
		$impuestoContent = [];	
		foreach ($impuesto as $index => $i) {
			$rowImpuesto = [];
			foreach ($impuestoHeaders as $header) {
				$rowImpuesto[$header] = $this->getNodeData($i, $header, 0);
			}
			$impuestoContent[$index] = $rowImpuesto;
		}
		return $impuestoContent;
	}


}