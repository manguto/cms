<?php

namespace manguto\gms;

use Rain\Tpl;

class Page{

	private $tpl;
	private $options = [];
	private $optionsDefault = [
		"data"=>[]
	];
	
	public function __construct($opts=array(),$tpl_dir='/views/____site/')
	{	    
		$this->options = array_merge($this->optionsDefault,$opts);		
		//deb($this->options);
		
		// config
		$config = array(
			"tpl_dir"       => ROOT_TPL.$tpl_dir,			
		    "cache_dir"     => ROOT_TPL."/views/_cache/",
			"debug"         => true  // set to false to improve the speed
		);

		Tpl::configure( $config );
		
		// create the Tpl object
		$this->tpl = new Tpl;
		
		$this->assignDataArray($this->options['data']);
		
				
	}
	
	private function assignDataArray($data=array())
	{
		foreach ($data as $key=>$value){		    
			$this->tpl->assign($key,$value);
		}
	}
	
	public function setTpl($filename,$data=array(),$toString=true)
	{
		$this->assignDataArray($data);
		$html = $this->tpl->draw($filename,$toString);
		
		$html = self::TplReferencesFix($html);
		echo $html;
	}
	
	static function TplReferencesFix($html){
	    
	    if(!defined('ROOT')){
	        throw new \Exception("Constante 'ROOT' não encontrada (definida).");
	    }
	    
	    //--- href
	    $html = str_replace('href="','href="'.ROOT,$html);
	    $html = str_replace("href='","href='".ROOT,$html);
	    //--- href errors fix
	    $html = str_replace(ROOT.'javascript','javascript',$html); //<a href='javascript:void(0)'>
	    $html = str_replace(ROOT.'#','#',$html); //<a href='#'>
	    
	    //--- src
	    $html = str_replace('src="','src="'.ROOT,$html);
	    $html = str_replace("src='","src='".ROOT,$html);
	    
	    //--- action
	    $html = str_replace('action="','action="'.ROOT_ACTION,$html);
	    $html = str_replace("action='","action='".ROOT_ACTION,$html);
	    
	    return $html;
	}
	
	public function __destruct()
	{
	   //...
	}	

}



?>