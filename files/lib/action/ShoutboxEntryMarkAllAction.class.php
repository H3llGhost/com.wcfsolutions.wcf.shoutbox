<?php
// wcf imports
require_once(WCF_DIR.'lib/action/AbstractSecureAction.class.php');
require_once(WCF_DIR.'lib/data/shoutbox/ShoutboxEntryEditor.class.php');

/**
 * Marks all shoutbox entries.  
 * 
 * @author	Sebastian Oettl
 * @copyright	2009-2011 WCF Solutions <http://www.wcfsolutions.com/index.html>
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.wcfsolutions.wcf.shoutbox
 * @subpackage	action
 * @category	Community Framework
 */
class ShoutboxEntryMarkAllAction extends AbstractSecureAction {	
	/**
	 * @see Action::execute()
	 */
	public function execute() {
		parent::execute();
		
		// check permission
		WCF::getUser()->checkPermission('mod.shoutbox.canDeleteEntry');
		
		// mark shoutbox entries
		$entryIDArray = array();
		$sql = "SELECT	entryID
			FROM	wcf".WCF_N."_shoutbox_entry";
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			$entryIDArray[] = $row['entryID'];
		}
		ShoutboxEntryEditor::mark($entryIDArray);
		$this->executed();
		
		// forward to shoutbox entry archives
		HeaderUtil::redirect('index.php?page=ShoutboxEntryArchives'.SID_ARG_2ND_NOT_ENCODED);
		exit;
	}
}
?>