// sanity.js - Checks for invalid form entries on client side.
// Author: ubdussamad <ubdussamad at jeemail>
// License: MIT


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
