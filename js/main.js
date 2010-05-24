function format(person) {
	return person.NickName + " " + person.LastName + " : " + $.trim(person.PersonID);
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
	$('#result').hide().html(item).show();

	$('dl').addClass("dontsplit");

	$('#result').columnize({
		width: 350,
		lastNeverTallest: true
	});


	$('#addComments').submit(function () {
		addToHistory($('#PersonID').next().html(), $('#newComments').val());
		return false;
	});

}

function getPerson(person) {
	var resultsURL = 'getPerson.php?id=' + $.trim(person.PersonID);
	$.get(resultsURL, function (data) {
		displayResults(data);
	});
}

function resetPassword(inUsername) {
	var resetURL = 'resetPassword.php?username=' + $.trim(inUsername);
	$.get(resetURL, function (data) {
		$('#result').prepend(data);
	});
}

$().ready(function () {
	$("#searchField")
		.focus()

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
						//TODO Make the textbox clear after a result is successfully returned
						}
					});
				},

			formatItem: function (item) {
				return format(item);
				}
			})

		.result(function (e, item) {
			getPerson(item);
			})

		.focus();
	});
