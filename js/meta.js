$().ready(function () {
	$('.button')
		.mousedown(function () {$(this).addClass('activeButton');})
		.mouseup(function () {$(this).removeClass('activeButton');})
		.mouseleave(function () {$(this).removeClass('activeButton');});

	$('#submit').submit(function () {

		var cName = $('#canonicalName').val();
		var rName = $('#readableName').val();
		var cat = $('#category').val();
		var mField = $('#mysqlField').val();
		var lField = $('#ldapField').val();
		var authReq = $('#authorizationLevelRequired').val();

		$.post('addField.php', {
			canonicalName: cName,
			readableName: rName,
			category: cat,
			mysqlField: mField,
			ldapField: lField,
			authorizationLevelRequired: authReq});
			
		return false;
	});
});
