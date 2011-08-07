<?php
// wcf imports
require_once(WCF_DIR.'lib/action/AbstractAction.class.php');
require_once(WCF_DIR.'lib/data/shoutbox/ShoutboxEntryEditor.class.php');

/**
 * Adds a new shoutbox entry.
 * 
 * @author	Sebastian Oettl
 * @copyright	2009-2011 WCF Solutions <http://www.wcfsolutions.com/index.html>
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.wcfsolutions.wcf.shoutbox
 * @subpackage	action
 * @category	Community Framework
 */
class ShoutboxEntryAddAction extends AbstractAction {
	/**
	 * username
	 * 
	 * @var	string
	 */
	public $username = '';
	
	/**
	 * new message
	 * 
	 * @var	string
	 */
	public $message = '';
	
	/**
	 * new shoutbox entry editor object
	 * 
	 * @var	ShoutboxEntryEditor
	 */
	public $entry = null;
	
	/**
	 * @see Action::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		try {
			// check permissions
			WCF::getUser()->checkPermission('user.shoutbox.canAddEntry');
			
			// do flood control	
			if (WCF::getUser()->getPermission('user.shoutbox.floodControlTime')) {
				$sql = "SELECT		time
					FROM		wcf".WCF_N."_shoutbox_entry
					WHERE		".(WCF::getUser()->userID ? "userID = ".WCF::getUser()->userID : "ipAddress = '".escapeString(WCF::getSession()->ipAddress)."'")."
							AND time > ".(TIME_NOW - WCF::getUser()->getPermission('user.shoutbox.floodControlTime'))."
					ORDER BY	time DESC";
				$row = WCF::getDB()->getFirstRow($sql);
				if (isset($row['time'])) {
					throw new NamedUserException(WCF::getLanguage()->getDynamicVariable('wcf.shoutbox.entry.error.floodControl', array(
						'waitingTime' => $row['time'] - (TIME_NOW - WCF::getUser()->getPermission('user.shoutbox.floodControlTime')),
						'floodControlTime' => WCF::getUser()->getPermission('user.shoutbox.floodControlTime')
					)));
				}
			}
			
			// get username
			if (isset($_POST['username'])) {
				$this->username = StringUtil::trim($_POST['username']);
				if (CHARSET != 'UTF-8') $this->username = StringUtil::convertEncoding('UTF-8', CHARSET, $this->username);
			}
			if (WCF::getUser()->userID == 0) {
				if (empty($this->username)) {
					throw new NamedUserException(WCF::getLanguage()->get('wcf.shoutbox.entry.error.username.empty'));
				}
				if (!UserUtil::isValidUsername($this->username)) {
					throw new NamedUserException(WCF::getLanguage()->get('wcf.user.error.username.notValid'));
				}
				if (!UserUtil::isAvailableUsername($this->username)) {
					throw new NamedUserException(WCF::getLanguage()->get('wcf.user.error.username.notUnique'));
				}
					
				WCF::getSession()->setUsername($this->username);
			}
			else {
				$this->username = WCF::getUser()->username;
			}
			
			// get message
			if (isset($_POST['message'])) {
				$this->message = StringUtil::trim($_POST['message']);
				if (CHARSET != 'UTF-8') {
					$this->message = StringUtil::convertEncoding('UTF-8', CHARSET, $this->message);
				}
			}
			if (empty($this->message)) {
				throw new NamedUserException(WCF::getLanguage()->get('wcf.shoutbox.entry.error.message.empty'));
			}
			if (StringUtil::length($this->message) > WCF::getUser()->getPermission('user.shoutbox.maxEntryLength')) {
				throw new NamedUserException(WCF::getLanguage()->getDynamicVariable('wcf.message.error.tooLong', array('maxTextLength' => WCF::getUser()->getPermission('user.shoutbox.maxEntryLength'))));
			}
			$this->message = StringUtil::replace("\n", '', StringUtil::unifyNewlines($this->message));
		}
		catch (UserException $e) {
			// show errors in a readable way
			if (empty($_REQUEST['ajax'])) {
				throw $e;
			}
			else {
				@header('HTTP/1.0 403 Forbidden');
				echo $e->getMessage();
				exit;
			}
		}
	}
	
	/**
	 * @see Action::execute()
	 */
	public function execute() {
		parent::execute();
		
		// add shoutbox entry
		$this->entry = ShoutboxEntryEditor::create(WCF::getUser()->userID, $this->username, $this->message);
		$this->executed();
		
		// forward
		if (WCF::getSession()->lastRequestURI && empty($_REQUEST['ajax'])) {
			HeaderUtil::redirect(WCF::getSession()->lastRequestURI, false);
		}
		exit;
	}
}
?>