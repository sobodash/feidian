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
// FEIDIAN BCR
//-----------------------------------------------------------------------------
// This routine will match tiles in one graphic to tiles in another. Useful
// for recycling a table when two games share a font, but have different tile
// orders.
//-----------------------------------------------------------------------------

//-----------------------------------------------------------------------------
// bcrtile
//-----------------------------------------------------------------------------
function bcrtile($tile_height, $tile_width, $source_file, $text_rep, $in_file, $out_file) {
  include("settings.php");
  // Read the source to an array
  print "Reading characters from $source_file to array...\n";
  if(GRAPHIC_FORMAT=="xpm")
    $bitmap = xpm2bitstring($in_file, 0);
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

  for($i=0; $i<($rows*$columns); $i++) {
    if($bpp==4) {
      $output="";
      $temp=substr($bitplane, ($i*($tile_height*$tile_width)), ($tile_height*$tile_width));
      // Transform back from binary string to data
      for($z=0; $z<strlen($temp)/2; $z++)
        $output .= chr(hexdec(substr($temp, $z*2, 2)));
    }
    else{
      $output="";
      $temp=substr($bitplane, ($i*($tile_height*$tile_width)), ($tile_height*$tile_width));
      while(strlen($temp)%8!=0)
        $temp .= "0";
      // Transform back from binary string to data
      for($z=0; $z<strlen($temp)/8; $z++)
        $output .= chr(bindec(substr($temp, $z*8, 8)));
    }
    if(HASH_METHOD=="md5", TRUE) $output=md5($output);
    elseif(HASH_METHOD=="sha1", TRUE) $output=sha1($output);
    elseif(HASH_METHOD=="crc32") $output=crc32($output);
    
    if(GZIP_TILES==TRUE) $source_tile_array[$i] = gzcompress($output);
    else $source_tile_array[$i] = $output;
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
  if(GRAPHIC_FORMAT=="xpm")
    $bitmap = xpm2bitstring($in_file, 0);
  elseif(GRAPHIC_FORMAT=="bmp") {
    if($bpp==4) {
      $bitmap = strrev(hexread($in_file, filesize($in_file)-0x76, 0x76));
    }
    else {
      $bitmap = strrev(binaryread($in_file, filesize($in_file)-62, 62, $invert));
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
  
  for($i=0; $i<($rows*$columns); $i++) {
    if($bpp==4) {
      $output="";
      $temp=substr($bitplane, ($i*($tile_height*$tile_width)), ($tile_height*$tile_width));
      // Transform back from binary string to data
      for($z=0; $z<strlen($temp)/2; $z++)
        $output .= chr(hexdec(substr($temp, $z*2, 2)));
    }
    else{
      $output="";
      $temp=substr($bitplane, ($i*($tile_height*$tile_width)), ($tile_height*$tile_width));
      while(strlen($temp)%8!=0)
        $temp .= "0";
      // Transform back from binary string to data
      for($z=0; $z<strlen($temp)/8; $z++)
        $output .= chr(bindec(substr($temp, $z*8, 8)));
    }
    if(HASH_METHOD=="md5", TRUE) $output=md5($output);
    elseif(HASH_METHOD=="sha1", TRUE) $output=sha1($output);
    elseif(HASH_METHOD=="crc32") $output=crc32($output);
    
    if(GZIP_TILES==TRUE) $target_tile_array[$i] = gzcompress($output);
    else $target_tile_array[$i] = $output;
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
      $output_table.=$not_found;
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
