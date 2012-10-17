<?php
/*
    FEIDIAN: The Freaking Easy, Indispensable Dot-Image formAt coNverter
    Copyright (C) 2003 Derrick Sobodash
    Version: 0.3
    Web    : https://github.com/sobodash/feidian
    E-mail : derrick@sobodash.com

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program (license.txt); if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

//-----------------------------------------------------------------------------
// FEIDIAN Subs Module
//-----------------------------------------------------------------------------
// This is just frequently used chunks of code for file/io, etc, that I would
// rather not have to write 90x.
//-----------------------------------------------------------------------------

function binaryread($filename, $length, $offset) {
	$fd = fopen($filename, "rb");
	fseek($fd, $offset, SEEK_SET);
	$fddump = fread($fd, $length);
	fclose($fd); $binarydump = "";
	// Transform our dump to a long binary string, 8-bits per byte, and pad
	// left with zeros when needed
	for($i=0; $i<strlen($fddump); $i++) {
		$binarydump .= str_pad(decbin(hexdec(bin2hex(substr($fddump, $i, 1)))), 8, "0", STR_PAD_LEFT);
	}
	return($binarydump);
}

function fileread($filename, $length, $offset) {
	$fd = fopen($filename, "rb");
	fseek($fd, $offset, SEEK_SET);
	$fddump = fread($fd, $length);
	fclose($fd);
	return($fddump);
}

function injectfile($out_file, $seekstart, $bitplane) {
	$top=""; $bottom="";
	if (file_exists($out_file)){
		$fd = fopen($out_file, "rb");
		$top = fread($fd, $seekstart);	
		fseek($fd, $seekstart + strlen($bitplane), SEEK_SET);
		$bottom = fread($fd, filesize($out_file)-($seekstart + strlen($bitplane)));
		fclose($fd);
	}	
	$fo = fopen($out_file, "wb");
	fputs($fo, $top . $bitplane . $bottom);
	fclose($fo);
}

function bitmapheader($length, $width, $height) {
	// Base of the bitmap header
	$header = "BM" . pack("V*", $length+62) . pack("V*", 0) . pack("V*", 62);
	
	$info_header = pack("V*", 40) . pack("V*", $width) . pack("V*", $height) . chr(1) . chr(0) .
               	chr(1) . chr(0) . pack("V*", 0) . pack("V*", $length) . pack("V*", 0) .
               	pack("V*", 0) . pack("V*", 0) . pack("V*", 0) . pack("V*", 0);
               	
	// As this is a monochrome image, no palette is needed. We write the
	// first palette entry anyway just to appease the Bitmap gods
	$rgbquad = chr(0xff) . chr(0xff) . chr(0xff) . chr(0);
	
	return($header . $info_header . $rgbquad);
}

?>
