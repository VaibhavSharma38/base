<?php

namespace xepan\base;

class Initiator extends \Controller_Addon {
	public $addon_name = 'xepan_base';

	function init(){
		parent::init();        

        $this->app->epan = $this->recall(
                            $this->app->current_website_name.'_epan',
                            $this->memorize(
                                $this->app->current_website_name.'_epan',
                                $this->add('xepan\base\Model_Epan')->tryLoadBy('name',$this->app->current_website_name)
                            )
                        );
        if(!$this->app->epan->loaded()){
            die('No site found, forwarding to 404 service');
        }
        $this->app->epan->config = $this->app->epan->ref('Configurations');
        
        date_default_timezone_set($this->app->epan->config->getConfig('TIME_ZONE')?:'UTC');
        $this->app->today = date('Y-m-d');
        $this->app->now   = date('Y-m-d H:i:s');
    }

    function setup_admin(){
        
        $this->routePages('xepan_base');
        $this->addLocation(array('template'=>'templates','js'=>'templates/js','css'=>'templates/css'))
        ->setBaseURL('../vendor/xepan/base/')
        ;

        $tinymce_addon_base_path=$this->app->locatePath('addons','tinymce\tinymce');
        $this->addLocation(array('js'=>'.','css'=>'skins'))
        ->setBasePath($tinymce_addon_base_path)
        ->setBaseURL('../vendor/tinymce/tinymce/');


        $elfinder_addon_base_path=$this->app->locatePath('addons','studio-42\elfinder');
        $this->addLocation(array('js'=>'js','css'=>'css','image'=>'img'))
        ->setBasePath($elfinder_addon_base_path)
        ->setBaseURL('../vendor/studio-42/elfinder/');

        $auth = $this->app->add('BasicAuth',['login_layout_class'=>'xepan\base\Layout_Login']);
        $auth->allowPage(['xepan_base_forgotpassword','xepan_base_resetpassword','xepan_base_registration']);
        if(in_array($this->app->page, $auth->getAllowedPages())){
            $this->app->layout->destroy();
            $this->app->add('xepan\base\Layout_Centered');
            $this->app->top_menu = new \Dummy;
            $this->app->side_menu = new \Dummy;
        }else{
            $this->app->top_menu = $this->app->layout->add('xepan\base\Menu_TopBar',null,'Main_Menu');
            $this->app->side_menu = $this->app->layout->add('xepan\base\Menu_SideBar',null,'Side_Menu');
        }
        $auth->addHook('createForm',function($a,$p){
            $this->app->loggingin=true;            
            $f = $p->add('Form',null,null,['form/minimal']);
            $f->setLayout(['layout/xepanlogin','form_layout']);
            $f->addField('Line','username','Email address');
            $f->addField('Password','password','Password');
            // $cc = $f->add('Columns');
            // $cc->addColumn()->add('Button')->set('Log in')->addClass('atk-size-milli atk-swatch-green');
            // $cc->addColumn()->addClass('atk-align-right')->addField('Checkbox','remember_me','Remember me');
            $this->breakHook($f);

        });
        
        $auth->addHook('loggedIn',function($auth,$user,$pass){
            $this->app->memorize('user_loggedin', $auth->model);
            $auth->model['last_login_date'] = $this->app->now;
            $auth->model->save();
        });

        $auth->add('auth/Controller_Cookie');

        $this->api->addHook('post-init',function($app){
            if(!$this->app->getConfig('developer_mode',false) && !isset($this->app->loggingin) && !$app->page_object instanceof \xepan\base\Page && !in_array($app->page, $app->auth->getAllowedPages())){
                throw $this->exception('Admin Page Must extend \'xepan\base\Page\'')
                            ->addMoreInfo('page',$app->page)
                            ->addMoreInfo('page_object_class',get_class($app->page_object))
                            ;
            }
        });

        $user = $this->add('xepan\base\Model_User_Active');
        $user->addCondition('epan_id',$this->app->epan->id);
        $user->addCondition('scope',['AdminUser','SuperUser']);

        $auth->usePasswordEncryption('md5');
        $auth->setModel($user,'username','password');
        
        $auth->check();
               
        if(!$this->app->isAjaxOutput()) {
            $this->app->jui->addStaticInclude('pace.min');
            $this->app->jui->addStaticInclude('elfinder.full');
            $this->app->jui->addStaticStyleSheet('elfinder.full');
            $this->app->jui->addStaticStyleSheet('elfindertheme');
            $this->app->jui->addStaticStyleSheet('elfindertheme');
            $this->app->jui->addStaticInclude('pnotify.custom.min');
            $this->app->jui->addStaticInclude('xepan.pnotify');
            $this->app->jui->addStaticStyleSheet('pnotify.custom.min');
            $this->app->jui->addStaticStyleSheet('animate');
            $this->app->jui->addStaticInclude('xepan_jui');
            
            $this->app->js(true,'PNotify.prototype.options.styling = "fontawesome"');
            $this->app->js(true)->_library('PNotify.desktop')->permission();
            $this->app->js(true)->_load('jquery.bootstrap-responsive-tabs.min')->_selector('.responsive-tabs')->responsiveTabs("accordionOn: ['xs', 'sm']");
        }
       
        $this->app->addHook('post-init',function($app){
            if($app->layout->template->hasTag('quick_search_form'))
                $app->layout->add('xepan\base\View_QuickSearch',null,'quick_search_form');
        });

        // Adding all other installed applications
        $this->setup_xepan_apps('admin');

        return $this;
	}

