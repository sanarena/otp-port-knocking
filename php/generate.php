<?php

#This is your secure key, which must match with client.
$secure_keyword='type_a_secure_key_here';

#This is the file that knockd will read sequences from
$sequence_file='onetime_sequence_file.txt';

#This is the number of knocks needed to unblock ports
$sequence_length=4;

#Knock port range
$port_min=15000;
$port_max=25000;

#Get client request
$time=$_GET['time'];
$code=$_GET['code'];

#Validate client request
if ($time=='') $teapot=true;
if (round($time)==0) $teapot=true;
if ($time<(time()-3)) $teapot=true;
if (md5($_SERVER['REMOTE_ADDR'].$secure_keyword.$time)!=$code) $teapot=true;

#If client request cannot be verified, convert to a teapot
if (isset($teapot)&&$teapot){
	header("HTTP/1.1 418 I'm a teapot");
	?>
	<title>418 I'm a teapot</title>
	<p>This server refuses to brew coffee because it is, permanently, a teapot.</p>
	<?php
	die();
}

#Generate new random sequence
$codes=[];
for ($i=0; $i<=$sequence_length; $i++){
        $codes[]=mt_rand($port_min,$port_max).":".(mt_rand(0,1)==1?"tcp":"udp");
}
$new_otp=" ".implode(",",$codes);

#Save new knockd sequence file
file_put_contents($sequence_file,$new_otp);

#Display new sequence for client
echo str_replace(","," ",$new_otp);

#reload knockd server, so it load new sequence file
exec("/bin/sudo /sbin/service knockd restart");

?>