/**
 * @author	Sebastian Oettl
 * @copyright	2009-2011 WCF Solutions <http://www.wcfsolutions.com/index.php>
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
var Shoutbox = Class.create({
	/**
	 * Inits Shoutbox.
	 */
	initialize: function(shoutboxID, entries, lastUpdateTime) {
		this.shoutboxID = shoutboxID;
		this.lastUpdateTime = lastUpdateTime;
		this.standby = false;
		this.options = Object.extend({
			langDeleteEntry:	'',
			langDeleteEntrySure:	'',
			imgDeleteEntrySrc:	'',
			entryReloadInterval: 	0,
			entrySortOrder: 	'ASC',
			unneededUpdateLimit:	1
		}, arguments[3] || { });
		this.unneededUpdates = 0;
		
		// remove entries
		var shoutboxContentDiv = $(this.shoutboxID+'Content');
		if (shoutboxContentDiv) {
			shoutboxContentDiv.update();
			shoutboxContentDiv.observe('mouseover', function() { if (this.standby) { this.stopStandby(); this.startEntryUpdate(); } }.bind(this));
		}
		
		// show smileys
		var smileyContainerDiv = $(this.shoutboxID+'SmileyContainer');
		if (smileyContainerDiv) {
			smileyContainerDiv.removeClassName('hidden');
		}
		
		// add event listener
		var entryAddForm = $(this.shoutboxID+'EntryAddForm');
		if (entryAddForm) {
			entryAddForm.observe('submit', function(event) { this.addEntry(); event.stop(); }.bind(this));
		}
		
		// insert entries
		this.insertEntries(entries, false);
		
		// start entry update
		this.startEntryUpdate();
	},
	
	/**
	 * Starts the entry update.
	 */
	startEntryUpdate: function() {
		if (this.options.entryReloadInterval != 0) {
			this.executer = new PeriodicalExecuter(function() { this.loadEntries(this.lastUpdateTime); }.bind(this), this.options.entryReloadInterval);
		}
	},
	
	/**
	 * Stops the entry update.
	 */
	stopEntryUpdate: function() {
		if (this.options.entryReloadInterval != 0) {
			this.executer.stop();
		}
	},
	
	/**
	 * Starts the standby mode.
	 */
	startStandby: function() {
		if (!this.standby) {			
			this.standby = true;
			
			// change opacity
			new Effect.Opacity(this.shoutboxID+'Content', { from: 1, to: 0.5 });
		}
	},
	
	/**
	 * Stops the standby mode.
	 */
	stopStandby: function() {
		if (this.standby) {
			this.standby = false;
			this.unneededUpdates = 0;
			
			// change opacity
			new Effect.Opacity(this.shoutboxID+'Content', { from: 0.5, to: 1 });
		}
	},
	
	/**
	 * Inserts a smiley.
	 */
	insertSmiley: function(code) {
		var messageInputField = $(this.shoutboxID+'Message');
		if (messageInputField) {
			messageInputField.value = messageInputField.value+' '+code+' ';
			messageInputField.focus();
		}
	},
	
	/**
	 * Adds a new entry.
	 */
	addEntry: function() {
		// get message
		var message = '';
		var messageInputField = $(this.shoutboxID+'Message');
		if (messageInputField) {
			message = messageInputField.value;
		}
		
		// get username
		var username = '';
		var usernameInputField = $(this.shoutboxID+'Username');
		if (usernameInputField) {
			username = usernameInputField.value;
		}
		
		// add entry
		new Ajax.Request('index.php?action=ShoutboxEntryAdd'+SID_ARG_2ND, {
			method: 'post',
			parameters: {
				message: message,
				username: username,
				ajax: 1
			},
			onSuccess: function(messageInputField) {
				// reset message
				if (messageInputField) {
					messageInputField.value = '';
					messageInputField.focus();
				}				
				
				// stop update
				this.stopEntryUpdate();
				
				// update entries
				this.loadEntries(this.lastUpdateTime);
				
				// stop standby
				this.stopStandby();
				
				// restart entry update
				this.startEntryUpdate();
			}.bind(this, messageInputField),
			onFailure: function(response) {
				alert(response.responseText);
			}
		});
	},
	
	/**
	 * Deletes an entry.
	 */	
	deleteEntry: function(id) {
		new Ajax.Request('index.php?action=ShoutboxEntryDelete&t='+SECURITY_TOKEN+SID_ARG_2ND, {
			method: 'post',
			parameters: {
				entryID: id,
				ajax: 1
			},
			onSuccess: function() {				
				// remove entry row
				var row = $(this.shoutboxID+'Entry'+id);
				if (row) {
					new Effect.Parallel([
						new Effect.BlindUp(row),
						new Effect.Fade(row)
					], { duration: 0.3 });
				}
			}.bind(this)
		});
	},
	
	/**
	 * Loads the new entries and inserts them.
	 */
	loadEntries: function(time) {
		// start request
		new Ajax.Request('index.php?page=ShoutboxEntryXMLList&t='+SECURITY_TOKEN+SID_ARG_2ND, {
			method: 'post',
			parameters: {
				time: time
			},
			onSuccess: function(response) {			
				// get entries
				var entries = response.responseXML.getElementsByTagName('entries');
				if (entries.length > 0) {
					if (entries[0].childNodes.length == 0) {
						this.unneededUpdates++;
						if (this.options.unneededUpdateLimit != 0 && this.unneededUpdates >= this.options.unneededUpdateLimit) {
							this.startStandby();
							this.stopEntryUpdate();
						}
						return;
					}
					var newEntries = new Hash();
					for (var i = 0; i < entries[0].childNodes.length; i++) {
						newEntries.set(entries[0].childNodes[i].childNodes[0].childNodes[0].nodeValue, {
							userID: entries[0].childNodes[i].childNodes[1].childNodes[0].nodeValue,
							username: entries[0].childNodes[i].childNodes[2].childNodes[0].nodeValue,
							time: entries[0].childNodes[i].childNodes[4].childNodes[0].nodeValue,
							message: entries[0].childNodes[i].childNodes[5].childNodes[0].nodeValue,
							isDeletable: entries[0].childNodes[i].childNodes[6].childNodes[0].nodeValue
						});
						
						// set last update time
						if (i == entries[0].childNodes.length - 1) {
							this.lastUpdateTime = entries[0].childNodes[i].childNodes[3].childNodes[0].nodeValue;
						}
					}
					this.unneededUpdates = 0;
					this.insertEntries(newEntries, true);
				}
			}.bind(this)
		});
	},
	
	/**
	 * Inserts the given entries into the shoutbox.
	 */
	insertEntries: function(entries, animate) {
		var shoutboxMessageDiv = $(this.shoutboxID+'Content');
		if (shoutboxMessageDiv) {
			// update shoutbox content
			var idArray = entries.keys();
			if (idArray.length > 0) {
				for (var i = 0; i < idArray.length; i++) {
					var id = idArray[i];
					var entry = entries.get(id);
					
					// create entry row
					var time = new Element('span').addClassName('light').update('['+entry.time+']');
					var entryRow = new Element('p', { id: this.shoutboxID+'Entry'+id }).hide().insert(time);
					if (entry.isDeletable == 1) {
						var removeImage = new Element('img', { src: this.options.imgDeleteEntrySrc, alt: '' });
						var removeLink = new Element('a', { title: this.options.langDeleteEntry }).observe('click', function(id, event) { if (confirm(this.options.langDeleteEntrySure)) { this.deleteEntry(id); } event.stop(); }.bind(this, id)).insert(removeImage);
						entryRow.insert(' ').insert(removeLink);
					}
					entryRow.insert(' ');
					if (entry.userID != 0) {
						var userLink = new Element('a', { href: 'index.php?page=User&userID='+entry.userID+SID_ARG_2ND }).insert(entry.username);
						entryRow.insert(userLink);
					}
					else {
						entryRow.insert(entry.username);
					}
					entryRow.insert(': '+entry.message);
					
					// insert new entry
					if (this.options.entrySortOrder == 'ASC') {
						shoutboxMessageDiv.insert({ bottom: entryRow });
					}
					else {
						shoutboxMessageDiv.insert({ top: entryRow });
					}
					
					var shoutboxEntryDiv = $(this.shoutboxID+'Entry'+id);
					if (shoutboxEntryDiv) {
						if (animate) {
							new Effect.Parallel([
								new Effect.BlindDown(shoutboxEntryDiv),
								new Effect.Appear(shoutboxEntryDiv)
							], { duration: 0.3 });
						}
						else {
							shoutboxEntryDiv.show();
						}
					}
				}
				
				// focus last entry
				if (animate) {
					new PeriodicalExecuter(function(executer) {
						this.focusLastEntry();
						executer.stop();
					}.bind(this), 0.3);
				}
				else {
					this.focusLastEntry();
				}
			}
		}
	},
	
	/**
	 * Focuses the last shoutbox entry.
 	 */
	focusLastEntry: function() {
		if (this.options.entrySortOrder == 'ASC') {
			var shoutboxMessageDiv = $(this.shoutboxID+'Content');
			if (shoutboxMessageDiv) {
				shoutboxMessageDiv.scrollTop = shoutboxMessageDiv.scrollHeight - shoutboxMessageDiv.offsetHeight + 100;
			}
		}		
	}
});