    function setup_frontend(){
        $this->routePages('xepan_base');
        $this->addLocation(array('template'=>'templates','js'=>'templates/js','css'=>'templates/css'))
        ->setBaseURL('./vendor/xepan/base/')
        ;
        
        $this->app->jui->addStaticInclude('pnotify.custom.min');
        $this->app->jui->addStaticInclude('xepan.pnotify');
        $this->app->jui->addStaticStyleSheet('pnotify.custom.min');
        $this->app->jui->addStaticStyleSheet('animate');
        $this->app->jui->addStaticInclude('xepan_jui');
        $this->app->jui->addStaticInclude('xepan_jui');
        $this->app->jui->addStaticStyleSheet('bootstrap.min');
        $this->app->jui->addStaticInclude('bootstrap.min');

        $auth = $this->app->add('BasicAuth',['login_layout_class'=>'xepan\base\Layout_Login']);
        $auth->usePasswordEncryption('md5');

        $user = $this->add('xepan\base\Model_User_Active');
        $user->addCondition('scope',['WebsiteUser','SuperUser','AdminUser']);
        $auth->setModel($user,'username','password');
        if(strpos($this->app->page,'_admin_')!==false){
            $user->addCondition('scope',['SuperUser','AdminUser']);
            $auth->setModel($user,'username','password');
            $auth->check();
        }


        $this->app->addMethod('exportFrontEndTool',function($app,$tool, $group='Basic'){
            if(!isset($app->fronend_tool)) $app->fronend_tool=[];
            $app->fronend_tool[$group][] = $tool;
        });

        $this->app->addMethod('getFrontEndTools',function($app){
            if(!isset($app->fronend_tool)) $app->fronend_tool=[];
            return $app->fronend_tool;
        });

        $this->app->exportFrontEndTool('xepan\base\Tool_UserPanel');
        $this->app->exportFrontEndTool('xepan\base\Tool_Location');

        $this->app->jui->addStaticStyleSheet('xepan-base');

        // Adding all other installed applications
        $this->setup_xepan_apps('frontend');

        return $this;
    }


    function setup_xepan_apps($side){
         foreach ($this->app->epan->ref('InstalledApplications')->setOrder('application_id') as $apps) {
            $this->app->xepan_addons[] = $apps['application_namespace'];   
        }

        foreach ($this->app->xepan_addons as $addon) {
            if($addon == 'xepan\base') continue;
            $this->app->xepan_app_initiators[$addon] = $app_initiators[$addon] = $this->add("$addon\Initiator");
        }

        // Pre setup call

        foreach ($this->app->xepan_app_initiators as $addon_name=>$addon_obj) {
            if($addon == 'xepan\base') continue;
            $func = 'setup_pre_'.$side;
            if($addon_obj->hasMethod($func)){
                $addon_obj->$func();
            }
        }

        // Setup call
        foreach ($this->app->xepan_app_initiators as $addon_name=>$addon_obj) {
            if($addon == 'xepan\base') continue;
            $func = 'setup_'.$side;
            $addon_obj->$func();
        }

        // Post Setup Call

        foreach ($this->app->xepan_app_initiators as $addon_name=>$addon_obj) {
            if($addon == 'xepan\base') continue;
            $func = 'setup_post_'.$side;
            if($addon_obj->hasMethod($func)){
                $addon_obj->$func();
            }
        }
    }

