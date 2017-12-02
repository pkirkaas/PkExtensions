<?php
namespace PkExtensions;
use App\Models\User;
use \PkExtenstions\Models\PkModel;
use \PkExtenstions\PkCollection;
use \Request;
use \Auth;

/**
 * PkAjaxController - Base Ajax Controller - Doesn't do much...
 * @author pkirk
 */
abstract class PkAjaxController extends PkController {
  public $data;
  public $me;
  public function __construct() {
    if (method_exists(get_parent_class(),'__construct')) {
      parent::__construct();
    }
    header('content-type: application/json');
    $this->data = request()->all();
    if (class_exists('App\Models\User')) {
      $this->me = Auth::user();
      if (!User::instantiated($this->me)) {
        $this->me = null;
      }
    }
  }

  /** An AJAX controller just has to call $this->success($msg);
   * If it's a complicated msg, send an array; else a string. This
   * will arrify it, json it, & die
   * @param string|array $msg
   */
  public function success($msg = []) {
    if (!is_array($msg)) {
      $msg=['success'=>$msg];
    }
      die(json_encode($msg));
  }

  /** If msg is just a string, makes an array ['error'=>$msg], BUT ALSO 
   * sets the response code to 499 - my custom error code, handled by jQuery
   * @param string|array $msg
   */
  public function error($msg = null) {
    //http_response_code(499);
    //http_response_code(401);
    header('HTTP/1.1 499 Custom AJAX Request Error Message');
    if (!is_array($msg)) {
      $msg=['error'=>$msg];
    }
    die(json_encode($msg));
  }
/** For Vue or JS or jQuery calls to request model attribute values to 
   * populate templates.
   * Params: 
   * model: the PkModel Name
   * id: The ID for the model OR ARRAY OF IDS
   * method - opt - to call on the model
   * arg - opt - to call w. method
   * method2 - opt
   * arg2 - opt
   * tpl array - optional - a template for the key/values of the attributes
   *   to return, rather than everything - NOT IMPLEMENTED
   * 
   */
  public function attributes() {
    $data = request()->all();
    pkdebug("Data:",$data);
    $model = keyVal('model', $data);
    $method = keyVal('method', $data);
    $arg = keyVal('arg', $data);
    $method2 = keyVal('method2', $data);
    $arg2 = keyVal('arg2', $data);
    $id = to_int(keyVal('id', $data));
    $ids = keyVal('ids', $data); //A comma separated set of instance ids

    $getinstance = function($id) use ($model, $method,$arg,$method2, $arg2) {
      if (!($res=$model::find($id))) {
        return $this->error("Couldn't find an instance of $model with id $id");
      }
      if ( ($res instanceOf PkModel) && !$res->authRead()) {
        return $this->error("Not allowed to see this data");
      }
      if ($method) {
        $res = $res->$method($arg);
      }
      if ($method2) {
        $res = $res->$method2($arg2);
      }
      return $res;
    }

    #The onlything we really need is the model
    if (!$model || !is_subclass_of($model, PkModel::class, 1)) {
      pkdebug("Error: Model: '$model', ID:",$id);
      return $this->error("Invalid Model [$model]");
    }
    if ($ids && is_string($ids)) {
      $ids = explode(',',$ids);
      $res = new PkCollection();
      foreach ($ids as $id) {
        $res[]=$getinstance($id);
      }
    } else if ($id) { # $res is a model; if we have $id, we're looking for an instance
      $res = $getinstance($id);
    } else {  We have a model, not an instance
      $res = $model::$method($arg); #This should have given us an instance
      if ($method2) {
        $res = $res->$method2($arg2);
      }
    }
    if (!$res) {
      return $this->success([]);
    }
    #$res should be a PKModel or PkCollection of PkModels
    //pkdebug("The Res Atts:",$res->getCustomAttributes());
    return $this->success($res->getCustomAttributes());
  }



}
