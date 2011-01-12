<?php
require_once(APPPATH.'../libraries/Model.php'); 
/**
 * Synt4x Model
 *
 * Extension del modelo de CodeIgniter para soportar metodos basicos.
 *
 * Para PHP5+
 *
 * @author Ian Murray
 */
class Synt4x_Model extends Model
{
  /**
   * The name of the table
   *
   * @var string
   */
  var $table_name;
  
  /**
   * The name of the primary key (only single primary keys are supported)
   *
   * @var string
   */
  var $primary_key;
  
  /**
   * Whether or not the primary key is autoincremental (VERY important to set this)
   *
   * @var string
   */
  var $auto_incremental;
  
  /**
   * An array with all the names of the foreign keys, so that automatic get_by_ methods can be created
   *
   * @var string
   */
  var $foreign_keys = array();
  
  /**
   * The types of each field. This is used primarily for validation, if the data passed to 
   * the create or update methods does not validate with this array, then an error is returned
   *
   * If this is left empty (or a field does not have an entry), no validations are performed (for that field).
   *
   * Format:
   * Array (
   *   field_name => 'preg compatible regexp'  
   * )
   *
   * @var string
   */
  var $field_validations = array();
  
  // -----------------------------------------------------------------------------------------------
  
  /**
   * Constructor
   *
   * @param string $table_name 
   * @param string $primary_key 
   * @param string $foreign_keys 
   * @param string $auto_incremental 
   * @param string $field_types 
   * @return void
   * @author Ian Murray
   */
  function Synt4x_Model($table_name = null, $primary_key = null, $foreign_keys = null, $auto_incremental = TRUE, $field_validations = null)
  {
    parent::Model();
    $this->table_name = $table_name;
    $this->primary_key = $primary_key;
    $this->auto_incremental = $auto_incremental;
    
    if ($foreign_keys !== null && is_array($foreign_keys)) {
      $this->foreign_keys = $foreign_keys;
    }
    
    if ($field_validations !== null && is_array($field_validations)) {
      $this->field_validations = $field_validations;
    }
  }
  
  // -----------------------------------------------------------------------------------------------
  
  /**
   * Allows for calls to get_by_<foreign_key>
   *
   * @param string $method 
   * @param string $arguments 
   * @return void
   * @author Ian Murray
   */
  function __call($method, $arguments) {
    // Revisamos si existe un foreign key, sino, failfail
    $foreign_key = str_replace('get_by_', '', $method); // asi tenemos el foreign_key

    if(!in_array($foreign_key, $this->foreign_keys)) {
      trigger_error('MY_Model Fatal Error: call to undefined method ' . __CLASS__ . '::' . $method . '.', E_USER_ERROR);
    }
    else {
      // Nuestra logica de llamada
      // Necesitamos llamar a $this->get ... pero tenemos que revisar si los argumentos que vamos
      // a mandar estan seteados.
      if(!isset($arguments[0])) {
        // Ni siquiera tenemos el id, error
        trigger_error('MY_Model Fatal Error: call to method ' . __CLASS__ . '::' . $method . ' with too few arguments, at least $id is required.', E_USER_ERROR);
      }

      $id = $arguments[0]; // Tenemos el ID

      // El resto de las cosas las obtenemos de $arguments[1] que es un arreglo compatible con $this->get()
      // Necesitamos "appendear" el $id al where (si lo hubiere)
      if(isset($arguments[1]['where'])) {
        if(is_array($arguments[1]['where'])) {
          // Es un array asociativo, agregamos el foreign_key
          $arguments[1]['where'][$foreign_key] = $id;
        }
        else {
          // Deberia ser un string ...
          $arguments[1]['where'] .= ', ' . $foreign_key . ' = ' . $id;
        }
      }
      else {
        // Lo seteamos
        $arguments[1]['where'][$foreign_key] = $id;
      }

      // Llamamos a get
      return $this->get($arguments[1]);
    }
  }
  
  // -----------------------------------------------------------------------------------------------
  
