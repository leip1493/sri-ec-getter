<?php require_once 'XmlParser.php' ?>
<?php 

class ComprobanteRetencionParser extends XmlParser{
	public function __construct(DOMDocument $dom)
    {
        parent::__construct($dom);
    }

    public function getInfoCompRetencion(){
		$infoComprobanteContent = [
			'fechaEmision', 'contribuyenteEspecial', 
			'obligadoContabilidad', 'tipoIdentificacionSujetoRetenido', 
			'razonSocialSujetoRetenido', 'identificacionSujetoRetenido', 
			'periodoFiscal'
		];
		$infoComprobante = $this->getNode($this->dom, 'infoCompRetencion', 0);
		$infoComprobanteData = [];
		foreach ($infoComprobanteContent as $content) {
			$infoComprobanteData[$content] = $this->getNodeData($infoComprobante, $content, 0);			
		}
		return $infoComprobanteData;
	}
	
	public function getImpuestos(){
		$impuestos = $this->getNode($this->dom, 'impuestos', 0);
		$impuesto = $this->getNodes($this->dom, 'impuesto');
		$impuestoHeaders = [
			'codigo', 'codigoRetencion', 'baseImponible', 'porcentajeRetener', 
			'valorRetenido', 'codDocSustento', 'numDocSustento', 'fechaEmisionDocSustento'
		];
		$impuestosContent = [];	
		foreach ($impuesto as $index => $imp) {
			$rowImpuesto = [];
			foreach ($impuestoHeaders as $header) {
				$rowImpuesto[$header] = $this->getNodeData($imp, $header, 0);
			}
			$impuestosContent[$index] = $rowImpuesto;
		}
		return $impuestosContent;
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

}