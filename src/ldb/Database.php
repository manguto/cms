<?php
namespace manguto\cms\ldb;

use manguto\lib\Arquivos;
use manguto\lib\Arrays;

class Database
{

    const filename = 'database/db.php';

    const fileIni = '<?php /*';

    const fileEnd = '*/ ?>';

    public $tables = array();

    public $info = array();

    public function __construct()
    {
        // echo "@@@";
        
        // verificacao de inicializacao
        if (! file_exists(self::filename)) {
            $this->save();
        }
        // carregamento
        $this->load();
    }

    private function table_verify(string $tablename)
    {
        $tablename = strtolower($tablename);
        if (! isset($this->tables[$tablename])) {
            $this->tables[$tablename] = new DatabaseTable($tablename);
        }
    }

    public function table_entries_array(string $tablename, array $filters = [])
    {
        $tablename = strtolower($tablename);
        $results = $this->select($tablename, $filters);
        foreach ($results as &$r) {
            $r = $r->getData();
        }
        return $results;
    }

    public function table_entries_amount(string $tablename)
    {
        $tablename = strtolower($tablename);
        
        $this->table_verify($tablename);
        
        $amount = sizeof($this->tables[$tablename]->entries);
        
        return $amount;
    }

    static public function table_entry_shape(Model $model):Model
    {   
        $db = new Database();
        
        $tablename = strtolower( $model->getModelName());
        
        $db->table_verify($tablename);
        
        $model = $db->tables[$tablename]->columns_update($model);
        
        return $model;
    }

    public function table_entry_save(Model &$model)
    {
        $tablename = $model->getModelName();
        
        $tablename = strtolower($tablename);
        
        $this->table_verify($tablename);
        
        $this->tables[$tablename]->entry_insert_update($model);
        
        $this->save();
        
        return $model;
    }

    public function table_entry_delete(Model &$model)
    {
        $tablename = $model->getModelName();
        
        $tablename = strtolower($tablename);
        
        $this->table_verify($tablename);
        
        $this->tables[$tablename]->entry_delete($model);
        
        $this->save();
        
        return $model;
    }

    public function table_column_update(Model $model, string $columnName, array $updates)
    {   
        $tablename = strtolower($model->getModelName());
        
        $this->table_verify($tablename);
        
        $this->tables[$tablename]->column_update($columnName, $updates);
        
        $this->save();
    }

    public function table_column_list(Model &$model)
    {
        $tablename = $model->getModelName();
        
        $tablename = strtolower($tablename);
        
        $this->table_verify($tablename);
        
        $table = $this->tables[$tablename];
        // if(false) $table = new DatabaseTable($tablename);
        
        return $table->columns;
    }

    private function save()
    {
        $data = self::fileIni . serialize($this) . self::fileEnd;
        Arquivos::escreverConteudo(self::filename, $data);
    }

    private function load()
    {
        $contentSerializedWrapped = Arquivos::obterConteudo(self::filename);
        $contentSerializedWrapped = str_replace(self::fileIni, '', $contentSerializedWrapped);
        $contentSerializedWrapped = str_replace(self::fileEnd, '', $contentSerializedWrapped);
        $contentSerialized = trim($contentSerializedWrapped);
        $content = unserialize($contentSerialized);
        
        if (isset($content->tables) && isset($content->tables)) {
            $this->info = $content->info;
            $this->tables = $content->tables;
        } else {
            throw new \Exception("Conteúdo do arquivo fonte da base de dados corrompido.");
        }
    }


