<?php
class Controller {
    protected $view;

    public function __construct(\Slim\Views\Twig $view, $config, $router=null) {
        $this->view = $view;
        $this->config = $config;
        $this->router = $router;

    }

}
class HomeControlador extends Controller
{
    public function home($request, $response, $args) {
        
        
        return $this->view->render($response, 'home.html') ;
    }
}
class ServicesController extends Controller
{
    const SLUG_DENTAL_VENEERS = 'dental-veneers';
    const SLUG_SERVICES = 'cosmetic-dentistry';
    const SLUG_INVISALIGN = 'invisalign';
    const SLUG_LUMINEERS = 'lumineers';
    
    // Los servicios por peticion GET
    public function service_get($request, $response, $args){
        $context=array();
        $page="";
        if(array_key_exists("page", $args) == true){
            $page = $args["page"];
        }
        $form = new ContactForm(array(), $this->config["recaptcha"]);
        $slug = $args["service"];
        
        $services_model = new ServiceModel("publico/datos/services.json"); 
        $service = $services_model->get_service_by_slug($slug);
        if($service == null){
            return ErrorController::error_404($request, $response, $this->view);
        }
        
        $context["form"] = $form->get_form();
        $context["title"] = $service["title"];
        $context["description"] = $service["description"];
        $context["slug"] = $service["slug"];
        $context["page"] = $page;

        return $this->view->render($response, $service["template"],$context) ;
    }
    // Los servicios por peticion POST
    public function service_post($request, $response, $args){
        $context=array();
        $slug = $args["service"];
        $page="";
        if(array_key_exists("page", $args) == true){
            $page = $args["page"];
        }
        $services_model = new ServiceModel("publico/datos/services.json"); 
        $service = $services_model->get_service_by_slug($slug);
        $data = $request->getParsedBody();
        $form = new ContactForm($data, $this->config["recaptcha"]);
        
        $is_valid = $form->is_valid();
        if($is_valid === True){
            $cleaned_data = $form->get_cleaned_data();
            $email_send =$this->config["configuracion"]["correo_electronico_enviar"];
            $email = new Email($email_send, "Request a consultation", $cleaned_data, Template::TEMPLATE_CONTACT_US);
            $sent = $email->send();
            if($sent == true){
                $uri = $request->getUri()->withPath($this->router->pathFor('thank_you_contact_us_service')); 
                return $response->withRedirect((string)$uri);
            }else{
                $context["contact_us_form_error"] = $form->get_error_email_sent();        
            }
        }
        $context["form"] = $form->get_form();
        $context["title"] = $service["title"];
        $context["description"] = $service["description"];
        $context["slug"] = $service["slug"];
        $context["page"] = $page;
        return $this->view->render($response, $service["template"],$context) ;
    }
    public function thank_you_contact_us($request, $response){
        return $this->view->render($response, "services/thank_you_contact_us.html") ;
    }
    /*
        Redirecciona a la pagin de gracias de los servicios
    */
    protected function redirect_thak_you_contact_us_service($request, $response){
        $uri = $request->getUri()->withPath($this->router->pathFor('thank_you_contact_us_service')); 
        return $response->withRedirect((string)$uri);
    }
    
