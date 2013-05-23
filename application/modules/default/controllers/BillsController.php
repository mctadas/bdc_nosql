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
        $this->_helper->redirector('pdfdownload', 'bills', 'default', array('id'=> '519dcc41cc6d9f8f06000000', 'doc'=>'pdf_doc'));
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
        $bill_document = array ( 'date' => date('Y F d').' d.',
                                 'type' => 'Saskaita',
                                 'pdf_doc'  => new MongoBinData(file_get_contents("example.pdf")),
                                 'has_doc' => true,
                                 'period' => '2012 birzelis',
                                 'amount' => '29,90 Lt',
                                 //'pdf_report' => 'todo bin',
                                 'has_report' => false,
                                 'paid'   => false);
        $user = $this->_getDiContainer()->userViewModel->get_user($this->_user['username']);   
        $this->_getDiContainer()->billViewModel->save($bill_document, $user['key']);
        
        // add one antry to db and rediredt to history page
        $this->_helper->redirector('history', 'bills');
    }       
    
    public function lastAction()
    {
        $this->view->bill = $this->_getDiContainer()->billViewModel->get_random_bill();
    }
}
