<?php

namespace lib\Validacao;

use Slim\Slim;

/**
 * GUMP - A fast, extensible PHP input validation class
 *
 * @author      Sean Nieuwoudt (http://twitter.com/SeanNieuwoudt)
 * @copyright   Copyright (c) 2011 Wixel.net
 * @link        http://github.com/Wixel/GUMP
 * @version     1.0
 */

class Validacao extends \GUMP
{
    // Validation rules for execution
    protected $validation_rules = array();

    // Filter rules for execution
    protected $filter_rules = array();

    // Instance attribute containing errors from last run
    protected $errors = array();

    // Custom validation methods
    protected static $validation_methods = array();

    // Customer filter methods
    protected static $filter_methods = array();

    // ** ------------------------- Validation Data ------------------------------- ** //

    public static $basic_tags     = "<br><p><a><strong><b><i><em><img><blockquote><code><dd><dl><hr><h1><h2><h3><h4><h5><h6><label><ul><li><span><sub><sup>";

    public static $en_noise_words = "about,after,all,also,an,and,another,any,are,as,at,be,because,been,before,
                                     being,between,both,but,by,came,can,come,could,did,do,each,for,from,get,
                                     got,has,had,he,have,her,here,him,himself,his,how,if,in,into,is,it,its,it's,like,
                                     make,many,me,might,more,most,much,must,my,never,now,of,on,only,or,other,
                                     our,out,over,said,same,see,should,since,some,still,such,take,than,that,
                                     the,their,them,then,there,these,they,this,those,through,to,too,under,up,
                                     very,was,way,we,well,were,what,where,which,while,who,with,would,you,your,a,
                                     b,c,d,e,f,g,h,i,j,k,l,m,n,o,p,q,r,s,t,u,v,w,x,y,z,$,1,2,3,4,5,6,7,8,9,0,_";

    protected $acentos_maiuscula = array('À','Á','Â','Ã','É','È','Ê','Ó','Ò','Ô','Õ','Ú','Ù','Û','Ü','Ä','Ë','Ï','Ö','Ç');

    protected $acentos_minuscula = array('à','á','â','ã','é','è','ê','ó','ò','ô','õ','ú','ù','û','ü','ä','ë','ï','ö','ç');

    /**
     * Construtor
     *
     * @param Slim $app
     */
    public function __construct(Slim $app)
    {
        $this->app   = $app;
    }

    // ** ------------------------- Validation Helpers ---------------------------- ** //

    /**
     * Adds a custom validation rule using a callback function
     *
     * @access public
     * @param string $rule
     * @param callable $callback
     * @return bool
     */
    public static function add_validator($rule, $callback)
    {
        $method = 'validate_'.$rule;
        if(method_exists(__CLASS__, $method) || isset(self::$validation_methods[$rule])) {
            throw new Exception("A regra de validação '$rule' já existe.");
        }

        self::$validation_methods[$rule] = $callback;

        return true;
    }

    /**
     * Adds a custom filter using a callback function
     *
     * @access public
     * @param string $rule
     * @param callable $callback
     * @return bool
     */
    public static function add_filter($rule, $callback)
    {
        $method = 'filter_'.$rule;
        if(method_exists(__CLASS__, $method) || isset(self::$filter_methods[$rule])) {
            throw new Exception("A regra de filtragem '$rule' já existe.");
        }

        self::$filter_methods[$rule] = $callback;

        return true;
    }

    /**
     * Run the filtering and validation after each other
     *
     * @param array $data
     * @return array
     * @return boolean
     */
    public function run(array $data, $check_fields = false)
    {
        $data = $this->filter($data, $this->filter_rules());

        $validated = $this->validate(
            $data, $this->validation_rules()
        );

        if($check_fields === true) {
            $this->check_fields($data);
        }

        if($validated !== true) {
            return false;
        }

        return $data;
    }

