# CustomVersioned

A very simple module for Silverstripe 3.0 that adds the Versionig also for DataObject, like for
SiteTree Object.

## Requirements

Silverstripe 3.0
Versioned

## Features

- Add Versioned support for DataObjects

## Install

- download the module and unzip it. Make sure the folder name is "CustomVersioned".
- add to your mysite/_config.php e.g.: `Object::add_extension('DataObject', 'Versioned');` (DataObject)
- add to your mysite/_config.php e.g.: `Object::add_extension('DataObject', 'CustomVersioned');` (DataObject)
- add to your mysite/_config.php e.g.: `Object::add_extension('Page', 'CustomVersionedHolderPage("GridFieldName")');` (Page holding the gridfield with DataObjects)

For example, if we have a DataObject called DoNews, a PageHolder called PghNews with a GridField named News:
Object::add_extension('DoNews', 'Versioned');
Object::add_extension('DoNews', 'CustomVersioned');
Object::add_extension('PghNews', 'CustomVersionedHolderPage("News")');

- due to a SS3 bug (actually SS 3.0.2), you have to put into the Page getCMSActions:
"` php
	public function getCMSFields() {
		// Disabilito l'updateCMSFields perché voglio che venga chiamto dopo
		// all'aggiunta dei miei campi
		self::disableCMSFieldsExtensions();
		$fields = parent::getCMSFields();
		self::enableCMSFieldsExtensions();

		// Add your fields here

		// Chiamo l'updateCMFFields che ho soppresso all'inizio della funzione
		$this->extend('updateCMSFields', $fields);

		return $fields;
	}
`"
- run /dev/build

## Usage
Simply enjoy your versioned DataObjects.

## Changelog

V1.0 (2013-01-27): 
published on GitHub

v0.2 (2012-11-09) : 
added ModelAdmin support

v0.1 (2012-11-09) : 
initial version
