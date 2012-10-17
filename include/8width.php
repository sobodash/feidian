<?php
/*
    FEIDIAN: The Freaking Easy, Indispensable Dot-Image formAt coNverter
    Copyright (C) 2003, 2004 Derrick Sobodash
    Version: 0.8a
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
// FEIDIAN (8*)x* Tile Module
//-----------------------------------------------------------------------------
// This module is used for dumping tiles that have a width based on a multiple
// of 8. While these tiles could be dumped using the AnyWidth module, this
// routine is much faster since it's reading bytes instead of bits.
//
// Since it was written first, there was no sense in removing it and forcing
// such tiles to lag with the other module.
//-----------------------------------------------------------------------------

//-----------------------------------------------------------------------------
// tile2bmp - converts bitplane tile data to bitmap
//-----------------------------------------------------------------------------
function tile2bmp($rows, $columns, $tile_height, $tile_width, $seekstart, $in_file, $out_file) {
	// Create a file suffix specifying font width/height
	$prefix = $tile_width . "x" . $tile_height;
	print "Dumping $prefix from $in_file...\n";
	$fd = fopen($in_file, "rb");
	$bitmap = "";
	fseek($fd, $seekstart, SEEK_SET);
	print "  Converting to bitmap...\n";
	for ($k=0; $k<$rows; $k++) {
		for ($i=0; $i<$columns; $i++) {
			for ($z=0; $z<$tile_height; $z++) {
				$line[$i][$z] = fread($fd, $tile_width/8);
			}
		}
		for ($z=0; $z<$tile_height; $z++) {
			for ($i=$columns-1; $i>-1; $i--) {
				$bitmap .= strrev($line[$i][$z]);
			}
		}
	}
	$fo = fopen($out_file . "_$prefix.BMP", "wb");
	fputs($fo, bitmapheader_1bpp(strlen($bitmap), $tile_width*$columns, $rows*$tile_height) . strrev($bitmap));
	fclose($fo);
	print $out_file . "_$prefix.BMP was written!\n\n";
	fclose($fd);
}

//-----------------------------------------------------------------------------
// bmp2tile - converts bitmap to bitplane tile data
//-----------------------------------------------------------------------------
function bmp2tile($rows, $columns, $tile_height, $tile_width, $seekstart, $in_file, $out_file) {
	// Create a file suffix specifying font width/height
	$prefix = $tile_width . "x" . $tile_height;
	print "Injecting $prefix into $out_file...\n";
	//$bitmap = strrev(fileread($in_file, filesize($in_file)-62, 62));
	$fd = fopen($in_file, "rb");
	fseek($fd, 62, SEEK_SET);
	$bitmap = strrev( fread($fd, filesize($in_file)-62) );
	fclose($fd);
	$ptr = 0;
	$bitplane = "";	
	print "  Converting bitmap to bitplane...\n";
	for ($k=0; $k<$rows; $k++) {
		for ($i=0; $i<$tile_height; $i++) {
			for ($z=0; $z<$columns; $z++) {
				$tile[$z][$i] = substr($bitmap, $ptr, $tile_width/8); $ptr += $tile_width/8;
			}
		}
		for ($z=$columns-1; $z>-1; $z--) {
			for ($i=0; $i<$tile_height; $i++) {
				$bitplane .= strrev($tile[$z][$i]);
			}
		}
		unset($tile);
	}

	print "  Injecting new bitplane data...\n";
	injectfile($out_file, $seekstart, $bitplane);
	
	print "$out_file was updated!\n\n";
}

?>
