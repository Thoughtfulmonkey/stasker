
// Confirmation of delete
function confirmDelete (page, ref){
	var response = confirm('Are you sure you want to delete? This cannot be undone.');
	if (response) window.location.href = page+"?delete="+ref;
}

// Display available parameters
function displayParams (){
	window.open('param-small.php', 'Parameters', 'width=400, height=300, location=no, menubar=no, resizable=yes, status=no, toolbar=no');
}