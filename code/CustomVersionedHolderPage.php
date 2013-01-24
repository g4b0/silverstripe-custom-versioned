<?php

class CustomVersionedHolderPage extends DataExtension {
	
	// Nome del GridField da estendere
	protected $gridfieldName;
	
	public function __construct($gridfieldName) {
		$this->gridfieldName = $gridfieldName;
		parent::__construct();
	}

	public function updateCMSFields(\FieldList $fields) {
		parent::updateCMSFields($fields);
		
		// Ottengo il GridField - ce l'ho nell'elenco dei fields
		// E non é readOnly
		$gf = $fields->dataFieldByName($this->gridfieldName);
		if ($gf !== null && !($gf instanceof ReadonlyField)) {
			$df = $gf->getConfig()->getComponentByType('GridFieldDetailForm');
			$df->setItemRequestClass('CustomVersionedGridFieldDetailForm_ItemRequest');
		}
	}
}