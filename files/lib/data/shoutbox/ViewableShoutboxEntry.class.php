<?php
// wcf imports
require_once(WCF_DIR.'lib/data/message/bbcode/SimpleMessageParser.class.php');
require_once(WCF_DIR.'lib/data/shoutbox/ShoutboxEntry.class.php');

/**
 * Represents a viewable shoutbox entry.
 * 
 * @author	Sebastian Oettl
 * @copyright	2009-2011 WCF Solutions <http://www.wcfsolutions.com/index.html>
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.wcfsolutions.wcf.shoutbox
 * @subpackage	data.shoutbox
 * @category	Community Framework
 */
class ViewableShoutboxEntry extends ShoutboxEntry {
	/**
	 * special username styling
	 * 
	 * @var string
	 */
	public $usernameStyle = '%s';
	
	/**
	 * Returns a special username styles.
	 * 
	 * @return	string
	 */
	public function getStyledUsername() {
		if ($this->usernameStyle != '%s') {
			return sprintf($this->usernameStyle, StringUtil::encodeHTML($this->username));
		}
		return StringUtil::encodeHTML($this->username);
	}
	
	/**
	 * Returns the formatted message.
	 * 
	 * @return 	string
	 */	
	public function getFormattedMessage() {	
		return SimpleMessageParser::getInstance()->parse($this->message);	
	}
}
?>