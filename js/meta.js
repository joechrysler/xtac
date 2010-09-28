$().ready(function () {
	$('.button')
		.mousedown(function () {$(this).addClass('activeButton');})
		.mouseup(function () {$(this).removeClass('activeButton');})
		.mouseleave(function () {$(this).removeClass('activeButton');});
});
