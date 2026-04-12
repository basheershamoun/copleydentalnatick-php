<?php
Class Regex{
    const NUMBER_PHONE = '/^((\([0-9]{3}\))|[0-9]{3})[\s\-]?[\0-9]{3}[\s\-]?[0-9]{4}$/';
}
Class FieldErrors{
    const ERROR_LENGTH_MIN_MAX = "The field must have between %d to %d characters.";
    const ERROR_LENGTH_MIN = "The field must have at least %d character(s).";
    const ERROR_LENGTH_MAX = "The field must have up to %d character(s).";
    const ERROR_REQUIRED = "This field is required.";
    const ERROR_EMAIL= "This field is not a valid email address.";
    const ERROR_DATE= "The date is invalid.";
    const ERROR_CHOICE_INVALID ="The choice is invalid.";
    const ERROR_ZIP_INVALID ="The ZIP is invalid.";
    const ERROR_PHONE_INVALID ="The phone number is invalid (example (111) 111-1111).";
    const EMAIL_NOT_SENT= "Sorry, but the verification process failed. Please, try again.";

}
Class Form 
{
    protected $data;
    protected $cleaned_data;
    protected $errors;
    protected $config_recaptcha;
    
    public function __construct($data, $config_recaptcha=array()){
        $this->data= $data;
        $this->cleaned_data = array();
        $this->errors = array();
        $this->config_recaptcha= $config_recaptcha;
    }
    public function get_error_email_sent(){
        return FieldErrors::EMAIL_NOT_SENT;
    }
    
    protected function is_empty($key){
        if(empty($this->data[$key])){
            return true;
        }else{
            return false;
        }
    }
    private function curlessRequest($url,$data){
        $options = array(
            'http' => array(
                'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                'method'  => 'POST',
                'content' => http_build_query($data),
            ),
        );
        $context  = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        return $result;
    }
    protected function validar_recaptcha($key){
        
        $empty = $this->is_empty($key);
        if($empty == true){
            $this->add_error($key, FieldErrors::ERROR_REQUIRED);
            return false;
        }
        $value = $this->get_value($key);
        $ur_verify=$this->config_recaptcha["url_verify"];
        $secret_key=$this->config_recaptcha["secret_key"];
        $data = array(
            'secret' => $secret_key,
            'response' => $value
        );
        $request = $this->curlessRequest($ur_verify,$data);
        $result = json_decode($request, true);
        if($result['success'] == true){
            return true;
        }else {
            $this->add_error($key, FieldErrors::ERROR_REQUIRED);
            return false;
        }
    }
    protected function validate_length($key,  $min=null, $max=null){
        $value = $this->get_value($key);
        $length = strlen($value);
        if($min != null and $max != null){
            if(!($length >= $min  and $length <= $max )){
                $message = sprintf(FieldErrors::ERROR_LENGTH_MIN_MAX, $min, $max);
                $this->add_error($key, $message);
                return false;
            }
        }else if($min != null){
            if($length < $min ){
                $message = sprintf(FieldErrors::ERROR_LENGTH_MIN, $min);
                $this->add_error($key, $message);
                return false;
            }
        }else if($max != null){
            if($length > $max){
                $message = sprintf(FieldErrors::ERROR_LENGTH_MAX, $max);
                $this->add_error($key, $message);
                return false;
            }
        }
        return true;

    }
    protected function is_date($key, $format){
        $value = $this->get_value($key);
        $validate = date_parse_from_format ( $format, $value);
        
        if($validate["warning_count"]>=1 or $validate["error_count"]>=1){
            return false;
        }else{
            return true;
        }

    }
    protected function validate_charfield($key,$required=false, $min=null, $max=null){
        $empty = $this->is_empty($key);
        $error_required = false;
        if($required == true and $empty == true){
            $this->add_error($key, FieldErrors::ERROR_REQUIRED);
            $error_required = true;
        }
        if($error_required == false){
            $this->validate_length($key, $min, $max);
        }

    }
    protected function validate_charfield_regex($key,$required=false, $pattern=null, $message_error=null){
        $empty = $this->is_empty($key);
        $error_required = false;
        if($required == true and $empty == true){
            $this->add_error($key, FieldErrors::ERROR_REQUIRED);
            $error_required = true;
        }
        if($required == true or $empty == false){
            $this->validate_regex($key, $pattern, $message_error);
        }

    }
    protected function validate_regex($key, $pattern, $message_error){
        $value = $this->get_value($key);
        $valid = preg_match($pattern,$value, $matches, PREG_OFFSET_CAPTURE);
        if ( $valid == 0){
            $this->add_error($key, $message_error);
        }
        

    }
    protected function validate_email($key,$required=false, $min=null, $max=null){
        $this->validate_charfield($key, $required, $min, $max);

        $email = $this->get_value($key);
        $valided = filter_var($email, FILTER_VALIDATE_EMAIL);
        if($valided == false){
            $this->add_error($key, FieldErrors::ERROR_EMAIL);
        }
    }
    protected function validate_date($key, $required, $format){

        $empty = $this->is_empty($key);
        $error_required = false;
        if($required == true and $empty == true){
            $this->add_error($key, FieldErrors::ERROR_REQUIRED);
            $error_required = true;
        }
        //validar cuando es requerido o cuando hay informacion en el campo
        if($required == true or $empty == false){
            $validated = $this->is_date($key, $format);
            if($validated == false){
                $this->add_error($key, FieldErrors::ERROR_DATE);
            }
        }
    }
    protected function validate_choice($key, $required, $choices){
        $empty = $this->is_empty($key);
        $error_required = false;
        if($required == true and $empty == true){
            $this->add_error($key, FieldErrors::ERROR_REQUIRED);
            $error_required = true;
        }
        //validar cuando es requerido o cuando hay informacion en el campo
        if($required == true or $empty == false){
            $validated = $this->is_valid_choice($key, $choices);
            if($validated == false){
                $this->add_error($key, FieldErrors::ERROR_CHOICE_INVALID);
            }
        }

    }
    protected function is_valid_choice($key, $choices){
        $value = $this->get_value($key);
        foreach( $choices as $item){
            if($item["value"] == $value){
                return true;
            }
        }
        return false;
    }
    protected function get_choice($key, $choices){
        $value = $this->get_value($key);
        foreach( $choices as $item){
            if($item["value"] == $value){
                return $item['label'];
            }
        }
        return "";
    }
    protected function get_value($key){
        if(!empty($this->data[$key])){
            return $this->data[$key];
        }else{
            return "";
        }
    }
    protected function add_error($key, $message){
        if(!array_key_exists($key, $this->errors)){
            $this->errors[$key] = array();
        }
        array_push($this->errors[$key], $message) ;

    }
    protected function add_key_data($key, $value){
        if(!array_key_exists($key,$this->data)){
            $this->data[$key] = $value;
        }
    }    
    public function get_errors(){
        return $this->errors;
    }
    public function get_data(){
        return $this->data;
    }
    
    public function get_form(){
        $keys = array_keys($this->data);
        $form = array();
        foreach($keys as $key){
            $form[$key]["value"] = $this->data[$key];
            if(!empty($this->errors[$key])){
                $form[$key]["errors"] = $this->errors[$key];
            }
        }
        return $form;
    }
    
} 
Class ContactForm extends Form{
    private $first_name ="first_name";
    private $last_name ="last_name";
    private $email ="email";
    private $phone ="phone";
    private $message ="message";
    private $recaptcha ="g-recaptcha-response";

    public function is_valid(){
        $this->validate_charfield($this->first_name, true, null,50);
        $this->validate_charfield($this->last_name,true, null,50);
        $this->validate_email($this->email, true,null,50);
        $this->validate_charfield_regex(
            $this->phone, 
            true, 
            Regex::NUMBER_PHONE,
            FieldErrors::ERROR_PHONE_INVALID 
        );
        $this->validate_charfield($this->message, true, null,500);
        $this->validar_recaptcha($this->recaptcha);
        $count_errors = count(array_keys($this->errors));
        
        if($count_errors >=1){
            return false;
        }else{
            return true;
        }
        
    }
    public function get_cleaned_data(){
        return array(
            $this->first_name=>htmlentities($this->get_value($this->first_name)),
            $this->last_name=>htmlentities($this->get_value($this->last_name)),
            $this->email=>htmlentities($this->get_value($this->email)),
            $this->phone=>htmlentities($this->get_value($this->phone)),
            $this->message=>htmlentities($this->get_value($this->message))
        );
    }  
}
Class SavingsPlanForm extends Form{    
private $first_name ="first_name";   
 private $last_name ="last_name";    
 private $email ="email";    
 private $phone ="phone";    
 private $plan ="plan";    
 private $recaptcha ="g-recaptcha-response";    
 public function is_valid(){        
 $this->validate_charfield($this->first_name, true, null,50);       
 $this->validate_charfield($this->last_name,true, null,50);        
 $this->validate_email($this->email, true,null,50);       
 $this->validate_charfield_regex(            
 $this->phone,             
 true,             
 Regex::NUMBER_PHONE,            
 FieldErrors::ERROR_PHONE_INVALID         );        
 $this->validar_recaptcha($this->recaptcha);       
 $count_errors = count(array_keys($this->errors));                
 if($count_errors >=1){           
 return false;        
 }
 else{            
 return true;        
 }            
 }   
 public function get_cleaned_data(){        
 return array(            
 $this->first_name=>htmlentities($this->get_value($this->first_name)),           
 $this->last_name=>htmlentities($this->get_value($this->last_name)),           
 $this->email=>htmlentities($this->get_value($this->email)),           
 $this->phone=>htmlentities($this->get_value($this->phone)),            
 $this->plan=>htmlentities($this->get_value($this->plan))       
 );   
 }  
 }
