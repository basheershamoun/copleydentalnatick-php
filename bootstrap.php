<?php
error_reporting(E_ALL & ~E_DEPRECATED);

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;


require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/publico/datos/models.php';
require __DIR__ . '/publico/forms.php';
require __DIR__ . '/publico/controllers.php';
require __DIR__ . '/publico/emails.php';


$configuracion = parse_ini_file(__DIR__ . '/config.ini', true);
$config = [
    'settings' => [
        'displayErrorDetails' => $configuracion["configuracion"]["mostrar_error"]
    ],
];

$app = new \Slim\App($config);
$contenedor = $app->getContainer();
$contenedor["configuracion"] = function($contenedor){
    return parse_ini_file(__DIR__ . '/config.ini', true);
};

/*
    Configuracion del motor de plantillas Twig
*/
$contenedor['view'] = function($contenedor){
    #Obtener configuracion
    $configuracion = $contenedor->get("configuracion");
    $dir_plantillas = $configuracion["motor_plantillas"]["dir_plantillas"];
    $cache = $configuracion["motor_plantillas"]["cache"];
    $view = new \Slim\Views\Twig($dir_plantillas, [
        'cache' => false
    ]);
    $services_model = new ServiceModel("publico/datos/services.json");
    $services = $services_model->get_services();

    // basePath is always empty — site is served from document root on Cloudflare Pages.
    $basePath = '';
    $view->addExtension(new Slim\Views\TwigExtension($contenedor->get('router'), $basePath));
    //Adicionar varible donde se encuentra los archivos estaticos
    $enviroment = $view->getEnvironment();
    //Direccion de archivos estaticos
    $enviroment->addGlobal("static", $configuracion["configuracion"]["url_archivos_estaticos"]);
    //Informacion de los servicios
    $enviroment->addGlobal("services", $services) ;
    //Informacion de recaptcha
    $enviroment->addGlobal("site_key", $configuracion["recaptcha"]["site_key"]) ;
    $enviroment->addGlobal("secret_key", $configuracion["recaptcha"]["secret_key"]) ;
    // Poner el slugs de los servicios
    $enviroment->addGlobal("slug_services", ServicesController::SLUG_SERVICES) ;
    $enviroment->addGlobal("slug_invisalign", ServicesController::SLUG_INVISALIGN) ;
    $enviroment->addGlobal("slug_dental_veneers", ServicesController::SLUG_DENTAL_VENEERS) ;
    $enviroment->addGlobal("slug_lumineers", ServicesController::SLUG_LUMINEERS) ;


    return $view;
};
// Manejador del error 404
$contenedor["notFoundHandler"] = function($c){
    return function($request, $response) use($c){
        $view = $c->get("view");
        return ErrorController::error_404($request, $response, $view);
    };
};
// Manejador del error 500
$contenedor["errorHandler"] = function($c){
    return function($request, $response) use($c){
        $view = $c->get("view");
        return ErrorController::error_500($request, $response, $view);
    };
};

// Manejador del error 405
$contenedor["notAllowedHandler"] = function($c){
    return function($request, $response) use($c){
        $view = $c->get("view");
        return ErrorController::error_405($request, $response, $view);
    };
};

//Controlador Home
$contenedor["Home"] = function($c){
    $view = $c->get("view");
    $config = $c->get("configuracion");
    $router = $c->get("router");
    return new HomeControlador($view, $config, $router);
};
$contenedor["Services"] = function($c){
    $config = $c->get("configuracion");
    $view = $c->get("view");
    $router = $c->get("router");
    return new ServicesController($view, $config,$router);
};
$contenedor["RequestAppointment"] = function($c){
    $view = $c->get("view");
    $config = $c->get("configuracion");
    $router = $c->get("router");
    return new RequestAppointmentController($view, $config, $router);
};
$contenedor["DentalEmergency"] = function($c){
    $view = $c->get("view");
    $config = $c->get("configuracion");
    $router = $c->get("router");
    return new DentalEmergencyController($view, $config, $router);
};
$contenedor["OurTeam"] = function($c){
    $view = $c->get("view");
    $config = $c->get("configuracion");
    $router = $c->get("router");
    return new OurTeamController($view, $config, $router);
};
$contenedor["Invisalign"] = function($c){
    $view = $c->get("view");
    $config = $c->get("configuracion");
    $router = $c->get("router");
    return new InvisalignController($view, $config, $router);
};
$contenedor["ContactUs"] = function($c){
    $view = $c->get("view");
    $config = $c->get("configuracion");
    $router = $c->get("router");
    return new ContactUsController($view, $config, $router);
};
$contenedor["SocialResponsibility"] = function($c){
    $view = $c->get("view");
    $config = $c->get("configuracion");
    $router = $c->get("router");
    return new SocialResponsibilityController($view, $config, $router);
};

