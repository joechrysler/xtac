$().ready(function () {
	$('.button')
		.mousedown(function () {$(this).addClass('active');})
		.mouseup(function () {$(this).removeClass('active');})
		.mouseleave(function () {$(this).removeClass('active');});
});