Class RequestAppointmentForm extends Form{
    private $first_name ="first_name";
    private $last_name ="last_name";
    private $date_birth ="date_birth";
    private $address ="address";
    private $city ="city";
    private $state = array(
        "name"=>"state",
        "choices"=>array()
    );
    private $zip='zip';
    private $email='email';
    private $phone = 'phone';
    private $cel = 'cel';
    private $dental_insurance = 'dental_insurance';
    private $insurance_id='insurance_id';
    private $employer = 'employer';
    private $desired_date = 'desired_date';
    private $desired_hour = array(
        "name"=>"desired_hour",
        "choices"=>array()
    );
    private $second_option = array(
        "name"=>"second_option",
        "choices"=>array()
    );
    private $current_patient = array(
        "name"=>"current_patient",
        "choices"=>array(
            array("value"=>'yes', 'label'=>"Yes"),
            array("value"=>'no', 'label'=>"No"),
        )
    );
    private $hear_about_us = 'hear_about_us';
    private $purpose_visit = 'purpose_visit';
    private $recaptcha ="g-recaptcha-response";
    
    
    public function __construct($data, $config_captcha){
        parent::__construct($data, $config_captcha);
        $state_model = new StateModel("publico/datos/request_appointment.json");
        $hour_model = new HourModel("publico/datos/hours.json");
        $this->state["choices"] = $state_model->get_states();
        $this->desired_hour["choices"] = $hour_model->get_hours();
        $this->second_option["choices"] = $hour_model->get_hours();
        
        

    }
    
    public function get_form(){
        //Poner por default a current patient
        $this->add_key_data($this->current_patient['name'], "");
        $form = parent::get_form();
        $form[$this->state["name"]]["choices"] = $this->state["choices"] ;
        $form[$this->desired_hour["name"]]["choices"] = $this->desired_hour["choices"] ;
        $form[$this->second_option["name"]]["choices"] = $this->second_option["choices"] ;
        $form[$this->current_patient["name"]]["choices"] = $this->current_patient["choices"] ;
        return $form; 
    }
    public function is_valid(){
        $this->validate_charfield($this->first_name, true, null,50);
        $this->validate_charfield($this->last_name,true, null,50);
        $this->validate_date($this->date_birth,false, "Y-m-d");
        $this->validate_charfield($this->address,false, null,200);
        $this->validate_charfield($this->city,true, null,40);
        $this->validate_choice($this->state['name'], false, $this->state['choices']);
        $this->validate_charfield_regex(
            $this->zip, 
            true, 
            '/^\d{5}$/',
            FieldErrors::ERROR_ZIP_INVALID 
        );
        $this->validate_email($this->email, true,null,60);
        $this->validate_charfield_regex(
            $this->phone, 
            false, 
            '/^((\([0-9]{3}\))|[0-9]{3})[\s\-]?[\0-9]{3}[\s\-]?[0-9]{4}$/',
            FieldErrors::ERROR_PHONE_INVALID 
        );
        $this->validate_charfield_regex(
            $this->cel, 
            true, 
            '/^((\([0-9]{3}\))|[0-9]{3})[\s\-]?[\0-9]{3}[\s\-]?[0-9]{4}$/',
            FieldErrors::ERROR_PHONE_INVALID 
        );
        $this->validate_charfield($this->dental_insurance,false, null,50);
        $this->validate_charfield($this->insurance_id,false, null,50);
        $this->validate_charfield($this->employer,false, null,60);
        // Appointment information
        $this->validate_date($this->desired_date,false, "Y-m-d");
        $this->validate_choice($this->desired_hour['name'], false, $this->desired_hour['choices']);
        $this->validate_choice($this->second_option['name'], false, $this->second_option['choices']);
        $this->validate_choice($this->current_patient['name'], false, $this->current_patient['choices']);
        $this->validate_charfield($this->hear_about_us,false, null,300);
        $this->validate_charfield($this->purpose_visit,false, null,300);
        $this->validar_recaptcha($this->recaptcha);
        
        
        $count_errors = count(array_keys($this->errors));
        
        if($count_errors >=1){
            return false;
        }else{
            return true;
        }
        
    }
    public function get_cleaned_data(){
        return array(
            $this->first_name=>htmlentities($this->get_value($this->first_name)),
            $this->last_name=>htmlentities($this->get_value($this->last_name)),
            $this->date_birth=>htmlentities($this->get_value($this->date_birth)),
            $this->address=>htmlentities($this->get_value($this->address)),
            $this->city=>htmlentities($this->get_value($this->city)),
            $this->state["name"]=>htmlentities($this->get_choice(
                $this->state["name"],
                $this->state["choices"]
                )
            ),
            $this->zip=>htmlentities($this->get_value($this->zip)),
            $this->email=>htmlentities($this->get_value($this->email)),
            $this->phone=>htmlentities($this->get_value($this->phone)),
            $this->cel=>htmlentities($this->get_value($this->cel)),
            $this->dental_insurance=>htmlentities($this->get_value($this->dental_insurance)),
            $this->insurance_id=>htmlentities($this->get_value($this->insurance_id)),
            $this->employer=>htmlentities($this->get_value($this->employer)),
            $this->desired_date=>htmlentities($this->get_value($this->desired_date)),
            $this->desired_hour['name']=>htmlentities($this->get_choice(
                $this->desired_hour['name'],
                $this->desired_hour['choices']
                )
            ),
            $this->second_option['name']=>htmlentities($this->get_choice(
                $this->second_option['name'],
                $this->second_option['choices']
                )
            ),
            $this->current_patient['name']=>htmlentities($this->get_choice(
                $this->current_patient['name'],
                $this->current_patient['choices']
                )
            ),
            $this->hear_about_us=>htmlentities($this->get_value($this->hear_about_us)),
            $this->purpose_visit=>htmlentities($this->get_value($this->purpose_visit)),
        );

    }


} 

class AskDoctorForm extends Form{
    private $email = 'email';
    private $message = 'message';
    private $recaptcha ="g-recaptcha-response";
    public function is_valid(){
        
        $this->validate_email($this->email, true,null,50);
        $this->validate_charfield($this->message, true, null,300);
        $this->validar_recaptcha($this->recaptcha);
        $count_errors = count(array_keys($this->errors));
        
        if($count_errors >=1){
            return false;
        }else{
            return true;
        }
        
    }
    public function get_cleaned_data(){
        return array(
            $this->email=>htmlentities($this->get_value($this->email)),
            $this->message=>htmlentities($this->get_value($this->message))
        );
    }

}