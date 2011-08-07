<?php
// wcf imports
require_once(WCF_DIR.'lib/data/shoutbox/ShoutboxEntry.class.php');

/**
 * Provides functions to manage shoutbox entries.
 * 
 * @author	Sebastian Oettl
 * @copyright	2009-2011 WCF Solutions <http://www.wcfsolutions.com/index.html>
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.wcfsolutions.wcf.shoutbox
 * @subpackage	data.shoutbox
 * @category	Community Framework
 */
class ShoutboxEntryEditor extends ShoutboxEntry {
	/**
	 * Deletes this shoutbox entry.
	 */
	public function delete() {
		self::deleteAll($this->entryID);
	}
	
	/**
	 * Creates a new shoutbox entry.
	 * 
	 * @param	integer			$userID
	 * @param	string			$username
	 * @param	string			$message
	 * @return	ShoutboxEntryEditor
	 */
	public static function create($userID, $username, $message) {
		$sql = "INSERT INTO	wcf".WCF_N."_shoutbox_entry
					(userID, username, time, message, ipAddress)
			VALUES		(".$userID.", '".escapeString($username)."', ".TIME_NOW.", '".escapeString($message)."', '".escapeString(WCF::getSession()->ipAddress)."')";
		WCF::getDB()->sendQuery($sql);
		
		$entryID = WCF::getDB()->getInsertID("wcf".WCF_N."_shoutbox_entry", 'entryID');
		return new ShoutboxEntryEditor($entryID);
	}
	
	/**
	 * Deletes all shoutbox entries with the given entry ids.
	 * 
	 * @param	string		$entryIDs
	 */	
	public static function deleteAll($entryIDs) {
		if (empty($entryIDs)) return;
		
		// update user activity points
		$userActivityPoints = array();
		$sql = "SELECT	userID
			FROM	wcf".WCF_N."_shoutbox_entry
			WHERE	entryID IN (".$entryIDs.")
				AND userID <> 0";
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			if (!isset($userActivityPoints[$row['userID']])) $userActivityPoints[$row['userID']] = 0;
			$userActivityPoints[$row['userID']] -= ACTIVITY_POINTS_PER_SHOUTBOX_ENTRY;
		}
		
		// save activity points
		if (count($userActivityPoints)) {
			require_once(WCF_DIR.'lib/data/user/rank/UserRank.class.php');
			foreach ($userActivityPoints as $userID => $points) {
				UserRank::updateActivityPoints($points, $userID);
			}
		}
		
		// delete entries
		$sql = "DELETE FROM	wcf".WCF_N."_shoutbox_entry
			WHERE		entryID IN (".$entryIDs.")";
		WCF::getDB()->sendQuery($sql);
	}
	
	/**
	 * Marks shoutbox entries.
	 * 
	 * @param	array<integer>		$entryIDArray
	 */
	public static function mark($entryIDArray) {
		$markedShoutboxEntries = WCF::getSession()->getVar('markedShoutboxEntries');
		if ($markedShoutboxEntries !== null) {
			foreach ($entryIDArray as $entryID) {
				if (!in_array($entryID, $markedShoutboxEntries)) {
					$markedShoutboxEntries[] = $entryID;
				}
			}
		}
		else {
			$markedShoutboxEntries = $entryIDArray;
		}
		WCF::getSession()->register('markedShoutboxEntries', $markedShoutboxEntries);
	}
	
	/**
	 * Unmarks shoutbox entries.
	 * 
	 * @param	array<integer>		$entryIDArray
	 */
	public static function unmark($entryIDArray) {
		$markedShoutboxEntries = WCF::getSession()->getVar('markedShoutboxEntries');
		if ($markedShoutboxEntries !== null) {
			foreach ($entryIDArray as $entryID) {
				if (($key = array_search($entryID, $markedShoutboxEntries)) !== false) {
					unset($markedShoutboxEntries[$key]);
				}
			}
			if (count($markedShoutboxEntries)) WCF::getSession()->register('markedShoutboxEntries', $markedShoutboxEntries);
			else WCF::getSession()->unregister('markedShoutboxEntries');
		}
	}
}
?>