    /*
        Formulario de contactanos de los servicios, es una plantilla.
    */
    protected function __contact_us_form_services($request, $response, $context, $template){
        if($request->isGet() == true ){
            $form = new ContactForm(array(), $this->config["recaptcha"]);
            $context["form"] = $form->get_form();
        }else{
            $data = $request->getParsedBody();
            $form = new ContactForm($data, $this->config["recaptcha"]);
            $is_valid = $form->is_valid();
            if($is_valid === True){
                $cleaned_data = $form->get_cleaned_data();
                $email_send =$this->config["configuracion"]["correo_electronico_enviar"];
                $email = new Email($email_send, "Request a consultation", $cleaned_data, Template::TEMPLATE_CONTACT_US);
                $sent = $email->send();
                if($sent == true){
                    return $this->redirect_thak_you_contact_us_service($request,$response);
                }else{
                    $context["contact_us_form_error"] = $form->get_error_email_sent();        
                }
                
            }
            $context["form"] = $form->get_form();
        }
        return $this->view->render($response, $template, $context);
    }
	protected function __savings_plan_form_services($request, $response, $context, $template){
        if($request->isGet() == true ){
            $form = new SavingsPlanForm(array(), $this->config["recaptcha"]);
            $context["form"] = $form->get_form();
        }else{
            $data = $request->getParsedBody();
            $form = new SavingsPlanForm($data, $this->config["recaptcha"]);
            $is_valid = $form->is_valid();
            if($is_valid === True){
                $cleaned_data = $form->get_cleaned_data();
                $email_send =$this->config["configuracion"]["correo_electronico_enviar"];
                $email = new Email($email_send, "Request a consultation", $cleaned_data, Template::TEMPLATE_SAVINGS_PLAN);
                $sent = $email->send();
                if($sent == true){
                    return $this->redirect_thak_you_savings_plan_service($request,$response);
                }else{
                    $context["savings_plan_form_error"] = $form->get_error_email_sent();        
                }
                
            }
            $context["form"] = $form->get_form();
        }
        return $this->view->render($response, $template, $context);
    }
     protected function redirect_thak_you_savings_plan_service($request, $response){
        $uri = $request->getUri()->withPath($this->router->pathFor('thank_you_savings_plan_service')); 
        return $response->withRedirect((string)$uri);
    }
    /*
        Video Copley 
    */ 
    public function video_copley($request, $response){
        $context= array();
        return $this->__contact_us_form_services($request, $response, $context, 'services/video_copley.html');
    }
	 /*
        savings plan
    */ 
    public function savings_plan($request, $response){
        $context= array();
        return $this->__contact_us_form_services($request, $response, $context, 'services/savings_plan.html');
    }
	 /*
        savings plan form family
    */ 
    public function savings_plan_form_family($request, $response){
        $context = array();
        if($request->isGet()){
            $form = new SavingsPlanForm(array(), $this->config["recaptcha"]);
            $context["form"] = $form->get_form();
        }else{
            $data = $request->getParsedBody();
            $form = new SavingsPlanForm($data, $this->config["recaptcha"]);
            $is_valid = $form->is_valid();
            if($is_valid === True){
                $cleaned_data = $form->get_cleaned_data();
                $email_send =$this->config["configuracion"]["correo_electronico_enviar"];
                $email = new Email($email_send, "Savings Plan Family", $cleaned_data, Template::TEMPLATE_SAVINGS_PLAN);
                $sent = $email->send();
                if($sent == true){
                    return $this->redirect_thank_you_savings_plan_family($request,$response);
                }else{
                    $context["savings_plan_form_error"] = $form->get_error_email_sent();        
                }
                
            }
            $context["form"] = $form->get_form();
        }
        return $this->view->render($response, 'services/savings_plan_form_family.html', $context);

    }
	 /*
        savings plan form individual
    */ 
   public function savings_plan_form_individual($request, $response){
        $context = array();
        if($request->isGet()){
            $form = new SavingsPlanForm(array(), $this->config["recaptcha"]);
            $context["form"] = $form->get_form();
        }else{
            $data = $request->getParsedBody();
            $form = new SavingsPlanForm($data, $this->config["recaptcha"]);
            $is_valid = $form->is_valid();
            if($is_valid === True){
                $cleaned_data = $form->get_cleaned_data();
                $email_send =$this->config["configuracion"]["correo_electronico_enviar"];
                $email = new Email($email_send, "Savings Plan", $cleaned_data, Template::TEMPLATE_SAVINGS_PLAN);
                $sent = $email->send();
                if($sent == true){
                    return $this->redirect_thank_you_savings_plan($request,$response);
                }else{
                    $context["savings_plan_form_error"] = $form->get_error_email_sent();        
                }
                
            }
            $context["form"] = $form->get_form();
        }
        return $this->view->render($response, 'services/savings_plan_form_individual.html', $context);
    }
	
