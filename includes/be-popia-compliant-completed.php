<?php


function bpc_active_check() {
    if ( isset($_REQUEST) ) {
        global $wpdb;    

        $url = "https://py.bepopiacompliant.co.za/api/domain/check_expiry/" . $_SERVER['SERVER_NAME'];
        
        $args = array(
            'headers' => array(
                'Content-Type' => 'application/json',
            ),
            'body' => array(),
        );
    
        $response = wp_remote_get( $url, $args );
    
        $response_code = wp_remote_retrieve_response_code( $response );
        $body = wp_remote_retrieve_body( $response );

        
        if ( 401 === $response_code ) {
            echo "Unauthorized access, You do not seem to be authorised to access this data!";
        }
    
        if ( 200 !== $response_code ) {
            echo " Error in pinging API, Please try again later.";
        }
    
        if ( 200 === $response_code ) {
            $trim_brackets = trim($body, "[{}]");
            $explode = explode(',', $trim_brackets); 
            $trim_date = str_replace('"renew_date":', '', $explode[1]); 
            $go_on = str_replace('"is_subscribed":', '', $explode[2]);      
            $trim_date = trim($trim_date, '"');
            $go_on = trim($go_on, '"');
            $date = strtotime($trim_date);
            $date = date('Y-m-d',$date);

            if($date >= date("Y-m-d")){

                if($go_on == 1){
                    global $wpdb;
                    $privacy = 1;
                    $table_name = $wpdb->prefix . 'be_popia_compliant_admin';
                    
                    $wpdb->update( $table_name, array( 'value' => 0),array('id'=>3)); 
                        echo '<style>
                            .BePopiaCompliant {
                                background-color: whitesmoke;
                                color: #000;
                                text-align: center;
                                box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);
                            }
                            .cont1 {
                                margin: auto;
                                width: 50%;
                                height: 146px;
                                display: flex;
                            }
                            .bpc_img {
                                margin: auto 0 auto auto;
                                width: 200px;
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
                                    width: 50%;
                                    height: 245px;
                                    display: block;
                                }
                            }
                        </style>
                        <div class="BePopiaCompliant">
                            <div class="cont1">
                                <div class="bpc_img">
                                    <a href="https://bepopiacompliant.co.za" target="_blank"><img alt="POPIA Compliant" src="data:image/png;base64,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" oncontextmenu="return false;" />
                                    </a>
                                </div>
                                <div class="bpc_links">
                                        ';
                                        echo '<a href="' . $privacy .'" target="_blank"><span style="white-space:nowrap">PRIVACY POLICY</span></a> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <a href="https://manageconsent.co.za/manage_consent/' . $_SERVER['SERVER_NAME'] . '" target="_blank"><span style="white-space:nowrap">MANAGE CONSENT</span></a> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <a href="https://bepopiacompliant.co.za/responsible_parties/' . $_SERVER['SERVER_NAME'] . '" target="_blank"><span style="white-space:nowrap">RESPONSIBLE PARTIES</span></a> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <a href="https://bepopiacompliant.co.za/information_regulator" target="_blank"><span style="white-space:nowrap">INFORMATION REGULATOR</span></a> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
                                        
                                    echo '
                                </div>
                            </div>
                        </div>';
                        
                } else {
                    global $wpdb;
                    $table_name = $wpdb->prefix . 'be_popia_compliant_admin';
                    
                    $wpdb->update( $table_name, array( 'value' => 1),array('id'=>3)); 

                    $rowcount = $_SESSION['rowcount'];
                    $rowcount2 = $_SESSION['rowcount2'];

                    
                    

                    $rowcount = ($rowcount / $rowcount2) * 100;

                    echo '<br>';

                    if($rowcount == 100) {
                        $table_name = $wpdb->prefix . 'be_popia_compliant_checklist';
                        $privacy = $wpdb->get_var( $wpdb->prepare(
                            " SELECT content FROM $table_name WHERE id = 6")
                        );
                        $data = $wpdb->get_var( $wpdb->prepare(
                            " SELECT content FROM $table_name WHERE id = 21")
                        );
                        $parties = $wpdb->get_var( $wpdb->prepare(
                            " SELECT content FROM $table_name WHERE id = 32")
                        );
                        echo '<style>
                            .BePopiaCompliant {
                                background-color: whitesmoke;
                                color: #000;
                                text-align: center;
                                box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);
                            }
                            .cont1 {
                                margin: auto;
                                width: 50%;
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
                                    width: 50%;
                                    height: 245px;
                                    display: block;
                                }
                            }
                        </style>
                        <div class="BePopiaCompliant">
                            <div class="cont1">
                                <div class="bpc_img">
                                    <a href="https://bepopiacompliant.co.za" target="_blank"><img alt="POPIA Compliant" src="data:image/png;base64,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" oncontextmenu="return false;" />
                                    </a>
                                </div>
                                <div class="bpc_links">
                                    <a href="' . $privacy .'" target="_blank"><span style="white-space:nowrap">PRIVACY POLICY</span></a> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <a href="' . $data .'" target="_blank"><span style="white-space:nowrap">DATA REQUESTS</span></a> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <a href="' . $parties .'" target="_blank"><span style="white-space:nowrap">RESPONSIBLE PARTIES</span></a> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <a href="https://bepopiacompliant.co.za/information_regulator" target="_blank"><span style="white-space:nowrap">INFORMATION REGULATOR</span></a> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;;
                                </div>
                            </div>
                        </div>';
                    }

                }

            }

        }

    }
   die();
}
bpc_active_check();