<?php
// wcf imports
require_once(WCF_DIR.'lib/data/DatabaseObject.class.php');

/**
 * Represents a shoutbox entry.
 * 
 * @author	Sebastian Oettl
 * @copyright	2009-2011 WCF Solutions <http://www.wcfsolutions.com/index.html>
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.wcfsolutions.wcf.shoutbox
 * @subpackage	data.shoutbox
 * @category	Community Framework
 */
class ShoutboxEntry extends DatabaseObject {
	/**
	 * Creates a new ShoutboxEntry object.
	 * 
	 * @param 	integer 	$entryID
	 * @param 	array 		$row
	 */
	public function __construct($entryID, $row = null) {		
		if ($entryID !== null) {			
			$sql = "SELECT	* 
				FROM	wcf".WCF_N."_shoutbox_entry
				WHERE	entryID = ".$entryID;
			$row = WCF::getDB()->getFirstRow($sql);			
		}
		parent::__construct($row);
	}
	
	/**
	 * Returns true, if this shoutbox entry is marked.
	 * 
	 * @return	integer
	 */
	public function isMarked() {
		$sessionVars = WCF::getSession()->getVars();
		if (isset($sessionVars['markedShoutboxEntries'])) {
			if (in_array($this->entryID, $sessionVars['markedShoutboxEntries'])) return 1;
		}		
		return 0;
	}
	
	/**
	 * Returns true, if the active user can delete this shoutbox entry.
	 * 
	 * @return	boolean
	 */
	public function isDeletable() {
		if (($this->userID && $this->userID == WCF::getUser()->userID && WCF::getUser()->getPermission('user.shoutbox.canDeleteOwnEntry')) || WCF::getUser()->getPermission('mod.shoutbox.canDeleteEntry')) {
			return true;
		}
		return false;
	}
}
?>