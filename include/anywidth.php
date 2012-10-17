<?php
/*
    FEIDIAN: The Freaking Easy, Indispensable Dot-Image formAt coNverter
    Copyright (C) 2003, 2004 Derrick Sobodash
    Version: 0.89
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
  if(GRAPHIC_FORMAT=="xpm") {
    $color0 = "#000000";
    $color1 = "#FFFFFF";
    $color2 = "#000000";
    $color3 = "#000000";
    $color4 = "#000000";
    $color5 = "#000000";
    $color6 = "#000000";
    $color7 = "#000000";
    $color8 = "#000000";
    $color9 = "#000000";
    $colorA = "#000000";
    $colorB = "#000000";
    $colorC = "#000000";
    $colorD = "#000000";
    $colorE = "#000000";
    $colorF = "#000000";
    writexpm($bitmap, $tile_width*$columns, $rows*$tile_height, $out_file, $prefix, $color0, $color1, $color2, $color3, $color4, $color5, $color6, $color7, $color8, $color9, $colorA, $colorB, $colorC, $colorD, $colorE, $colorF);
  }
  elseif(GRAPHIC_FORMAT=="bmp") {
    $bitmap2 = "";
    $bitmap = strrev($bitmap);
          
    // Transform the string from bits to bytes using the chr() command
    // (Note: It's faster than pack() in this case)
    for($i=0; $i<strlen($bitmap)/8; $i++)
      $bitmap2 .= chr(bindec(substr($bitmap, $i*8, 8)));
    $bitmap = strrev($bitmap2);
     
    $fo = fopen($out_file . "_$prefix.bmp", "wb");
    fputs($fo, bitmapheader_1bpp(strlen($bitmap), $tile_width*$columns, $rows*$tile_height) . strrev($bitmap));
    fclose($fo);
    print $out_file . "_$prefix.bmp was written!\n\n";
  }
  else die(print "FATAL ERROR: You haven't defined an image format! Please check your setings.php\n");
}

//-----------------------------------------------------------------------------
// bit2tile - converts bitmap to bitplane tile data
//-----------------------------------------------------------------------------
function bit2tile($rows, $columns, $tile_height, $tile_width, $seekstart, $in_file, $out_file, $invert) {
  // Create a file suffix specifying font width/height
  $prefix = $tile_width . "x" . $tile_height;
  print "Injecting $prefix into $out_file...\n";

  if(GRAPHIC_FORMAT=="xpm")
    $bitmap = xpm2bitstring($in_file, 0);
  elseif(GRAPHIC_FORMAT=="bmp")
    $bitmap = strrev(binaryread($in_file, filesize($in_file)-62, 62, $invert));
  else die(print "FATAL ERROR: You haven't defined an image format! Please check your setings.php\n");
  
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
