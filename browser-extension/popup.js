machineid                 = localStorage['machineid'] || Math.random().toString(36).replace(/[^a-z]+/g, '').substr(0, 16);
localStorage['machineid'] = machineid;
status                    = localStorage['status'] || 'nonauthorized';
localStorage['status']    = status;
authurl                   = "https://telegram.me/trafficrobot?start=AUTH_"+machineid;
qrauthurl                 = "http://qrcoder.ru/code/?" + encodeURIComponent(authurl) + '&4&0';







function renderStatus(statusText) {
  ///document.getElementById('status').textContent = statusText;
  $('#status').html(statusText);
}

document.addEventListener('DOMContentLoaded', function() {
  renderStatus("machineid = " + machineid);

    $.ajax({
          method: "GET",
          dataType: "JSON",
          url: "https://trafficrobot.tk/browser_extension_api/get_auth_status/" + machineid
        })
        .done(function(msg) {
          if (msg.error) {
              $("#authurl").attr('href', authurl).text(authurl);
              $("#qrauthurl").attr('src', qrauthurl);
              $("#nonauthorized").show();
              status                 = 'nonauthorized';
              localStorage['status'] = status;
          }
          if (msg.ok) {
              $("#authorized").show();
              status                 = 'authorized';
              localStorage['status'] = status;
          }
        });


});





