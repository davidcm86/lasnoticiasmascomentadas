var page = require('webpage').create(),
  system = require('system');


page.open(system.args[1], function() {
    setTimeout(function() {
        page.injectJs('/var/www/lasnoticiasmascomentadas/webroot/js/jquery-3.2.1.js');
        
            var comments = page.evaluate(function(){
                    return jQuery('.fb_comments_count').text();
            })
        
        console.log('xxx' + comments);
        phantom.exit();
    }, 5000);
});