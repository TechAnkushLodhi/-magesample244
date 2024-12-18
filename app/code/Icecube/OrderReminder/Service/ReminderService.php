<?php
namespace Icecube\OrderReminder\Service;

use Icecube\OrderReminder\Model\SendMail\Sending;
use Icecube\OrderReminder\Helper\Data as IcecubeOrderHelper;
use Icecube\OrderReminder\Model\Complete90\LoginCustomers;

class ReminderService
{
    protected $sendMail;
    protected $icecubeOrderHelper;
    protected $LoginCustomers;


    public function __construct(
        Sending $sendMail,
        IcecubeOrderHelper $icecubeOrderHelper,
        LoginCustomers $LoginCustomers,
    ) {
        $this->sendMail = $sendMail;
        $this->icecubeOrderHelper = $icecubeOrderHelper;
        $this->LoginCustomers = $LoginCustomers;
    }

        public function execute()
        {
           
            
            if ($this->icecubeOrderHelper->isModuleEnabled()) {
                $response = [
                'status' => 'error',
                'message' => ''
            ];
                $OrderDetails = $this->LoginCustomers->getLoginCustomers();
                    if($OrderDetails['status']==='success'){
                        $Emails = $this->sendMail->SendingMale($OrderDetails['message']);
                            if($Emails['status']==='success'){
                            $EmailIds='';
                                foreach ($Emails['message'] as  $Email) {
                                $EmailIds.= $Email.',';
                                }
                                $response['status'] = 'success';
                                $response['message'] = ' Email sent successfully to  '.$EmailIds;
                            }else{
                            $response['message'] = $Emails['message'];    
                            }
                    }else{
                        $response['message'] = $OrderDetails['message'] ;    
                    }
                    // Send mail with order details using SendMail class                    
                    if($response['status']==='success'){
                        $lastCommaPosition = strrpos($response['message'], ',');
                        if ($lastCommaPosition !== false) {
                            $emailListWithPeriod = substr_replace($response['message'], '.', $lastCommaPosition, 1);

                        }
                        return $emailListWithPeriod;
                        }else{
                          return $response['message'];
                        } 
                    }else {
                        return "Icecube_OrderReminder Module is Not enable";
                            
                    }
    }
}
