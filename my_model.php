<?php
/**
 * Validation constants
 */
define('VALID_REQUIRED', '/^.+$/');

define('VALID_INT', '/^\-?\d+$/');
define('VALID_INT_OPTIONAL', '/^\-?\d*$/');

define('VALID_EMAIL', '/^[a-z][+\w\.]+@([\w\-]+\.)+[a-z]{2,7}$/i');
define('VALID_EMAIL_OPTIONAL', '/^([a-z][+\w\.]+@([\w\-]+\.)+[a-z]{2,7})?$/i');
define('VALID_MD5', '/^\{?[a-fA-F\d]{32}\}?$/');
define('VALID_BOOL', '/(t|f)/');

define('VALID_PHONE', '/^[0-9\(\)\- ]*$/');

//define('VALID_DATETIME_STRICT', '/{^(((\d{4})(-)(0[13578]|10|12)(-)(0[1-9]|[12][0-9]|3[01]))|((\d{4})(-)(0[469]|11)(-)([0][1-9]|[12][0-9]|30))|((\d{4})(-)(02)(-)(0[1-9]|1[0-9]|2[0-8]))|(([02468][048]00)(-)(02)(-)(29))|(([13579][26]00)(-)(02)(-)(29))|(([0-9][0-9][0][48])(-)(02)(-)(29))|(([0-9][0-9][2468][048])(-)(02)(-)(29))|(([0-9][0-9][13579][26])(-)(02)(-)(29)))(\s([0-1][0-9]|2[0-4]):([0-5][0-9]):([0-5][0-9]))$}/');
define('VALID_DATETIME', '/^[0-9][0-9][0-9][0-9](-[0-1][0-9](-[0-3][0-9]( [0-9][0-9](:[0-9][0-9](:[0-9][0-9])?)?)?)?)?$/');

define('VALID_ALFASPACE', '/^[a-zA-Z ]*$/');
define('VALID_ALFANUMSPACE', '/^[a-zA-Z0-9 ]*$/');
define('VALID_ALFA', '/^[a-zA-Z]*$/');
define('VALID_ALFANUM', '/^[a-zA-Z0-9]*$/');

define('VALID_ALFASPACE_REQUIRED', '/^[a-zA-Z ]+$/');
define('VALID_ALFANUMSPACE_REQUIRED', '/^[a-zA-Z0-9 ]+$/');
define('VALID_ALFA_REQUIRED', '/^[a-zA-Z]+$/');
define('VALID_ALFANUM_REQUIRED', '/^[a-zA-Z0-9]+$/');

// For names for example
define('VALID_NONNUMERIC', '/^[^0-9]+$/');
define('VALID_NONNUMERIC_NOSPACES', '/^[^0-9 ]+$/');



if(phpversion() < 5) {
  require("synt4x_model_php4.php");
}
else {
  require("synt4x_model_php5.php");
}

/**
* Clase base que extiende a la clase synt4x_model
*/
class MY_Model extends Synt4x_Model
{
  function MY_Model($table_name = null, $primary_key = null, $foreign_keys = null, $auto_incremental = TRUE, $field_validations = null) {
    parent::Synt4x_Model($table_name, $primary_key, $foreign_keys, $auto_incremental, $field_validations);
  }
}
