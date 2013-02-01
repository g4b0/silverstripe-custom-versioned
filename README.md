# CustomVersioned

Silverstripe 3.0 module that adds the Versionig also for DataObject, like for SiteTree Object.

## Requirements

Silverstripe 3.0
Versioned

## Features

- Add Versioned support for DataObjects

## Install

- Download the module and unzip it. Make sure the folder name is "CustomVersioned".
- Add to your mysite/_config.php e.g.: `Object::add_extension('DataObject', 'Versioned');` (DataObject)
- Add to your mysite/_config.php e.g.: `Object::add_extension('DataObject', 'CustomVersioned');` (DataObject)
- Add to your mysite/_config.php e.g.: `Object::add_extension('Page', 'CustomVersionedHolderPage("GridFieldName")');` (Page holding the gridfield with DataObjects)
- It's important to define $searchable_fields and $summary_fields into the extended DataObjects, because CustomVersioned will extend them.

### Example 1 - Simple DataObject
We have a DataObject called DoNews, a PageHolder called PghNews with a GridField named News:
```php
Object::add_extension('DoNews', 'Versioned');
Object::add_extension('DoNews', 'CustomVersioned');
Object::add_extension('PghNews', 'CustomVersionedHolderPage("News")');
```

### Example 2 - Inherited DataObject
We have an ancestor DataObject called DoTest, and its child DoTestSub. We define a Holder page, PghTest, that holds DoTestSub pages.

```php
// CustomVersioned - ANCHESTOR is extended by Versioned and CustomVersioned
Object::add_extension('DoTest', 'Versioned');
Object::add_extension('DoTest', 'CustomVersioned');
// CustomVersioned - CHILD HOLDER is eventually extended by CustomVersionedHolderPage. If we work with ModelAdmin it is not necessary
Object::add_extension('PghTest', 'CustomVersionedHolderPage("Tests")');
```

```php
class DoTest extends DataObject {
	public static $db = array(
			'TestStr' => 'Varchar(255)',
	);
	static $searchable_fields = array(
			'TestStr'
	);
	static $summary_fields = array(
			'TestStr' => 'TestStr',
	);

	public function getCMSFields() {
		$fields = parent::getCMSFields();

		// Rimuovo il FormItem Version
		$fields->removeByName('Version');

		return $fields;
	}

}

class DoTestSub extends DoTest {
	public static $db = array(
			'TestNum' => 'Int',
	);
	static $searchable_fields = array(
			'TestStr',
			'TestNum',
	);
	static $summary_fields = array(
			'TestStr' => 'TestStr',
			'TestNum' => 'TestNum',
	);
	static $has_one = array(
			'Holder' => 'PghTest'
	);

	public function getCMSFields() {
		$fields = parent::getCMSFields();
		$fields->removeByName('HolderID');
		return $fields;
	}

}

class PghTest extends Page {
	static $allowed_children = array(
	);
	static $has_many = array(
			'Tests' => 'DoTestSub'
	);
	static $singular_name = 'Cont. Test';
	static $plural_name = 'Cont. Test';
	// Solo gli admin possono creare questo tipo di pagina
	static $can_create = false;


	public function getCMSFields() {

		// Disabilito l'updateCMSFields perché voglio che venga chiamto dopo
		// all'aggiunta dei miei campi
		self::disableCMSFieldsExtensions();
		$fields = parent::getCMSFields();
		self::enableCMSFieldsExtensions();
		
		/* GRIDFIELD */
		$gridFieldConfig = GridFieldConfig_RelationEditor::create();
		$gridFieldConfig->getComponentByType('GridFieldDetailForm');
		$newsField = new GridField(
										'Tests',
										'Tutti i Tests',
										$this->Tests(),
										$gridFieldConfig
		);
		$fields->addFieldToTab('Root.Test', $newsField);

		// Chiamo l'updateCMSFields che ho soppresso all'inizio della funzione
		$this->extend('updateCMSFields', $fields);

		return $fields;
	}

	public function getNum() {
		//var_dump($this->Tests()->count()); exit;
		return $this->Tests()->count();
	}
}
```

### Warning:
Due to a SS3 bug (actually SS 3.0.2), you have to put into the Page Holder getCMSActions:

```php
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
```

- run /dev/build

## Usage
Simply enjoy your versioned DataObjects.

## Changelog

V1.2 (2013-02-01)
bugfix: now using updateSummaryFields

V1.1 (2013-01-31)
bugfix and code cleanup

V1.0 (2013-01-27): 
published on GitHub

v0.2 (2012-11-09) : 
added ModelAdmin support

v0.1 (2012-11-09) : 
initial version
