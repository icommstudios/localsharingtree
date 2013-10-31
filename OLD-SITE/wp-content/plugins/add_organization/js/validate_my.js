function isValidEmailAddress(emailAddress) {
    var pattern = new RegExp(/^(("[\w-+\s]+")|([\w-+]+(?:\.[\w-+]+)*)|("[\w-+\s]+")([\w-+]+(?:\.[\w-+]+)*))(@((?:[\w-+]+\.)*\w[\w-+]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$)|(@\[?((25[0-5]\.|2[0-4][\d]\.|1[\d]{2}\.|[\d]{1,2}\.))((25[0-5]|2[0-4][\d]|1[\d]{2}|[\d]{1,2})\.){2}(25[0-5]|2[0-4][\d]|1[\d]{2}|[\d]{1,2})\]?$)/i);
    return pattern.test(emailAddress);
};
		function CheckFormData()
		{
		    var organization = document.getElementById("org_name");
		    var contact_name = document.getElementById("contact_name");
		    var email = document.getElementById("email");
	         
			if(organization.value == '')
			{
				alert('Fill any organization to continue');
				org_name.style.border = 'thin solid red';
				org_name.focus();
				return false;
			}
			
			return true;
		}