    /**
     * Sanitize the input data
     *
     * @access public
     * @param  array $data
     * @return array
     */
    public function sanitize(array $input, $fields = NULL, $utf8_encode = true)
    {
        $magic_quotes = (bool)get_magic_quotes_gpc();

        if(is_null($fields))
        {
            $fields = array_keys($input);
        }

        foreach($fields as $field)
        {
            if(!isset($input[$field]))
            {
                continue;
            }
            else
            {
                $value = $input[$field];

                if(is_string($value))
                {
                    if($magic_quotes === TRUE)
                    {
                        $value = stripslashes($value);
                    }

                    if(strpos($value, "\r") !== FALSE)
                    {
                        $value = trim($value);
                    }

                    if(function_exists('iconv') && function_exists('mb_detect_encoding') && $utf8_encode)
                    {
                        $current_encoding = mb_detect_encoding($value);

                        if($current_encoding != 'UTF-8' && $current_encoding != 'UTF-16') {
                            $value = iconv($current_encoding, 'UTF-8', $value);
                        }
                    }

                    $value = filter_var($value, FILTER_SANITIZE_STRING);
                }

                $input[$field] = $value;
            }
        }

        return $input;
    }

    /**
     * Perform data validation against the provided ruleset
     *
     * @access public
     * @param  mixed $input
     * @param  array $ruleset
     * @return mixed
     */
    public function validate(array $input, array $ruleset)
    {
        $this->errors = array();

        foreach($ruleset as $field => $rules)
        {
            #if(!array_key_exists($field, $input))
            #{
            #   continue;
            #}

            $rules = explode('|', $rules);

            foreach($rules as $rule)
            {
                $method = NULL;
                $param  = NULL;

                if(strstr($rule, ',') !== FALSE) // has params
                {
                    $rule   = explode(',', $rule);
                    $method = 'validate_'.$rule[0];
                    $param  = $rule[1];
                    $rule   = $rule[0];
                }
                else
                {
                    $method = 'validate_'.$rule;
                }

                if(is_callable(array($this, $method)))
                {
                    $result = $this->$method($field, $input, $param);

                    if(is_array($result)) // Validation Failed
                    {
                        $this->errors[] = $result;
                    }
                }
                else if (isset(self::$validation_methods[$rule]))
                {
                    if (isset($input[$field])) {
                        $result = call_user_func(self::$validation_methods[$rule], $field, $input, $param);

                        if (!$result) // Validation Failed
                        {
                            $this->errors[] = array(
                                'field' => $field,
                                'value' => $input[$field],
                                'rule'  => $method,
                                'param' => $param
                            );
                        }
                    }
                }
                else
                {
                    throw new Exception("O método de validação '$method' não existe.");
                }
            }
        }

        return (count($this->errors) > 0)? $this->errors : TRUE;
    }