$contenedor["Faqs"] = function($c){
    $view = $c->get("view");
    $config = $c->get("configuracion");
    $router = $c->get("router");
    return new FaqsController($view, $config, $router);
};
$contenedor["DentalInformation"] = function($c){
    $view = $c->get("view");
    $config = $c->get("configuracion");
    $router = $c->get("router");
    return new DentalInformationController($view, $config, $router);
};
$contenedor["Gallery"] = function($c){
    $view = $c->get("view");
    $config = $c->get("configuracion");
    $router = $c->get("router");
    return new GalleryController($view, $config, $router);
};
$contenedor["PatientInformation"] = function($c){
    $view = $c->get("view");
    $config = $c->get("configuracion");
    $router = $c->get("router");
    return new PatientInformationController($view, $config, $router);
};
$contenedor["AskDoctor"] = function($c){
    $view = $c->get("view");
    $config = $c->get("configuracion");
    $router = $c->get("router");
    return new AskDoctorController($view, $config, $router);
};
// Inicio
$app->get('/', \Home::class.":home")->setName("home");
// Gracias de los servicios
$app->get('/services/thank-you-contact-us/', \Services::class.":thank_you_contact_us")->setName("thank_you_contact_us_service");
// Video Copley
$app->map(["GET", "POST"],'/services/video/', \Services::class.":video_copley")->setName("video_copley");
// savings plan
$app->map(["GET", "POST"],'/services/savings-plan/', \Services::class.":savings_plan")->setName("savings_plan");
// savings plan individual
$app->map(["GET", "POST"],'/services/savings-plan/form/individual/', \Services::class.":savings_plan_form_individual")->setName("savings_plan_form_individual");
$app->get( '/services/savings-plan/thank_you_savings_plan/', \Services::class.":thank_you_savings_plan")->setName("thank_you_savings_plan");
// savings plan family
$app->map(["GET", "POST"],'/services/savings-plan/form/family/', \Services::class.":savings_plan_form_family")->setName("savings_plan_form_family");
$app->get( '/services/savings-plan/thank_you_savings_plan_family/', \Services::class.":thank_you_savings_plan_family")->setName("thank_you_savings_plan_family");
// savings plan periodontal maintenance
$app->map(["GET", "POST"],'/services/savings-plan/form/periodontal-maintenance/', \Services::class.":savings_plan_form_periodontal_maintenance")->setName("savings_plan_form_periodontal_maintenance");
$app->get( '/services/savings-plan/thank_you_savings_plan_periodontal_maintenance/', \Services::class.":thank_you_savings_plan_periodontal_maintenance")->setName("thank_you_savings_plan_periodontal_maintenance");
// Dental venner preguntas frecuentes
$app->map(["GET", "POST"],'/services/dental-veneers/frequent-questions/', \Services::class.":dental_veneers_faq")->setName("denta_veneers_faq");
// Service zoom teeth whitening treatment
$app->map(["GET", "POST"],'/services/zoom-teeth-whitening-treatment/', \Services::class.":zoom_teeth_whitening_treatment")->setName("zoom_teeth_whitening_treatment");
// service home teeth whitening treatment
$app->map(["GET", "POST"],'/services/home-teeth-whitening-treatment/', \Services::class.":home_teeth_whitening_treatment")->setName("home_teeth_whitening_treatment");
// service white_dental_fillings
$app->map(["GET", "POST"],'/services/white-dental-fillings/', \Services::class.":white_dental_fillings")->setName("white_dental_fillings");
// Service all porcelain dental crowns
$app->map(["GET", "POST"],'/services/all-porcelain-dental-crowns/', \Services::class.":all_porcelain_dental_crowns")->setName("all_porcelain_dental_crowns");
// Service procera-allceram-crowns
$app->map(["GET", "POST"],'/services/procera-allceram-crowns/', \Services::class.":procera_allceram_crowns")->setName("procera_allceram_crowns");

