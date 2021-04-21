$(document).ready(function() {
	$('.modal').modal();
});

function clickSearch() {
	if (event.keyCode === 13) {
		sendData();
	}
}

function sendData() {
	// start searching
	searchData($('#q').val());
}

function searchData(q) {
	// do not let small string pass
	if(q.length < 3) {
		M.toast({html: 'Mínimo 3 letras'});
		return false;
	}

	// send the request
	apretaste.send({
		'command': 'REVOLTILLO SEARCH',
		'data': {'q': q},
		'redirect': true
	});
}

function showDetails(id, q) {
	apretaste.send({
		command: "REVOLTILLO DETAILS",
		'data': {'id':id, 'q':q},
		'redirect': true
	});
}

function createShareText(price, title) {
	var newPrice = price ? '[' + price + ' CUC] ' : '';
	var newTitle = title.length > 40 ? title.substring(0, 40) + '...' : title;
	return newPrice + newTitle;
}

function share(id, price, title) {
	apretaste.send({
		command: 'PIZARRA PUBLICAR',
		redirect: false,
		data: {
			text: $('#message').val(),
			image: '',
			link: {
				command: btoa(JSON.stringify({
					command: 'REVOLTILLO DETAILS',
					data: {'id':id, 'q':''}
				})),
				icon: 'store',
				text: createShareText(price, title)
			}
		}
	});

	M.toast({html: 'Tu artículo fue compartido'});
}