    /**
     * Process the validation errors and return human readable error messages
     *
     * @param bool $convert_to_string = false
     * @param string $field_class
     * @param string $error_class
     * @return array
     * @return string
     */
    public function get_readable_errors($convert_to_string = false, $field_class="field", $error_class="error-message")
    {
        if(empty($this->errors)) {
            return ($convert_to_string)? null : array();
        }

        $resp = array();

        foreach($this->errors as $e) {

            $field = ucwords(str_replace(array('_','-'), chr(32), $e['field']));
            $param = $e['param'];

            switch($e['rule']) {
                case 'mismatch' :
                    $resp[] = "Não existe regra de validação para <span class=\"$field_class\">$field</span>";
                    break;
                case 'validate_required':
                    $resp[] = "O campo <span class=\"$field_class\">$field</span> é obrigatório";
                    break;
                case 'validate_valid_email':
                    $resp[] = "O campo <span class=\"$field_class\">$field</span> deve conter um endereço de e-mail válido";
                    break;
                case 'validate_max_len':
                    if($param == 1) {
                        $resp[] = "O campo <span class=\"$field_class\">$field</span> deve ser menor que $param caracter";
                    } else {
                        $resp[] = "O campo <span class=\"$field_class\">$field</span> deve ser menor que $param caracteres";
                    }
                    break;
                case 'validate_min_len':
                    if($param == 1) {
                        $resp[] = "O campo <span class=\"$field_class\">$field</span> deve ser maior que $param caracter";
                    } else {
                        $resp[] = "O campo <span class=\"$field_class\">$field</span> deve ser maior que $param caracteres";
                    }
                    break;
                case 'validate_exact_len':
                    if($param == 1) {
                        $resp[] = "O campo <span class=\"$field_class\">$field</span> deve ser do tamanho exato de $param caracter";
                    } else {
                        $resp[] = "O campo <span class=\"$field_class\">$field</span> deve ser do tamanho exato de $param caracteres";
                    }
                    break;
                case 'validate_alpha':
                    $resp[] = "O campo <span class=\"$field_class\">$field</span> deve conter somente letras (a-z)";
                    break;
                case 'validate_alpha_numeric':
                    $resp[] = "O campo <span class=\"$field_class\">$field</span> deve conter somente caracteres alfanuméricos";
                    break;
                case 'validate_alpha_dash':
                    $resp[] = "O campo <span class=\"$field_class\">$field</span> deve conter somente caracteres alfanuméricos e traços (-)";
                    break;
                case 'validate_numeric':
                    $resp[] = "O campo <span class=\"$field_class\">$field</span> deve conter somente caracteres numéricos";
                    break;
                case 'validate_integer':
                    $resp[] = "O campo <span class=\"$field_class\">$field</span> deve conter somente valor numérico";
                    break;
                case 'validate_boolean':
                    $resp[] = "O campo <span class=\"$field_class\">$field</span> deve conter somente os valores true ou false";
                    break;
                case 'validate_float':
                    $resp[] = "O campo <span class=\"$field_class\">$field</span> deve conter somente um valor float";
                    break;
                case 'validate_valid_url':
                    $resp[] = "O campo <span class=\"$field_class\">$field</span> deve conter uma URL válida";
                    break;
                case 'validate_url_exists':
                    $resp[] = "A URL <span class=\"$field_class\">$field</span> não existe";
                    break;
                case 'validate_valid_ip':
                    $resp[] = "O campo <span class=\"$field_class\">$field</span> deve conter um endereço IP válido";
                    break;
                case 'validate_valid_cc':
                    $resp[] = "O campo <span class=\"$field_class\">$field</span> deve conter um número de cartão de crédito válido";
                    break;
                case 'validate_valid_name':
                    $resp[] = "O campo <span class=\"$field_class\">$field</span> deve conter um nome válido";
                    break;
                case 'validate_contains':
                    $resp[] = "O campo <span class=\"$field_class\">$field</span> deve conter um destes valores: ".implode(', ', $param);
                    break;
                case 'validate_street_address':
                    $resp[] = "O campo <span class=\"$field_class\">$field</span> deve conter um endereço válido";
                    break;
                case 'validate_date':
                    $resp[] = "O campo <span class=\"$field_class\">$field</span> deve conter uma data válida";
                    break;
                case 'validate_min_numeric':
                    $resp[] = "O campo <span class=\"$field_class\">$field</span> deve ser um valor numérico igual a ou maior que $param";
                    break;
                case 'validate_max_numeric':
                    $resp[] = "O campo <span class=\"$field_class\">$field</span> deve ser um valor numérico igual a ou menor que $param";
                    break;
            }
        }

        if(!$convert_to_string) {
            return $resp;
        } else {
            $buffer = '';
            foreach($resp as $s) {
                $buffer .= "<span class=\"$error_class\">$s</span>";
            }
            return $buffer;
        }
    }

    /**
     * Filter the input data according to the specified filter set
     *
     * @access public
     * @param  mixed $input
     * @param  array $filterset
     * @return mixed
     */
    public function filter(array $input, array $filterset)
    {
        foreach($filterset as $field => $filters)
        {
            if(!array_key_exists($field, $input))
            {
                continue;
            }

            $filters = explode('|', $filters);

            foreach($filters as $filter)
            {
                $params = NULL;

                if(strstr($filter, ',') !== FALSE)
                {
                    $filter = explode(',', $filter);

                    $params = array_slice($filter, 1, count($filter) - 1);

                    $filter = $filter[0];
                }

                if(is_callable(array($this, 'filter_'.$filter)))
                {
                    $method = 'filter_'.$filter;
                    $input[$field] = $this->$method($input[$field], $params);
                }
                else if(function_exists($filter))
                {
                    $input[$field] = $filter($input[$field]);
                }
                else if (isset(self::$filter_methods[$filter]))
                {
                    $input[$field] = call_user_func(self::$filter_methods[$filter], $input[$field], $params);
                }
                else
                {
                    throw new Exception("O método filtro '$filter' não existe.");
                }
            }
        }

        return $input;
    }

