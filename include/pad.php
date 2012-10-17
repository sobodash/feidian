<?php
/*
    FEIDIAN: The Freaking Easy, Indispensable Dot-Image formAt coNverter
    Copyright (C) 2003,2004 Derrick Sobodash
    Version: 0.6
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
// FEIDIAN Padding Module
//-----------------------------------------------------------------------------
// This is used to pad extra cols and rows onto tiles in a set without
// increasing the actual character size. Useful for toys like OCRs. :)
//-----------------------------------------------------------------------------

//-----------------------------------------------------------------------------
// padtile - pads a tile by cols,rows
//-----------------------------------------------------------------------------
function padtile($tile_width, $tile_height, $pad_width, $pad_height, $in_file, $out_file) {
	list($img_width, $img_height) = getbmpscale($in_file);
	$rows=$img_height/$tile_height;
	$columns=$img_width/$tile_width;
	// Create a file suffix specifying font width/height
	$prefix = $tile_width . "x" . $tile_height;
	print "Converting to bitplane...\n";

	$bitmap = strrev(binaryread($in_file, filesize($in_file)-62, 62, 0));

	$ptr=0; $bitplane = "";
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
	print ($rows*$columns) . " tiles read!\nPadding tiles...\n";
	print "  Padding by +" . $pad_width . "x" . $pad_height . " per tile...\n";
	$bit_rows=array(); $z=0;
	for($i=0; $i<strlen($bitplane); $i=$i+$tile_height){
		$bit_rows[$z]=substr($bitplane, $i, $tile_height);
		$z++;
	}
	$binarydump="";
	for($i=0; $i<count($bit_rows); $i++)
		//print "row $i\n";
		for($k=0; $k<$pad_width; $k++)
			$bit_rows[$i].="0";
	for($i=0; $i<count($bit_rows); $i++){
		if((($i+1)%$tile_height)==0){
			$binarydump.=$bit_rows[$i];
			for($z=0; $z<$pad_height; $z++)
				for($k=0; $k<($tile_width+$pad_width); $k++)
					$binarydump.="0";
		}
		else
			$binarydump.=$bit_rows[$i];
	}
	$bitmap = "";
	print "Converting back to bitmap...\n";
	$tile_width=$tile_width+$pad_width;
	$tile_height=$tile_height+$pad_height;
	$pointer=0;
	for ($k=0; $k<$rows; $k++) {
		for ($i=0; $i<$columns; $i++) {
			for ($z=0; $z<$tile_height; $z++) {
				// Grab $tile_width bits from the string and
				// store them as a row
				$line[$i][$z] = substr($binarydump, $pointer, $tile_width);
				$pointer = $pointer + $tile_width;
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
	// Transform back from bit string to data
	for($i=0; $i<strlen($bitmap)/8; $i++)
		$bitmap2 .= chr(bindec(substr($bitmap, $i*8, 8)));
	$bitmap = strrev($bitmap2);
	
	$fo = fopen($out_file . "_$prefix.BMP", "wb");
	fputs($fo, bitmapheader(strlen($bitmap), $tile_width*$columns, $rows*$tile_height) . strrev($bitmap));
	fclose($fo);
	print $out_file . "_$prefix.BMP was written!\n\n";
	

}
?>