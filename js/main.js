function format(person) {
	return "<span class=\"autoLogin\">" + person.Login + " | </span>" + person.NickName + " " + person.LastName + " : " + $.trim(person.PersonID);
}

function getAllHistory(inID) {
	var retrievalURL = 'getHistory.php?id=' + $.trim(inID);
	var history = $('#history');
	var scroller = history.children('div.scrollable');
	$.get(retrievalURL, function (data) {
		
		scroller.css('max-height', scroller.css('height'));
		scroller.prepend(data)
		scroller.children('.newItem').slideDown('slow');
		scroller.css('overflow', 'auto');


		scroller.animate({ scrollTop: 0}, 1000);
		history.children('h2:first').unbind('click');
		history.children('h2:first').removeClass('moreAvailable');
			
	});

	return false;
}

function addToHistory(inID, inComment) {
	var commentURL = 'addComment.php?id=' + $.trim(inID) + '&comment=' + $.trim(inComment);
	var history = $('#history');
	var scroller = history.children('div.scrollable');
	$.get(commentURL, function (data) {
		scroller.css('max-height', scroller.css('height'));
		scroller.css('overflow', 'auto');
		scroller.append(data);
		$('.newComment').show();
		$('#newComments').val('');
		scroller.animate({ scrollTop: scroller.attr("scrollHeight")}, 1000);
	});
}

function displayResults(item) {
	$('#result')
		.hide()
		.html(item)
		.show();

	$('ul').addClass('dontsplit');
	$('dl').addClass('dontsplit');

	$('#result')
		.columnize({
			width: 354,
			lastNeverTallest: true
		});



	$('#addComments')
		.submit(function () {
			addToHistory($('#PersonID').next().html(), $('#newComments').val());
			return false;
		});
	
	$('#resetPassword')
		.submit(function () {
			$('p#dialog-username').html($('input#username').val());
			$('#dialog').dialog('open');
			return false;
		});

	$('#cmdResetPassword')
		.mousedown(function () {$(this).addClass('active');})
		.mouseup(function () {$(this).removeClass('active');})
		.mouseleave(function () {$(this).removeClass('active');});

	$('#history').children('h2:first').unbind('click');
	$('#history').children('h2:first').click(function () {getAllHistory($('#PersonID').next().html())});
}

function getPerson(id) {
	var resultsURL = 'getPerson.php?id=' + $.trim(id);
	$.get(resultsURL, function (data) {
		displayResults(data);
	});
}

function resetPassword(inUsername) {
	var resetURL = 'resetPassword.php?cn=' + $.trim(inUsername);
	
	$.get(resetURL, function (data) {
		$('#resetPassword')
			.hide()
			.before(data);
	});
}

function filterNamelessAccounts(inPerson) {
	return inPerson.NickName !== undefined && inPerson.LastName !== undefined ?
		inPerson.NickName + ' ' + inPerson.LastName:
		inPerson.Login;
}

	$.widget("custom.personComplete", $.ui.autocomplete, {
		_renderMenu: function( ul, items ) {
			var self = this,
				currentResultNumber = 0;
			$.each( items, function( index, item ) {
				currentResultNumber += 1;
				if ( currentResultNumber <= 10 ) {
					self._renderItem( ul, item );
				}
			});
		}
	});

$().ready(function () {
	$('#searchField').change(function () {$('#searchField').addClass('active');});
	$("#searchField").personComplete({
		/*source: projects,*/
			source: 'search.php',
			minLength: 2,
			focus: function(event, ui) {
				$('#searchField').val(filterNamelessAccounts(ui.item));
				return false;
			},		
			select: function (event, ui) {
				getPerson(ui.item.PersonID);
			}
	})
	.focus()
	.data( "personComplete" )._renderItem = function( ul, item ) {
			return $( "<li></li>" )
				.data( "item.autocomplete", item )
				.append( '<a><span class="autoLogin">' + item.Login + '</span>'
					+ filterNamelessAccounts(item) + ' '
					+ '<span class="autoID">' + item.PersonID + '</span></a>')
				.appendTo( ul );
	};

	$('#dialog').dialog({
		autoOpen: false,
		width: 400,
		modal: true,
		resizable: false,
		buttons: {
			'Reset Password': function() {resetPassword($('input#username').val());$(this).dialog('close');}
			}
		});
	});
