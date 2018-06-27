<?php

/**
 * DATETIME PHP 5.0 Functions Descriptions (http://www.w3schools.com/php/php_ref_date.asp)
 * checkdate()	Validates a Gregorian date
 * date_add()	Adds days, months, years, hours, minutes, and seconds to a date
 * date_create_from_format()	Returns a new DateTime object formatted according to a specified format
 * date_create()	Returns a new DateTime object
 * date_date_set()	Sets a new date
 * date_default_timezone_get()	Returns the default timezone used by all date/time functions
 * date_default_timezone_set()	Sets the default timezone used by all date/time functions
 * date_diff()	Returns the difference between two dates
 * date_format()	Returns a date formatted according to a specified format
 * date_get_last_errors()	Returns the warnings/errors found in a date string
 * date_interval_create_from_date_string()	Sets up a DateInterval from the relative parts of the string
 * date_interval_format()	Formats the interval
 * date_isodate_set()	Sets the ISO date
 * date_modify()	Modifies the timestamp
 * date_offset_get()	Returns the timezone offset
 * date_parse_from_format()	Returns an associative array with detailed info about a specified date, according to a specified format
 * date_parse()	Returns an associative array with detailed info about a specified date
 * date_sub()	Subtracts days, months, years, hours, minutes, and seconds from a date
 * date_sun_info()	Returns an array containing info about sunset/sunrise and twilight begin/end, for a specified day and location
 * date_sunrise()	Returns the sunrise time for a specified day and location
 * date_sunset()	Returns the sunset time for a specified day and location
 * date_time_set()	Sets the time
 * date_timestamp_get()	Returns the Unix timestamp
 * date_timestamp_set()	Sets the date and time based on a Unix timestamp
 * date_timezone_get()	Returns the time zone of the given DateTime object
 * date_timezone_set()	Sets the time zone for the DateTime object
 * date()	Formats a local date and time
 * getdate()	Returns date/time information of a timestamp or the current local date/time
 * gettimeofday()	Returns the current time
 * gmdate()	Formats a GMT/UTC date and time
 * gmmktime()	Returns the Unix timestamp for a GMT date
 * gmstrftime()	Formats a GMT/UTC date and time according to locale settings
 * idate()	Formats a local time/date as integer
 * localtime()	Returns the local time
 * microtime()	Returns the current Unix timestamp with microseconds
 * mktime()	Returns the Unix timestamp for a date
 * strftime()	Formats a local time and/or date according to locale settings
 * strptime()	Parses a time/date generated with strftime()
 * strtotime()	Parses an English textual datetime into a Unix timestamp
 * time()	Returns the current time as a Unix timestamp
 * timezone_abbreviations_list()	Returns an associative array containing dst, offset, and the timezone name
 * timezone_identifiers_list()	Returns an indexed array with all timezone identifiers
 * timezone_location_get()	Returns location information for a specified timezone
 * timezone_name_from_ abbr()	Returns the timezone name from abbreviation
 * timezone_name_get()	Returns the name of the timezone
 * timezone_offset_get()	Returns the timezone offset from GMT
 * timezone_open()	Creates new DateTimeZone object
 * timezone_transitions_get()	Returns all transitions for the timezone
 * timezone_version_get()	Returns the version of the timezone db
 * PHP 5 Predefined Date/Time Constants
 * Constant	Description
 * DATE_ATOM	Atom (example: 2005-08-15T16:13:03+0000)
 * DATE_COOKIE	HTTP Cookies (example: Sun, 14 Aug 2005 16:13:03 UTC)
 * DATE_ISO8601	ISO-8601 (example: 2005-08-14T16:13:03+0000)
 * DATE_RFC822	RFC 822 (example: Sun, 14 Aug 2005 16:13:03 UTC)
 * DATE_RFC850	RFC 850 (example: Sunday, 14-Aug-05 16:13:03 UTC)
 * DATE_RFC1036	RFC 1036 (example: Sunday, 14-Aug-05 16:13:03 UTC)
 * DATE_RFC1123	RFC 1123 (example: Sun, 14 Aug 2005 16:13:03 UTC)
 * DATE_RFC2822	RFC 2822 (Sun, 14 Aug 2005 16:13:03 +0000)
 * DATE_RSS	RSS (Sun, 14 Aug 2005 16:13:03 UTC)
 * DATE_W3C	World Wide Web Consortium (example: 2005-08-14T16:13:03+0000)
 */




namespace manguto\lib;




class Datas{

