<?php
 /** Generates test data - 
  */

namespace PkExtensions;

class PkTestGenerator {
#This belongs in a localized class, but will do for now
  public static $comments = [
      'She is doing better than I expected',
      'Very good progress since our previous session',
      "I'm concerned - she seems to be on a downward slope",
      "It's so harde for her to go through this alone, but she's much better off without him",
      "His substance abuse seems to be getting worse",
      "Excellent progress since last time",
      "The family is so poor they can't focus on other important issues",
      "The medication seems to be helping",
      "Her spirit is very good, under the circumstances",
      "We have to keep an eye on her progress and check which way she's going next time",

  ];
  public static function getRandomComment() {
    return static::getRandomData(static::$comments);
  }
  /**
   * Returns random data item (possibly with invalid data item) from an array 
   * @param array $dataArr
   * @param type $badData
   * @return single instance or array, depending on num items
   */
  public static function getRandomData( $dataArr, $items=1) {
    if (!$items) throw new \Exception("Invalid value or type for items");
    if (!is_arrayish($dataArr)) throw new \Exception("dataArr not arrayish");
    if (is_object($dataArr)) {
      if (method_exists($dataArr,'getArrayCopy')) {
        $copy = $dataArr->getArrayCopy();
      } else if (method_exists($dataArr, 'toArray')) {
        $copy = $dataArr->toArray();
      } else {
        $type = typeOf($dataArr);
        throw new \Exception("dataArr is type: $type");
      }
    }
    else $copy = (array) $dataArr;
    $keys = array_keys($copy);
    $numkeys = count($keys);
    if ($items === 1) {
      return $copy[$keys[mt_rand(0,  $numkeys - 1)]];
    }
    #More than one item to return; so an array. Unique menbers
    $retarr = [];
    for ($i = 0 ; $i < min($numkeys, $items) ; $i++) {
      $keysleft = array_keys($copy);
      $numkeysleft = count($keysleft);
      $key = $keysleft[mt_rand(0,  $numkeysleft - 1)];
      $retarr[] =  $copy[$key];
      unset($copy[$key]);
    }
    return $retarr;
  }


  public static $jobTitles = null;

  public static function getRandomJobTitle() {
    if (static::$jobTitles === null) {
      static::$jobTitles = require(__DIR__.'/References/JobTitles.php');
    }
    return static::getRandomData(static::$jobTitles);
  }



  /**
   * Returns a date within the the range (default: years)
   * @param integer $yrsFrom: Plus or Minus years from now
   * @param integer $yrsTo: Plus or Minus years from now. Default: Now
   * @param integer $multiplier - num days - default, 365, 1 yr
   * @return string: Pretty Date within the range
   */
  public static function getRandomUnixDateFromRange($from, $to=0, $multiplier = 365) {
    $from = $from * $multiplier;
    $to = $to * $multiplier;
    if ($from < $to) {
      $rndOffset = mt_rand($from, $to);
    } else {
      $rndOffset = mt_rand($to, $from);
    }
    $day = 24 * 60 * 60;
    $now = time();
    $rndTime = $now + ($rndOffset * $day);
    return $rndTime;
  }



  /**
   * 
   * @param int $from - num of units from now
   * @param int $to - num of units from now
   * @param int $multiplier - multiply from/to days - default, 365, so from/to are years
   * @return type
   */
  public static function getRandomSqlDateFromRange($from, $to=0, $multiplier = 365) {
    return static::sqlDateFromUnix(static::getRandomUnixDateFromRange($from, $to, $multiplier));
  }

  public static function getRandomSqlRecentDate($multiplier = 365) {
    return static::sqlDateFromUnix(static::getRandomUnixDateFromRange(0, -1, $multiplier));
  }

  public static function getRandomSqlBirthdate($multiplier = 365) {
    return static::sqlDateFromUnix(static::getRandomUnixDateFromRange(-20, -70, $multiplier));
  }

  public static function sqlDateFromUnix($unixDate) {
    //$mysqldate = date('Y-m-d H:i:s', $unixDate);
    $mysqldate = date('Y-m-d', $unixDate);
    return $mysqldate;
  }


######################  THIS DOESN'T WORK YET W. LARAVEL - BUT GET IT GOING.....
  /** 'Uploads' a random file from the directory, of 'type'
   * 
   * @param string $path - directory path
   * @param string $type - file object 'type' - 'image', 'audio', 'doc', etc.
   *     default: 'image'
   * @return int - the ID of the newly uploaded file object
   */
  //public static function importRandomFileFromDir($dir, $type = 'image') {
  public static $tmpdir = __DIR__.'/tmp';
  public static function getRandomFilePathFromDir($dir, $type = 'image') {
    pkdebug("DIR:  [$dir] ");
    if (!is_dir($dir)) {
      pkdebug("Couldn't resolve [$dir] to a directory");
      return null;
    }
    $tmpdir = static::$tmpdir;
    if (!is_dir($tmpdir)) {
      mkdir($tmpdir);
    }

    $entries = scandir($dir);
    pkdebug("Entries:  ", $entries);
    if (!is_array($entries) || !sizeOf($entries)) {
      pkdebug("No entries in [$dir]. Entries:", $entries);
      return null;
    }
    #Make array of valid file type paths from entries
    $paths = [];
    //$validentries = [];
    foreach ($entries as $entry) {
      $newpath = pkuntrailingslashit($dir) . "/$entry";
      if (!is_file($newpath)) continue;
     // $validentries[]=$entry;





//      $valid = BaseFileHandler::validateFiletype($newpath, $type);
 //     if ($valid === false) continue;
      $paths[] = $newpath;
    }
    pkdebug("For [$dir], got valid paths:", $paths);
    #We have a set of valid file paths of the required type. Pick one:
    $path = static::getRandomData($paths);
    $base = basename($path);
    $copypath = pkuntrailingslashit(static::$tmpdir)."/$base";
    copy($path,$copypath);
    //Copy it to 
    return $copypath;
    /*
  //  $relPath = BaseFileHandler::relPathFromFullPath($path);
    //pkdebug("Your rel path:", $relPath);
    $mimeType = getFileMimeType($newpath);
    $newFileObjArgs = [
        'mimetype' => $mimeType,
        'path' => $relPath,
        'type' => $type,
    ];

    $fileObj = BaseFileHandler::makeNewFileObj($newFileObjArgs);
    if (!($fileObj instanceOf BaseFile)) return false;
    //pkdebug("Wow - made a file obj!");
    $fileObj->save();
    $fileObjId = $fileObj->getId();
    return $fileObjId;
    #We have copied a file into our upload dir. Now let's make a file obj of it -
    //}
     * 
     */
  }












}
