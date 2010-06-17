function format(person) {
	return person.NickName + " " + person.LastName + " : " + $.trim(person.PersonID);
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


		history.children('h2:first').unbind('click');
		history.children('h2:first').removeClass('moreAvailable');
			
	});

	return false;
}

function addToHistory(inID, inComment) {
	var commentURL = 'addComment.php?id=' + $.trim(inID) + '&comment=' + $.trim(inComment);
	$.get(commentURL, function (data) {
		$('#addComments').before(data);
		$('.newComment').show('fast');
		$('#newComments').val('');
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

function getPerson(person) {
	var resultsURL = 'getPerson.php?id=' + $.trim(person.PersonID);
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

$().ready(function () {
	$("#searchField")
		.autocomplete('search.php', {
			dataType: "json",
			width: 200,
			parse: function (data) {
				return $.map(data, function (row) {
					return {
						data: row,
						value: row.NickName,
						/*result: row.NickName + " " + row.LastName + " : " + $.trim(row.PersonID)*/
						result: ''
						}
					});
				},

			formatItem: function (item) {
				return format(item);
				}
			})
		.result(function (e, item) {
			$(this).addClass('moved');
			getPerson(item);
			})
		.focus();

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
