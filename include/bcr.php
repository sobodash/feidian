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
// FEIDIAN Psudo-OCR
//-----------------------------------------------------------------------------
// This orutine will match tiles in one graphic to tiles in another. Useful
// for recyclign a table when two games share a font but have different tile
// orders.
//-----------------------------------------------------------------------------

//-----------------------------------------------------------------------------
// ocrtile
//-----------------------------------------------------------------------------
function ocrtile($tile_height, $tile_width, $source_file, $text_rep, $in_file, $out_file) {
	// Read the source to an array
	print "Reading characters from $source_file to array...\n";
	list($img_width, $img_height, $bpp) = getbmpinfo($source_file);
	if($bpp==4) {
		$bitmap = strrev(hexread($in_file, filesize($source_file)-0x76, 0x76));
	}
	else {
		$bitmap = strrev(binaryread($in_file, filesize($source_file)-62, 62, 0));
	}
	$rows=$img_height/$tile_height;
	$columns=$img_width/$tile_width;
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
	for($i=0; $i<($rows*$columns); $i++) {
		$output="";
		$temp=substr($bitplane, ($i*($tile_height*$tile_width)), ($tile_height*$tile_width));
		while(strlen($temp)%8!=0)
			$temp .= "0";
		// Transform back from binary string to data
		for($z=0; $z<strlen($temp)/8; $z++)
			$output .= chr(bindec(substr($temp, $z*8, 8)));
		$source_tile_array[$i] = $output;
	}
	unset($i, $bitplane, $rows, $columns, $ptr, $z, $img_width, $img_height);
	
	// Read the textual representation to an array
	print "Reading text equivalents $text_rep to array...\n";
	$fd=fopen($text_rep, "rb");
	$fddump=fread($fd, filesize($text_rep));
	fclose($fd);
	$fddump=str_replace("\r\n", "", $fddump);
	for($i=0; $i<(strlen($fddump)/2); $i++)
		$source_text_array[$i]=substr($fddump, $i*2, 2);
	unset($fddump, $i, $fd);
	
	// Read the target to an array
	print "Reading characters from $in_file to array...\n";
	list($img_width, $img_height, $bpp) = getbmpinfo($in_file);
	if($bpp==4) {
		$bitmap = strrev(hexread($in_file, filesize($in_file)-0x76, 0x76));
	}
	else {
		$bitmap = strrev(binaryread($in_file, filesize($in_file)-62, 62, 0));
	}
	$rows=$img_height/$tile_height;
	$columns=$img_width/$tile_width;
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
	for($i=0; $i<($rows*$columns); $i++) {
		$temp=substr($bitplane, ($i*($tile_height*$tile_width)), ($tile_height*$tile_width));
		$output="";
		while(strlen($temp)%8!=0)
			$temp .= "0";
		// Transform back from binary string to data
		for($z=0; $z<strlen($temp)/8; $z++)
			$output .= chr(bindec(substr($temp, $z*8, 8)));
		$target_tile_array[$i] = $output;
		//$target_tile_array[$i] = gzcompress(substr($bitplane, ($i*($tile_height*$tile_width)), ($tile_height*$tile_width)));
	}
	unset($i, $bitplane, $ptr, $z, $img_width, $img_height);
	
	// Start the comparison
	print "Looking for matched tiles... be patient...\n";
	$output_table="";
	$found=0; $nfound=0;
	for($i=0; $i<count($target_tile_array); $i++) {
		$key = array_search($target_tile_array[$i], $source_tile_array);
		if ($key!==FALSE) {
			$output_table.=$source_text_array[$key];
			$found++;
		}
		else {
			$output_table.=chr(0xa1) . chr(0xbd);
			$nfound++;
		}
		unset($key);
	}
	print "  Found $found tiles\n  Failed to find $nfound tiles\n";
	
	// Write the output
	print "Writing table to $out_file...\n";
	$fo=fopen($out_file, "wb");
	fputs($fo, wordwrap($output_table, (2*$columns), "\r\n", 1));
	fclose($fo);
}

?>
