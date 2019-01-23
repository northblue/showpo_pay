<?php
class Showpo_OnlinePayment_Model_Payment extends Mage_Payment_Model_Method_Abstract{

    protected $_code = "showpo_pay";

    protected $_canAuthorize = true;
    protected $_canCapture   = true;

    //TODO It should be moved to admin as a configuration value later
    protected $url = "www.mocky.io/v2/5c46b1c13100000e0005f401";
    

    public function isAvailable($quote = null){
        if(!$quote){
            return false;
        }

        if($quote->getAllVisibleItems() <= 2){
            return false;
        }

        return true;
    }
    
    //capture function will make API call and save the response info together with Transaction
    public function capture(Varien_Object $payment, $amount)
    {
        $order = $payment->getOrder();
        $result = $this->callApi( $amount = $order->getGrandTotal(), $customerId = $order->getCustomerId(), $reference = $order->getQuoteId());
        
        Mage::log($result, null, $this->getCode().'.log');
        if($result === false) {
            $errorMsg = $this->_getHelper()->__('When Process API Call');
        } else {

            if($result['status'] == 'Success'){
                 Mage::log('Showpo Payment - Saving Transaction: '.json_encode($result), null, $this->getCode().'.log');
                $payment->setTransactionId($result['transaction_id']);
                $payment->setIsTransactionClosed(1);
                $payment->setTransactionAdditionalInfo(Mage_Sales_Model_Order_Payment_Transaction::RAW_DETAILS,array('txn_ref'=>$result['transaction_id']));
            }else{
                Mage::throwException('API Call Failed');
            }
        }
        if($errorMsg){
            Mage::throwException($errorMsg);
        }
 
        return $this;
    }
    
    
    private function callApi( $amount,$customerId,$reference){

        $dataArray = array("amount"=>$amount, "customer_id"=>$customerId,"reference"=>$reference);
        Mage::log('Showpo Payment - API sending'.json_encode($dataArray), null, $this->getCode().'.log');
              
        $ch = curl_init($this->url);
        
        try{
            //set the url, number of POST vars, POST data
            curl_setopt($ch, CURLOPT_URL, $this->url);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_POST,1);
            curl_setopt($ch, CURLOPT_POSTFIELDS,json_encode($dataArray));
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION ,1);
            curl_setopt($ch, CURLOPT_HEADER ,0); // DO NOT RETURN HTTP HEADERS
            curl_setopt($ch, CURLOPT_RETURNTRANSFER ,1); // RETURN THE CONTENTS OF THE CALL
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 120); // Timeout on connect (2 minutes)
            //execute post
            $result = curl_exec($ch);

            Mage::log('Showpo Payment - API Response'.$result, null, $this->getCode().'.log');
            curl_close($ch);
            $resultArray = json_decode($result, true);
;
            return array('status'=>$resultArray['status'],'amount' => $resultArray['amount'] , 'reference' => $resultArray['reference'], 
                'txn_ref' => $resultArray['txn_ref'],'transaction_id' =>time());
        }
        catch(Exception $e)
        {
            Mage::throwException(Mage::helper('showpo_onlinepayment')->__('API call failed'));
            return false;
        }
    }   
      
}