	/**
	 * RECEBE UMA DATA NO FORMATO DD-MM-YYYY e RETORNA
	 * O TIMESTAMP PARA A MESMA ÀS 00H00-00
	 * $data
	 */
	static function timestampDataPTBR($data){
		if(!is_string($data)){
			throw new \Exception("Data em formato incorreto ($data => ".gettype($data).").");
		}
		//debug($data);

		$data_ = explode('-', $data);
		//debug($data_);

		if(sizeof($data_)!=3) {
			throw new \Exception("Formato de Data incorreto ('$data' => dd-mm-yyyy).");
		}
		$dia = intval($data_[0]);
		$mes = intval($data_[1]);
		$ano = intval($data_[2]);
		//debug("$ano | $mes | $dia",0);

		if(checkdate($mes, $dia , $ano)==false){ 
			throw new \Exception("Data incorreta ('$data' => inexistente).");
		}

		$return = mktime(0,0,0,$mes,$dia,$ano);
		//debug($return);
		return $return;
	}


	/**
	 * FORMATACAO PADRÃO PARA DATA PT-BR com DD(2), MM(2) e ANO (4)
	 *  $data
	 */
	static function ajusteFormatoPadraoDataPTBR($data,$separadorParaRetorno='-'){
		$dataFormatada = '';
		
		{
			//analise separador de entrada
			$caso1 = strpos($data,'-');
			$caso2 = strpos($data,'/');
			
			if($caso1){
				$separadorDeEntrada = '-';
			}else if($caso2){
				$separadorDeEntrada = '/';
			}else{
				throw new \Exception("O formato da data informado precisa ter como separadores ou '-' ou '/' ('$data').");
			}
		}
				
		$dataArray = explode($separadorDeEntrada, $data);
		if(sizeof($dataArray)!=3) {
			throw new \Exception("Data em formato incorreto ('$data').");
		}
		
		$dia = intval($dataArray[0]);
		$mes = intval($dataArray[1]);
		$ano = intval($dataArray[2]);			
		//debug("$dia $mes $ano");
		
		$verificarData = checkdate($mes,$dia,$ano);
		//debug($verificarData);
		if(!$verificarData)throw new \Exception("Data incorreta ou impossível! Favor verificar! ($data => $mes/$dia/$ano).");	
		
		$dataFormatada = date('d-m-Y',mktime(0,0,0,$mes,$dia,$ano));
				
		return $dataFormatada;
	}
	
	
	/**
	 * RECEBE UMA DATA (antes de 19-01-2038 00:14:07) 
	 * EM UM FORMATO INFORMADO e RETORNA O TIMESTAMP 
	 * PARA A MESMA ÀS 00H00-00 
	 * $data
	 */
	static function timestampDataHoraFormato($formato='d-m-Y',$data){

		$datetime = date_create_from_format($formato,$data);
		//debug($datetime);

		$timestamp = date_timestamp_get($datetime);
		//debug(date('H:i:s d-m-Y',$timestamp));

		//VALIDAÇÃO DE DATA #01
		if($datetime===false) throw new \Exception("Data|Hora incorreta ('$data' F!= '$formato').");

		//VALIDAÇÃO DE DATA #02
		$dataGerada = date($formato,$timestamp);
		if($data!=$dataGerada) throw new \Exception("A Data|Hora informada ('$data') está incorreta para o formato informado ('$formato'). A data|hora obtida está com (de|a)créscimo ('$dataGerada').");

		return $timestamp;
	}



	static function obterNumeroMesesEntreDatas($data1,$data2,$formato='d-m-Y'){

		$ts1 = self::timestampDataHoraFormato($formato,$data1);
		$ts2 = self::timestampDataHoraFormato($formato,$data2);

		$return = self::obterNumeroMesesEntreTimestamps($ts1, $ts2);

		return $return;
	}



	static function obterNumeroMesesEntreTimestamps($ts1,$ts2){
		if($ts2<$ts1){
			$ts1_temp = $ts1;
			
			$ts1 = $ts2;
			$ts2 = $ts1_temp;
		}
		
		$year1 = date('Y', $ts1);
		$year2 = date('Y', $ts2);

		$month1 = date('m', $ts1);
		$month2 = date('m', $ts2);

		$diff = (($year2 - $year1) * 12) + ($month2 - $month1);

		return $diff;
	}


	/**
	 * OBTEM O ULTIMO DIA DO MES,ANO INFORMADOS
	 * $mes
	 * $ano
	 */
	static public function ultimoDiaMes($mes, $ano) {
	    //tratamento de erro quanto ao inicio ou fim do ano
	    extract(self::Correcao($ano, $mes));
	    
		// Using first day of the month, it doesn't really matter
		$date = $ano."-".$mes."-1";
		$return = date("t", strtotime($date));
		return $return;
	}



