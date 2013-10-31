<?php
if (!defined("DB_USER")){ 
	if (file_exists("./wp-config.php")){@include("./wp-config.php");}
	elseif (file_exists("../wp-config.php")){@include("../wp-config.php");}
	elseif (file_exists("../../wp-config.php")){@include("../../wp-config.php");}
	elseif (file_exists("../../../wp-config.php")){@include("../../../wp-config.php");}
	elseif (file_exists("../../../../wp-config.php")){@include("../../../../wp-config.php");}
	elseif (file_exists("../../../../../wp-config.php")){@include("../../../../../wp-config.php");}
	elseif (file_exists("../../../../../../wp-config.php")){@include("../../../../../../wp-config.php");}
	elseif (file_exists("../../../../../../../wp-config.php")){@include("../../../../../../../wp-config.php");}
	elseif (file_exists("../../../../../../../../wp-config.php")){@include("../../../../../../../../wp-config.php");}

	$username=DB_USER;
	$password=DB_PASSWORD;
	$database=DB_NAME;
	$host=DB_HOST;
}

?>