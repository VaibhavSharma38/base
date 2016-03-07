<?php 
namespace xepan\base;
class page_general_email extends \Page{
	public $title="Email Settings";
	function init(){
		parent::init();
		$action = $this->api->stickyGET('action')?:'view';

		$email_setting= $this->add('xepan\base\Model_Epan_EmailSetting')->tryLoadBy('id',$this->api->stickyGET('emailsetting_id'));
		
		$email_view=$this->add('xepan\hr\View_Document',['action'=>$action,'submit_button'=>''],null,['view/setting/email-setting']);

		$email_view->setIdField('emailsetting_id');
		$email_view->setModel($email_setting,[
												'email_transport','encryption',
												'email_host','email_port',
												'email_username','email_password',
												'email_reply_to','email_reply_to_name',
												'from_email','from_name','sender_email',
												'sender_name','imap_email_host',
												'imap_email_port','imap_email_username',
												'imap_email_password','imap_flags',
												'smtp_auto_reconnect','email_threshold',
												'emails_in_BCC','bounce_imap_email_host',
												'bounce_imap_email_port','return_path',
												'bounce_imap_email_password',
												'bounce_imap_flags'
											],
											[
												'email_transport','encryption',
												'email_host','email_port',
												'email_username','email_password',
												'email_reply_to','email_reply_to_name',
												'from_email','from_name','sender_email',
												'sender_name','imap_email_host',
												'imap_email_port','imap_email_username',
												'imap_email_password','imap_flags',
												'smtp_auto_reconnect','email_threshold',
												'emails_in_BCC','bounce_imap_email_host',
												'bounce_imap_email_port','return_path',
												'bounce_imap_email_password',
												'bounce_imap_flags'
											]
							);
		$this->form = $email_view->form;

	}

	function render(){
		$this->js(true)->_load('wizard')
			->_Selector('.wizard')
			->wizard()
			;
		
		$this->js('finished',$this->form->js()->submit())->_selector('.wizard');

		$this->app->jui->addStylesheet('compiled/wizard');
		parent::render();
	}
}