<?php

Class Model {
    protected $data_json;
    public function __construct($path){
        $json_file = file_get_contents($path);
        $json = json_decode($json_file);
        $this->data_json = $json;
    }
}
Class StateModel extends Model{
    protected $states;

    public function __construct($path){
        parent::__construct($path);
        $this->states = array();
        $this->load_states($this->data_json);
        
    }
    private function load_states($json){
        $states = array();
        foreach ($json->states as  $item) {
            array_push($states, array("value"=>$item->value, "label"=>$item->label));
        }
        $this->states =$states;
    }
    public function get_states(){
        return $this->states;
    }

}
class HourModel extends Model{
    protected $hours;

    public function __construct($path){
        parent::__construct($path);
        $this->hours = array();
        $this->load_hours($this->data_json);
        
    }
    private function load_hours($json){
        $hours = array();
        foreach ($json->hours as  $item) {
            array_push($hours, array("value"=>$item->value, "label"=>$item->label));
        }
        $this->hours =$hours;
    }
    public function get_hours(){
        return $this->hours;
    }
}

Class ServiceModel extends Model{
    protected $services ;

    public function __construct($path){
        parent::__construct($path);
        $this->states = array();
        $this->load_services($this->data_json);
        //Update services that have other_service=true
        $this->update_services();
        
    }
    function load_services($json){
        $services = array();
        foreach ($json->services as  $item) {
            array_push($services, array(
                "name"=>$item->name, 
                "slug"=>$item->slug,
                "template"=>$item->template,
                "template"=>$item->template,
                "title"=>$item->title,
                "description"=>$item->description,
                "other_service"=>$item->other_service,
                "slug_other_service"=>$item->slug_other_service,
                )
            );
        }
        $this->services = $services;
    }
    protected function update_services(){
        $services = array();
        foreach($this->services as $item){
            $update_service = $item;
            if($item["other_service"] == true){
                $slug_other = $item["slug_other_service"];
                $other_service =$this->search_slug($slug_other);
                if ($other_service != null){
                    $update_service = $other_service;
                    $update_service["name"] = $item["name"];
                }
            }
            array_push($services, $update_service);
        }
        $this->services = $services;
    }
    public function get_services(){
        return $this->services;
    }
    public function get_service_by_slug($slug){
        $service = $this->search_slug($slug);
        return $service;
    }
    public function search_slug($slug){
        
        foreach($this->services as $service){
            if($service["slug"] == $slug){
                return $service;
            }
        }
        return null;
    }


}

Class GalleryModel extends Model{
    protected $cases;

    public function __construct($path){
        parent::__construct($path);
        $this->cases = array();
        $this->load_cases($this->data_json);
        
    }
    private function load_cases($json){
        $cases = array();
        foreach ($json->cases as  $item) {
            array_push($cases, array(
                "nro"=>$item->nro, 
                "label"=>$item->label,
                "image_before"=>$item->image_before,
                "image_after"=>$item->image_after,
                "image_both"=>$item->image_both, 
                "slug"=>$item->slug,
                "slug_service"=>$item->slug_service,
                "name_url"=>$item->name_url,

                )
            );
        }
        $this->cases =$cases;
    }
    public function get_cases(){
        return $this->cases;
    }

    public function get_tuple_cases(){
        $cases = array();
        $tuple = array();
        $i = 1;
        $length = count($this->cases);
        
        foreach($this->cases as $item){
            array_push($tuple, $item);
            if(($i % 2) == 0){
                array_push($cases, $tuple);
                $tuple = array();
            }
            if($i == $length && count($tuple) == 1){
                array_push($cases, $tuple);
            }
            $i=$i +1;

        }
        return $cases;
    }

    public function search_slug($slug){
        
        foreach($this->cases as $case){
            if($case["slug"] == $slug){
                return $case;
            }
        }
        return null;
    }

}
