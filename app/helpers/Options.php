<?php

class Options {

    public static function refresh(){
        $defaults = Config::get('options.defaults');

        foreach($defaults as $d=>$v){

            $opt = Option::where('varname', $d)->first();
            if($opt){

            }else{
                $opt = new Option();
                $opt->varname = $d;
                foreach ($v as $k=>$v){
                    $opt->$k = $v;
                }
                $opt->save();
            }

        }
    }

    public function set($varname, $value){

    }

    public function get($varname, $value){

    }

}