    function resetDB($write_sql=false,$install_apps=true){
        // $this->app->old_epan = clone $this->app->epan;

        // Clear DB
        $truncate_models = ['Epan_Category','Epan','User','Epan_Configuration','Epan_InstalledApplication','Application','Country','State'];
        foreach ($truncate_models as $t) {
            $this->add('xepan\base\Model_'.$t)->deleteAll();
        }

        // orphan contact_info and contacts
        
        $this->app->db->dsql()->table('contact_info')->where('epan_id',null)->delete();
        $this->app->db->dsql()->table('contact')->where('epan_id',null)->delete();
        
        $d = $this->app->db->dsql();
        $d->sql_templates['delete'] = "delete [table] from  [table] [join] [where]";
        $d->table('contact_info')->where('contact.id is null')->join('contact',null,'left')->delete();

        // orphan document_attachements
        $d = $this->app->db->dsql();
        $d->sql_templates['delete'] = "delete [table] from  [table] [join] [where]";
        $d->table('attachment')->where('document.id is null')->join('document',null,'left')->delete();

        // Create default Epan_Category and Epan

        $epan_category = $this->add('xepan\base\Model_Epan_Category')
            ->set('name','default')
            ->save();

        $epan = $this->add('xepan\base\Model_Epan')
                    ->set('epan_category_id',$epan_category->id)
                    ->set('name','www')
                    ->save();

        $this->app->epan = $epan;
        $this->app->epan->config = $this->app->epan->ref('Configurations');
        // $this->app->new_epan = clone $this->app->epan;

        // Create Default User
        $user = $this->add('xepan\base\Model_User_SuperUser');
        $this->app->auth->addEncryptionHook($user);
        $user=$user->set('username','admin@epan.in')
             ->set('scope','SuperUser')
             ->set('password','admin')
             ->set('epan_id',$epan->id)
             ->saveAs('xepan\base\Model_User_Active');

        $this->app->auth->login($user);

        // Create Default Applications and INstall with all with root application
        
        $addons = ['xepan\\communication', 'xepan\\hr','xepan\\projects','xepan\\marketing','xepan\\accounts','xepan\\commerce','xepan\\production','xepan\\crm','xepan\\cms','xepan\\blog','xepan\\epanservices'];

        foreach ($addons as $ad) {
            $ad_array = explode("\\", $ad);
            $app = $this->add('xepan\base\Model_Application')
                ->set('name',array_pop($ad_array))
                ->set('namespace',$ad)
                ->save();
            if($install_apps)
                $epan->installApp($app);
        }

        // create default filestore volume
        
        $fv = $this->add('xepan\filestore\Model_Volume');
        $fv->addCondition('name','upload');
        $fv->tryLoadAny();
        $fv['dirname']='upload';
        $fv['total_space'] = '1000000000';
        $fv['used_space'] = '0';
        $fv['stored_files_count'] = '0';
        $fv['enabled'] = '1';
        $fv->save();


        // if($write_sql){
        //     $dump = new \MySQLDump(new \mysqli('localhost', 'root', 'winserver', 'xepan2'));
        //     $dump->save(getcwd().'/../vendor/'.str_replace("\\",'/',__NAMESPACE__).'/install.sql');
        // }

        // Insert default country and states
        $this->api->db->dsql()->expr(file_get_contents(realpath(getcwd().'/vendor/xepan/base/countriesstates.sql')))->execute();
        
        //Set Epan config 
        $admin_config = $this->app->epan->config;
        $file_reset_subject_admin = file_get_contents(realpath(getcwd().'/vendor/xepan/base/templates/default/reset_subject_admin.html'));
        $file_reset_body_admin = file_get_contents(realpath(getcwd().'/vendor/xepan/base/templates/default/reset_body_admin.html'));
        
        $admin_config->setConfig('RESET_PASSWORD_SUBJECT_FOR_ADMIN',$file_reset_subject_admin,'communication');
        $admin_config->setConfig('RESET_PASSWORD_BODY_FOR_ADMIN',$file_reset_body_admin,'communication');
        
        $file_update_subject_admin = file_get_contents(realpath(getcwd().'/vendor/xepan/base/templates/default/update_subject_admin.html'));
        $file_update_body_admin = file_get_contents(realpath(getcwd().'/vendor/xepan/base/templates/default/update_body_admin.html'));
        
        $admin_config->setConfig('UPDATE_PASSWORD_SUBJECT_FOR_ADMIN',$file_update_subject_admin,'communication');
        $admin_config->setConfig('UPDATE_PASSWORD_BODY_FOR_ADMIN',$file_update_body_admin,'communication');
      
        // Do other tasks needed
        // Like empting any folder etc
    }


    function extract_domain($domain)
    {
        if(preg_match("/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i", $domain, $matches))
        {
            return $matches['domain'];
        } else {
            return $domain;
        }
    }

    function extract_subdomains($domain)
    {
        $subdomains = $domain;
        $domain = $this->extract_domain($subdomains);
        $subdomains = rtrim(strstr($subdomains, $domain, true), '.');

        return $subdomains;
    }



}