	 /*
        savings plan form periodontal maintenance
    */ 
	public function savings_plan_form_periodontal_maintenance($request, $response){
       $context = array();
        if($request->isGet()){
            $form = new SavingsPlanForm(array(), $this->config["recaptcha"]);
            $context["form"] = $form->get_form();
        }else{
            $data = $request->getParsedBody();
            $form = new SavingsPlanForm($data, $this->config["recaptcha"]);
            $is_valid = $form->is_valid();
            if($is_valid === True){
                $cleaned_data = $form->get_cleaned_data();
                $email_send =$this->config["configuracion"]["correo_electronico_enviar"];
                $email = new Email($email_send, "Savings Plan", $cleaned_data, Template::TEMPLATE_SAVINGS_PLAN);
                $sent = $email->send();
                if($sent == true){
                    return $this->redirect_thank_you_savings_plan($request,$response);
                }else{
                    $context["savings_plan_form_error"] = $form->get_error_email_sent();        
                }
                
            }
            $context["form"] = $form->get_form();
        }
        return $this->view->render($response, 'services/savings_plan_form_periodontal_maintenance.html', $context);
    }
	 /*
        savings plan form thank you
    */ 
    public function thank_you_savings_plan($request, $response){
        $context= array();
		return $this->view->render($response, 'services/thank_you_savings_plan.html');
        //return $this->__savings_plan_form_services($request, $response, $context, 'services/thank_you_savings_plan.html');
    }
	protected function redirect_thank_you_savings_plan($request, $response){
        $uri = $request->getUri()->withPath($this->router->pathFor('thank_you_savings_plan')); 
        return $response->withRedirect((string)$uri);
    }
	public function thank_you_savings_plan_family($request, $response){
        $context= array();
		return $this->view->render($response, 'services/thank_you_savings_plan_family.html');
        //return $this->__savings_plan_form_services($request, $response, $context, 'services/thank_you_savings_plan.html');
    }
	protected function redirect_thank_you_savings_plan_family($request, $response){
        $uri = $request->getUri()->withPath($this->router->pathFor('thank_you_savings_plan_family')); 
        return $response->withRedirect((string)$uri);
    }
	public function thank_you_savings_plan_periodontal_maintenance($request, $response){
        $context= array();
		return $this->view->render($response, 'services/thank_you_savings_plan_periodontal_maintenance.html');
        //return $this->__savings_plan_form_services($request, $response, $context, 'services/thank_you_savings_plan.html');
    }
	protected function redirect_thank_you_savings_plan__periodontal_maintenance($request, $response){
        $uri = $request->getUri()->withPath($this->router->pathFor('thank_you_savings_plan_periodontal_maintenance')); 
        return $response->withRedirect((string)$uri);
    }
    /*
        Preguntas frecuentres de dental veneers 
    */ 
    public function dental_veneers_faq($request, $response){
        $context= array("slug_dental_veneers"=>ServicesController::SLUG_DENTAL_VENEERS);
        return $this->__contact_us_form_services($request, $response, $context, 'services/dental_veneers/frequent_questions.html');
    }
    /*
        Zoom! Teeth Whitening Treatment
    */ 
    public function zoom_teeth_whitening_treatment($request, $response){
        return $this->__contact_us_form_services($request, $response, $context, 'services/zoom_teeth_whitening/index.html');
    }
    /*
        home teeth whitening treatment
    */
    public function home_teeth_whitening_treatment($request, $response){
        return $this->__contact_us_form_services($request, $response, $context, 'services/home_teeth_whitening/index.html');
    }
    /*
        White Dental Fillings
    */
    public function white_dental_fillings($request, $response){
        return $this->__contact_us_form_services($request, $response, $context, 'services/white_dental_fillings/index.html');
    }
    /*
        All Porcelain Dental Crowns  
     */
    public function all_porcelain_dental_crowns($request, $response){
        return $this->__contact_us_form_services($request, $response, $context, 'services/all_porcelain_dental_crowns/index.html');
    }
    /*
      Procera AllCeram Crowns  
    */
    public function procera_allceram_crowns($request, $response){
        return $this->__contact_us_form_services($request, $response, $context, 'services/procera_allceram_crowns/index.html');
    }


}
class DentalEmergencyController extends ServicesController{

