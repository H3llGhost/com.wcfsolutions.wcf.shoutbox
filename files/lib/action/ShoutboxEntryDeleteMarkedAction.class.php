<?php
// wcf imports
require_once(WCF_DIR.'lib/action/AbstractSecureAction.class.php');
require_once(WCF_DIR.'lib/data/shoutbox/ShoutboxEntryEditor.class.php');

/**
 * Deletes all marked shoutbox entries.
 * 
 * @author	Sebastian Oettl
 * @copyright	2009-2011 WCF Solutions <http://www.wcfsolutions.com/index.html>
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.wcfsolutions.wcf.shoutbox
 * @subpackage	action
 * @category	Community Framework
 */
class ShoutboxEntryDeleteMarkedAction extends AbstractSecureAction {
	/**
	 * @see Action::execute()
	 */
	public function execute() {
		// check permission
		WCF::getUser()->checkPermission('mod.shoutbox.canDeleteEntry');
		
		// delete marked shoutbox entries
		$markedEntries = WCF::getSession()->getVar('markedShoutboxEntries');
		if ($markedEntries !== null) {
			foreach ($markedEntries as $entryID) {
				$entry = new ShoutboxEntryEditor($entryID);	
				if ($entry->entryID) $entry->delete();
			}
		}
		
		// unmark shoutbox entries
		WCF::getSession()->unregister('markedShoutboxEntries');
		$this->executed();
		
		// forward to shoutbox entry archives
		HeaderUtil::redirect('index.php?page=ShoutboxEntryArchives'.SID_ARG_2ND_NOT_ENCODED);
		exit;
	}
}
?>