<?php
namespace manguto\lib;

class URL
{

    // #########################################################################################################
    // ############################################ POST #####################################################
    // #########################################################################################################
    
    /**
     * verifica se uma variavel POST foi definida
     * $varName
     *
     * @return boolean
     */
    static function POST_isSet($varName)
    {
        if (isset($_POST[$varName])) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * verifica se uma variavel POST esta vazia
     * $varName
     *
     * @return boolean
     */
    static function POST_isEmpty(string $varName):bool
    {
        if (self::POST_isSet($varName) && trim($_POST[$varName]) == "") {
            return true;
        } else {
            return false;
        }
    }

    /**
     * retorna uma variavel POST conforme os parametros solicitados
     */
    static function POST2Var(string $varName,bool $notSetIsOK = false,bool $emptyIsOK = false,bool $throwExpection = true)
    {
        $return = null;
        if (self::POST_isSet($varName)) {
            $return = trim($_POST[$varName]);
            if (self::POST_isEmpty($varName)) {
                if ($emptyIsOK == false) {
                    if ($throwExpection) {
                        throw new \Exception("Parâmetro sem valor encontrado (\$_POST[$varName]='').");
                    } else {
                        $return = false;
                    }
                }
            }
        } else {
            if ($notSetIsOK == false) {
                if ($throwExpection) {
                    throw new \Exception("Parâmetro indefinido (\$_POST[$varName]).");
                } else {
                    $return = false;
                }
            }
        }
        return $return;
    }

    // #########################################################################################################
    // ############################################ GET ######################################################
    // #########################################################################################################
    
    /**
     * retorna a URL atual com os parametros GET alinhados
     *
     * @param array $removeGetVars
     * @return array|string
     */
    static function GET2String(array $removeGetVars = array())
    {
        if (sizeof($_GET) > 0) {
            $return = array();
            foreach ($_GET as $k => $v) {
                if (in_array($k, $removeGetVars)) {
                    continue;
                }
                if ($v != '') {
                    $return[] = "$k=$v";
                } else {
                    $return[] = "$k";
                }
            }
            $return = '?' . implode('&', $return);
        } else {
            $return = '';
        }
        return $return;
    }

    /**
     * verifica se uma certa variavel esta definda no GET
     * $varName
     *
     * @return boolean
     */
    static function GET_isSet(string $varName)
    {
        if (isset($_GET[$varName])) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * verifica se uma variavel esta vazia no GET
     * $varName
     *
     * @return boolean
     */
    static function GET_isEmpty(string $varName)
    {
        if (self::GET_isSet($varName) && trim($_GET[$varName]) == "") {
            return true;
        } else {
            return false;
        }
    }

    /**
     * obtem uma certa variavel GET conforme os parametros solicitados na funcao
     * $varName
     *
     * @param boolean $notSetIsOK
     * @param boolean $emptyIsOK
     * @param boolean $throwExpection
     * @throws \Exception
     * @return NULL|boolean|string
     */
    static function GET2Var(string $varName,bool $notSetIsOK = false,bool  $emptyIsOK = false,bool  $throwExpection = true)
    {
        $return = null;
        if (self::GET_isSet($varName)) {
            $return = trim($_GET[$varName]);
            if (self::GET_isEmpty($varName)) {
                if ($emptyIsOK == false) {
                    if ($throwExpection) {
                        throw new \Exception("Parâmetro sem valor encontrado (\$_GET[$varName]='').");
                    } else {
                        $return = false;
                    }
                }
            }
        } else {
            if ($notSetIsOK == false) {
                if ($throwExpection) {
                    throw new \Exception("Parâmetro indefinido (\$_GET[$varName]).");
                } else {
                    $return = false;
                }
            }
        }
        return $return;
    }

    static function URLString2Array(String $urlString)
    {
        $return = array();
        
        { // separacao - parte 1 (arquivo.php[?]) e parte 2 (par=1[&]par=3)
            $urlArrayCompleto = explode('?', $urlString);
            if (sizeof($urlArrayCompleto) == 2) {
                $urlPath = $urlArrayCompleto[0];
                $urlParameteres = $urlArrayCompleto[1];
            } else if (sizeof($urlArrayCompleto) == 1) {
                $urlPath = '';
                $urlParameteres = $urlArrayCompleto[0];
            } else {
                throw new \Exception("URL com formato incorreto, ou seja, mais de um caractere '?' ($urlString).");
            }
            // debug($urlPath,0); debug($urlParameteres);
        }
        // return 01
        $return['path'] = $urlPath;
        
        { // tratamento parte 2
            
            $urlArrayP2 = explode('&', $urlParameteres);
            foreach ($urlArrayP2 as $keyVal) {
                $keyValArray = explode('=', $keyVal);
                if (sizeof($keyValArray) == 1) {
                    $key = $keyValArray[0];
                    $val = null;
                } else if (sizeof($keyValArray) == 2) {
                    $key = $keyValArray[0];
                    $val = $keyValArray[1];
                } else {
                    throw new \Exception("URL com formacao parametrial incorreta ($keyVal).");
                }
                // tratamento possivel 'url encode'
                $val = urldecode($val);
                // return 02
                $return[$key] = $val;
            }
            // debug($parametros);
        }
        // debug($return);
        return $return;
    }

    /**
     * returna uma string com a url correspondente ao array (padrao do sistema) informado
     *
     * @param array $urlArray
     * @throws \Exception
     * @return string
     */
    static function URLArray2String(Array $urlArray)
    {
        $return = '';
        { // ----------------------------------------------------------------------------------- path
            if (! isset($urlArray['path'])) {
                throw new \Exception('Nao foi encontrado o parametro "path" no array informado.');
            }
            $path = $urlArray['path'];
            unset($urlArray['path']);
            $return = $path . '?';
        }
        { // ----------------------------------------------------------------------------------- parameters
            $par = array();
            foreach ($urlArray as $key => $val) {
                if ($val != null) {
                    $val = urlencode($val);
                    $par[] = "$key=$val";
                } else {
                    $par[] = "$key=";
                }
            }
            $return .= implode('&', $par);
        }
        return $return;
    }
}