	/**
	 * OBTEM UMA DATA NO FORMADO YYYY-MM e RETORNA
	 * O TIMESTAMP PARA O DIA 1º DESTA DATA ÀS 00H00-00
	 * $data
	 */
	static function timestampDataAnoMes($data){

		if(!is_string($data)) return false;
		//debug($data);

		$data_ = explode('-', $data);
		//debug($data_);

		if(sizeof($data_)!=2) return false;
		$ano = intval($data_[0]);
		$mes = intval($data_[1]);
		//debug("$ano / $mes");

		if(checkdate($mes, 1, $ano)==false) return false;

		$return = mktime(0,0,0,$mes,1,$ano);
		//debug($return);
		return $return;
	}



	/**
	 * OBTEM UMA DATA NO FORMADO YYYY-MM-DD e RETORNA
	 * O TIMESTAMP DESTA DATA ÀS 00H00-00
	 * $data
	 */
	static function timestampData($data,$hora=0,$min=0,$seg=0){
		//debug($data);
		if(!is_string($data)){
			throw new \Exception("A data informada está em formato incorreto ('$data' => '".gettype($data)."').");
		}

		$data_ = explode('-', $data);
		//debug($data_);

		if(sizeof($data_)!=3) return false;
		$ano = intval($data_[0]);
		$mes = intval($data_[1]);
		$dia = intval($data_[2]);
		//debug("$ano / $mes  / $dia");

		if(checkdate($mes, $dia, $ano)==false){
			throw new \Exception("A data informada está incorreta ('$data').");
		}

		$return = mktime($hora,$min,$seg,$mes,$dia,$ano);
		//debug($return);
		return $return;
	}

	/**
	 * retorna o NUMERO do mes informado em PT-BR
	 *  $mesNome
	 * @throws \Exception
	 * @return number
	 */
	static function NumeroDoMes($mesNome){
	    for($m=1;$m<=12;$m++){
	        if(strtolower(trim($mesNome))== strtolower(self::NomeDoMes($m))){
	            return $m;
	        }
	    }
	    throw new \Exception("O número do mês informado não foi encontrado ($mesNome).");
	}
	/**
	 * retorna o NOME do mes em PT-BR
	 *  $mesNumero
	 * @return string
	 */
	static function NomeDoMes($mesNumero){	    
	    $mesNumero = intval($mesNumero);
	    
	    $mesNome = array();
	    $mesNome[1] = 'Janeiro';
	    $mesNome[2] = 'Fevereiro';
	    $mesNome[3] = 'Março';
	    $mesNome[4] = 'Abril';
	    $mesNome[5] = 'Maio';
	    $mesNome[6] = 'Junho';
	    $mesNome[7] = 'Julho';
	    $mesNome[8] = 'Agosto';
	    $mesNome[9] = 'Setembro';
	    $mesNome[10] = 'Outubro';
	    $mesNome[11] = 'Novembro';
	    $mesNome[12] = 'Dezembro';
	    	    
	    if(isset($mesNome[$mesNumero])){
	        return $mesNome[$mesNumero];
	    }else{
	       return 'Mês Desconhecido: '.$mesNumero;  
	    }
	}

    /**
     * Corecao de datas em casos de somas de meses ou dias
     * Ex.: 01/13/2018 = 01/01/2019 
     *  $ano
     *  $mes
     * @param number $dia
     * @return number
     */
	static function Correcao($ano,$mes,$dia=null){   
	    if($dia==null){
	        $timestamp = mktime(0,0,0,$mes,1,$ano);
	        $return['ano'] = intval(date('Y',$timestamp));
	        $return['mes'] = intval(date('m',$timestamp));	        
	    }else{
	        $timestamp = mktime(0,0,0,$mes,$dia,$ano);
	        $return['ano'] = intval(date('Y',$timestamp));
	        $return['mes'] = intval(date('m',$timestamp));
	        $return['dia'] = intval(date('d',$timestamp));
	    }	
	    return $return;
	    //debug(Datas::Correcao(2018,02,29));
	}

	//...

}

//debug(date('d-m-Y',Datas::timestampDataHoraFormato('d-m-Y','32-12-2016')));
//debug(Datas::obterNumeroMesesEntreDatas('01-01-2016', '12-10-2016'));
//debug(Datas::timestampDataHoraFormato('d-m-Y H:i:s','19-01-2038 00:14:07'));





