<?php
/**
 * @author	Sebastian Oettl
 * @copyright	2009-2011 WCF Solutions <http://www.wcfsolutions.com/index.html>
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
$packageID = $this->installation->getPackageID();

// get obsolete language category
$sql = "SELECT	languageCategoryID
	FROM	wcf".WCF_N."_language_category
	WHERE	languageCategory = 'wcf.shoutbox.entry'";
$row = WCF::getDB()->getFirstRow($sql);
if ($row['languageCategoryID']) {
	// delete language items
	$sql = "DELETE FROM	wcf".WCF_N."_language_item
		WHERE		languageCategoryID = ".$row['languageCategoryID']."
				AND packageID = ".$packageID;
	WCF::getDB()->sendQuery($sql);	
}

// cleanup language categories
require_once(WCF_DIR.'lib/system/language/LanguageEditor.class.php');
LanguageEditor::deleteEmptyCategories();
?>