<?php
// wcf imports
require_once(WCF_DIR.'lib/data/shoutbox/ShoutboxEntryFactory.class.php');
require_once(WCF_DIR.'lib/page/AbstractPage.class.php');

/**
 * Outputs an XML document with a list of shoutbox entries.
 * 
 * @author	Sebastian Oettl
 * @copyright	2009-2011 WCF Solutions <http://www.wcfsolutions.com/index.html>
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.wcfsolutions.wcf.shoutbox
 * @subpackage	page
 * @category	Community Framework
 */
class ShoutboxEntryXMLListPage extends AbstractPage {
	/**
	 * last update time
	 * 
	 * @var integer
	 */
	public $time = 0;
	
	/**
	 * shoutbox entry factory object
	 * 
	 * @var	ShoutboxEntryFactory
	 */
	public $factory = null;
	
	/**
	 * list of shoutbox entries
	 * 
	 * @var	array<ShoutboxEntry>
	 */
	public $entries = array();
	
	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['time'])) $this->time = intval($_REQUEST['time']);
	}
	
	/**
	 * @see Page::readData()
	 */	
	public function readData() {
		parent::readData();
		
		// init shoutbox entry factory
		$this->factory = new ShoutboxEntryFactory(false);
		$this->factory->entryList->sqlConditions = 'time > '.$this->time;
		$this->factory->init();
		
		// get shoutbox entries
		$this->entries = array_reverse($this->factory->entryList->getObjects());
	}
	
	/**
	 * @see Page::show()
	 */
	public function show() {
		parent::show();
		
		// reset URI in session
		if (WCF::getSession()->lastRequestURI) {
			WCF::getSession()->setRequestURI(WCF::getSession()->lastRequestURI);
		}
		
		// output shoutbox entries (xml)
		header('Content-type: text/xml; charset='.CHARSET);
		echo "<?xml version=\"1.0\" encoding=\"".CHARSET."\"?>\n<entries>";
		foreach ($this->entries as $entry) {
			echo "<entry>";
			echo "<entryID>".$entry->entryID."</entryID>";
			echo "<userID>".$entry->userID."</userID>";
			echo "<username><![CDATA[".StringUtil::escapeCDATA($entry->getStyledUsername())."]]></username>";
			echo "<time>".$entry->time."</time>";
			echo "<formattedTime><![CDATA[".DateUtil::formatShortTime(null, $entry->time, true)."]]></formattedTime>";
			echo "<message><![CDATA[".StringUtil::escapeCDATA($entry->getFormattedMessage())."]]></message>";
			echo "<isDeletable>".intval($entry->isDeletable())."</isDeletable>";
			echo "</entry>";
		}
		echo '</entries>';
		exit;
	}
}
?>