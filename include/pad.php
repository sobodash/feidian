<?php
/*

FEIDIAN: The Freaking Easy, Indispensable Dot-Image formAt coNverter

A tool for all kinds of tasks involving bitplane to bitmap graphic conversion.

Version:   0.90b
Author:    Derrick Sobodash <derrick@sobodash.com>
Copyright: (c) 2003, 2004, 2012 Derrick Sobodash
Web site:  https://github.com/sobodash/feidian/
License:   BSD License <http://opensource.org/licenses/bsd-license.php>

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
  if(($tile_width + $pad_width) % 2 > 0)
    die(print "ERROR: Please pad by a multiple width (height can be odd).\n");
  // Create a file suffix specifying font width/height
  $prefix = $tile_width . "x" . $tile_height;
  print "Converting to bitplane...\n";
  if(GRAPHIC_FORMAT=="xpm") {
    list($img_width, $img_height, $bpp, $palette) = getxpminfo($in_file);
    $bitmap = xpm2bitstring($in_file, 0);
  }
  elseif(GRAPHIC_FORMAT=="bmp") {
    list($img_width, $img_height, $bpp) = getbmpinfo($in_file);
    if($bpp==4) {
      $bitmap = strrev(hexread($in_file, filesize($in_file)-0x76, 0x76));
      // The BMP standard forgets to mention bitmap is based on WORDs, not BYTEs.
      // This is a fix to chop off the padding added to a tile to force
      // compliance with the bitmap format.
      if(($columns*$tile_width)%8 >0) {
        $tempmap = "";
        $true_row = $tile_width*$columns;
        while($true_row%8 > 0)
          $true_row++;
        for($i=0; $i<($tile_height*$rows); $i++) {
          $temp = substr($bitmap, ($i*$true_row), ($true_row));
          $temp = substr($temp, $true_row-($tile_width*$columns), ($tile_width*$columns));
          $tempmap .= $temp;
        }
        $bitmap = $tempmap;
      }
    }
    else {
      $bitmap = strrev(binaryread($in_file, filesize($in_file)-62, 62, $invert));
      // The BMP standard forgets to mention bitmap is based on WORDs, not BYTEs.
      // This is a fix to chop off the padding added to a tile to force
      // compliance with the bitmap format.
      if(($columns*$tile_width)%32 >0) {
        $tempmap = "";
        $true_row = $tile_width*$columns;
        while($true_row%32 > 0)
          $true_row++;
        for($i=0; $i<($tile_height*$rows); $i++) {
          $temp = substr($bitmap, ($i*$true_row), ($true_row));
          $temp = substr($temp, $true_row-($tile_width*$columns), ($tile_width*$columns));
          $tempmap .= $temp;
        }
        $bitmap = $tempmap;
      }
    }
  }
  else die(print "FATAL ERROR: You haven't defined an image format! Please check your setings.php\n");
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
  print ($rows*$columns) . " tiles read!\nPadding tiles...\n";
  print "  Padding by +" . $pad_width . "x" . $pad_height . " per tile...\n";
  $bit_rows=array(); $z=0;
  for($i=0; $i<strlen($bitplane)/$tile_width; $i++){
    $bit_rows[$i]=substr($bitplane, $i*$tile_width, $tile_width);
  }
  $binarydump="";
  for($i=0; $i<count($bit_rows); $i++)
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
  
  if(GRAPHIC_FORMAT=="xpm") {
    writexpmpal($bitmap, $tile_width*$columns, $rows*$tile_height, $out_file, $prefix, $palette);
  }
  elseif(GRAPHIC_FORMAT=="bmp") {
    // Transform back from bit string to data
    if($bpp==4) {
      // The BMP standard forgets to mention bitmap is based on WORDs, not BYTEs.
      // This is a fix to chop off the padding added to a tile to force
      // compliance with the bitmap format.
      if(($tile_width*$columns)%8 > 0) {
        $tempmap = "";
        for($i=0; $i<($tile_height*$rows); $i++) {
          $temp = substr($bitmap, ($i*$tile_width*$columns), ($tile_width*$columns));
          while(strlen($temp) % 8 > 0)
            $temp = "0" . $temp;
          $tempmap .= $temp;
        }
        $bitmap = $tempmap;
      }
    
      $bitmap2 = "";
      $bitmap = strrev($bitmap);
      
      for($i=0; $i<strlen($bitmap)/2; $i++)
        $bitmap2 .= chr(hexdec(substr($bitmap, $i*2, 2)));
    }
    else {
      // The BMP standard forgets to mention bitmap is based on WORDs, not BYTEs.
      // This is a fix to chop off the padding added to a tile to force
      // compliance with the bitmap format.
      if(($tile_width*$columns)%32 > 0) {
        $tempmap = "";
        for($i=0; $i<($tile_height*$rows); $i++) {
          $temp = substr($bitmap, ($i*$tile_width*$columns), ($tile_width*$columns));
          while(strlen($temp) % 32 > 0)
            $temp = "0" . $temp;
          $tempmap .= $temp;
        }
        $bitmap = $tempmap;
      }
    
      $bitmap2 = "";
      $bitmap = strrev($bitmap);
      
      for($i=0; $i<strlen($bitmap)/8; $i++)
        $bitmap2 .= chr(bindec(substr($bitmap, $i*8, 8)));
    }
    $bitmap = strrev($bitmap2);
    
    // Create a file suffix specifying font width/height
    $prefix = $tile_width . "x" . $tile_height;
    
    $fo = fopen($out_file . "_$prefix.bmp", "wb");
    if($bpp==4) {
      $fd=fopen($in_file, "rb");
      fseek($fd, 0x36, SEEK_SET);
      $palette=fread($fd, 64);
      fclose($fd);
      fputs($fo, bitmapheader_xbpp(strlen($bitmap), $tile_width*$columns, $rows*$tile_height, $palette) . strrev($bitmap));
    }
    else {
      fputs($fo, bitmapheader_1bpp(strlen($bitmap), $tile_width*$columns, $rows*$tile_height) . strrev($bitmap));
    }
    fclose($fo);
    print $out_file . "_$prefix.bmp was written!\n\n";
  }
  else die(print "FATAL ERROR: You haven't defined an image format! Please check your setings.php\n");
}

?>
