var trafficrobot_status, trafficrobot_machineid;

var emailElements = [];


function colorize(){
    for (var i = 0; i < emailElements.length; i++) {
        emailElements[i].style.borderColor="green";
        emailElements[i].style.borderStyle =  "solid";
        emailElements[i].style.borderWidth = "2px";
    }

    setTimeout(function(){
        for (var i = 0; i < emailElements.length; i++) emailElements[i].style.borderColor="yellow";
    }, 500);

    setTimeout(function(){
        for (var i = 0; i < emailElements.length; i++) emailElements[i].style.borderColor="red";
    }, 1000);
}





setTimeout(function () {



    chrome.runtime.sendMessage({greeting: "hello"}, function(response) {
        trafficrobot_status = response.status;
        trafficrobot_machineid = response.machineid;


        if (trafficrobot_status != 'authorized') return; // nothing to do here

        var inputs = document.getElementsByTagName("input");
        for (var i = 0; i < inputs.length; i++) {
            var name = inputs[i].getAttribute("name");
            var type = inputs[i].getAttribute("type");
            var id = inputs[i].getAttribute("id");
            var placeholder = inputs[i].getAttribute("placeholder");

            if (
                name == 'email' || type == 'email' || id == 'email' ||
                (name && name.toLowerCase().indexOf('email') != -1) ||
                (placeholder && placeholder.toLowerCase().indexOf('email') != -1) ||
                (placeholder && placeholder.toLowerCase().indexOf('e-mail') != -1) ||
                (id && id.toLowerCase().indexOf('email') != -1)
            ){
                emailElements.push(inputs[i]);
            }
        }

        if (emailElements.length > 0){

            var x = new XMLHttpRequest();
            x.open('GET', 'https://trafficrobot.tk/browser_extension_api/get_email_for_domain/' + window.location.hostname  + '/' + trafficrobot_machineid);
            x.responseType = 'json';
            x.onload = function() {
                var response = x.response;
                if (!response || !response.ok) {
                    console.log('Error ' + response.error);
                    return;
                }

                if (response.ok){
                    for (var i = 0; i < emailElements.length; i++) {
                        if (emailElements[i].value == 'Email') emailElements[i].value = ''; // stupid exception
                        if (!emailElements[i].value) { // only if field is currently empty
                            emailElements[i].value = response.ok;
                        }
                    }
                    setInterval(function(){ colorize() }, 1500);
                    colorize();
                }
            };
            x.onerror = function() {
                console.log('Network error.');
            };
            x.send();



        }
    }); // end sendMessage






}, 500);