<?php
/** Extends PkHtmlRenderer, for explicit repeating subform (Items in Cart) 
 * rendering, with template, dependent upon pklib.js definitions
 * 
 * Should eventually support nesting, initially single one-many level
 * ... But still can be several per form/object
 */
namespace PkExtensions;
use PkHtml;
use PkForm;
use PkExtensions\Models\PkModel;

//if (!defined('RENDEROPEN')) define('RENDEROPEN', true);

/**
 * Usage: 
 * $subform = new PkMultiSubformRenderer;
 * $subform->basename = $tablename;
 * //Make the base subform
 * $subform->hidden('id');
 * $subform->text('name',$attributes);
 * $subform->text('ssn');
 * //$subform->$tpl_fields = ['id','name','ssn'];
 * $subform->subform_data = [
 *   ['id'=>7, 'name'=>'Joe', 'ssn'=>'555-33-4444',],
 *   ['id'=>9, 'name'=>'Jane', 'ssn'=>'666-22-8888',],
 *  ];
 * // (Generally, build $subform_data in a foreach )
 * echo $subform;
 * // If you want to keep the default classes/atts for pklib.js, but ADD css
 * // classes, use appent_atts():
//$subform->append_atts('create_button','tst-create-btn-class');
//$subform->append_atts('deletable_dataset','tst-dds-class');
//$subform->append_atts('js_template',['class'=>'tst-add-class-arr']);
 */
class PkMultiSubformRenderer  extends PkHtmlRenderer {
  /**
   * @var null|array  - idxd arr of assoc array of
   *    field-string  => values, as above
   */
  public $subform_data = null;
  /**Should be array of string template fieldnames to be converted
   * using "__FLD_TPL__$val"
   * eg, ['id','name','ssn'];
   * @var null|array
   */
  public $tpl_fields=null;
  public $cnt_tpl = '__CNT_TPL__';
  public $fld_tpl_prefix = '__FLD_TPL__';
  public $templatables_attributes = ['class'=>'templatable-data-sets'];
  public $deletable_dataset_attributes = 'deletable-data-set';
  public $create_button_label = 'Create';
  public $create_button_attributes = ['class'=>'js btn mf-btn pkmvc-button create-new-data-set'];
  public $create_button_tag = 'div';
  public $delete_button_label = 'Delete';
  public $create_button_wrap_tag = 'div';
  public $create_button_wrap_attributes = ['class'=>''];
  public $delete_button_wrap_tag = 'div';
  public $delete_button_wrap_attributes = ['class'=>''];
  public $delete_button_tag = 'div';
  public $delete_button_attributes = ['class'=>'js btn mf-btn pkmvc-button data-set-delete'];
  public $js_template_tag = 'fieldset';
  public $js_template_attributes=['disabled'=>true,'style'=>'display: none;', 'class'=>'template-container'];
  /** Generally, the table name */
  public $basename = null;
  /** Generally, the owner ID - like cart_id for 'items'
   * @var null|scalar  
   */
  public $owner_id = null;

  /** Generally want to set at least basemodel & tpl_fields
   * @param array $args - initialization array
   */
  public function __construct($args=[]) {
    foreach ($args as $key => $val) {
      if (property_exists($this,$key)) {
        $this->$key = $val;
      }
    }
    parent::__construct();
  }
  /*
  public function tagged($tag, $content = null, $attributes=null, $raw = false) {
    if (is_arrayis($content)) {
      $content = keyVal('content', $content);
      $attributes = keyVal('attributes', $content);
      $raw = keyVal('raw', $content,false);
    }
    $attributes = $this->cleanAttributes($attributes);
    if (!$attributes) $attributes = [];
  }
   * 
   */

  public function __toString() {
    $baseSubForm = parent::__toString();
    $out = new PkHtmlRenderer();
    $out[] = "\n";
    $cnt = 0;
    if (is_arrayish($this->subform_data)) $cnt = count($this->subform_data);
    $out->div(RENDEROPEN,$this->templatables_attributes);
      if (is_arrayish($this->subform_data)) foreach ($this->subform_data as $idx=> $row) {
        $out[] = $this->makeSubformPart($baseSubForm, $idx, $row);
      }
      $create_button_attributes = $this->create_button_attributes;
      $create_button_tag = $this->create_button_tag;
      $create_button_wrap_tag = $this->create_button_wrap_tag;
      $create_button_attributes['data-itemcount'] = $cnt;
      //$createbutton = $this->div($this->create_button_label, $this->create_button_attributes);
      $out->$create_button_wrap_tag(RENDEROPEN, $this->create_button_wrap_attributes);
        $out ->$create_button_tag($this->create_button_label, $create_button_attributes);
      $out->RENDERCLOSE();
      //$out[] = $this->div($this->create_button_label, $create_button_attributes);
    /*
      $out[] = $this->$create_button_tag($this->create_button_label, $this->create_button_attributes);
     */
      $out[]="\n";
      $out[] = $this->makeSubformPart($baseSubForm);
    $out->RENDERCLOSE();
    $out[] = "\n";
    //PkForm::setNamePrefix($newNamePrefix);
    return $out->__toString();
  }