    public function select(string $tablename, array $filter = [])
    {
        $tablename = strtolower($tablename);
        
        // echo "<h1 style='background:red;'>SELECT</h1>";
        // deb($tablename,0); deb($filter,0);
        $this->table_verify($tablename);
        
        // deb($this->tables);
        
        $return = array();
        foreach ($this->tables[$tablename]->entries as $entry) {
            $accepted = true;
            // echo "<hr />";
            // deb($entry,0);
            foreach ($filter as $filterAttrName => $filterAttrValue) {
                
                $entryAttrValue = trim($entry->{'get' . $filterAttrName}());
                $filterAttrValue = trim($filterAttrValue);
                // deb('<hr />',0);
                // deb('FILTER ATTR NAME: '.$filterAttrName,0);
                // deb('FILTER ATTR VALUE: '.$filterAttrValue,0);
                // deb('ENTRY ATTR VALUE: '.$entryAttrValue,0);
                
                if ($entryAttrValue != $filterAttrValue) {
                    $accepted = false;
                }
            }
            if ($accepted) {
                $return[] = $entry;
            }
        }
        return $return;
    }

    public function table_delete(string $tablename)
    {
        $tablename = strtolower($tablename);
        
        if (isset($this->tables[$tablename])) {
            unset($this->tables[$tablename]);
            $this->save();
        }
    }

    // ###########################################################################################################
    // ###########################################################################################################
    // ###########################################################################################################
    // ###########################################################################################################
    // ###########################################################################################################
    // ###################################### SHOW ###############################################################
    // ###########################################################################################################
    // ###########################################################################################################
    // ###########################################################################################################
    // ###########################################################################################################
    // ###########################################################################################################
    // ###########################################################################################################
    
    static function ManagerOperations($tablename)
    {
        $target = isset($_POST['target']) ? trim($_POST['target']) : false;
        $option = isset($_POST['option']) ? trim($_POST['option']) : false;
        $parameter = isset($_POST['parameter']) ? trim($_POST['parameter']) : false;
        // deb($_POST);
        { // ------------------------------------------------------------------------------- table
            if ($tablename != '' && $target == 'table' && $option == 'save' && $parameter == '') {
                $database = new Database();
                $database->table_verify($tablename);
                $database->save();
                header('Location:' . $_SERVER['REQUEST_URI']);
                exit();
            }
            if ($tablename != '' && $target == 'table' && $option == 'delete' && $parameter == '') {
                $database = new Database();
                $database->table_delete($tablename);
                header('Location:' . self::url_back());
                exit();
            }
        }
        
        { // ------------------------------------------------------------------------------- table entry
            if ($tablename != '' && $target == 'entry' && $option == 'add' && $parameter == '0') {
                self::verifyModelClass($tablename);
                $tablename = 'Lib\Model\\' . $tablename;
                $newEntry = new $tablename();
                $newEntry->save();
            }
            if ($tablename != '' && $target == 'entry' && $option == 'delete' && $parameter != '0') {
                $tablename = 'Lib\Model\\' . $tablename;
                $newEntry = new $tablename($parameter);
                $newEntry->delete();
            }
        }
        
        { // ------------------------------------------------------------------------------- table info
          // --- add
            if ($tablename != '' && $target == 'info' && $option == 'add' && $parameter != '') {
                $database = new Database();
                $className = 'lib\model\\' . $tablename;
                $model = new $className();                
                $database->table_column_update($model, $_POST['parameter'], []);                
                header('Location:' . self::url_back() . '/' . $tablename);
                exit();
            }
            
            // --- update
            if ($tablename != '' && $target == 'info' && $option == 'save' && $parameter != '') {
                // deb("save info $parameter",0);
                
                $database = new Database();
                $className = 'lib\model\\' . $tablename;
                $model = new $className();
                $database->table_column_update($model, $_POST['name'], [
                    'type' => $_POST['type'],
                    'default' => $_POST['default']
                ]);
                header('Location:' . self::url_back() . '/' . $tablename);
                exit();
            }
            // --- delete
            if ($tablename != '' && $target == 'info' && $option == 'delete' && $parameter != '') {
                deb("delete info $parameter", 0);
                /*
                 * $tablename = 'Lib\Model\\'.$tablename;
                 * $newEntry = new $tablename($parameter);
                 * $newEntry->delete();
                 */
            }
        }
    }

