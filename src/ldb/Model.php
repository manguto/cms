<?php
namespace manguto\cms\ldb;

use manguto\help\Help;

class Model
{

    protected $values = [];
  
    public function __construct($identifierValue = 0)
    {
        $this->setIdentifier((int) $identifierValue);
        
        if ($identifierValue != 0) {
            $this->get();
        } else {
            $this_shaped = Database::table_entry_shape($this);            
            $this->setData($this_shaped->getData());            
        }
    }


    // magic methods GET & SET
    public function __call($name, $args)
    {
        $method = substr($name, 0, 3);
        $fieldName = strtolower(substr($name, 3, strlen($name)));
        
        switch ($method) {
            case "get":
                $return = (isset($this->values[$fieldName]) ? ($this->values[$fieldName]) : NULL);
                break;
            
            case "set":
                $return = true;
                $this->values[$fieldName] = ($args[0]);
                break;
            default:
                throw new \Exception("Parâmetro de acesso incorreto (model->$name).");
        }
        return $return;
    }

    public function setData(array $data = array())
    {
        foreach ($data as $key => $value) {
            $key = strtolower($key);
            $this->{"set" . $key}($value);
        }
    }

    public function getData()
    {
        return $this->values;
    }

    

    public function get()
    {
        $db = new Database();
        $results = $db->select($this->getModelName(), [
            $this->getIdentifierName() => $this->getIdentifier()
        ]);
        if (sizeof($results) == 1) {
            $this->setData($results[0]->getData());
        } elseif (sizeof($results) > 1) {
            throw new \Exception("Foram encontrados mais de um(a) '" . $this->getModelName() . "' para o identificador '" . $this->getIdentifier() . "'.");
        } else {
            throw new \Exception("Não foram encontrados(as) '" . $this->getModelName() . "' para o identificador '" . $this->getIdentifier() . "'.");
        }
        return $results;
    }

    public function save()
    {
        $db = new Database();
        
        $db->table_entry_save($this);
    }

    public function delete()
    {
        $db = new Database();
        
        $db->table_entry_delete($this);
    }

  

    public function getModelName()
    {
        $modelName = get_class($this);
        //deb($modelName,0);
        //deb(DIRECTORY_SEPARATOR,0);
        $modelName = Help::fixds($modelName);
        $modelName = explode(DIRECTORY_SEPARATOR, $modelName);
        //deb($modelName);
        $modelName = array_pop($modelName);
        return $modelName;
    }

    public function getIdentifierName()
    {
        $identifierName = strtolower($this->getModelName() . 'id');
        return $identifierName;
    }

    public function getIdentifier()
    {
        $method = 'get' . $this->getIdentifierName() . '';
        return $this->$method();
    }

    public function setIdentifier(int $identifierValue)
    {
        $method = 'set' . $this->getIdentifierName() . '';
        return $this->$method(intval($identifierValue));
    }

    public function __toString()
    {
        $return = array();
        $values = $this->values;
        foreach ($values as $c => $v) {
            $return[] = "$c='<b>$v</b>'";
        }
        $return = self::getModelName($this) . " [ " . implode(', ', $return) . " ]";
        
        return $return;
    }
    
    
}

?>