    public function dental_emergency($request, $response){
        $args = array("service"=>"emergency-dentist", "page"=>"emergency-dentist");
        if($request->isGet() == true){
            return $this->service_get($request,$response, $args);
        }else if( $request->isPost() == true){
            return $this->service_post($request,$response, $args);
        }
    }
}
class RequestAppointmentController extends Controller{

    public function request_appointment($request, $response){
        $context = array();
        if($request->isPost()){
            $data = $request->getParsedBody();
            
            $form = new RequestAppointmentForm($data, $this->config["recaptcha"]);
            $is_valid = $form->is_valid();
            if($is_valid === True){
                $cleaned_data = $form->get_cleaned_data();
                $email_send =$this->config["configuracion"]["correo_electronico_enviar"];
                $email = new Email($email_send, "Request Appointment", $cleaned_data, Template::TEMPLATE_REQUEST_APPOINTMENT);
                $sent = $email->send();
                if($sent == true){
                    return $this->redirect_thank_you($request,$response);
                }else{
                    $context["request_appointment_form_error"] = $form->get_error_email_sent();        
                }
            }    
        }else{
            $form = new RequestAppointmentForm(array(), $this->config["recaptcha"]);
        }
        $context["form"] = $form->get_form();
        
        return $this->view->render($response, 'request_appointment/index.html',$context) ;

    }
    public function thank_you($request, $response){
        return $this->view->render($response, 'request_appointment/thank_you.html');
    }

    /*
        Redirecciona a la pagin de gracias 
    */
    protected function redirect_thank_you($request, $response){
        $uri = $request->getUri()->withPath($this->router->pathFor('thank_you_request_appointment')); 
        return $response->withRedirect((string)$uri);
    }


}
class OurTeamController extends Controller{

    public function our_team($request, $response){
        return $this->view->render($response,"our_team/index.html");
    }
}

class InvisalignController extends ServicesController{

    public function what_is_invisalign($request, $response){
        $context = array("slug_invisalign"=>ServicesController::SLUG_INVISALIGN);
        return $this->__contact_us_form_services($request, $response,$context,'services/invisalign/what_is_invisalign.html');
        
    }
    public function treatment_process($request, $response){
        $context = array("slug_invisalign"=>ServicesController::SLUG_INVISALIGN);
        return $this->__contact_us_form_services($request, $response,$context,'services/invisalign/treatment_process.html');
        
    }
    public function treatment_comparison($request, $response){
        $context = array("slug_invisalign"=>ServicesController::SLUG_INVISALIGN);
        return $this->__contact_us_form_services($request, $response,$context,'services/invisalign/treatment_comparison.html');
        
    }
    public function invisalign_for_teenagers($request, $response){
        $context = array("slug_invisalign"=>ServicesController::SLUG_INVISALIGN);
        return $this->__contact_us_form_services($request, $response,$context,'services/invisalign/invisalign_for_teenagers.html');
        
    }
    public function viviera_retainers($request, $response){
        $context = array("slug_invisalign"=>ServicesController::SLUG_INVISALIGN);
        return $this->__contact_us_form_services($request, $response,$context,'services/invisalign/viviera_retainers.html');
        
    }
    public function can_invisalign_work_for_you($request, $response){
        $context = array("slug_invisalign"=>ServicesController::SLUG_INVISALIGN);
        return $this->__contact_us_form_services($request, $response,$context,'services/invisalign/can_invisalign_work_for_you.html');
        
    }
    public function results($request, $response){
        $context = array("slug_invisalign"=>ServicesController::SLUG_INVISALIGN);
        return $this->__contact_us_form_services($request, $response,$context,'services/invisalign/results.html');
        
    }
    public function stories_about_smiles($request, $response){
        $context = array("slug_invisalign"=>ServicesController::SLUG_INVISALIGN);
        return $this->__contact_us_form_services($request, $response,$context,'services/invisalign/stories_about_smiles.html');
        
    }
}
class ContactUsController extends Controller{

