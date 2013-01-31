<?php

class CustomVersionedGridFieldDetailForm_ItemRequest extends GridFieldDetailForm_ItemRequest {

	public function ItemEditForm() {
		$form = parent::ItemEditForm();
		// Inserisco una nuova action
		//$form->Actions()->push($yourNewAction);

		if ($this->record->hasExtension('Versioned')) {
			// Sostituisco le actions 
			$form->setActions($this->getCMSActionsWithStaging());
		}

		return $form;
	}

	/**
	 * Ispirato al getCMSActions di SiteTree. Gestisce le bozze per i DataObject
	 * che sono estesi tramite Versioned.
	 * 
	 * @return FieldList The available actions for this page.
	 */
	function getCMSActionsWithStaging() {
		// Il record attuale
		$record = $this->record;

		$minorActions = CompositeField::create()->setTag('fieldset')->addExtraClass('ss-ui-buttonset');
		$actions = new FieldList($minorActions);

		if ($record->stagesDiffer('Stage', 'Live') && !$record->IsDeletedFromStage) {
			if ($record->isPublished() && $record->canEdit()) {
				// "rollback" - Cancella le modifiche salvate in bozza - OK
				$minorActions->push(
								FormAction::create('doRollback', _t('SiteTree.BUTTONCANCELDRAFT', 'Cancel draft changes'), 'delete')
												->setDescription(_t('SiteTree.BUTTONCANCELDRAFTDESC', 'Delete your draft and revert to the currently published page'))
				);
			}
		}

		if ($record->canEdit()) {

			if ($record->canDelete() && !$record->isPublished() && $record->ID > 0) {
				// Permetto di cancellare la bozza solo se il record non é pubblicato
				// ed é stata salvata almeno una bozza
				// "delete" - Elimina dal sito bozza" - OK
				$minorActions->push(
								FormAction::create('doDelete', _t('CMSMain.DELETE', 'Delete draft'))->addExtraClass('delete ss-ui-action-destructive')
												->setAttribute('data-icon', 'decline')
				);
			}

			// "save"
			$minorActions->push(
							FormAction::create('doSave', _t('CMSMain.SAVEDRAFT', 'Save Draft'))->setAttribute('data-icon', 'addpage')
			);
		}


		if ($record->canPublish() && !$record->IsDeletedFromStage) {
			if ($record->isPublished() && $record->canDeleteFromLive()) {
				// "unpublish" - Non pubblicare - OK
				$minorActions->push(
								FormAction::create('doUnpublish', _t('SiteTree.BUTTONUNPUBLISH', 'Unpublish'), 'delete')
												->setDescription(_t('SiteTree.BUTTONUNPUBLISHDESC', 'Remove this page from the published site'))
												->addExtraClass('ss-ui-action-destructive')->setAttribute('data-icon', 'unpublish')
				);
			}
			// "publish" - Salva e Pubblica - OK
			$actions->push(
							FormAction::create('doPublish', _t('SiteTree.BUTTONSAVEPUBLISH', 'Save & Publish'))
											->addExtraClass('ss-ui-action-constructive')->setAttribute('data-icon', 'accept')
			);
		}

		// getCMSActions() can be extended with updateCMSActions() on a extension
		//$record->extend('updateCMSActions', $actions);

		return $actions;
	}

	/**
	 * Publish this page.
	 */
	function doPublish($data, $form) {
		if (!$this->record->canPublish())
			return false;

		$this->doSave($data, $form);
		$this->record->publish("Stage", "Live");

		// Need to update pages linking to this one as no longer broken, on the live site
		$origMode = Versioned::get_reading_mode();
		Versioned::reading_stage('Live');
		Versioned::set_reading_mode($origMode);

		// Messaggio di ritorno
		$message = sprintf(
						_t('GridFieldDetailForm.Published', 'Published as %s %s'), $this->record->singular_name(), '<a href="' . $this->Link('edit') . '">"' . htmlspecialchars($this->record->Title, ENT_QUOTES) . '"</a>'
		);

		$form->sessionMessage($message, 'good');
		return $this->edit(Controller::curr()->getRequest());
	}

	/**
	 * Unpublish this page - remove it from the live site
	 */
	function doUnpublish($data, $form) {
		if (!$this->record->canDeleteFromLive())
			return false;
		if (!$this->record->ID)
			return false;

		$origStage = Versioned::current_stage();
		Versioned::reading_stage('Live');

		// This way our ID won't be unset
		$clone = clone $this->record;
		$clone->delete();

		Versioned::reading_stage($origStage);

		// If we're on the draft site, then we can update the status.
		// Otherwise, these lines will resurrect an inappropriate record
		$className = get_class($this->record);
		if (DB::query("SELECT \"ID\" FROM \"$className\" WHERE \"ID\" = {$this->record->ID}")->value()
						&& Versioned::current_stage() != 'Live') {
			$this->record->write();
		}

		// Messaggio di ritorno
		$message = sprintf(
						_t('GridFieldDetailForm.Unpublished', 'Unpublished as %s %s'), $this->record->singular_name(), '<a href="' . $this->Link('edit') . '">"' . htmlspecialchars($this->record->Title, ENT_QUOTES) . '"</a>'
		);

		$form->sessionMessage($message, 'good');
		return $this->edit(Controller::curr()->getRequest());
	}

	/**
	 * Clona la versione Live in Staging, compreso il numero di versione
	 */
	public function doRollback($data, $form) {
		// Clono il record di Live in Staging - Mi crea una nuova versione
		$this->record->doRollbackTo('Live');
		// Ottengo la version id della Live
		$className = get_class($this->record);
		$liveVersionId = Versioned::get_versionnumber_by_stage($className, 'Live', $this->record->ID);
		// Aggiorno la version della Staging
		DB::query("UPDATE $className SET Version=$liveVersionId WHERE ID={$this->record->ID}");

		// Recupero l'ultimo record pubblicato e mi ci allineo
		$lastPub = $this->record->Versions('WasPublished=1', 'Version DESC', 1);
		$this->record = $lastPub->pop();

		// Messaggio di ritorno
		$message = sprintf(
						_t('GridFieldDetailForm.Rollback', 'Rolback %s as %s'), $this->record->singular_name(), '<a href="' . $this->Link('edit') . '">"' . htmlspecialchars($this->record->Title, ENT_QUOTES) . '"</a>'
		);

		$form->sessionMessage($message, 'good');
		return $this->edit(Controller::curr()->getRequest());
	}

}
