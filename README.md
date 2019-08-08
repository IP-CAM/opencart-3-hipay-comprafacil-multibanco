# HiPay Multibanco Gateway extension for Opencart 3

## API credentials

HiPay API production or sandbox account credentials for each currency:
   - username
   - password
   - entity

## Setup
    
  - Sandbox: enable or disable sandbox/test account
  - Username and Password: credentials for Multibanco API 
  - Entity: Multibanco Entity enabled for your account
  - Expiry date: expiry date (in days). For entities without expiry date set to 0
  - Minimum and maximum amount to activate the payment method
  - Debug: enable to log payment info 
  - Order status for pending, cancelled, failed, expired and paid transactions.
  - Geo zone: zones where the payment method is activated
  - Status: enable or disable the extension
  - Sort order: payment method checkout order
  
## Show Multibanco reference on success page
Edit file ***catalog/controller/checkout/success.php*** and find 

    $this->cart->clear();

After that line add

		if ($this->session->data['payment_method']['code'] == 'hipay_multibanco'){
			$this->load->language('extension/payment/hipay_multibanco');
			$this->load->model('extension/payment/hipay_multibanco');
			$data['hipay_multibanco_reference'] = $this->model_extension_payment_hipay_multibanco->getMultibancoReference($this->session->data['order_id']);

			if (isset($data['hipay_multibanco_reference']->row["reference"] )) {
				$data['multibanco_entity_value'] = $data['hipay_multibanco_reference']->row["entity"];
				$data['multibanco_reference_value'] = $data['hipay_multibanco_reference']->row["reference"];	
				$data['multibanco_amount_value'] = $data['hipay_multibanco_reference']->row["total"];	
				if ($data['hipay_multibanco_reference']->row["expiry_days"] > 1) {
					$data['hipay_multibanco_reference']->row["expiry_days"]++;
				}		
				$data['multibanco_expiry_date_value'] = date('Y-m-d',strtotime('+' . $data['hipay_multibanco_reference']->row["expiry_days"] . ' days', strtotime($data['hipay_multibanco_reference']->row["date_added"]))) ;
					
				$multibancoReference = $this->load->view('extension/payment/hipay_multibanco_reference', $data);
			}
		}

Then find

    $data['continue'] = $this->url->link('common/home');

and before that line add

    if (isset($multibancoReference)){
    	$data['text_message'] .= $multibancoReference;
    }


## Requirements
  - SOAP extension


Version 1.0.0.0
