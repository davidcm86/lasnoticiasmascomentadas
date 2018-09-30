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
                    console.log('href:' + $("div[id^='tab-mas-comentadas']").find('a').attr("href"));
                    console.log('comentarios:' + $("div[id^='tab-mas-comentadas']").find('p .livefyre-commentcount').first().text());
                    window.callPhantom({type: "exit"});
                });
            }, 5000);
        }       
    }
    else{
        phantom.exit();
    }
});