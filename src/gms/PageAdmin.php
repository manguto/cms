<?php

namespace manguto\gms;

class PageAdmin extends Page
{

    public function __construct($opts = array(), $tpl_dir = '/views/____admin/')
    {   
        parent::__construct($opts,$tpl_dir);
    }
}

?>