<?php
/**
 * @author	Sebastian Oettl
 * @copyright	2009-2011 WCF Solutions <http://www.wcfsolutions.com/index.html>
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
$packageID = $this->installation->getPackageID();
$parentPackageID = 0;
$newParentPackageID = 0;

// update package
$sql = "UPDATE 	wcf".WCF_N."_package 
	SET 	parentPackageID = ".$newParentPackageID." 
	WHERE 	packageID = ".$packageID;
WCF::getDB()->sendQuery($sql);

// rebuild parent package dependencies								
Package::rebuildParentPackageDependencies($packageID);

// rebuild parent's parent package dependencies
Package::rebuildParentPackageDependencies($parentPackageID);

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

// file cleanup
$files = array(
	'lib/page/ShoutboxActionPage.class.php',
	'style/shoutbox.css'
);

// delete database entries
$sql = "DELETE FROM	wcf".WCF_N."_package_installation_file_log
	WHERE		filename IN ('".implode("','", array_map('escapeString', $files))."')
			AND packageID = ".$packageID;
WCF::getDB()->sendQuery($sql);

// delete files
foreach ($files as $file) {
	@unlink(RELATIVE_WCF_DIR.$this->installation->getPackage()->getDir().$file);
}

// user and mod options
$sql = "UPDATE 	wcf".WCF_N."_group_option_value
	SET	optionValue = 1
	WHERE	groupID IN (4,5,6)
		AND optionID IN (
			SELECT	optionID
			FROM	wcf".WCF_N."_group_option
			WHERE	(
					optionName LIKE 'mod.shoutbox.%'
					OR optionName LIKE 'user.shoutbox.%'
				)
				AND packageID IN (
					SELECT	dependency
					FROM	wcf".WCF_N."_package_dependency
					WHERE	packageID = ".$packageID."
				)
		)
		AND optionValue = '0'";
WCF::getDB()->sendQuery($sql);

// refresh style files
require_once(WCF_DIR.'lib/data/style/StyleEditor.class.php');
$sql = "SELECT 	*
	FROM 	wcf".WCF_N."_style";
$result = WCF::getDB()->sendQuery($sql);
while ($row = WCF::getDB()->fetchArray($result)) {
	$style = new StyleEditor(null, $row);
	$style->writeStyleFile();
}
?>