<?php
$exists2=(class_exists("ZipArchive"))? "<span style='color:green'>exists" : "<span style='color:red'>doesn't exist" ;
print "'ZipArchive' class $exists2</span>, ";
$exists=(function_exists("zip_open"))? "<span style='color:green'>exists" : "<span style='color:red'>doesn't exist" ;
print "'zip_open' function $exists</span>";
?>