    static private function verifyModelClass($tablename)
    {
        $filename = "lib/model/" . ucfirst($tablename) . ".php";
        if (! file_exists($filename)) {
            $data = '<?php

namespace Lib\Model;

use manguto\ldb\Model;

class ' . ucfirst($tablename) . ' extends Model
{
        
}

?>';
            file_put_contents($filename, $data);
        }
    }

    static function Manager(string $tablename = '', string $style = '')
    {
        // tablenames to lowercase
        $tablename = strtolower($tablename);
        
        { // OPERATIONS
            self::ManagerOperations($tablename);
        }
        
        $return = array();
        
        { // JAVASCRIPT
            
            $return[] = "<script>
            
            function tableSetTarget(value){
                $('#table input[name=\"target\"]').val(value);
            }
            function tableSetOption(value){
                $('#table input[name=\"option\"]').val(value);
            }
            function tableSetParameter(value){
                $('#table input[name=\"parameter\"]').val(value);
            }
            function tableSetAction(new_tablename){
                var action = $('#table').attr('action');
                action = action + '/' + new_tablename
                $('#table').attr('action',action);            
            }
            function tableSubmit(){
                $('#table').submit();
            }
            
            //--------------------------------------------- table control
            function table_new(){
                var tableName = prompt('Digite o nome da nova tabela:');                
                if (tableName != null) {
                    tableSetTarget('table');
                    tableSetOption('add');
                    tableSetParameter('');
                    tableSetAction(tableName);
                    tableSubmit();
                }
            }
            
            $(document).ready(function(){
            
            });
            
            </script>";
        }
        
        { // --- FORMULARIO
            $action = Database::form_action();
            $return[] = "<form id='table' method='post' action='$action'>";
            $return[] = "<input type='hidden' name='target' value=''>";
            $return[] = "<input type='hidden' name='option' value=''>";
            $return[] = "<input type='hidden' name='parameter' value=''>";
            $return[] = "</form>";
        }
        
        if ($tablename == '') {
            $return[] = "<br/>";
            $return[] = "<h3>Tabelas do Sistema</h3>";
            $return[] = "<br/>";
            $database = new Database();
            foreach ($database->tables as $table) {
                // checks filter
                if (false)
                    $table = new DatabaseTable('teste');
                $href = Database::form_action() . '/' . $table->name;
                $return[] = "<a class='btn btn-danger btn-sm float-left'href='$href'>" . strtoupper($table->name) . "</a><br/><br/>";
            }
            $return[] = "<br/><button class='btn btn-success btn-sm float-left' onclick='table_new()' title='Adicionar/Criar nova tabela'>";
            $return[] = "<img src='/res/general/images/icons/plus-2x.png'/>&nbsp;&nbsp;&nbsp;ADICIONAR";
            $return[] = "</button><br/><br/>";
        } else {
            
            $database = new Database();
            if (isset($database->tables[$tablename])) {
                $table = $database->tables[$tablename];
                $return[] = $table->Manager($style);
            } else {
                throw new \Exception("Tabela não encontrada no Banco de Dados ('$tablename').");
            }
        }
        
        return implode('', $return);
    }

    static function form_action()
    {
        $request_uri = str_replace('\\', DIRECTORY_SEPARATOR, $_SERVER['REQUEST_URI']);
        $request_uri = str_replace('/', DIRECTORY_SEPARATOR, $request_uri);
        $action = explode(DIRECTORY_SEPARATOR, $request_uri);
        array_shift($action);
        array_shift($action);
        $action = DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $action);
        return $action;
    }

    static function url_back()
    {
        $request_uri = str_replace('\\', DIRECTORY_SEPARATOR, $_SERVER['REQUEST_URI']);
        $request_uri = str_replace('/', DIRECTORY_SEPARATOR, $request_uri);
        $action = explode(DIRECTORY_SEPARATOR, $request_uri);
        array_shift($action);
        array_pop($action);
        $action = DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $action);
        return $action;
    }
}

?>