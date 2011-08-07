<?php
// wcf imports
require_once(WCF_DIR.'lib/action/AbstractSecureAction.class.php');
require_once(WCF_DIR.'lib/data/shoutbox/ShoutboxEntryEditor.class.php');

/**
 * Marks / unmarks shoutbox entries.
 * 
 * @author	Sebastian Oettl
 * @copyright	2009-2011 WCF Solutions <http://www.wcfsolutions.com/index.html>
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.wcfsolutions.wcf.shoutbox
 * @subpackage	action
 * @category	Community Framework
 */
class ShoutboxEntryMarkAction extends AbstractSecureAction {
	/**
	 * list of shoutbox entry ids
	 * 
	 * @var	array
	 */
	public $entryIDArray = array();
	
	/**
	 * action (mark/unmark)
	 * 
	 * @var	string
	 */
	public $action = '';
	
	/**
	 * @see Action::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['shoutboxEntryID'])) {
			$this->entryIDArray = ArrayUtil::toIntegerArray($_REQUEST['shoutboxEntryID']);
			if (!is_array($this->entryIDArray)) {
				$this->entryIDArray = array($this->entryIDArray);
			}
		}
		if (isset($_POST['action'])) $this->action = $_POST['action'];
	}
	
	/**
	 * @see Action::execute()
	 */
	public function execute() {
		parent::execute();
		
		// check permission
		WCF::getUser()->checkPermission('mod.shoutbox.canDeleteEntry');
		
		// mark / unmark		
		if ($this->action == 'mark') ShoutboxEntryEditor::mark($this->entryIDArray);
		else if ($this->action == 'unmark') ShoutboxEntryEditor::unmark($this->entryIDArray);
		$this->executed();
	}
}
?>