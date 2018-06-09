



chrome.runtime.onMessage.addListener(
    function(request, sender, sendResponse) {
        if (request.greeting == "hello")
            sendResponse({status : localStorage['status'], farewell: "goodbye!!", "machineid" : localStorage['machineid']});
    });