  /**
   * Get generico
   * $fields puede ser un string o un arreglo simple, Array(attr1, attr2, ...)
   * $where puede ser un arreglo asociativo o un string (de acuerdo a DB::WHERE)
   * $order puede ser un string directo o un arreglo asociativo Array(attr => dir, attr2 => dir)
   * $limit y $offset son enteros
   * $join:
   * Si queremos hacer un join necesitamos un arreglo del estilo
   * Array( [0] => Array($table, $join_condition) [1] => Array($table, $join_condition) ... )
   * Cada join debe ir acompañado de su select correspondiente obviamente
   * @author Ian Murray
   */
  //function get($fields, $where = null, $order = null, $limit = null, $offset = null, $join = null)
  function get($args)
  {
    // Si se le pasa un string asumir que es un sql query
    if(is_string($args)) {
      $query = $this->db->query($args);
      return $query->row_array();
    }
       
    // Extraccion de variables, permite llamadas mas genericas
    $fields = $where = $order = $limit = $offset = $join = $group_by = $having = null; // Inicializamos a null
    extract($args, EXTR_OVERWRITE);
    
    //
    // Atributos
    //
    if($fields === null) {
      $fields = '*'; // Defaults to *
    }
    
    // Juntamos todos los campos con comas si es que nos pasan un array
    if (is_array($fields)) {
      $fields = implode(', ', $fields);
    }
    
    $this->db->select($fields);
    
    //
    // Where
    //
    
    // El $where deberia ser o bien un string o un array asociativo
    if ($where !== null || (!is_array($where) && trim($where) != '')) {      
      $this->db->where($where);
    }
    
    //
    // Order
    //
    
    // El order puede ser un string directo o un array asociativo
    if(is_array($order)) {
      foreach($order as $field => $direction) {
        $this->db->order_by($field, $direction);
      }
    }
    else if ($order !== NULL) {
      // Es un string directo
      $this->db->order_by($order);
    }
    
    //
    // Limit & Offset
    //
    //if($limit !== null && $offset !== null) {
    $this->db->limit($limit, $offset);
    //}
    
    //
    // Group By y Having
    //
    if($group_by !== NULL) {
      $this->db->group_by($group_by);
    }
    
    if($having !== NULL) {
      $this->db->having($having);
    }
    
    //
    // Join
    //
    
    // Si queremos hacer un join necesitamos un arreglo del estilo
    // Array( $table => $join_condition, $table => $join_condition, ... ) 
    // $join_condition puede ser un arreglo que sea estilo Array($condition, $join_type)
    // para poder hacer LEFT, RIGHT, OUTER, etc.
    // Cada join debe ir acompañado de su select correspondiente obviamente
    if ($join) {
      // Queremos hacer uno o varios join
      foreach ($join as $table => $condition) {
        if(is_array($condition)) {
          $this->db->join($table, $condition[0], $condition[1]); 
        }
        else {
          $this->db->join($table, $condition);
        }
      }
    }
    
    // Finalmente
    $result = $this->db->get($this->table_name);
    //echo '<!-- ' . $this->db->last_query() . ' -->';
    return $result->result_array();
  }
  
  // -----------------------------------------------------------------------------------------------
  
  /**
   * Get by ID generico siendo que tenemos la primary key.
   *
   * @param integer $id El id
   * @return mixed
   * @author Ian Murray
   */
  function get_by_id($id)
  {
    //$result = $this->get('*', array($this->primary_key => $id), null, 1); // Get * from table where primary_key = ? limit 1
    $result = $this->get(array(
      'fields' => '*',
      'where' => array($this->primary_key => $id),
      'limit' => 1
    ));
    if(count($result)) {
      return $result[0];
    }
    else {
      return false;
    }
  }
  
  // -----------------------------------------------------------------------------------------------
  
  /** 
   * Funciona igual que el get, solo que devuelve los datos formateados
   * para un dropdown del helper de formularios.
   * 
   * El key indica la columna de la tabla que se usara para los "value" 
   * del dropdown, y el value indica lo que el usuario vera (tambien
   * un nombre de columna.
   * 
   * @param object $args
   * @param string $key
   * @param string $value
   * @return 
   */
  function get_for_dropdown($args, $key, $value)
  {
    $results = $this->get($args);
    $dropdown = array();
    
    foreach($results as $result) {
      $dropdown[$result[$key]] = $result[$value];
    }
    
    return $dropdown;
  }
  
