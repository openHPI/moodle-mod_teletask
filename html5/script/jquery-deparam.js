jQuery.deparam = function (str) {
  var params = {};
  var chunks = str.split('&');
  var keyval, i;

  for (i = 0; i < chunks.length; i++) {
    keyval = chunks[i].split('=', 2);
    params[decodeURIComponent(keyval[0])] =
      (keyval.length == 2 ? decodeURIComponent(keyval[1].replace(/\+/g, ' ')) : '');
  }
  return params;
};

