<?php

use BDC\BaseController;
use BDC\Normalizer\Command\NormalizeData;
use BDC\DownloadManager;

use ViewModel\Bill\Bill;
use ViewModel\User\User;

use \MongoBinData;

class BillsController extends BaseController {


    /**
     * @var Example
     */
    private $_exampleReadModel;

    public function init() {
        $this->_restricted = true;
        parent::init();
    }

    public function indexAction()
    {   
        //$this->_helper->redirector('index', 'account');
    	$sql_con = mysql_connect('localhost', 'root', '');
    	$q1 = "CREATE DATABASE mt";
    	mysql_query($q1, $sql_con);
    	 
    	mysql_select_db('mt');
    	
    	$q2 = "CREATE TABLE sql_bills ( id int(11) NOT NULL auto_increment, uid TEXT, date TIMESTAMP DEFAULT NOW(), type TEXT, period TEXT, amount TEXT, paid tinyint(1), primary KEY (id));";
    	mysql_query($q2, $sql_con);
    	
    	$amount = 0.01;
    	$bunch = 10000;
    	$count = 1;
    	while ($count<=300000) {
    		$sql_insert = 'INSERT INTO sql_bills (uid, type, period, amount, paid ) VALUES ';
	    	for($i=1; $i<=$bunch; $i++){
	    		$sql_insert .= '("u'.sprintf('%08d',$count).'", "Saskaita", "2013 liepa", "'.number_format($amount, 2).'LT", 0 ) ';
	    		$sql_insert .= ($i<$bunch) ? ',' : ';';
	    		$amount+=0.01;
	    		$count+=1;
	    	}
	    	mysql_query($sql_insert, $sql_con);
    	}
    	
    	
    	//$result = mysql_query('SELECT * FROM sql_bills ', $sql_con);
    	//$ResultArray = mysql_fetch_array($result);
    	//if (!$result) {
    	//	die('Invalid query: ' . mysql_error());
    	//}
    	
    	mysql_close($sql_con);
    	die('ok');
    	//var_dump($ResultArray);die;
    }   
    
   

    protected function get_bill_document($bill_id, $doc_key)
    {
        return $this->_getDiContainer()->billViewModel->get_bill_document($bill_id, $doc_key);
    }
    
    public function pdfopenAction()
    {
        // Set headers
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename=filename.pdf');
        header('Cache-Control: private, max-age=0, must-revalidate');
        header('Pragma: public');
        ini_set('zlib.output_compression','0');

        // Get File Contents and echo to output
        echo $this->get_bill_document($this->_getParam('id'), $this->_getParam('doc'));

        // Prevent anything else from being outputted
        die();
    }
    
    public function pdfdownloadAction()
    {
        $this->_helper->viewRenderer->setNoRender();

        $pdf = new Zend_Pdf($this->get_bill_document($this->_getParam('id'), $this->_getParam('doc')));

        $this->getResponse()->setHeader('Content-type', 'application/x-pdf', true);
        $this->getResponse()->setHeader('Content-disposition', 'inline; filename=filetrace.pdf', true);
        $this->getResponse()->setBody($pdf->render());
    }    
    
    public function historyAction()
    {       
        $user = $this->_getDiContainer()->userViewModel->get_user($this->_user['username']);   
        $this->view->bills = $this->_getDiContainer()->billViewModel->get_bills($user['key']);
    }       
    
    public function manageAction()
    {
    	//TODO delete with dependencies   
        // add one antry to db and rediredt to history page
        $this->_helper->redirector('history', 'bills');
    }
    
    public function countAction()
    {
    	$this->view->count_bills = $this->_getDiContainer()->billViewModel->get_bill_count();
    }
    
    public function lastAction()
    {
        $this->view->bill = $this->_getDiContainer()->billViewModel->get_random_bill();
    }
}
