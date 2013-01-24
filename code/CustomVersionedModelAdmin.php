<?php

class CustomVersionedModelAdmin extends Extension {

	function updateEditForm(&$form) {
		$modelclass = $this->owner->modelClass;
		$gridfieldconfig = $form->Fields()->fieldByName($modelclass)->getConfig();

		if (singleton($modelclass)->hasExtension("CustomVersioned")) {

			$df = $gridfieldconfig->getComponentByType('GridFieldDetailForm');
			$df->setItemRequestClass('CustomVersionedGridFieldDetailForm_ItemRequest');
		}
	}

}