<?php
/**
 * PkController - Base Controller that provides some common
 * methods
 *
 * @author Paul.Kirkaas@gmail.com
 */
namespace PkExtensions;
use App\Http\Controllers\Controller;
use Illuminate\Support\MessageBag;
use PkExtensions\Models\PkModel;
use Request;
class PkController extends Controller {




  /** Submits POST data to the PkModel instance to save updates. 
   * 
   * @param \App\Extensions\Models\PkModel OR Collecton/Array of such $pkmodel
   * @param array $inits - Associative array of supplimental data to submit
   * @param string|null $modelkey - If we have an array of models to process, what is the post key for them?
   * @return type
   */
  /** Experimenting with handling array/collections of models - then, the 
   * 'Submit' button has the name 'modelset', and the fully namespaced value of
   * the model class. We need to get the original set of models, because we don't
   * want to delete models that didn't belong to that collection in the first place...
   * @param PkModel $pkmodel
   * @param array $inits
   * @param type $modelkey
   * @return booleankkk
   */
  public function processSubmit( $pkmodel, Array $inits = [], $modelkey = null) {
    if (Request::method() != 'POST') return null;
       #We are processing a submission
    /*
      #Processing a POST - what to do? Look at args:
      if (is_string($pkmodel) && class_exists($pkmodel,1)
              && is_subclass_of($pkmodel, 'App\\Models\\PkModel')) { #It's a PkModel name
        #So what do we do with that?
      }
     */
      $data = Request::all();
      $tpkm = typeOf($pkmodel);
      pkdebug("TPO: [$tpkm]");
      //if (!$pkmodel) return false;
      if ($pkmodel instanceOf PkModel) {
        if (is_array($inits)) foreach ($inits as $key => $val) {
          $data[$key] = $val;
        }
        pkdebug("The POST:", $_POST, 'DATA:', $data);
        $result = $pkmodel->saveRelations($data);
        return $result;
      }

      if (is_arrayish($pkmodel) && ($modelName = $this->isModelSetSubmit())) {
         #Then we look for a key of 'modelset' in the $data array, which
         #should have the value of a full model name 'App\Models\Item'
         #THEN we look for the Model Name Key in the $data - name the
         #controls by name='App\Models\Item[$idx][id]', etc
        $modelDataArray = keyValOrDefault($modelName,$data,false);
        if ($modelDataArray === false) return false;
        if ((!is_arrayish($modelDataArray) || !count($modelDataArray)) && 
                !count($pkmodel)) return false;
        if (!is_subclass_of($modelName, 'App\Extensions\Models\PkModel')) throw new Exception ("[$modelName] does not extend PkModel");
        #We assume $pkmodel is a collection of the original models, and $modelDataArray
        #contains whatever changes/additions/deletions. We hand off to the Model
        #class to manage.
        return $modelName::updateModels($pkmodel, $modelDataArray);
      }
      throw new \Exception ("Don't know what to do with pkmodel: ".print_r($pkmodel,1));
  }

  /** Not an action - but checks if the POST/Submission is for an
   * array/collection of models without an owner. It does this by checking
   * if the POST key 'modelset' exists - which should have the value of the
   * fully qualified 'App\Models\Item' model name or whatever.
   * @return false | ModelName
   */
  public function isModelSetSubmit() {
    if(Request::method() !== 'POST') return false;
    $data = Request::all();
    return keyValOrDefault('modelset', $data,false);
  }

  /**
   * THIS IS NOT AN ACTION - The route('error') should lead to an action, by 
   * default, the "displayerror" action below...
   * Redirects to error report page
   * @param string $msg - the error to report
   * @return Redirect Response
   */
  public function error($msg) {
      return redirect()->route('showerror')->withError(new MessageBag(['error'=>$msg]));
  }

  /** Ideally, the error will NOT be in the URL, but in the flashed message bag
   * 
   * @param type $error
   * @return Redirected to the error page with appropriate error msg.
   */
  public function showerror($error=null) {
  if ($error === null) $error = \Session::get('error');
    if (! $error instanceOf MessageBag) {
      if (is_string($error)) $error = new MessageBag(['error'=>$error]);
      else $error = new MessageBag(['error' => print_r($error,1)]);
    }
		return view('showerror', ['error'=>$error]);
  }

  public function message($msg) {
    return redirect()->route('showmessage')->withMessage(new MessageBag(['message'=>$msg]));
  }

  public function showmessage($message=null) {
  if ($message === null) $message = \Session::get('message');
    if (! $message instanceOf MessageBag) {
      if (is_string($message)) $message = new MessageBag(['message'=>$message]);
      else $message = new MessageBag(['message' => print_r($message,1)]);
    }
		return view('showmessage', ['message'=>$message]);
  }
}
