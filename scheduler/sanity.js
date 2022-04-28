// sanity.js - Checks for invalid form entries on client side.
// Author: ubdussamad <ubdussamad at jeemail>
// License: MIT

function getCookie(cname) {
  var cookies = ` ${document.cookie}`.split(";");
  var val = "";
  for (var i = 0; i < cookies.length; i++) {
    var cookie = cookies[i].split("=");
    if (cookie[0] == ` ${cname}`) {
      return cookie[1];
    }
  }
  return "";
}


var cookies = document.cookie;

if (cookies) {
  console.log("Cookies found");
  console.log(cookies);
  var current_cookie = document.cookie;

  if (current_cookie.includes("agrred_to_cookie=true")) {
    console.log("Cookie accepted");
    if ( current_cookie.includes("usr_name")) {
      console.log("usr_name found");
      user = getCookie('usr_name');
      console.log(user);
      document.getElementById('usr_name').value = user;
      document.getElementById('sch_txt').value = user;
    }

}
} else {
  console.log("No cookies found");
  if (confirm("We use browser cookies to ONLY store your username so that it'd be easy for you to work with our tool.\n\n Please accept the use of cookies.")) {
    document.cookie = "agrred_to_cookie=true;path=/;SameSite=None; Secure";
  } else {
    document.cookie = "agrred_to_cookie=false;path=/;";
  }
}



function validate_job_submission() {
  var a = document.getElementById('job_name');
  var b = document.getElementById('usr_name');
  
  if ( a.value == '' || b.value == '' ) {
    alert("Empty Input fields.");
    return(false);
  }

  const regex = new RegExp('[;&|`^]');
  if ( regex.test( a.value ) ||  regex.test( b.value ) ) {
    alert("Only allowed characters are: [A-Z],[a-z],@,_ \n Please edit the input.");
    return(false);
  }

  var file = document.getElementById('file_name').files[0];

  if(file) {// perform the size check only if a file is present.
    if(file.size > 100 && file.size < 26214400  ) { // 50 MB (this size is in bytes)
        if (a != '' & b != '') {
          current_cookie = document.cookie;
          if (current_cookie.includes("agrred_to_cookie=true")) {
            document.cookie = current_cookie + ";usr_name=" + b.value + ";";
            console.log("Username cookie set");
            console.log(document.cookie);
          return(true);
        }       
    }
    else {
      alert("Inappropriate file size! (1MB-50MB)");
      return false;
    }
  }
  else {
    alert("Please select a file first!");
    return false;
  }
}
}
function validate_job_search() {
  var sch_str = document.getElementById('sch_txt');
  const regex = new RegExp('[;&|`^]');

  if ( regex.test( sch_str.value ) ) {
    alert("Invalid characters in input. \nOnly allowed characters are: [A-Z],[a-z],@,_ \n Please edit the input.");
    return(false);
  }

  if (sch_str.value != '') {
    return (true);
  }

  else {
    alert("Empty Search query.");
    return(false);
  }
}
