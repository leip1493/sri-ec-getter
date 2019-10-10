<?php require_once 'XmlParser.php' ?>
<?php 

class FacturaParser extends XmlParser{
	public function __construct(DOMDocument $dom)
    {
        parent::__construct($dom);
    }
	//////////////////
	// INFO FACTURA //
	//////////////////
	public function getInfoFactura(){
		$infoFacturaContent = [
			'fechaEmision', 'dirEstablecimiento', 
			'contribuyenteEspecial', 'obligadoContabilidad', 
			'tipoIdentificacionComprador', 'razonSocialComprador', 
			'identificacionComprador', 'totalSinImpuestos', 
			'totalSubsidio', 'totalDescuento', 
			'propina', 'importeTotal', 
			'moneda', 'placa', 
			'totalConImpuestos', 'pagos'
		];
		$infoFactura = $this->getNode($this->dom, 'infoFactura', 0);
		$infoFacturaData = [];
		foreach ($infoFacturaContent as $content) {
			$infoFacturaData[$content] = $this->getNodeData($infoFactura, $content, 0);			
		}
		$infoFacturaData['totalConImpuestos'] = $this->getInfoFacturaImpuestos();
		$infoFacturaData['pagos'] = $this->getInfoFacturaPagos();
		return $infoFacturaData;
	}

	//////////////
	// DETALLES //
	//////////////
	public function getDetalles(){
		$detalles = $this->getNode($this->dom, 'detalles', 0);
		$detalle = $this->getNodes($this->dom, 'detalle');
		$detalleHeaders = [
			'codigoPrincipal', 'descripcion', 'cantidad', 'precioUnitario', 'precioSinSubsidio', 'descuento', 'precioTotalSinImpuesto',
			'impuestos'
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


	////////////////////////////////////////
	// INFO FACTURA - TOTAL CON IMPUESTOS //
	////////////////////////////////////////
	private function getInfoFacturaImpuestos(){
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

	//////////////////////////
	// INFO FACTURA - PAGOS //
	//////////////////////////
	private function getInfoFacturaPagos(){
		$pagos = $this->getNode($this->dom, 'pagos', 0);
		$pago = $this->getNodes($this->dom, 'pago');
		$pagoHeaders = [
			'formaPago', 'total', 'plazo', 'unidadTiempo'
		];
		$pagosContent = [];	
		foreach ($pago as $index => $p) {
			$rowPago = [];
			foreach ($pagoHeaders as $header) {
				$rowPago[$header] = $this->getNodeData($p, $header, 0);
			}
			$pagosContent[$index] = $rowPago;
		}

		return $pagosContent;
	}

	//////////////////////////
	// DETALLES - IMPUESTOS //
	//////////////////////////
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