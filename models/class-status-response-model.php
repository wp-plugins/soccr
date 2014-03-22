<?php 

class SOCCR_Status {
    const SUCCESS = 1;
    const FAILED = -1;
}

class SOCCR_StatusResponse {
    
    private $status;
    private $response_object;
    
    function __construct($status, $response_object = null) {
        $this->status = $status;
        $this->response_object = $response_object;
    }
    
    public function get_status() {
        return $this->status;
    }

    public function get_response_object() {
        return $this->response_object;
    }     
}
?>