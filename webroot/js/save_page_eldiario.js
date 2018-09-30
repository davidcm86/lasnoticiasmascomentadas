var page = require("webpage").create(),system = require('system');
var url = system.args[1];
page.onCallback = function(data){
    if (data.type === "exit") {
        phantom.exit();
    }
};
page.onConsoleMessage = function(msg){
    console.log("remote: " + msg);
};
page.open(url, function(status) {
    if (status == "success"){
        if (page.injectJs("/var/www/lasnoticiasmascomentadas/webroot/js/jquery-3.2.1.js")){
            setTimeout(function(){
                page.evaluate(function(){
                    console.log('xxx' + $('#p-comments span').find('.show-comments').text());
                    window.callPhantom({type: "exit"});
                });
            }, 3000);
        }       
    }
    else{
        console.log(status);
        phantom.exit();
    }
});