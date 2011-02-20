<?php

class StatusController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
        // action body
        $daemon = new \SiteSpeed\Daemon\Runner();
        $this->view->status = $daemon->getStatus();
        
        $this->view->gearmanclient = false;
        if ($this->_request->getPost('example3-gearman')) {
        	$this->view->gearmanclient = true;
        	
			$gmclient= new GearmanClient();
			$gmclient->addServer();
			$job_handle = $gmclient->doBackground("SiteSpeed_Example", "badieblasdasd");

        }
    }

}

