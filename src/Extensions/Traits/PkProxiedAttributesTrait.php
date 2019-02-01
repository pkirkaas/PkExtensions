<?php
/*
 * To allow 2 related one-to-one models appear & behave as a single instance -
 * That is, where attibutes of object B appear to be part of object A, can be
 * set & fetched & saved on A, but really on B. This is used in A, which takes
 * care of B
 * 
 * Implementor A must have a static variable of classname for B;
 * public static::$proxiedClass="App\Models\Something;"
 * @author pkirkaas
 */
namespace PkExtensions\Traits;
use PkExtensions\PkModel;
trait PkProxiedAttributesTrait {
  #public static $except = ['name','email'];
  #Using classes may optionally have this static array
  /**
   * Returns table field/attribute names in B not in A, unless listed in argument
   * array $except, or in a static member array $except
   * @param array $except
   * @return idx array of keys to proxy 
   */

  public static $proxiedKeys = false;
  public $proxyInstance;
  static public function proxyAtts($except=[]) {
    if (static::$proxiedKeys === false) {
      if (property_exists(static::class,'except')) {
        $except = array_merge(static::$except, $except);
      }
      $proxiedClass=static::$proxiedClass;
        //$proxiedkeys = array_diff((static::$proxiedClass)::fieldNames(),
      static::$proxiedKeys = array_diff($proxiedClass::fieldNames(),
          $except,static::fieldNames());
    }
    return static::$proxiedKeys;
  }

  public function setProxyInstance(PkModel $proxyInstance) {
    $this->proxyInstance = $proxyInstance;
  }

  /** Part of PkModel __get()
   * MUST RETURN failure() if no match
   * @param type $key
   */
  public function __getPT($key) {
    if (in_array($key,static::proxyAtts(),1)) {
      return $this->proxyInstance->$key;
    } else {
      return failure();
    }
  }
  public function __setPT($key,$value) {
    if (in_array($key,static::proxyAtts(),1)) {
      return $this->proxyInstance->$key = $value;
    }
    return failure();
  }
  public static $allKeys=false;
  public static function combinedKeys() {
    if (static::$allKeys === false) {
      static::$allKeys = array_merge(static::fieldNames(),static::proxyAtts());
    }
    return static::$allKeys;
  }
  public function getAttributeNames($andPropertyNames = false) {
    $attNames = parent::getAttributeNames($andPropertyNames);
    $attNames = array_merge($attNames,static::proxyAtts());
    return $attNames;

  }


}