    public function contact_us($request, $response){
        $context = array();
        if($request->isGet()){
            $form = new ContactForm(array(), $this->config["recaptcha"]);
            $context["form"] = $form->get_form();
        }else{
            $data = $request->getParsedBody();
            $form = new ContactForm($data, $this->config["recaptcha"]);
            $is_valid = $form->is_valid();
            if($is_valid === True){
                $cleaned_data = $form->get_cleaned_data();
                $email_send =$this->config["configuracion"]["correo_electronico_enviar"];
                $email = new Email($email_send, "Contact Us", $cleaned_data, Template::TEMPLATE_CONTACT_US);
                $sent = $email->send();
                if($sent == true){
                    return $this->redirect_thank_you_contact_us($request,$response);
                }else{
                    $context["contact_us_form_error"] = $form->get_error_email_sent();        
                }
                
            }
            $context["form"] = $form->get_form();
        }
        return $this->view->render($response, 'contact_us/index.html', $context);
    }
    public function thank_you($request, $response){
        return $this->view->render($response,'contact_us/thank_you.html');
    }
    protected function redirect_thank_you_contact_us($request, $response){
        $uri = $request->getUri()->withPath($this->router->pathFor('thank_you_contact_us')); 
        return $response->withRedirect((string)$uri);
    }
}

class SocialResponsibilityController extends Controller{

    public function corporate_social_responsibility($request, $response){

        return $this->view->render($response,'social_responsibility/index.html');

    }
    public function ecuador_humanitarian_mission_2010($request, $response){
        return $this->view->render($response, 'social_responsibility/ecuador_humanitarian_mission_2010.html');
    }

    public function ecuador_humanitarian_mission_2010_video($request, $response){
        return $this->view->render($response, 'social_responsibility/ecuador_humanitarian_mission_2010_video.html');
    }

    public function ecuador_medical_mission_for_children_2011($request, $response){
        return $this->view->render($response, 'social_responsibility/ecuador_medical_mission_for_children_2011.html');
    }

    public function ecuador_medical_mission_for_children_2011_video($request, $response){
        return $this->view->render($response, 'social_responsibility/ecuador_medical_mission_for_children_2011_video.html');
    }

    public function guatemala_humanitarian_mission_2011($request, $response){
        return $this->view->render($response, 'social_responsibility/guatemala_humanitarian_mission_2011.html');
    }

    public function guatemala_humanitarian_mission_2011_video($request, $response){
        return $this->view->render($response, 'social_responsibility/guatemala_humanitarian_mission_2011_video.html');
    }

    public function guatemala_humanitarian_mission_2014($request, $response){
        return $this->view->render($response, 'social_responsibility/guatemala_humanitarian_mission_2014.html');
    }
    public function guatemala_humanitarian_mission_2014_video($request, $response){
        return $this->view->render($response, 'social_responsibility/guatemala_humanitarian_mission_2014_video.html');
    }

    public function guatemala_humanitarian_mission_2015($request, $response){
        return $this->view->render($response, 'social_responsibility/guatemala_humanitarian_mission_2015.html');
    }    	public function philippines_humanitarian_mission_2019_video($request, $response){        return $this->view->render($response, 'social_responsibility/philippines_humanitarian_mission_2019_video.html');    }

    public function medical_missions_103_MMFC_video($request, $response){
        return $this->view->render($response, 'social_responsibility/medical_missions_103_MMFC_video.html');
    } 
}

class FaqsController extends Controller{

    public function faqs($request, $response){
        return $this->view->render($response, 'faqs/index.html');
    }
}

class DentalInformationController extends ServicesController{

    public function bad_breath($request, $response){
        $context = array();
        return $this->__contact_us_form_services($request, $response,$context,'dental_information/bad_breath/index.html');
    }

    public function dental_sealants($request, $response){
        $context = array();
        return $this->__contact_us_form_services($request, $response,$context,'dental_information/dental_sealants/index.html');
    }
    
    public function digital_x_rays($request, $response){
        $context = array();
        return $this->__contact_us_form_services($request, $response,$context,'dental_information/digital_x_rays/index.html');
    }

