<?php
// wcf imports
require_once(WCF_DIR.'lib/data/cronjobs/Cronjob.class.php');

/**
 * Cronjob empties the shoutbox.
 * 
 * @author	Sebastian Oettl
 * @copyright	2009-2011 WCF Solutions <http://www.wcfsolutions.com/index.html>
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.wcfsolutions.wcf.shoutbox
 * @subpackage	system.cronjob
 * @category	Community Framework
 */
class EmptyShoutboxCronjob implements Cronjob {
	/**
	 * @see Cronjob::execute()
	 */
	public function execute($data) {
		if (EMPTY_SHOUTBOX_CYCLE > 0) {
			// delete shoutbox entries
			$sql = "SELECT	entryID
				FROM	wcf".WCF_N."_shoutbox_entry
				WHERE	time < ".(TIME_NOW - EMPTY_SHOUTBOX_CYCLE * 86400);
			$result = WCF::getDB()->sendQuery($sql);
			if (WCF::getDB()->countRows($result) > 0) {
				require_once(WCF_DIR.'lib/data/shoutbox/ShoutboxEntryEditor.class.php');
				$entryIDs = '';
				while ($row = WCF::getDB()->fetchArray($result)) {
					if (!empty($entryIDs)) $entryIDs .= ',';
					$entryIDs .= $row['entryID'];
				}
				ShoutboxEntryEditor::deleteAll($entryIDs);
			}
		}
	}
}
?>