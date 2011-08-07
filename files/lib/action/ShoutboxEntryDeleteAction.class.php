<?php
// wcf imports
require_once(WCF_DIR.'lib/action/AbstractSecureAction.class.php');
require_once(WCF_DIR.'lib/data/shoutbox/ShoutboxEntryEditor.class.php');

/**
 * Deletes a shoutbox entry.
 * 
 * @author	Sebastian Oettl
 * @copyright	2009-2011 WCF Solutions <http://www.wcfsolutions.com/index.html>
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.wcfsolutions.wcf.shoutbox
 * @subpackage	action
 * @category	Community Framework
 */
class ShoutboxEntryDeleteAction extends AbstractSecureAction {
	/**
	 * shoutbox entry id
	 * 
	 * @var	integer
	 */
	public $entryID = 0;
	
	/**
	 * shoutbox entry editor object
	 * 
	 * @var	ShoutboxEntryEditor
	 */
	public $entry = null;
	
	/**
	 * @see Action::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		// get shoutbox entry
		if (isset($_REQUEST['entryID'])) $this->entryID = intval($_REQUEST['entryID']);
		$this->entry = new ShoutboxEntryEditor($this->entryID);
		if (!$this->entry->entryID) {
			throw new IllegalLinkException();
		}
	}
	
	/**
	 * @see Action::execute()
	 */
	public function execute() {
		parent::execute();
		
		// check permission
		if (!$this->entry->isDeletable()) {
			throw new PermissionDeniedException();
		}
		
		// delete shoutbox entry
		$this->entry->delete();
		$this->executed();
		
		// forward
		if (WCF::getSession()->lastRequestURI && empty($_REQUEST['ajax'])) {
			HeaderUtil::redirect(WCF::getSession()->lastRequestURI, false);
		}
		exit;
	}
}
?>