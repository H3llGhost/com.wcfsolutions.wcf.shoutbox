/**
 * @author	Sebastian Oettl
 * @copyright	2009-2011 WCF Solutions <http://www.wcfsolutions.com/index.php>
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
var ShoutboxEntryListEdit = Class.create({
	/**
	 * Inits ShoutboxEntryListEdit.
	 */
	initialize: function(data, count) {
		this.data = data;
		this.count = count;
		
		// get parent object
		this.parentObject = new InlineListEdit('shoutboxEntry', this);
	},	
	
	/**
	 * Show the status of an entry.
	 */
	showStatus: function(id) {
		var entry = this.data.get(id);
		
		// get row
		var row = $('shoutboxEntryRow'+id);
		
		// update css class
		if (row) {
			// remove all classes
			row.removeClassName('marked');
			
			// marked
			if (entry.isMarked) {
				row.addClassName('marked');
			}
		}
	},
	
	/**
	 * Saves the marked status.
	 */
	saveMarkedStatus: function(data) {
		new Ajax.Request('index.php?action=ShoutboxEntryMark&t='+SECURITY_TOKEN+SID_ARG_2ND, {
			method: 'post',
			parameters: data
		});
	},
	
	/**
	 * Returns a list of the edit options for the edit menu.
	 */
	getEditOptions: function(id) {
		return new Array();
	},
	
	/**
	 * Returns a list of the edit options for the edit marked menu.
	 */
	getEditMarkedOptions: function() {
		var options = new Array();
		var i = 0;
		
		// delete
		options[i] = new Object();
		options[i]['function'] = 'shoutboxEntryListEdit.removeMarked();';
		options[i]['text'] = language['wcf.global.button.delete'];
		i++;
		
		// unmark all
		options[i] = new Object();
		options[i]['function'] = 'shoutboxEntryListEdit.unmarkAll();';
		options[i]['text'] = language['wcf.global.button.unmark'];
		i++;
		
		return options;
	},
	
	/**
	 * Returns the title of the edit marked menu.
	 */
	getMarkedTitle: function() {
		return eval(language['wcf.shoutbox.markedEntries']);
	},
	
	/**
	 * Deletes all marked entries.
	 */
	removeMarked: function() {
		if (confirm(language['wcf.shoutbox.entry.deleteMarked.sure'])) {
			document.location.href = fixURL('index.php?action=ShoutboxEntryDeleteMarked&t='+SECURITY_TOKEN+SID_ARG_2ND);
		}
	},
	
	/**
	 * Unmarkes all marked entries.
	 */
	unmarkAll: function() {
		new Ajax.Request('index.php?action=ShoutboxEntryUnmarkAll&t='+SECURITY_TOKEN+SID_ARG_2ND, {
			method: 'get'
		});
		
		// checkboxes
		this.count = 0;
		var entryIDArray = this.data.keys();
		for (var i = 0; i < entryIDArray.length; i++) {
			var id = entryIDArray[i];
			var entry = this.data.get(id);
		
			entry.isMarked = 0;
			var checkbox = $('shoutboxEntryMark'+id);
			if (checkbox) {
				checkbox.checked = false;
			}
			
			this.showStatus(id);
		}
		
		// mark all checkboxes
		this.parentObject.checkMarkAll(false);
		
		// edit marked menu
		this.parentObject.showMarked();
	}
});