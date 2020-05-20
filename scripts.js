$(document).ready(function(){
	$('.fixed-action-btn').floatingActionButton({
		direction: 'left',
		hoverEnabled: false
	});
});

function clickSearch() {
	if (event.keyCode === 13) {
		sendData();
	}
}
function sendData() {
	// get the data to search
	var q = document.getElementById('q').value;

	// start searching
	searchData(q);
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
		'data': {'q':q},
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
