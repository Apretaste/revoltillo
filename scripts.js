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
