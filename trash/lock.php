<?php
$path =dirname($_SERVER["SCRIPT_FILENAME"]); 
$a = fopen(".htaccess", "w");
fwrite($a, "AuthName \"Protected Administration Page\"\nAuthType Basic\nAuthUserFile $path/.htpasswd\nRequire valid-user");
fclose($a);
$login = 'guimon';
$pass = 'genichesk';
$encrypted_password = crypt($pass, base64_encode($pass));
 
// Print line to be added to .htpasswd file
echo $contents = $login . ':' . $encrypted_password;
file_put_contents('.htpasswd', $contents);

?>