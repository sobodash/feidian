<?php
/*
    FEIDIAN: The Freaking Easy, Indispensable Dot-Image formAt coNverter
    Copyright (C) 2003, 2004 Derrick Sobodash
    Version: 0.85
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
// FEIDIAN Scale Module
//-----------------------------------------------------------------------------
// This is used only for upscaling a font a given multiple of times.
//-----------------------------------------------------------------------------

function scale($height, $width, $in_file, $out_file, $invert) {
  if(GRAPHIC_FORMAT=="xpm") {
    list($img_width, $img_height, $bpp, $palette) = getxpminfo($in_file);
    $bitmap = xpm2bitstring($in_file, 0);
  }
  elseif(GRAPHIC_FORMAT=="bmp") {
    list($img_width, $img_height, $bpp) = getbmpinfo($in_file);
    if($bpp==4) {
      $bitmap = strrev(hexread($in_file, filesize($in_file)-0x76, 0x76));
    }
    else {
      $bitmap = strrev(binaryread($in_file, filesize($in_file)-62, 62, $invert));
    }
  }
  else die(print "FATAL ERROR: You haven't defined an image format! Please check your setings.php\n");

  // Break the image rows into arrays. It's slow but easier.
  for($i=0; $i<$img_height; $i++)
    $line_array[$i] = substr($bitmap, $i*$img_width, $img_width);
  
  // We'll do the hscale first
  for($i=0; $i<count($line_array); $i++) {
    $currentrow = $line_array[$i];
    $templine="";
    for($k=0; $k<strlen($currentrow); $k++){
      for($j=0; $j<$width; $j++){
        $templine .= $currentrow[$k];
      }
    }
    $line_array[$i] = $templine;
    unset($templine);
  }
  
  // Now we'll do the vscale
  $bitmap="";
  for($i=0; $i<count($line_array); $i++) {
    //$currentline=$line_array[$i];
    for($k=0; $k<$height; $k++){
      $bitmap .= $line_array[$i];
    }
  }
  
  if(GRAPHIC_FORMAT=="xpm") {
    writexpmpal($bitmap, $img_width*$width, $img_height*$height, $out_file, $prefix, $palette);
  }
  elseif(GRAPHIC_FORMAT=="bmp") {
    $bitmap2 = "";
    $bitmap = strrev($bitmap);
    // Transform back from bit string to data
    if($bpp==4) {
      for($i=0; $i<strlen($bitmap)/2; $i++)
        $bitmap2 .= chr(hexdec(substr($bitmap, $i*2, 2)));
    }
    else {
      for($i=0; $i<strlen($bitmap)/8; $i++)
        $bitmap2 .= chr(bindec(substr($bitmap, $i*8, 8)));
    }  
    $bitmap = strrev($bitmap2);
    
    $fo = fopen($out_file . "_scaled.bmp", "wb");
    if($bpp==4) {
      $fd=fopen($in_file, "rb");
      fseek($fd, 0x36, SEEK_SET);
      $palette=fread($fd, 64);
      fclose($fd);
      fputs($fo, bitmapheader_xbpp(strlen($bitmap), $img_width*$width, $img_height*$height, $palette) . strrev($bitmap));
    }
    else {
      fputs($fo, bitmapheader_1bpp(strlen($bitmap), $img_width*$width, $img_height*$height) . strrev($bitmap));
    }
    fclose($fo);
    print $out_file . "_scaled.bmp was written!\n\n";
  }
  else die(print "FATAL ERROR: You haven't defined an image format! Please check your setings.php\n");
}

?>
