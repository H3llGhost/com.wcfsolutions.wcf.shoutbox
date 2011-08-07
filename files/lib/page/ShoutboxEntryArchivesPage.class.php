<?php
// wcf imports
require_once(WCF_DIR.'lib/data/shoutbox/ShoutboxEntryList.class.php');
require_once(WCF_DIR.'lib/page/SortablePage.class.php');
require_once(WCF_DIR.'lib/page/util/menu/PageMenu.class.php');

/**
 * Shows a list of all shoutbox entries.
 * 
 * @author	Sebastian Oettl
 * @copyright	2009-2011 WCF Solutions <http://www.wcfsolutions.com/index.html>
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.wcfsolutions.wcf.shoutbox
 * @subpackage	page
 * @category	Community Framework
 */
class ShoutboxEntryArchivesPage extends SortablePage {
	// system
	public $templateName = 'shoutboxEntryArchives';
	public $neededPermissions = 'user.shoutbox.canViewEntryArchives';
	public $itemsPerPage = 20;
	public $defaultSortField = SHOUTBOX_ENTRY_ARCHIVES_SORT_FIELD;
	public $defaultSortOrder = SHOUTBOX_ENTRY_ARCHIVES_SORT_ORDER;
	
	/**
	 * list of shoutbox entries
	 * 
	 * @var ShoutboxEntryList
	 */
	public $entryList = null;
	
	/**
	 * number of marked shoutbox entries
	 * 
	 * @var	integer
	 */
	public $markedEntries = 0;
	
	/**
	 * @see Page::readParameters()
	 */	
	public function readParameters() {
		parent::readParameters();
		
		// init shoutbox entry list
		$this->entryList = new ShoutboxEntryList();
	}
	
	/**
	 * @see Page::readData()
	 */	
	public function readData() {
		parent::readData();
		
		// read shoutbox entries
		$this->entryList->sqlOffset = ($this->pageNo - 1) * $this->itemsPerPage;	
		$this->entryList->sqlLimit = $this->itemsPerPage;
		$this->entryList->sqlOrderBy = 'shoutbox_entry.'.$this->sortField." ".$this->sortOrder;
		$this->entryList->readObjects();
		
		// get marked shoutbox entries
		$sessionVars = WCF::getSession()->getVars();
		if (isset($sessionVars['markedShoutboxEntries'])) {
			$this->markedEntries = count($sessionVars['markedShoutboxEntries']);
		}
	}
	
	/**
	 * @see MultipleLinkPage::countItems()
	 */	
	public function countItems() {
		parent::countItems();
		
		return $this->entryList->countObjects();
	}
	
	/**
	 * @see Page::show()
	 */
	public function show() {
		// set active menu item
		PageMenu::setActiveMenuItem('wcf.footer.menu.shoutbox.entry.archives');
		
		// show page
		parent::show();
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {	
		parent::assignVariables();
				
		WCF::getTPL()->assign(array(
			'markedEntries' => $this->markedEntries,
			'entries' => $this->entryList->getObjects()
		));
	}
	
	/**
	 * @see SortablePage::validateSortField()
	 */
	public function validateSortField() {
		parent::validateSortField();
		
		switch ($this->sortField) {
			case 'entryID':
			case 'username':
			case 'message':
			case 'time': break;
			default: $this->sortField = $this->defaultSortField;
		}
	}
}
?>