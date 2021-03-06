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
// FEIDIAN Font File Module
//-----------------------------------------------------------------------------
// This module is used for handling font files (BDF or FD).
//-----------------------------------------------------------------------------

//-----------------------------------------------------------------------------
// makefd - converts bitmap to FD font descriptor
//-----------------------------------------------------------------------------
function makefd($tile_width, $tile_height, $vwf, $spacing, $descent, $in_file, $out_file){
  $rows=16; $columns=16;
  if(GRAPHIC_FORMAT=="xpm")
    $bitmap = xpm2bitstring($in_file, 0);
  elseif(GRAPHIC_FORMAT=="bmp") {
    list($img_width, $img_height, $bpp) = getbmpinfo($in_file);
    if($bpp==4) {
      die(print "ERROR: Fonts can only be made from monochrome images!\n");
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

  $ptr=0; $bitplane = "";
  print "  Converting bitmap to bitplane...\n";
  for ($k=0; $k<$rows; $k++) {
    for ($i=0; $i<$tile_height; $i++) {
      for ($z=0; $z<$columns; $z++) {
        $tile[$z][$i] = strrev(substr($bitmap, $ptr, $tile_width));
        $ptr += $tile_width;
      }
    }
    for ($z=$columns-1; $z>-1; $z--) {
      for ($i=0; $i<$tile_height; $i++) {
        $bitplane .= $tile[$z][$i];
      }
    }
    unset($tile);
  }
  include("settings.php");
  $output = "facename " . $fontname . "\ncopyright " . $copyright . "\n\nheight " . $tile_height . "\nascent " . ($tile_height-$descent) . "\n\n";
  for($i=0; $i<256; $i++)
    $tilebank[$i] = substr($bitplane, $i*(($tile_width*$tile_height)), (($tile_width*$tile_height)));
  for($i=0; $i<256; $i++) {
    if($vwf==0){
      $output .= "char $i\nwidth $tile_width\n" . wordwrap($tilebank[$i], $tile_width, "\n", 1) . "\n\n";
    }
    elseif($vwf==1) {
      list($thistile, $tilewidth) = makevwftile($tilebank[$i], $tile_width, $spacing);
      $output .= "char $i\nwidth $tilewidth\n" . wordwrap($thistile, $tilewidth, "\n", 1) . "\n\n";
    }
  }
  $fo=fopen($out_file, "wb");
  fputs($fo, $output);
  fclose($fo);
}

//-----------------------------------------------------------------------------
// makebdf - converts bitmap to BDF text font descriptor
//
// This function is based on Adobe's Glyph Bitmap Distribution Format (BD)
// Specification Version 2.2 from Adobe's Developer Support archive, dated
// March 22, 1993.
//-----------------------------------------------------------------------------
function makebdf($tile_width, $tile_height, $vwf, $spacing, $descent, $in_file, $out_file){
  $rows=16; $columns=16;
  if(GRAPHIC_FORMAT=="xpm")
    $bitmap = xpm2bitstring($in_file, 0);
  elseif(GRAPHIC_FORMAT=="bmp") {
    list($img_width, $img_height, $bpp) = getbmpinfo($in_file);
    if($bpp==4) {
      die(print "ERROR: Fonts can only be made from monochrome images!\n");
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

  $ptr=0; $bitplane = "";
  print "  Converting bitmap to bitplane...\n";
  for ($k=0; $k<$rows; $k++) {
    for ($i=0; $i<$tile_height; $i++) {
      for ($z=0; $z<$columns; $z++) {
        $tile[$z][$i] = strrev(substr($bitmap, $ptr, $tile_width));
        $ptr += $tile_width;
      }
    }
    for ($z=$columns-1; $z>-1; $z--) {
      for ($i=0; $i<$tile_height; $i++) {
        $bitplane .= $tile[$z][$i];
      }
    }
    unset($tile);
  }
  include("copyright.php");
  $output = "STATFONT 2.1\n" .
            "COMMENT Outputted by FEIDIAN\n" .
            "FONT $fontname\n" .
            "SIZE SIZE $tile_height 72 72\n" .
            "FONTBOUNDINGBOX $tile_width $tile_height 0 $descent\n" .
            "STARTPROPERTIES 6\n" .
            "PIXEL_SIZE $tile_height\n" .
            "RESOLUTION_X 72\n" .
            "RESOLUTION_Y 72\n" .
            "FONT_DESCENT $descent\n" .
            "FONT_ASCENT " . ($tile_height-$descent) . "\n" .
            "COPYRIGHT $copyright\n" .
            "ENDPROPERTIES\nCHARS 256\n";
  for($i=0; $i<256; $i++)
    $tilebank[$i] = substr($bitplane, $i*(($tile_width*$tile_height)), (($tile_width*$tile_height)));
  for($i=0; $i<256; $i++) {
    $output .= "STARTCHAR 00" . str_pad(dechex($i), 2, "0", STR_PAD_LEFT) . "\n" .
                           "ENCODING 1\n";
    if($vwf==0){
      $output .= "SWIDTH " . ($tile_width*30) . " 0\n" .
                 "DWIDTH $tile_width 0\n" .
                 "FONTBOUNDINGBOX $tile_width $tile_height 0 $descent\n" .
                 "BITMAP\n";
      $tile_lines = explode("\n", wordwrap($tilebank[$i], $tile_width, "\n", 1));
      for($k=0; $k<count($tile_lines); $k++){
        while(strlen($tile_lines[$k])%8!=0)
          $tile_lines[$k].="0";
        if(strlen($tile_lines[$k])>8){
          $tile_frags = explode("\n", wordwrap($tile_lines[$k], 8, "\n", 1));
          for($z=0; $z<count($tile_frags); $z++)
            $output .= str_pad(dechex(bindec($tile_frags[$z])), 2, "0", STR_PAD_LEFT);
          $output .= "\n";
        }
        else $output .= str_pad(dechex(bindec($tile_lines[$k])), 2, "0", STR_PAD_LEFT) . "\n";
      }
    }
    elseif($vwf==1) {
      list($thistile, $tilewidth) = makevwftile($tilebank[$i], $tile_width, $spacing);
      $output .= "SWIDTH " . ($tilewidth*30) . " 0\n" .
                 "DWIDTH $tilewidth 0\n" .
                 "FONTBOUNDINGBOX $tilewidth $tile_height 0 $descent\n" .
                 "BITMAP\n";
      $tile_lines = explode("\n", wordwrap($thistile, $tilewidth, "\n", 1));
      for($k=0; $k<count($tile_lines); $k++){
        while(strlen($tile_lines[$k])%8!=0)
          $tile_lines[$k].="0";
        if(strlen($tile_lines[$k])>8){
          $tile_frags = explode("\n", wordwrap($tile_lines[$k], 8, "\n", 1));
          for($z=0; $z<count($tile_frags); $z++)
            $output .= str_pad(dechex(bindec($tile_frags[$z])), 2, "0", STR_PAD_LEFT);
          $output .= "\n";
        }
        else $output .= str_pad(dechex(bindec($tile_lines[$k])), 2, "0", STR_PAD_LEFT) . "\n";
      }
    }
    $output .= "ENDCHAR\n";
  }
  $output .= "ENDFONT\n";
  $fo=fopen($out_file, "wb");
  fputs($fo, $output);
  fclose($fo);
}

?>
