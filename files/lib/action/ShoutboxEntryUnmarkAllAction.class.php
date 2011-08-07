<?php
// wcf imports
require_once(WCF_DIR.'lib/action/AbstractSecureAction.class.php');

/**
 * Unmarks all shoutbox entries.
 * 
 * @author	Sebastian Oettl
 * @copyright	2009-2011 WCF Solutions <http://www.wcfsolutions.com/index.html>
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.wcfsolutions.wcf.shoutbox
 * @subpackage	action
 * @category	Community Framework
 */
class ShoutboxEntryUnmarkAllAction extends AbstractSecureAction {
	/**
	 * @see Action::execute()
	 */
	public function execute() {
		parent::execute();
		
		// unmark shoutbox entries
		WCF::getSession()->unregister('markedShoutboxEntries');
		$this->executed();
	}
}
?>