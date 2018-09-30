var page = require("webpage").create(),system = require('system');
var url = system.args[1];
console.log('url: ' + url);
page.onCallback = function(data){
    if (data.type === "exit") {
        phantom.exit();
    }
};
page.onConsoleMessage = function(msg){
    console.log("remote: " + msg);
};
page.open(url, function(status) {
    console.log(status);
    if (status == "success"){
        console.log(status);
        if (page.injectJs("/var/www/lasnoticiasmascomentadas/webroot/js/jquery-3.2.1.js")){
            console.log("jquery included");
            setTimeout(function(){
                page.evaluate(function(){
                    console.log("xxx" + $('.livefyre-commentcount').attr("lf-total-count"));
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