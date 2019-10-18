"use strict";

function clickSearch() {
  if (event.keyCode === 13) sendData();
}

function sendData() {
  var value = document.getElementById('q').value; // search

  apretaste.send({
    'command': 'REVOLTILLO SEARCH',
    'data': {
      'q': value
    },
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

function showDetail(anuncio){ 

   apretaste.send({
      command: "REVOLTILLO SHOWDETAIL",
      'data': {'anuncio':anuncio},
      'redirect': true
    });
  
}


