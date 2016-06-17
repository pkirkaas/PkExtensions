/* 
 * This library manages if you have encoded model/instance details encoded as 
 * data-attributes in JS and you want to pop them into a dialog when the user
 * hovers or clicks on an element. Assumes pklib.js
 */


/** This section is for managing object details encoded into data attribute
 * values in the HTML generated by PHP.
 * First, we declare a global JS object "var decodedObjectCache = {};"
 * to hold/cache the decoded values so we don't have to decode them again every time.
 * 
 * Function "getDecodedData(objtype,objid)" returns the data object details for
 * object of type=objtype and id=objid.
 * 
 * First checks the global cache "decodedObjectCache.type.id" - if it exists, 
 * returns it. If not, checks the page for element of class: 'encoded-data-holder',
 * with the elements "data-encoded-data-objtype=type" and 'data-encoded-data-objid=id'
 * 
 *  If it finds that element, parses the value of 'data-encoded-data-data' into
 *  a JavaScript object, assigns the result to "decodedObjectCache.objtype.objid", and
 *  returns it.
 * 
 * @returns object - the decoded data object.
 */

//var decodedObjectCache = {};
var decodedObjectCache = null;

/** We get the decoded data for a particular object instance (objtype/objid)
 * getDecodedData(objtype,objid): returns the data for that object
 * OR for all instances of that type:
 * getDecodedData(objtype): data for all instances of that type, keyed by ID
 * OR all the encoded data we have on the page:
 * getDecodedData(): ALL the decoded data, keyed by type/id
 * 
 * @param string objtype
 * @param string objid
 * @returns object data
 */


/* As defined in app.blade.php
     var popDefObj = {
       dataModelType:'data-model-type-name',
        dataModelId:'data-model-id',
        jsPopupTmpCallerDataAttr:'data-js_popup-template-calls',
        jsPopupTmpCalledDataAttr:'data-js-popup-template-called',
        encodedDataHolderClass:'encoded-data-holder',
        encDatMdlDataAttr:'data-encoded-data-objtype',
        encDatIdDataAttr:'data-encoded-data-objid',
        encDatDatDataAttr:'data-encoded-data-data'
     };
*/



/**
 * Gets decoded object attribute data for the given model/id - or for all
 * instances of model if id not given, or for all known if neither given
 * @param string|object|nul objtype - if string, the object type. If object,
 *   should be of the form: {objtype:objtype, objid:objid}
 *   if null, all encoded data returned.
 * @param string|null objid
 * @returns {decodedObjectCache}
 */
function getDecodedData(objtype,objid) {
  if (decodedObjectCache === null) {
    inititializeDecodedObjectCache();
  }
  if (isObject(objtype)) {
    objid = objtype.objid;
    objtype = objtype.objtype;
  }
  
  if (objtype === undefined) {
    return decodedObjectCache;
  }
  if (decodedObjectCache[objtype] === undefined) {
    decodedObjectCache[objtype] = {};
    console.log("The cache attribute ["+objtype+"] was undefined");
  }
  if (objid === undefined) {
    return decodedObjectCache[objtype];
  }
  if (decodedObjectCache[objtype][objid] === undefined) {
    decodedObjectCache[objtype][objid] = {};
  }
  return decodedObjectCache[objtype][objid];
}

function inititializeDecodedObjectCache() {
  if (decodedObjectCache === null) {
    decodedObjectCache = {};
  }
  var encodedEls = $('.'+popDefObj.encodedDataHolderClass);
  if (!encodedEls.length) {
    return decodedObjectCache;
  }
  encodedEls.each(function() {
    var objtype = $(this).attr(popDefObj.encDatMdlDataAttr);
    var objid = $(this).attr(popDefObj.encDatIdDataAttr);
    var encoded = $(this).attr(popDefObj.encDatDatDataAttr);
    var decoded =  $.parseJSON(encoded);
    if  (decodedObjectCache[objtype] === undefined) {
      decodedObjectCache[objtype] = {};
    }
    decodedObjectCache[objtype][objid] = decoded;
  });
  return decodedObjectCache;
}


function objDataDecode(objtype, objid) {
  var datael = $('.encoded-data-holder[data-encoded-data-objtype="'+
          objtype+'"][data-encoded-data-objid="'+
          objid + '"]');
  var encoded = datael.attr('data-encoded-data-data');
  console.log ('encoded:', encoded);
  var decoded =  $.parseJSON(encoded);
  console.log ('decoded:', decoded);
  return decoded;
}



$(function () {
  console.log("Trying to get data atts");
  //var res = getDecodedData('borrower','1');
  var res = getDecodedData();
  console.log("RES:", res);
  /*
  var data_atts = $('div.data-atts-holder');
  if (data_atts.length) {
    var enc_data = data_atts.attr('data-atts');
    console.log("The encoded data", enc_data);
    var dec_obj = $.parseJSON(enc_data);
    console.log("The Decoded Obj:", dec_obj);
  }
  */

});





/** Special pop-up JS to show model/id details when hovering...
 * 
 * @param {type} theVar
 * @param {type} subStr
 * @returns {Boolean}
 */
//$('body').on('hover', '.'+popDefObj.popTemplateClass, function (event) {
//$('body').on('hover', '['+popDefObj.jsPopupTmpCallerDataAttr+']', function (event) {
//$('body').on('hover', '.'+popDefObj.popCallerClass, function (event) {
$('body').on('click', '.'+popDefObj.popCallerClass, function (event) {
  console.log("Hovering...");

  var src = $(event.target).attr(popDefObj.jsPopupTmpCallerDataAttr);
  if (!src) return;
  var dlg = $('.'+popDefObj.popTemplateClass+'['+popDefObj.jsPopupTmpCalledDataAttr+'="' + src + '"]');
  if (dlg.length === 0) return;
  var dlgHtml = dlg.prop('outerHTML');
  console.log("DLGHTML: "+dlgHtml);

});
