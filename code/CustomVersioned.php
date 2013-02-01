<?php

class CustomVersioned extends DataExtension {

	public function updateSummaryFields(&$fields) {
		$fields['Published'] = 'Published';
		$fields['Modified'] = 'Modified';		
	}
	
	public function updateFieldLabels(&$labels) {
		$labels['Published'] = _t('CustomVersioned.PUBLISHED', 'Published');
    $labels['Modified'] = _t('CustomVersioned.MODIFIED', 'Modified');
	}

	/**
	 * Rimuovo il field Version, essendo un internals
	 * @param \FieldList $fields
	 */
	public function updateCMSFields(\FieldList $fields) {
		parent::updateCMSFields($fields);

		// Rimuovo il FormItem Version
		$fields->removeByName('Version');
	}
		
	/**
	 * Estrae la data di pubblicazione
	 * @return String
	 */
	public function Published() {

		$retVal = null;
		if ($this->owner->isPublished()) {

			$lastPub = $this->owner->Versions('WasPublished=1', 'Version DESC', 1);
			$pub = $lastPub->pop();
			$pubEditedTime = new DateTime($pub->LastEdited);
			$retVal = $pubEditedTime->format('Y-m-d H:i:s');
		}
		return $retVal;
	}

	/**
	 * Estrae la data di ultima modifica
	 * @return String
	 */
	public function Modified() {

		$retVal = null;
		if ($this->owner->stagesDiffer('Stage', 'Live')) {

			$thisEditedTime = new DateTime($this->owner->LastEdited);
			$retVal = $thisEditedTime->format('Y-m-d H:i:s');
		}
		return $retVal;
	}

	/**
	 * Verifica il permesso di pubblicazione
	 * 
	 * @param Member $member
	 * @return boolean True se l'utente corrente puó pubblicare il DO
	 */
	public function canPublish($member = null) {

		$className = get_class($this->owner);
		if (Permission::check("PUBLISH_$className", 'any', $member))
			return true;
		else
			return false;
	}

	/**
	 * Verifica il permesso di rimozione dal Live (pubbicato)
	 * 
	 * @param Member $member
	 * @return boolean True se l'utente corrente puó "spubblicare" il DO
	 */
	public function canDeleteFromLive($member = null) {

		return $this->canPublish($member);
	}

	/**
	 * Verifica se il DO é nuovo, cioé se deve ancora essere scritto sul DB
	 *
	 * @return boolean True se il DO é nuovo
	 */
	function isNew() {
		if (empty($this->owner->ID))
			return true;

		if (is_numeric($this->owner->ID))
			return false;

		return stripos($this->owner->ID, 'new') === 0;
	}

	/**
	 * Verifica se il DO é pubblicato
	 *
	 * @return boolean True se il DO é pubblicato
	 */
	function isPublished() {

		if ($this->owner->isNew())
			return false;

		$className = get_class($this->owner);
		return (DB::query("SELECT \"ID\" FROM \"" . $className . "_Live\" WHERE \"ID\" = {$this->owner->ID}")->value()) ? true : false;
	}
	
	/**
	 * Elimino il DO anche dalla tabella Live
	 */
	public function onBeforeDelete() {
		
		$className = get_class($this->owner);
		$id = $this->owner->ID;
		
		DB::query("DELETE FROM {$className}_Live WHERE ID=$id");	
		
		parent::onBeforeDelete();
	}
	
}