    // ** ------------------------- Filters --------------------------------------- ** //

    /**
     * Remove todos os acentos de uma string
     *
     * Uso: '<index>' => 'rm_acentos'
     *
     * @access protected
     * @param  string $value
     * @param  array $params
     *
     * @return string
     */
    protected function filter_rm_acentos($value, $params = NULL)
    {
        if(is_null($params)) $params = 'UTF-8';

        /*if (function_exists('iconv')) {
            $text = iconv($params, 'us-ascii//TRANSLIT', $value);

            return  preg_replace('#[^-\w]+#', '', $text);
        }*/

        $acentos = array(
            'A' => '/&Agrave;|&Aacute;|&Acirc;|&Atilde;|&Auml;|&Aring;/',
            'a' => '/&agrave;|&aacute;|&acirc;|&atilde;|&auml;|&aring;/',
            'C' => '/&Ccedil;/',
            'c' => '/&ccedil;/',
            'E' => '/&Egrave;|&Eacute;|&Ecirc;|&Euml;/',
            'e' => '/&egrave;|&eacute;|&ecirc;|&euml;/',
            'I' => '/&Igrave;|&Iacute;|&Icirc;|&Iuml;/',
            'i' => '/&igrave;|&iacute;|&icirc;|&iuml;/',
            'N' => '/&Ntilde;/',
            'n' => '/&ntilde;/',
            'O' => '/&Ograve;|&Oacute;|&Ocirc;|&Otilde;|&Ouml;/',
            'o' => '/&ograve;|&oacute;|&ocirc;|&otilde;|&ouml;/',
            'U' => '/&Ugrave;|&Uacute;|&Ucirc;|&Uuml;/',
            'u' => '/&ugrave;|&uacute;|&ucirc;|&uuml;/',
            'Y' => '/&Yacute;/',
            'y' => '/&yacute;|&yuml;/',
            'a.' => '/&ordf;/',
            'o.' => '/&ordm;/'
        );

        return preg_replace($acentos, array_keys($acentos), htmlentities($value,ENT_NOQUOTES, $params));
    }

    /**
     * Converte uma string para letras maiúsculas
     *
     * Uso: '<index>' => 'maiusculas'
     *
     * @access protected
     * @param  string $value
     * @param  array $params
     *
     * @return string
     */
    protected function filter_maiusculas($value, $params = NULL)
    {
        if(is_null($params)) $params = 'UTF-8';

        if (function_exists('mb_strtoupper')) {
            return mb_strtoupper($value, $params);
        }

        for ($x = 0; $x < count($this->acentos_maiuscula); $x++) {
            $value = str_replace($this->acentos_minuscula[$x], $this->acentos_maiuscula[$x], $value);
        }

        return strtoupper($value);
    }

    /**
     * Converte uma string para letras minúsculas
     *
     * Uso: '<index>' => 'minusculas'
     *
     * @access protected
     * @param  string $value
     * @param  array $params
     *
     * @return string
     */
    protected function filter_minusculas($value, $params = 'utf-8')
    {
        if (function_exists('mb_strtolower')) {
            return mb_strtolower($value, $params);
        }

        for ($x = 0; $x < count($this->acentos_minuscula); $x++) {
            $value = str_replace($$this->acentos_maiuscula[$x], $this->acentos_minuscula[$x], $value);
        }

        return strtolower($value);
    }

    /**
     * Limitação de string sem perder contexto
     *
     * Uso: '<index>' => 'corta_string'
     *
     * @access protected
     * @param  string $value
     * @param  array $params
     *
     * @return string
     */
    protected function filter_corta_string($value, $params = 10)
    {
        if (strlen($value) < $params)
            return $value;

        $aryValor   = explode(" ", $value);
        $intTamanho = 0;
        $intItem    = 0;

        while ($intTamanho < $params) {
            if (($intTamanho + strlen($aryValor[$intItem])) > $params) {
                $strValorLimitado .= " ...";
                break;
            }

            $strValorLimitado .= " " . $aryValor[$intItem];
            $intTamanho += (strlen($aryValor[$intItem])+1);
            $intItem++;

            if ($intTamanho == $params) $strValorLimitado .= " ...";
        }

        return $strValorLimitado;
    }

    // ** ------------------------- Validators ------------------------------------ ** //

}