//what is invisalign
$app->map(["GET", "POST"],'/services/invisalign/what-is-invisalign/', \Invisalign::class.":what_is_invisalign")->setName("what_is_invisalign");
// Invisalign treatment process
$app->map(["GET", "POST"],'/services/invisalign/treatment-process/', \Invisalign::class.":treatment_process")->setName("treatment_process");
// Invisalign treatment comparison
$app->map(["GET", "POST"],'/services/invisalign/treatment-comparison/', \Invisalign::class.":treatment_comparison")->setName("treatment_comparison");
// Invisalig for teenagers
$app->map(["GET", "POST"],'/services/invisalign/invisalign-for-teenagers/', \Invisalign::class.":invisalign_for_teenagers")->setName("invisalign_for_teenagers");
// Invisalig viviera retainers
$app->map(["GET", "POST"],'/services/invisalign/viviera-retainers/', \Invisalign::class.":viviera_retainers")->setName("viviera_retainers");
// Invisalig can invisalign work for you
$app->map(["GET", "POST"],'/services/invisalign/can-invisalign-work-for-you/', \Invisalign::class.":can_invisalign_work_for_you")->setName("can_invisalign_work_for_you");
//Invisalign results
$app->map(["GET", "POST"],'/services/invisalign/results/', \Invisalign::class.":results")->setName("invisalign_results");
//Invisalign stories_about_smiles
$app->map(["GET", "POST"],'/services/invisalign/stories-about-smiles/', \Invisalign::class.":stories_about_smiles")->setName("stories_about_smiles");


//Todos los servicios
$app->get('/services/{service}/', \Services::class.":service_get")->setName("service");
$app->post('/services/{service}/', \Services::class.":service_post")->setName("service_post");
// Emergency dental
$app->map(["GET", "POST"], '/emergency-dentist/', \DentalEmergency::class.":dental_emergency")->setName("emergency_dental");
//Our Team
$app->get('/meet-the-copley-dental-team/', \OurTeam::class.":our_team")->setName("our_team");
// Contact Us
$app->map(["GET", "POST"], '/contact-us/', \ContactUs::class.":contact_us")->setName("contact_us");
$app->get( '/contact-us/thank_you/', \ContactUs::class.":thank_you")->setName("thank_you_contact_us");
//Social Responsibility
$app->get( '/corporate-social-responsibility/', \SocialResponsibility::class.":corporate_social_responsibility")->setName("corporate_social_responsibility");
$app->get( '/corporate-social-responsibility/medical-missions-for-children-quito/', \SocialResponsibility::class.":ecuador_humanitarian_mission_2010")->setName("ecuador_humanitarian_mission_2010");
$app->get( '/corporate-social-responsibility/medical-missions-for-children-quito/video/', \SocialResponsibility::class.":ecuador_humanitarian_mission_2010_video")->setName("ecuador_humanitarian_mission_2010_video");

$app->get( '/corporate-social-responsibility/ecuador-medical-mission-for-children-quito-2011/', \SocialResponsibility::class.":ecuador_medical_mission_for_children_2011")->setName("ecuador_medical_mission_for_children_2011");
$app->get( '/corporate-social-responsibility/ecuador-medical-mission-for-children-quito-2011/video/', \SocialResponsibility::class.":ecuador_medical_mission_for_children_2011_video")->setName("ecuador_medical_mission_for_children_2011_video");

$app->get( '/corporate-social-responsibility/medical-missions-for-children-guatemala-2011/', \SocialResponsibility::class.":guatemala_humanitarian_mission_2011")->setName("guatemala_humanitarian_mission_2011");
$app->get( '/corporate-social-responsibility/medical-missions-for-children-guatemala-2011/video/', \SocialResponsibility::class.":guatemala_humanitarian_mission_2011_video")->setName("guatemala_humanitarian_mission_2011_video");

