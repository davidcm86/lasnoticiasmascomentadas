var page = require('webpage').create(),
  system = require('system');

page.open("http://www.elespanol.com/mundo/20170825/241726687_0.html", function() {
  page.settings.userAgent = 'Mozilla/5.0 (Macintosh; Intel Mac OS X x.y; rv:42.0) Gecko/20100101 Firefox/42.0';

    setTimeout(function() {
        page.injectJs('/var/www/lasnoticiasmascomentadas/webroot/js/jquery-3.2.1.js');
        
            var comments = page.evaluate(function(){
				return jQuery('.tools-action__comments-button').attr("data-value");
            })
        
        console.log(comments);
        phantom.exit();
    }, 3000);
});
  

/*var page = require('webpage').create();
console.log('The default user agent is ' + page.settings.userAgent);
page.settings.userAgent = 'SpecialAgent';
page.open('http://www.elespanol.com/espana/20170824/241476734_0.html', function(status) {
  if (status !== 'success') {
    console.log('Unable to access network');
  } else {
    var ua = page.evaluate(function() {
      return document.getElementByClass('.tools-action__comments-button').textContent;
    });
    console.log(ua);
  }
  phantom.exit();
});*/
/*var page = require('webpage').create();
page.open("http://www.elespanol.com/espana/20170824/241476734_0.html", function (status) {
    var mainTitle = page.evaluate(function () {
        console.log('message from the web page');
        return document.querySelector("h1").textContent;
    });
    console.log('First title of the page is ' + mainTitle);
    slimer.exit()
});*/