  // -----------------------------------------------------------------------------------------------
  
  /**
   * Performs validation of fields before sending them to the database
   *
   * @param string $data 
   * @return void
   * @author Ian Murray
   */
  function _validate_fields($data)
  {    
    // Perform validations to the fields if applicable
    foreach ($this->field_validations as $field_name => $pattern)
    {
      if ( ! isset($data[$field_name])) 
      {
        // This field is not being passed, continue.
        continue;
      }

      // Match it to the pattern, if it does not match, return false.
      if ( ! preg_match($pattern, $data[$field_name]))
      {
        //echo "preg_match failed on field '" . $field_name . "' = '" . $data[$field_name] . "'"; exit;
        return FALSE;
      }
    }
    
    // If we reach this point, fields are valid, return TRUE
    return TRUE;
  }
  
  // -----------------------------------------------------------------------------------------------
  
  /**
   * Crea una entrada en la BD
   *
   * @param array $data Los datos a ingresar
   * @return mixed Intenta devolver la ultima fila que fue ingresada (si no existe get_by_id, devuelve null)
   * @author Ian Murray
   */
  function create($data)
  {
    // Validate
    if ( ! $this->_validate_fields($data)) 
    {
      return FALSE;
    }
    
    $this->db->insert($this->table_name, $data);
    if(method_exists($this, 'get_by_id')) {
      return $this->get_by_id(($this->auto_incremental) ? $this->db->insert_id() : $data[$this->primary_key]);
    }
    elseif ($this->primary_key !== null) {
      return $this->get('*', array(
        $this->primary_key => ($this->auto_incremental) ? $this->db->insert_id() : $data[$this->primary_key]
      ));
    }
    else {
      return null;
    }
  }
  
  // -----------------------------------------------------------------------------------------------
  
  /**
   * Un sinonimo de create
   * 
   * @param object $data
   * @return 
   */
  function insert($data)
  {
    return $this->create($data);
  }
  
  // -----------------------------------------------------------------------------------------------
  
  /**
   * Modificar generico
   *
   * @param mixed $id Recibe el id o bien un array con el id dentro + los campos a modificar
   * @param array $data Los datos a modificar en caso de pasar un id especifico en $id
   * @return void
   * @author Ian Murray
   */
  function modify($id, $data = null)
  {    
    // Validate
    if ( ! $this->_validate_fields($data)) 
    {
      return FALSE;
    }
    
    if(is_array($id)) {
      // Sacamos id de ahi
      $data = $id;
      $id = $data[$this->primary_key]; // el id lo sacamos de $id
      unset($data[$this->primary_key]); // eliminamos esa entrada.
    }
    //echo 'in_here';
    $this->db->where($this->primary_key, $id);
    // return $this->db->update($this->table_name, $data);
    $this->db->update($this->table_name, $data);
    
    return $this->get_by_id($id);
    //echo $this->db->last_query();
  }
  
  // -----------------------------------------------------------------------------------------------
  
  /**
   * Para simplificar cosas, un sinonimo de update.
   * 
   * @param object $id
   * @param object $data [optional]
   * @return 
   */
  function update($id, $data = null)
  {
    return $this->modify($id, $data);
  }
  
  // -----------------------------------------------------------------------------------------------
  
  /**
   * Delete generico. Requiere de MY_Model::primary_key seteado.
   *
   * @param string $id 
   * @return void
   * @author Ian Murray
   */
  function delete($id)
  {
    if (is_array($id))
    {
      $this->db->where($id);
      $this->db->from($this->table_name);
      return $this->db->delete();
    }
    else
    {
      return $this->db->delete($this->table_name, array($this->primary_key => $id));
    }
  }
  
  // -----------------------------------------------------------------------------------------------
  
  /**
   * Calcula cantidad de items dado una condicion.
   *
   * @param string $params 
   * @return void
   * @author Ian Murray
   */
  function count_all($params = array()) 
  {
    $params['fields'] = 'COUNT(*) as ammount';
    
    $result = $this->get($params);
    
    return $result[0]['ammount'];
  }
}
