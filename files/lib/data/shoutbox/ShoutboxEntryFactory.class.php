<?php
// wcf imports
require_once(WCF_DIR.'lib/system/event/EventHandler.class.php');
require_once(WCF_DIR.'lib/data/shoutbox/ShoutboxEntryList.class.php');

/**
 * Manages the shoutbox entries.
 * 
 * @author	Sebastian Oettl
 * @copyright	2009-2011 WCF Solutions <http://www.wcfsolutions.com/index.html>
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.wcfsolutions.wcf.shoutbox
 * @subpackage	data.shoutbox
 * @category	Community Framework
 */
class ShoutboxEntryFactory {
	/**
	 * number of max entries
	 * 
	 * @var	integer
	 */
	public $maxEntries = SHOUTBOX_MAX_ENTRIES;
	
	/**
	 * True, if the smileys should be loaded.
	 * 
	 * @var	boolean
	 */
	public $enableSmileyList = true;
	
	/**
	 * list of shoutbox entries
	 * 
	 * @var	ShoutboxEntryList
	 */
	public $entryList = array();
	
	/**
	 * list of smileys
	 * 
	 * @var array<Smiley>
	 */
	public $smileys = array();
	
	/**
	 * Creates a new ShoutboxEntryFactory.
	 * 
	 * @param	boolean		$enableSmileyList
	 */
	public function __construct($enableSmileyList = true) {
		$this->enableSmileyList = $enableSmileyList;
		
		// init shoutbox entry list
		$this->entryList = new ShoutboxEntryList();
	}
	
	/**
	 * Initializes the shoutbox entries.
	 */
	public function init() {
		// call shouldInit event
		EventHandler::fireAction($this, 'shouldInit');
		
		// read shoutbox entries
		$this->entryList->sqlLimit = $this->maxEntries;
		$this->entryList->readObjects();
		
		// get smileys
		if ($this->enableSmileyList) {
			require_once(WCF_DIR.'lib/data/message/smiley/Smiley.class.php');
			$smileys = WCF::getCache()->get('smileys', 'smileys');
			$this->smileys = (isset($smileys[0]) ? $smileys[0] : array());
		}
		
		// call didInit event
		EventHandler::fireAction($this, 'didInit');
	}
	
	/**
	 * Returns the shoutbox entries.
	 * 
	 * @return	array<ViewableShoutboxEntry>
	 */
	public function getEntries() {
		return array_reverse($this->entryList->getObjects());
	}
	
	/**
	 * Returns the smileys.
	 * 
	 * @return	array<Smiley>
	 */
	public function getSmileys() {
		return $this->smileys;
	}
}
?>