$app->get( '/corporate-social-responsibility/medical-missions-for-children-guatemala-2014/', \SocialResponsibility::class.":guatemala_humanitarian_mission_2014")->setName("guatemala_humanitarian_mission_2014");
$app->get( '/corporate-social-responsibility/medical-missions-for-children-guatemala-2014/video/', \SocialResponsibility::class.":guatemala_humanitarian_mission_2014_video")->setName("guatemala_humanitarian_mission_2014_video");

$app->get( '/corporate-social-responsibility/medical-missions-for-children-guatemala-2015/', \SocialResponsibility::class.":guatemala_humanitarian_mission_2015")->setName("guatemala_humanitarian_mission_2015");
$app->get( '/corporate-social-responsibility/medical-missions-for-children-philippines-2019/video/', \SocialResponsibility::class.":philippines_humanitarian_mission_2019_video")->setName("philippines_humanitarian_mission_2019_video");
$app->get( '/corporate-social-responsibility/medical-missions-103-MMFC/video/', \SocialResponsibility::class.":medical_missions_103_MMFC_video")->setName("medical_missions_103_MMFC_video");

// Faqs
$app->get( '/frequently-asked-questions/', \Faqs::class.":faqs")->setName("faqs");

// Dental Information
$app->map(["GET","POST"],  '/dental-information/bad_breath/', \DentalInformation::class.":bad_breath")->setName("bad_breath");
$app->map(["GET","POST"],  '/dental-information/dental-sealants/', \DentalInformation::class.":dental_sealants")->setName("dental_sealants");
$app->map(["GET","POST"],  '/dental-information/digital-x-rays/', \DentalInformation::class.":digital_x_rays")->setName("digital_x_rays");
$app->map(["GET","POST"],  '/dental-information/fluoride/', \DentalInformation::class.":fluoride")->setName("fluoride");
$app->map(["GET","POST"],  '/dental-information/healthy-gums/', \DentalInformation::class.":healthy_gums")->setName("healthy_gums");
$app->map(["GET","POST"],  '/dental-information/smoking-and-oral-health/', \DentalInformation::class.":smoking_and_oral_health")->setName("smoking_and_oral_health");
$app->map(["GET","POST"],  '/dental-information/snoring/', \DentalInformation::class.":snoring")->setName("snoring");
$app->map(["GET","POST"],  '/dental-information/oral-cancer/', \DentalInformation::class.":oral_cancer")->setName("oral_cancer");

// Gallery
$app->get('/gallery/', \Gallery::class.":gallery")->setName("gallery");
$app->get('/gallery/{case}/', \Gallery::class.":view_case")->setName("view_case");

// Patient Information
$app->map(['GET', 'POST'], '/patient-information/your-first-visit/', \PatientInformation::class.":your_first_visit")->setName("your_first_visit");
$app->map(['GET', 'POST'], '/patient-information/patient-forms/', \PatientInformation::class.":patient_forms")->setName("patient_forms");
$app->map(['GET', 'POST'], '/patient-information/financial-and-insurance-information/', \PatientInformation::class.":financial_and_insurance_information")->setName("financial_and_insurance_information");
$app->map(['GET', 'POST'], '/patient-information/privacy-policy/', \PatientInformation::class.":privacy_policy")->setName("privacy_policy");
$app->map(['GET', 'POST'], '/patient-information/terms-and-conditions/', \PatientInformation::class.":terms_and_conditions")->setName("terms_and_conditions");

//Ask Doctor
$app->map(['GET', 'POST'], '/ask-doctor/', \AskDoctor::class.":ask_doctor")->setName("ask_doctor");
$app->get( '/ask-doctor/thank-you/', \AskDoctor::class.":thank_you")->setName("thank_you_ask_doctor");


$app->map(["GET", "POST"],'/request_appointment/', \RequestAppointment::class.":request_appointment")->setName("request_appointment");
$app->get('/request_appointment/thank-you/', \RequestAppointment::class.":thank_you")->setName("thank_you_request_appointment");

return $app;