  /**Make subform component - either initialized or invisible template, 
   * depending if $values is null or array
   * 
   */
  public function makeSubformPart($baseSubForm, $idx = null, $values=null) {
    if (!is_arrayish($this->tpl_fields)) return $baseSubForm;

    if ($idx !== null) { #Existing data, value to be replaced in __toString
      $baseSubForm = str_replace($this->cnt_tpl,$idx,$baseSubForm);
    } else { #Subform template - all values to be null
      foreach ($this->tpl_fields as $inp_fld) {
        $valkey = $this->fieldValueTemplate($inp_fld);
        $baseSubForm = str_replace($valkey,'',$baseSubForm);
      }
    }
    $tpl = new PkHtmlRenderer();
    $tpl[]= "\n";
    if ($values === null) { //Invisible template part
      $js_template_tag = $this->js_template_tag;
      $tpl->$js_template_tag(RENDEROPEN,$this->js_template_attributes);
    } 
    //$tpl->div(RENDEROPEN,$this->templatable_attributes);
    $tpl->div(RENDEROPEN,$this->deletable_dataset_attributes);
      foreach ($this->tpl_fields as $inp_fld) {
        $valkey = $this->fieldValueTemplate($inp_fld);
        $val = keyVal($inp_fld,$values);
        //if (is_string($val)) $val = "'".$val."'";
        $baseSubForm = str_replace($valkey,$val,$baseSubForm);
      }
      $tpl[] = $baseSubForm."\n";
      $delete_button_tag = $this->delete_button_tag;
      $delete_button_wrap_tag = $this->delete_button_wrap_tag;
      $tpl->$delete_button_wrap_tag(RENDEROPEN,  $this->delete_button_wrap_attributes);
        $tpl->$delete_button_tag($this->delete_button_label, $this->delete_button_attributes);
      $tpl->RENDERCLOSE();
    $tpl->RENDERCLOSE();
    if ($values === null) { //Invisible template part
      $tpl->RENDERCLOSE();
    } 
    $tpl[]="\n";
    return $tpl;
  }

  public function fieldValueTemplate($name) {
    return $this->fld_tpl_prefix.$name;
  }

  /**Customize attributes for subform inputs */
  public function cleanAttributes($options) {
    $options = parent::cleanAttributes($options);
    if ($this->basename) {
      $options['name_prefix']=$this->basename."[{$this->cnt_tpl}]";
    }
    return $options;
  }

  public function input($type, $name, $value = null, $options = []) {
    if (is_arrayish($name)) {
      $name = keyVal('name', $name);
      $value = keyVal('value', $name,$value);
      $options = keyVal('options', $name,$options);
    }
    $options = $this->cleanAttributes($options);
    $options['value'] = $this->fieldValueTemplate($name);
    $options['name'] = keyVal('name',$options,$name);
    $return = parent::input($type,$name,$value,$options);
    //pkdebug("Return: $return\nOptions:",$options);
    return $return;
    //return parent::input($type,$name,$value,$options);
  }

  public function textarea($name, $value = null, $options = []) {
    if (is_arrayish($name)) {
      $name = keyVal('name', $name);
      $value = keyVal('value', $name,$value);
      $options = keyVal('options', $name,$options);
    }
    $options = $this->cleanAttributes($options);
    $value = $options['value'] = $this->fieldValueTemplate($name);
    $options['name'] = keyVal('name',$options,$name);
    return parent::textarea($name,$value,$options);
  }
  
  public function select($name, $list = [], $selected = null, $options = []) {
    if (is_arrayish($name)) {
      $name = keyVal('name', $name);
      $list = keyVal('list', $name,$list);
      $selected = keyVal('selected', $name,$selected);
      $options = keyVal('options', $name,$options);
    }
    $options = $this->cleanAttributes($options);
    $selected = $options['selected'] = $this->fieldValueTemplate($name);
    $options['name'] = keyVal('name',$options,$name);
    $options['data-selected']=$selected;
    return parent::select($name,$list,$selected,$options);
  }


  /** Don't worry for subforms */
  /*
  public function multiselect($name, $list = [], $values=null, $options=[], $unset = null) {
     if (is_string($options)) $options = ['class'=>$options];
     if ($this instanceOf PkMultiSubformRenderer && $this->basename) {
      $options['name_prefix']=$this->basename."{[$this->cnt_tpl]}";
    }
     $this[] = PkForm::multiselect($name, $list, $values, $options, $unset);
     return $this;
  }
   */

}