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
// FEIDIAN Any Width Tile Module
//-----------------------------------------------------------------------------
// This module is used for dumping tiles in normal byte ordering but using
// odd line widths, like 7x8, 14x18, 12x12, etc.
//
// It is a little bit slower than the (8*) width routine, that is why we
// use these separate functions to handle dumping. No need to make everything
// lag, right?
//-----------------------------------------------------------------------------

//-----------------------------------------------------------------------------
// bit2bmp - converts bitplane tile data to bitmap
//-----------------------------------------------------------------------------
function bit2bmp($rows, $columns, $tile_height, $tile_width, $seekstart, $in_file, $out_file, $invert) {
	// Create a file suffix specifying font width/height
	$prefix = $tile_width . "x" . $tile_height;
	print "Dumping $prefix from $in_file...\n";
	
	$binarydump = binaryread($in_file, ($tile_height*$tile_width*$rows*$columns)/8, $seekstart, $invert);	
	
	$bitmap = "";
	print "  Converting to bitmap...\n";
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

//-----------------------------------------------------------------------------
// bit2tile - converts bitmap to bitplane tile data
//-----------------------------------------------------------------------------
function bit2tile($rows, $columns, $tile_height, $tile_width, $seekstart, $in_file, $out_file, $invert) {
	// Create a file suffix specifying font width/height
	$prefix = $tile_width . "x" . $tile_height;
	print "Injecting $prefix into $out_file...\n";

	$bitmap = strrev(binaryread($in_file, filesize($in_file)-62, 62, $invert));

	$ptr=0; $bitplane = "";
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
	$output = "";
	// Make sure the binary string is a multiple of 8
	while(strlen($bitplane)%8!=0)
		$bitplane .= "0";
	// Transform back from binary string to data
	for($i=0; $i<strlen($bitplane)/8; $i++)
		$output .= chr(bindec(substr($bitplane, $i*8, 8)));
	
	print "  Injecting new bitplane data...\n";
	injectfile($out_file, $seekstart, $output);
	
	print "$out_file was updated!\n\n";
}

?>
