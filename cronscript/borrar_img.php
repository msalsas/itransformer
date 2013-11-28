<?php
/*
 * Itransformer.es is an online application to transform images
Copyright (C) 2013  Manolo Salsas

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU Affero General Public License as
published by the Free Software Foundation, either version 3 of the
License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU Affero General Public License for more details.

You should have received a copy of the GNU Affero General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.

Contact: manolez@gmail.com - http://msalsas.com
* */

$ruta_proy = __DIR__.'/..';
//path images
$ruta_imagenes = $ruta_proy. '/usuarios';


$handle_img = opendir($ruta_imagenes);
if ($handle_img == false) return -1;


while (($ifile = readdir($handle_img)) != false) {			
if($ifile!='.' && $ifile!='..')	{
$preg = 0;			
$handle = opendir($ruta_proy . '/app/cache/prod/sessions');
if ($handle == false) return -1;	
	while (($file = readdir($handle)) != false) {

		if (preg_match("/^sess/", $file)) {
		if(time()- fileatime(($ruta_proy . '/app/cache/prod/sessions/') . $file) < 1200) { // 1200 secs = 20 minutes session
			$idSess = substr($file,5);
			
			$dir = $ruta_imagenes.'/'.$ifile;
									
			if (preg_match("/^".$idSess.".*/", $ifile)) $preg = 1;
			
		}
		}
	}
	closedir($handle);	
	
	if($preg == 0){
		$dir = $ruta_imagenes.'/'.$ifile;
		if(!is_dir($dir)) unlink($dir);
		else {
			delTree($dir);
		}
	}
}	
}

closedir($handle_img);

			
function delTree($dir) { 
				
				$files = array_diff(scandir($dir), array('.','..'));
			    foreach ($files as $file) {
			      (is_dir("$dir/$file")) ? delTree("$dir/$file") : unlink("$dir/$file");
			    }
			    return rmdir($dir); 
			}



