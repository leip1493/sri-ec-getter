<?php 

class XmlParser{

	protected $dom;

	public function __construct(DOMDocument $dom){
		$this->dom = $dom;
	}

	/////////////////////
	// INFO TRIBUTARIA //
	/////////////////////	
	public function getInfoTributaria(){
		$infoTributariaContent = [
			'ambiente', 'tipoEmision', 'razonSocial', 'nombreComercial', 'ruc', 'claveAcceso', 'codDoc', 'estab', 'ptoEmi', 'secuencial', 'dirMatriz'
		];
		$infoTributaria = $this->getNode($this->dom, 'infoTributaria', 0);
		$infoTributariaData = [];
		foreach ($infoTributariaContent as $content) {
			$infoTributariaData[$content] = $this->getNodeData($infoTributaria, $content, 0);
		}
		return $infoTributariaData;
	}

	protected function getNodes($parent, $child){
		return $parent->getElementsByTagName($child);
	}

	protected function getNode($parent, $child, $position){
		return $this->getNodes($parent,$child)->item($position);
	}

	protected function getNodeData($parent, $child, $position){
		$node = $this->getNode($parent,$child, $position);
		return $node ?  $node->nodeValue: "";
	}

}