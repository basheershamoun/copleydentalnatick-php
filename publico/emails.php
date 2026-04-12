<?php
class Template {
    const TEMPLATE_CONTACT_US ='template_contact_us';
	const TEMPLATE_SAVINGS_PLAN ='template_savings_plan';
    const TEMPLATE_ASK_DOCTOR ='template_ask_doctor';
    const TEMPLATE_REQUEST_APPOINTMENT ='template_request_appointment';

    public static function render_template_request_appointment($first_name, $last_name, $date_birth, $address, $city, $state, $zip, $email, $phone, $cel, 
                                                        $dental_insurance, $insurance_id, $employer, $desired_date, $desired_hour, $second_option, $current_patient, $hear_about_us, $purpose_visit ){
        return "<div style='width:100%;'>
            <div>
                <h1>Appointment Request</h1>
                First Name: $first_name <br>
                Last Name: $last_name <br>
                Date of Birth: $date_birth <br>
                Address: $address <br>
                City: $city <br>
                State: $state <br>
                ZIP: $zip <br>
                Email: $email <br>
                Phone: $phone <br>
                Cel: $cel <br>
                Dental Insurance: $dental_insurance <br>
                Insurance ID: $insurance_id <br>
                Employer:$employer  <br>
                Desired Date:$desired_date  <br>
                Desired Hour:$desired_hour <br>
                Second Option:$second_option <br>
                Are you a current Patient? $current_patient <br>
                How did you hear about us:$hear_about_us <br>
                Purpose of Visit:$purpose_visit <br>
            </div>
        </div>";
    }
    public static function render_template_ask_doctor($email, $message){
        return "<div style='width:100%;'>
            <div>
                <h1>Ask the Doctor</h1>
                Email: $email <br>
                Message:<br> $message  <br>
            </div>
        </div>";
    }
    public static function render_template_contact_us($first_name, $last_name, $phone, $email, $message, $title){
        return "<div style='width:100%;'>
            <div>
                <h1>$title</h1>
                First Name: $first_name <br>
                Last Name: $last_name <br>
                Email: $email <br>
                Phone: $phone <br>
                Message:<br> $message  <br>
            </div>
        </div>";
    }
	public static function render_template_savings_plan($first_name, $last_name, $phone, $email, $plan, $title){
        return "<div style='width:100%;'>
            <div>
                <h1>$title</h1>
                First Name: $first_name <br>
                Last Name: $last_name <br>
                Email: $email <br>
                Phone: $phone <br>
                Plan: $plan  <br>
            </div>
        </div>";
    }
    
}
class Email{
    protected $to;
    protected $bbc='i.orosco@epicemarketing.com';
    protected $headers;
    protected $body;
    protected $data;
    protected $subject;
    protected $template;
    public function __construct($to,$subject, $data, $template){
        $this->to=$to;
        $this->subject = $subject;
        $this->data = $data;
        $this->template = $template;
        $this->add_headers();
        $this->select_template();

    }
    protected function add_headers(){
        $headers = "MIME-Version: 1.0\r\n"; 
        $headers .= "Content-type: text/html; charset=UTF-8\r\n"; 
        $headers .= "BCC: $this->bbc \n"; 
        $headers .= "From: Copley Dental Associates <info@copleydental.com>";
        $this->headers =$headers;
    }
    protected function add_body_contact_us(){
        $first_name = $this->data["first_name"];
        $last_name = $this->data["last_name"];
        $email = $this->data["email"];
        $phone = $this->data["phone"];
        $message = $this->data["message"];
        $this->body = Template::render_template_contact_us( $first_name, $last_name,$phone ,$email, $message, $this->subject);   
    }
	protected function add_body_savings_plan(){
        $first_name = $this->data["first_name"];
        $last_name = $this->data["last_name"];
        $email = $this->data["email"];
        $phone = $this->data["phone"];
        $plan = $this->data["plan"];
		echo"El plan es: ". $plan;
        $this->body = Template::render_template_savings_plan( $first_name,$last_name,$phone,$email,$plan,$this->subject);   
    }
    protected function add_body_ask_doctor(){
        $email = $this->data["email"];
        $message = $this->data["message"];
        $this->body = Template::render_template_ask_doctor( $email, $message);   
    }
    protected function add_body_request_appointment(){
        $first_name = $this->data["first_name"];
        $last_name = $this->data["last_name"];
        $date_birth = $this->data["date_birth"];
        $address = $this->data["address"];
        $city = $this->data["city"];
        $state = $this->data["state"];
        $zip = $this->data["zip"];
        $email = $this->data["email"];
        $phone = $this->data["phone"];
        $cel = $this->data["cel"];
        $dental_insurance = $this->data["dental_insurance"];
        $insurance_id = $this->data["insurance_id"];
        $employer = $this->data["employer"];
        $desired_date = $this->data["desired_date"];
        $desired_hour = $this->data["desired_hour"];
        $second_option = $this->data["second_option"];
        $current_patient = $this->data["current_patient"];
        $hear_about_us = $this->data["hear_about_us"];
        $purpose_visit = $this->data["purpose_visit"];

        
        $this->body = Template::render_template_request_appointment(
            $first_name, 
            $last_name,
            $date_birth,
            $address,
            $city,
            $state,
            $zip,
            $email,
            $phone,
            $cel,
            $dental_insurance,
            $insurance_id,
            $employer,
            $desired_date,
            $desired_hour,
            $second_option,
            $current_patient,
            $hear_about_us,
            $purpose_visit
        );   
    }

    protected function select_template(){
        if($this->template == Template::TEMPLATE_CONTACT_US){
            $this->add_body_contact_us();
		}else if($this->template == Template::TEMPLATE_SAVINGS_PLAN){
            $this->add_body_savings_plan();
        }else if($this->template == Template::TEMPLATE_ASK_DOCTOR){
            $this->add_body_ask_doctor();
        }else if($this->template == Template::TEMPLATE_REQUEST_APPOINTMENT){
            $this->add_body_request_appointment();
        }

    }

    public function send(){
        return mail($this->to, $this->subject, $this->body, $this->headers);
    }



} 
