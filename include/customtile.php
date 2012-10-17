<?php
/*
    FEIDIAN: The Freaking Easy, Indispensable Dot-Image formAt coNverter
    Copyright (C) 2003,2004 Derrick Sobodash
    Version: 0.5
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
// FEIDIAN Custom Tile Module
//-----------------------------------------------------------------------------
// This module is used for dumping user defined, non-standard tile formats.
// The code is a little bit slow (there's a shitload of transformations and
// pattern matches needed to make this work) but on my 1GHz machine, most fonts
// were dumpable within 3 seconds.
//
// If you're insane, that might be too long for you. But for everyone else
// it should be fine.
//-----------------------------------------------------------------------------

//-----------------------------------------------------------------------------
// cust2bmp - converts a custom bitplane tile definition to bitmap
//-----------------------------------------------------------------------------
function cust2bmp($rows, $columns, $tiledef, $seekstart, $in_file, $out_file, $invert){
	// Include the user defined tile where we will get the replacement
	// patter, tile width, height, and byte count
	include("tiles/" . $tiledef . ".php");
	
	// Nuke all the user's whitespaces from the pattern
	$byte_order = preg_replace("/( *)/", "", $byte_order);
	$byte_order = preg_replace("/(\\r*)/", "", $byte_order);
	$byte_order = preg_replace("/(\\n*)/", "", $byte_order);
	$pattern_rows = strlen($byte_order)/$tile_width;
	$bytes = ($tile_width*$tile_height)/8;
	
	// Create a file suffix specifying font width/height
	$prefix = $tile_width . "x" . $tile_height;
	print "Dumping $prefix from $in_file...\n";
	$fd = fopen($in_file, "rb");
	$bitmap = "";
	fseek($fd, $seekstart, SEEK_SET);
	print "  Converting to bitmap...\n";
	$pointer=0;
	for ($k=0; $k<$rows; $k++) {
		for ($i=0; $i<$columns; $i++) {
			for ($z=0; $z<$tile_height; $z=$z+0) {
				// Get the number of bytes required for the
				// pattern to repeat
				$lenbyte = fread($fd, $pat_size);
				$temp_pattern = $byte_order;
				
				// Transform the bytes to bits, then place them
				// accordingly in our pattern string.
				for ($g=0; $g<strlen($lenbyte); $g++) {
					$binstring = str_pad(decbin(hexdec(bin2hex($lenbyte[$g]))), 8, "0", STR_PAD_LEFT);
					if($g<26) $offvar = 0x41;
					else if($g<52) $offvar = 0x61 - 26;
					else if($g<62) $offvar = 0x30 - 52;
					$lele = 0;
					while(strpos($temp_pattern, chr($offvar+$g)) !== FALSE) {
						$temp_pattern[strpos($temp_pattern, chr($offvar+$g))] = $binstring[$lele];
						$lele++;
					}
				}
				
				// Split the pattern string to rows and store
				// them to an array
				for($g=0; $g<strlen($temp_pattern)/$tile_width; $g++)
					$line[$i][$z+$g] = substr($temp_pattern, $g*$tile_width, $tile_width);
				$z=$z+$g;
			}			
		}
		for ($z=0; $z<$tile_height; $z++) {
			for ($i=$columns-1; $i>-1; $i--) {
				$bitmap .= strrev($line[$i][$z]);
			}
		}
	}
	$bitmap2 = "";
	$bitmap = strrev($bitmap);
	
	if ($invert==1) {
		$invertmap = "";
		for ($lala=0; $lala<strlen($bitmap); $lala++) {
			if ($bitmap[$lala]==0) $invertmap.=1;
			else if ($bitmap[$lala]==1) $invertmap.=0;
			else die("What'chu talkin' bout Willis?\n");
		}
		$bitmap = $invertmap;
	}
	
	// Transform the string from bits to bytes using the chr() command
	// (Note: It's faster than pack() in this case)
	for($i=0; $i<strlen($bitmap)/8; $i++)
		$bitmap2 .= chr(bindec(substr($bitmap, $i*8, 8)));
	$bitmap = strrev($bitmap2);
	
	$fo = fopen($out_file . "_$prefix.BMP", "wb");
	fputs($fo, bitmapheader(strlen($bitmap), $tile_width*$columns, $rows*$tile_height) . strrev($bitmap));
	fclose($fo);
	print $out_file . "_$prefix.BMP was written!\n\n";
}

function bmp2cust($rows, $columns, $tiledef, $seekstart, $in_file, $out_file, $invert) {
	// Include the user defined tile where we will get the replacement
	// patter, tile width, height, and byte count
	include("tiles/" . $tiledef . ".php");
	
	// Nuke all the user's whitespaces from the pattern
	$byte_order = preg_replace("/( *)/", "", $byte_order);
	$byte_order = preg_replace("/(\\r*)/", "", $byte_order);
	$byte_order = preg_replace("/(\\n*)/", "", $byte_order);
	$pattern_rows = strlen($byte_order)/$tile_width;
	$bytes = ($tile_width*$tile_height)/8;
	
	// Create a file suffix specifying font width/height
	$prefix = $tile_width . "x" . $tile_height;
	print "Injecting $prefix into $out_file...\n";

	$bitmap = strrev(binaryread($in_file, filesize($in_file)-62, 62, $invert));

	$ptr=0; $bitplane="";
	print "  Converting bitmap to bitplane...\n";
	for ($k=0; $k<$rows; $k++) {
		for ($i=0; $i<$tile_height; $i++) {
			for ($z=0; $z<$columns; $z++) {
				$tile[$z][$i] = substr($bitmap, $ptr, $tile_width);
				$ptr += $tile_width;
			}
		}
		for ($z=$columns-1; $z>-1; $z--) {
			for ($i=0; $i<$tile_height; $i++) {
				$bitplane .= strrev($tile[$z][$i]);
			}
		}
		unset($tile);
	}
	$hackplane = "";
	// The routine matches the characters in our pattern definition to
	// points in the binary string, then writes whatever bit is present to
	// a new byte string.
	for ($z=0; $z<strlen($bitplane); $z=$z+$pat_size*8) {
		$tiles = substr($bitplane, $z, $tile_width*$pat_size);
		$temp_pattern = array("","","");
		for($g=0; $g<$pat_size; $g++) {
			$posx = 0;
/*			// Obsoleted code for 26 byte tile definition
			// Ascend from A-Z
			while(strpos($byte_order, chr(0x41+$g), $posx) !== FALSE) {
				$getpos = strpos($byte_order, chr(0x41+$g), $posx);
				$temp_pattern[$g] .= $tiles[$getpos];
				$posx=$getpos+1;
			}*/
			// Ascend through A-Z, a-z, 0-9
			if($g<26) $offvar = 0x41;
			else if($g<52) $offvar = 0x61 - 26;
			else if($g<62) $offvar = 0x30 - 52;
			while(strpos($byte_order, chr($offvar+$g), $posx) !== FALSE) {
				$getpos = strpos($byte_order, chr($offvar+$g), $posx);
				$temp_pattern[$g] .= $tiles[$getpos];
				$posx=$getpos+1;
			}
		}
		for($g=0; $g<count($temp_pattern); $g++)
			$hackplane .= $temp_pattern[$g];
	}
	$bitplane = $hackplane;
	
	// Transform out bitstring to bytes by evaling every 8 bits within a
	// chr();
	$output = "";
	for($i=0; $i<strlen($bitplane)/8; $i++)
		$output .= chr(bindec(substr($bitplane, $i*8, 8)));

	print "  Injecting new bitplane data...\n";
	injectfile($out_file, $seekstart, $output);
	
	print "$out_file was updated!\n\n";
}

?>
