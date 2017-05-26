<?php
/**
 * August, 2015, Paul Kirkaas, paul.kirkaas@disney.com - initially for the DLR Project.
 * The abstract "attachment" class, which uses "Stapler". For convenience/generalizability,
 * all attached documents should be called "doc"
 */
namespace PkExtensions\Models;
use Codesleeve\Stapler\ORM\StaplerableInterface;
use Codesleeve\Stapler\ORM\EloquentTrait;
abstract class PkAttachmentModel extends PkModel    implements StaplerableInterface   {
  use EloquentTrait;

  public static function isValidUploadType($mimeType) {
    if (!$mimeType || !is_string($mimeType) || !in_array($mimeType, static::$validUploadTypes)) {
      return false;
    }
    return true;
  }
  
  /*
  public function __call($method, $args = []) {
     return parent::__call($method, $args);
  }
   * 
   */

}