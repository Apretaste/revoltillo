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


/*function sendData(){

  var q = document.getElementById('q');
  var value = q.value;

	if(value.length> 0){
		apretaste.send({
			'command':'REVOLTILLO SEARCH',
			'data':{'q':value},
			'redirect':true,
			'callback':{'name':'sendMessageCallback','data':value}
		});
	}
	//else M.toast({html: 'Mínimo 30 caracteres'});
}*/

function sendData() {
	var value = document.getElementById('q').value;

	// search
	apretaste.send({
		'command': 'REVOLTILLO SEARCH',
		'data': {'q': value},
		'redirect': true
	});
}

function sendDataUrl(q, page) {
	apretaste.send({
		command: "REVOLTILLO SEARCHURL",
		'data': {
			'q': q,
			'page': page
		},
		'redirect': true
	});
}

function sendDataCategory(q, page) {
	apretaste.send({
		command: "REVOLTILLO SEARCHCATEGORY",
		'data': {
			'q': q,
			'page': page
		},
		'redirect': true
	});
}

function showDetail(id) {
	apretaste.send({
		command: "REVOLTILLO SHOWDETAIL",
		'data': {'id': id},
		'redirect': true
	});
}

