<?php
/** Helper functions for use with Laravel/PkExtensions
 * Depends on function file pklib.php
 * 26 Feb 2016
 * Paul Kirkaas
 */
use Carbon\Carbon;
//use PkLibConfig; #Defined in pklib

#Test update

function setAppLog() {
  PkLibConfig::setSuppressPkDebug(false);
  $logDir = realpath(storage_path().'/logs');
  $logPath = $logDir.'/pkapp.log';
  appLogPath($logPath);
}

/**
 * Returns a user friendly string date format for a Carbon date object, with default
 * format or given - BUT also returns "Never" if Carbon date is max/min - or null.
 * @param Carbon $date
 * @param string $format
 */
function friendlyCarbonDate(Carbon $date = null, $format =  'M j, Y') {
  if (!$date) return "Never";
  $min = Carbon::minValue();
  $max = Carbon::maxValue();
  if ($max->eq($date) || $min->eq($date)) return "Never";
  return $date->format($format);
}
/**
 * 
 * @param countable $items
 * @param string $word
 * @return string - count of items, with 'No" for 0, singular for 1, else plural
 */
function numberItems($items,$word) {
  $wordform = str_plural($word);
  if (!$items || !count($items)) {
    $num = "No";
  } else {
    $num = count($items);
    if ($num === 1) $wordform = str_singular($word);
  }
  return "<div class='num-items'>$num </div> <div class='item-desc'>$wordform</div>";
}

##View Helpers
function pk_showcheck($val = null, $checked = "&#9745;", $unchecked ="&#9744;", $styles = []) {
  $defaultstyles = ['font-size'=>'xx-large','margin'=>'auto','color'=>'black', 'text-align'=>'center'];
  $stylestr = '';
  foreach ($defaultstyles as $key => $value) {
    $stylestr.="$key:$value; ";
  }
  //$stylestr = implode (';',$defaultstyles);
  if ($val) return "<div style='$stylestr'> $checked </div>";
   return "<div style='$stylestr'>$unchecked</div>";
}

function div($content,$attributes) {
  return PkHtml::tag('div', $content, $attributes);
}