    public function fluoride($request, $response){
        $context = array();
        return $this->__contact_us_form_services($request, $response,$context,'dental_information/fluoride/index.html');
    }
    public function healthy_gums($request, $response){
        $context = array();
        return $this->__contact_us_form_services($request, $response,$context,'dental_information/healthy_gums/index.html');
    }

    public function smoking_and_oral_health($request, $response){
        $context = array();
        return $this->__contact_us_form_services($request, $response,$context,'dental_information/smoking_and_oral_health/index.html');
    }
    public function snoring($request, $response){
        $context = array();
        return $this->__contact_us_form_services($request, $response,$context,'dental_information/snoring/index.html');
    }

    public function oral_cancer($request, $response){
        $context = array();
        return $this->__contact_us_form_services($request, $response,$context,'dental_information/oral_cancer/index.html');
    }

}

class GalleryController extends Controller{

    public function gallery($request, $response){
        $context = array();
        $gallery = new GalleryModel("publico/datos/gallery.json");
        $cases = $gallery->get_tuple_cases();
        $context["cases"] = $cases;
        $this->view->render($response, "gallery/index.html", $context);
    }

    public function view_case($request, $response, $args){
        $context = array();
        $slug = $args["case"];
        $gallery = new GalleryModel("publico/datos/gallery.json");
        $case =$gallery->search_slug($slug);
        if($case == null){
            return ErrorController::error_404($request, $response, $this->view);    
        }
        $context["case"] = $case;
        return $this->view->render($response, "gallery/view_case.html", $context);

    }
}

class PatientInformationController extends ServicesController{

    public function your_first_visit($request, $response){
        $context = array();
        return $this->__contact_us_form_services($request, $response,$context,'patient_information/your_first_visit.html');
    } 
    public function patient_forms($request, $response){
        $context = array();
        return $this->__contact_us_form_services($request, $response,$context,'patient_information/patient_forms.html');
    }

    public function financial_and_insurance_information($request, $response){
        $context = array();
        return $this->__contact_us_form_services($request, $response,$context,'patient_information/financial_and_insurance_information.html');
    }

    public function privacy_policy($request, $response){
        $context = array();
        return $this->__contact_us_form_services($request, $response,$context,'patient_information/privacy_policy.html');
    }
    public function terms_and_conditions($request, $response){
        $context = array();
        return $this->__contact_us_form_services($request, $response,$context,'patient_information/terms_and_conditions.html');
    }
    
}

class AskDoctorController extends Controller{
    
    public function ask_doctor($request, $response){
        $context = array();
        if($request->isGet() == true ){
            $form = new AskDoctorForm(array(), $this->config["recaptcha"]);
            $context["form"] = $form->get_form();
        }else{
            $data = $request->getParsedBody();
            $form = new AskDoctorForm($data, $this->config["recaptcha"]);
            $is_valid = $form->is_valid();
            if($is_valid === True){
                $cleaned_data = $form->get_cleaned_data();
                $email_send =$this->config["configuracion"]["correo_electronico_enviar"];
                $email = new Email($email_send, "Ask the doctor", $cleaned_data, TEMPLATE::TEMPLATE_ASK_DOCTOR);
                $sent = $email->send();
                if($sent == true){
                    return $this->redirect_thank_you($request,$response);
                }else{
                    $context["ask_doctor_form_error"] = $form->get_error_email_sent();        
                }
                
            }
            $context["form"] = $form->get_form();
        }
        return $this->view->render($response, 'ask_doctor/index.html', $context);
    }
    public function thank_you($request, $response){
        return $this->view->render($response, 'ask_doctor/thank_you.html');
    }

    protected function redirect_thank_you($request, $response){
        $uri = $request->getUri()->withPath($this->router->pathFor('thank_you_ask_doctor')); 
        return $response->withRedirect((string)$uri);
    }
}

class ErrorController {

    
    public static function error_404($request, $response, $view){
        $view->render($response, 'errors/404.html');
        return $response->withStatus(404);
    }
    public static function error_500($request, $response, $view){
        $view->render($response, 'errors/500.html');
        return $response->withStatus(500);
    }
    public static function error_405($request, $response, $view){
        $view->render($response, 'errors/405.html');
        return $response->withStatus(405);
    }
}