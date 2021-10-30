<?php


function bpc_active_check() {
    if ( isset($_REQUEST) ) {
        global $wpdb;    

        $url = "https://py.bepopiacompliant.co.za/api/domain/check_expiry/" . $_SERVER['SERVER_NAME'];
        
        $args = array(
            'headers' => array(
                'Content-Type' => 'application/json',
            ),
            'body'    => array(),
        );
    
        $response = wp_remote_get( $url, $args );
    
        $response_code = wp_remote_retrieve_response_code( $response );
        $body         = wp_remote_retrieve_body( $response );
        
        if ( 401 === $response_code ) {
            echo "Unauthorized access";
        }
    
        if ( 200 !== $response_code ) {
            echo " Error in pinging API";
        }
    
        if ( 200 === $response_code ) {            
            $trimmed = trim($body, "[{}]");
            $trimmed = explode(',', $trimmed); 
            $trimmed = str_replace('"renew_date":', '', $trimmed[1]);      
            $trimmed = trim($trimmed, '"');
            $time = strtotime($trimmed);
            $time = date('Y-m-d',$time);

            if($time >= date("Y-m-d")){
                global $wpdb;
                $table_name = $wpdb->prefix . 'be_popia_compliant_admin';
                
                $wpdb->update( $table_name, array( 'value' => 0),array('id'=>3)); 
                echo '<style>
                        .BePopiaCompliant {
                            background-color: whitesmoke;
                            height: 15%;
                            color: #000;
                            align: center;
                            text-align: center;
                            box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);
                        }
                        .cont1 {
                            margin: auto;
                            width: 33%;
                            height: 125px;
                            display: flex;
                        }
                        .bpc_img {
                            margin: auto 0 auto auto;
                        }
                        .bpc_links {
                            margin: auto auto auto 0;
                            width: 75%;
                            font-weight:900;
                        }
                        .bpc_links a {
                            color: #BD2E2E;
                            text-decoration: none;
                            font-variant-caps: all-petite-caps;
                        }
                        @media only screen and (max-width: 600px) {    
                            .bpc_img {
                                margin: auto 0 auto auto;
                            }
                            .bpc_links {
                                margin: auto auto auto 0;
                                width: 100%;
                                font-weight: 900;
                            }
                            .cont1 {
                                margin: auto;
                                width: 33%;
                                height: 125px;
                                display: block;
                            }
                        }
                    </style>
                    <div class="BePopiaCompliant">
                        <div class="cont1">
                            <div class="bpc_img">
                                <a href="https://bepopiacompliant.co.za"><img alt="POPIA Compliant" src="data:image/png;base64,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" oncontextmenu="return false;" />
                                </a>
                            </div>
                            <div class="bpc_links">
                                <a href="https://bepopicompliant.co.za">MANAGE CONSENT
                                </a><br>
                                ';
                                
                                global $wpdb;
                                
                                $table_name = $wpdb->prefix . 'be_popia_compliant_checklist';
                            
                                $results = $wpdb->get_results("SELECT * FROM $table_name WHERE id = 21");
                                foreach($results as $result){
                                    if($result->does_comply == 1 && $result->content != ''){
                                        echo '
                                        <a href="' . $result->content . '">REQUEST DATA    
                                        </a>
                                        ';
                                    }
                                }

                                echo '
                            </div>
                        </div>
                    </div>';
            } else {
                global $wpdb;
                $table_name = $wpdb->prefix . 'be_popia_compliant_admin';
                
                $wpdb->update( $table_name, array( 'value' => 1),array('id'=>3)); 
            }
        }
    }

   die();
}
bpc_active_check();