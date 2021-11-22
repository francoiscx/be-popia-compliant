<?php

/*
    Plugin Name: Be POPIA Compliant
    Plugin URI: https://bepopiacompliant.co.za
    Description: The only plugin that assists with POPIA Compliance for any site that operates in South Africa. Visit <a href="https://bepopiacompliant.co.za/popia/act/index.php" target="_blank">https://bepopiacompliant.co.za/popia/act/index.php</a> for the full legislation.
    Version: 1.0.0
    Author: Web-X
    Author URI: https://web-x.co.za/
    License: GPLv2 or later
    Text Domain: be_popia_compliant
*/

/*
    This program is free software; you can redistribute it and/or
    modify it under the terms of the GNU General Public License
    as published by the Free Software Foundation; either version 2
    of the License, or (at your option) any later version.
    
    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.
    
    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
    
    Copyright 2021 Automatic, Inc.
*/
/* ------------------------------------------------------------------------------------------------------------------ 
  ____       _____   ____  _____ _____          _____                      _ _             _                        
 |  _ \     |  __ \ / __ \|  __ \_   _|   /\   / ____|                    | (_)           | |                       
 | |_) | ___| |__) | |  | | |__) || |    /  \ | |     ___  _ __ ___  _ __ | |_  __ _ _ __ | |_   ___ ___   ______ _ 
 |  _ < / _ \  ___/| |  | |  ___/ | |   / /\ \| |    / _ \| '_ ` _ \| '_ \| | |/ _` | '_ \| __| / __/ _ \ |_  / _` |
 | |_) |  __/ |    | |__| | |    _| |_ / ____ \ |___| (_) | | | | | | |_) | | | (_| | | | | |_ | (_| (_) | / / (_| |
 |____/ \___|_|     \____/|_|   |_____/_/    \_\_____\___/|_| |_| |_| .__/|_|_|\__,_|_| |_|\__(_)___\___(_)___\__,_|
                                                                    | |                                             
                                                                    |_|                                             
 ------------------------------------------------------------------------------------------------------------------- */


if(!defined('ABSPATH')){
    exit;
}

session_start();

add_action('user_register','add_user_details_to_py');

function add_user_details_to_py($user_id){
    $new_user = get_userdata($user_id);
    $user_email = $new_user -> user_email;
    $domain = $_SERVER['SERVER_NAME'];

    if( ! $new_user ){
        error_log( 'Unable to get userdata!' );
        return;
    }

    $url  = 'https://py.bepopiacompliant.co.za/api/newusercreated/';
    $body = array(
        'domain' => $domain,
        'email' => $user_email,
        'user_id' => $user_id
    );

    $args = array(
        'method'      => 'POST',
        'timeout'     => 45,
        'sslverify'   => false,
        'headers'     => array(
            'Content-Type'  => 'application/json',
        ),
        'body'        => json_encode($body),
    );

    $request = wp_remote_post( $url, $args );

    if ( is_wp_error( $request ) || wp_remote_retrieve_response_code( $request ) != 200 ) {
        error_log( print_r( $request, true ) );
    }

    $response = wp_remote_retrieve_body( $request );

}


//------------------------------------------------//
//* Create Database Table for Be POPIA Compliant *//
//------------------------------------------------//

function bpc_user_scripts() {
    $plugin_url = plugin_dir_url( __FILE__ );

    wp_enqueue_style( 'style',  $plugin_url . "styles.css");
}

add_action( 'admin_print_styles', 'bpc_user_scripts' );


function bpc_create() {
    global $wpdb;
    
    $bpc_tb_checklist= $wpdb->prefix ."be_popia_compliant_checklist";
    
    require_once(ABSPATH ."wp-admin/includes/upgrade.php");
    
    $bpc_query_checklist="
    CREATE TABLE $bpc_tb_checklist(
        id int(12) NOT NULL AUTO_INCREMENT,
        title varchar(100) DEFAULT '',
        description varchar(1500) DEFAULT '',
        content varchar(500) DEFAULT '',
        type int(1) DEFAULT '0',
        does_comply int(1) DEFAULT '0',
        is_active int(1) DEFAULT'1',
        PRIMARY KEY (id))";
    dbDelta($bpc_query_checklist);


    $bpc_tb_logs= $wpdb->prefix ."be_popia_compliant_logs";

    $bpc_query_logs="
    CREATE TABLE $bpc_tb_logs(
        id int(12) NOT NULL AUTO_INCREMENT,
        userID int(12) DEFAULT '',
        action varchar(1500) DEFAULT '',
        PRIMARY KEY (id))";
    dbDelta($bpc_query_logs);


    $bpc_tb_admin= $wpdb->prefix ."be_popia_compliant_admin";
    $bpc_query_admin="
    CREATE TABLE $bpc_tb_admin (
        id int(12) NOT NULL AUTO_INCREMENT,
        title varchar(100) DEFAULT '',
        value varchar(1500) DEFAULT '',
        PRIMARY KEY (id))";
    dbDelta($bpc_query_admin);

    

    // echo '<script>console.log('.$wpdb->last_error.')</script>';
}

function bpc_insert_data() {
    global $wpdb;

    $table_name = $wpdb->prefix . 'be_popia_compliant_checklist';

    $result = $wpdb->get_results("SELECT ID from $table_name");

    if(count($result) > 0){

    } else {

        $all_items = array(
            array( 'title' => 'Communication with Clients', 'description' => 'There are mainly two types of communication, service level communication and marketing communication.', 'type' => -1 ),
            array( 'title' => 'Service Level Communication', 'description' => 'Do you communicate with your clients via Telephone, Email, SMS or WhatsApp or any other means, for service delivery excluding marketing?', 'type' => 1 ),
            array( 'title' => 'Marketing Communication', 'description' => 'Do you communicate with your clients via Telephone, Email, SMS or WhatsApp or any other means, for marketing related purposes, not including service delivery?', 'type' => 1 ),
            
            array( 'title' => 'Digital Form Creation', 'description' => 'You will have to set up a form to capture info from your client.', 'type' => -1 ),
            array( 'title' => 'Privacy Policy', 'description' => 'Set up your Privacy Policy, you are required to have a Privacy Policy in place to serve as your basis for all internal staff to uderstand... Be specific - go to <a href="./options-privacy.php">Privacy Policy</a> to set it up now.', 'type' => 0 ),
            array( 'title' => 'Privacy Policy URL', 'description' => 'Provide the link where your Privacy Policy can be accessed.', 'type' => 2 ),
                        
            array( 'title' => 'Cookie Notice', 'description' => 'Set up a cookie notice.', 'type' => -1 ),
            array( 'title' => 'Cookie Notice Settings', 'description' => 'Set up or customise your cookie notice to inform customers that you are using cookies. This is WordPress, for that reason by default cookies is in use to temporarily store the session headers. - go to <a href="./admin.php?page=privacy-policy">Cookie Settings</a> to set it up now.', 'type' => 1 ),

            array( 'title' => 'Request Data (Digital Documents Creation)', 'description' => '', 'type' => -1 ),
            array( 'title' => 'Create a new form', 'description' => 'Set up a form where the public would be able to request their data. The following fields are required:', 'type' => 0 ),
            array( 'title' => 'First Name', 'description' => 'Textfield - Do not include Middle or Surname.', 'type' => 1 ),
            array( 'title' => 'Middle Names', 'description' => 'Textfield - Do not include First or Surname.', 'type' => 1 ),
            array( 'title' => 'Surname', 'description' => 'Textfield - Do not include First or Middle names.', 'type' => 1 ),
            array( 'title' => 'Company Name', 'description' => 'Textfield - In the event of requesting company data.', 'type' => 1 ),
            array( 'title' => 'Contact Number', 'description' => 'Textfield - Preferably try to collect cell numbers to accommodate future feature updates on the Be POPIA Compliant plugin.', 'type' => 1 ),
            array( 'title' => 'Email', 'description' => 'Email field - Must be the address they have registered with on your site.', 'type' => 1 ),
            array( 'title' => 'Identity Number / Company Registration Number', 'description' => 'Textfield - For verification against ID Document / Company Registration Document.', 'type' => 1 ),
            array( 'title' => 'Request type', 'description' => 'SelectField - Provide the following four (4) options: <ul><li>- Update Data</li><li>- Download Data</li><li>- Delete Data</li><li>- Download and Delete Data</li></ul>', 'type' => 1 ),
            array( 'title' => 'Upload ID / Upload ID and Company Registration Document', 'description' => 'File Upload Field - To verify that the request came from DATA SUBJECT.', 'type' => 1 ),
            array( 'title' => 'Upload Selfie (holding their ID)', 'description' => 'File Upload Field - To verify Requestor as DATA SUBJECT.', 'type' => 1 ),
            array( 'title' => 'Request Data URL', 'description' => 'Supply the link where your clients can request their Data.', 'type' => 2 ),

            array( 'title' => 'Details of RESPONSIBLE PARTIES (Digital Documents Creation)', 'description' => '', 'type' => -1 ),
            array( 'title' => 'Create a new form', 'description' => 'Set up a form where the public will be able to get the following details for your company as well as the details for the contractors you may use. The following details are required:', 'type' => 0 ),
            array( 'title' => 'Company Name', 'description' => 'Both Registered and Trading as Name if applicable.', 'type' => 1 ),
            array( 'title' => 'Company Registration Number', 'description' => 'If not a Registered Company, use your Name and Surname.', 'type' => 1 ),
            array( 'title' => 'Company Registration Type', 'description' => 'CC / PTY(LTD) / Partnership / Sole Propriator / Non-Profit / ID Number', 'type' => 1 ),
            array( 'title' => 'Contact Number/Cell', 'description' => 'Use a Cell Number to avoid changes with future updates on Be POPIA Compliant plugin.', 'type' => 1 ),
            array( 'title' => 'Email Address', 'description' => 'Use your Support / Customer Care email or create a dedicated email manned by the DATA OFFICER.', 'type' => 1 ),
            array( 'title' => 'Physical Address', 'description' => 'If trading from home, use your home address. Also, use a Google pin location and add a clickable link.', 'type' => 1 ),
            array( 'title' => 'Postal Address', 'description' => 'Declare Postal Address even if same as Physical Address.', 'type' => 1 ),
            array( 'title' => 'Do above for all RESPONSIBLE PARTIES', 'description' => 'Do this for you or your own company as well as all third-party providers that you use like COURIER, PAYMENT SERVICE PROVIDER, HOSTING COMPANY (since they store the data).', 'type' => 1 ),
            array( 'title' => 'Details of RESPONSIBLE PARTIES URL', 'description' => 'Provide the link where your clients can get all details about your company and that of the contractors you may use.', 'type' => 2 ),
            
            array( 'title' => 'Printable Form Creation', 'description' => 'You need to set up a printable document (Consent Form) that you can provide to each of your clients. This consent form needs to be filled out and signed, then be returned for filing and will serve as irrefutable proof that you have consent to process the data of such data subject. ***Some people believe that a simple tickbox will suffice. Well, yes, it will be proof that the data subject consented to something, but to what? It can easily be disputed in the court that the content within the tickbox area was updated; Unless you can prove otherwise, which is unlikely without a copy of the actual agreement that was in place at the time when the data subject clicked the button. You will most likely update your consent form at least a couple of times. Also, how will you provide a copy to the data subject?*** The consent form needs to cover the following sections for you to be compliant.', 'type' => -1 ),
            array( 'title' => 'Consent Form | Section A - PROCESSING OF PERSONAL INFORMATION', 'description' => 'You need to understand that: According to POPIA, every time you use, capture or disperse information about a client (any person/company) you are "PROCESSING DATA" and the person or company in question is referred to as the "DATA SUBJECT"', 'type' => 1 ),
            array( 'title' => 'Use and modify this caption for A.1.', 'description' => 'We are required by law to obtain your consent if we want/need to process your personal information.', 'type' => 1 ),
            array( 'title' => 'Use and modify this caption for A.2.', 'description' => 'To process *--insert your service here--*, we need to process your personal information.', 'type' => 1 ),
            array( 'title' => 'Use and modify this caption for A.3.', 'description' => 'Therefore, without your consent, we will not be able to process your request unless we have your express consent.', 'type' => 1 ),
            array( 'title' => 'Use and modify this caption for A.4.', 'description' => 'None of the parties above shall store and therfore shall not use your information for the purposes of marketing.', 'type' => 5 ),
            array( 'title' => 'Use and modify this caption for A.4.', 'description' => 'Any of the parties above shall only be allowed to market through the channels you select in section D.15.', 'type' => 6 ),
            array( 'title' => 'Consent Form | Section B - RESPONSIBLE PARTIES THAT IS REQUESTING CONSENT', 'description' => 'You need to understand that: According to POPIA, you need to appoint a "DATA OFFICER". In a small business setup, this will typically be you. Your organisation and everyone working in it as a collective is referred to as the "RESPONSIBLE PARTY". The Courier you provide data to for delivery is also a "RESPONSIBLE PARTY" and you need to also request consent on their behalf.', 'type' => 1 ),
            array( 'title' => 'List RESPONSIBLE PARTIES with responsibilities', 'description' => 'List each RESPONSIBLE PARTY and follow up with the service they will deliver. Do B.1. for the first, B.2. for the second etc.', 'type' => 1 ),
            array( 'title' => 'Consent Form | Section C - OUR COMMITMENT TO PROTECTING THE PERSONAL INFORMATION OF OUR CLIENTS', 'description' => 'According to POPIA, you need to properly inform your clients of their rights. Use the following paragraph as a basis and change accordingly:', 'type' => 0 ),
            array( 'title' => 'Use this caption', 'description' => 'The responsible parties mentioned in section (B) above is committed to protecting your privacy and recognises that it needs to comply with statutory requirements in collecting, processing, and distributing personal information. The Constitution of the Republic of South Africa provides that everyone has the right to privacy and the Protection of Personal Information Act 4 of 2013 (“POPI”) includes the right to protection against unlawful collection, retention, dissemination and use of personal information. In terms of section 18 of POPI, if personal information is collected, the above parties, as responsible parties, must take reasonably practicable steps to ensure that the data subject is made aware of the information being collected.', 'type' => 1 ),
            array( 'title' => 'Consent Form | Section D - PROCESSING OF WHAT PERSONAL INFORMATION AND HOW', 'description' => 'Define the Personal Info that is to be collected. The following 16 points need to be clearly explained in Section D of your Consent form.', 'type' => 0 ),
            array( 'title' => '1. TYPE OF INFORMATION:', 'description' => 'List the TYPES of information that you collect about a Client (ID, Company Registration Number, Full names, Cellphone Number, etc.)', 'type' => 1 ),
            array( 'title' => '2. NATURE OF INFORMATION:', 'description' => 'Describe the use/reason for collection of the above information.', 'type' => 1 ),
            array( 'title' => '3. PURPOSE:', 'description' => 'Explain what the purpose of the collection of this data is for.', 'type' => 1 ),
            array( 'title' => '4. SOURCE:', 'description' => 'How is the data obtained? ie. Voluntarily supplied by the DATA SUBJECT.', 'type' => 1 ),
            array( 'title' => '5. VOLUNTARILY/MANDATORY:', 'description' => 'Is the data that is being collected Mandatory/Voluntarily? Also, explain why.', 'type' => 1 ),
            array( 'title' => '6. LEGAL REQUIREMENT:', 'description' => 'State the act and a link to where the act can be accessed -> "Protection of Personal Information Act 4 of 2013 | POPIA -  https://bepopiacompliant.co.za/popia/act/index.php"', 'type' => 1 ),
            array( 'title' => '7. CONTRACTUAL REQUIREMENT:', 'description' => 'Explain why this info is required with regards to the contractual agreement you have with the client.', 'type' => 1 ),
            array( 'title' => '8. CONSEQUENCES OF FAILURE TO PROVIDE:', 'description' => 'According to POPIA, you are not allowed to hold the data of any DATA SUBJECT without their consent, and that you are required by law to delete all info of such person that in turn means that they may have to sign up again if in future they need to place a new order.', 'type' => 1 ),
            array( 'title' => '9. CROSS BORDER TRANSFER:', 'description' => 'Declare whether or not you move data over the border, and if so for what reason and if the country it is transferred through or too also comply with the requirements of the POPI Act.', 'type' => 1 ),
            array( 'title' => '10. RECIPIENTS OF PERSONAL INFORMATION:', 'description' => 'Disclose all parties that shall have access to this information, these are the parties listed as "RESPONSIBLE PARTIES."', 'type' => 1 ),
            array( 'title' => '11. ACCESS AND RIGHT TO AMEND:', 'description' => 'The DATA SUBJECT has the right to access information held by your company. They have the right to request their DATA at any time and you need to provide it within a reasonable time (72 Hours), they also have the right to have their DATA Updated or Deleted.', 'type' => 1 ),
            array( 'title' => '12. RIGHTS TO OBJECT:', 'description' => 'The DATA SUBJECT has the right to object to the use of his/her/its DATA. This needs to lead to the complete deletion of data of such personal information from your system, hence "deleting such persons\' account" and they will no longer be able to engage with your company unless providing consent again. Explain the just mentioned criteria at this point.', 'type' => 1 ),
            array( 'title' => '13. COMPLAINTS:', 'description' => 'Complaints regarding the use of Personal Information may be directed to the <a href=https://manageconsent.co.za/information_regulator_contact_details>INFORMATION REGULATOR</a>.', 'type' => 1 ),
            array( 'title' => '14. SERVICE LEVEL COMMUNICATION:', 'description' => 'Since you communicate with your clients on a service level, you need to add a clause to your consent form (with the heading: Service Level Communication) where your customers need to tick the methods they prefer as preferred means of communication. If they do not tick for eg. email, you are not allowed to contact them via email or send out statements or invoices via email. Include each of these with tickboxes, if you want to make use of such communication means:<ul><li>- Telephone</li><li>- SMS</li><li>- Email</li><li>- WhatsApp</li><li>- Telegram</li><li>- Messenger</li><li>- Add any other means whereby you send messages to your clients.</li></ul>', 'type' => 3 ),
            array( 'title' => '15. MARKETING COMMUNICATION:', 'description' => 'Since you send out marketing material to your clients, you need to add a clause to your consent form (with the heading: Marketing Communication) where your customers need to tick the methods they prefer as preferred means of marketing. If they do not tick for eg. WhatsApp, you are not allowed to contact them via WhatsApp. Include each of these with tickboxes, if you want to make use of such marketing means:<ul><li>- Telephone</li><li>- SMS</li><li>- Email</li><li>- WhatsApp</li><li>- Telegram</li><li>- Messenger</li><li>- Add any other means whereby you send messages to your clients.', 'type' => 4 ),
            array( 'title' => 'Use and modify this caption at the bottom of the agreement', 'description' => '"I _____________ with ID Number _______________ herewith give my consent to the parties mentioned as per the agreement above.<br>Signed at ___________ on the  ____________. Signature ________________". Remember that if youor agreement is spread out over more than 1 (one) page, a signature field need to be provided for each page.', 'type' => 1 ),
            
            
            array( 'title' => 'Register the Information Officer', 'description' => 'According to the POPI Act, for responsible parties to be compliant with POPIA they are required amongst many actions to appoint and register their Information Officers (IO) with the Information Regulator and apply for Prior Authorisation before processing personal information.', 'type' => -1 ),
            array( 'title' => 'Appoint a Data Officer', 'description' => 'By default, the CEO or head of the organisation is deligated as the Data Officer, but a Data Officer can formally be appointed to take over these duties from the CEO. Take note that ultimately the CEO is still responsible for the actions of the Data Officer, so make sure you designate someone responsible and competent. Whether or not you are the CEO or head and decide to or not to appoint someone for this duty, you\'d probably want to become better acquainted with the duties at hand, even if it is only as a supervisory position. Visit our <a href=https://bepopiacompliant.co.za/popia/Data_Officer_Guidance_Note/index.php>Data Officer Guidance Note</a> page for details.', 'type' => 1 ),
            array( 'title' => 'Follow these instructions to register your Information Officer', 'description' => 'Visit the <a href=https://www.justice.gov.za/inforeg/portal.html target="_blank">INFORMATION REGULATOR PORTAL</a> to Register.<br><br> ***At the time of the release of this plugin, the Information Officer has been experiencing technical issues with their Portal, which resulted in it not being accessible***<br><br> - If this is still the case, they provided a <a href=https://www.justice.gov.za/inforeg/docs/forms/InfoRegSA-eForm-InformationOfficersRegistration-2021.pdf target="_blank">PDF Registration</a> as an alternative, that can be filled out in the browser. You\'d still have to print it out in order to sign the document. Thereafter you can send it via email to: <a href=mailto:registration.IR@justice.gov.za>registration.IR@justice.gov.za</a>', 'type' => 1 ),

            array( 'title' => 'Active Tasks', 'description' => 'There are some tasks that you have to monitor daily, to stay compliant and to avoid hefty fines or imprisonment.', 'type' => -1 ),
            array( 'title' => 'For every request of the following:', 'description' => '', 'type' => 0 ),
            array( 'title' => 'Request | Data Download', 'description' => 'Every time you get this request, you have to react within a reasonable time (72 hours). In such an event, you need to verify that the requestor is indeed the owner of the data (the DATA SUBJECT). This is done by matching the Person\'s Selfie (holding their ID) with the image on their ID, also ensure that the id in the selfie image match that of the ID sent. You should also match the requestor\'s email against the data stored in your database. If you are uncertain about the request ie. The photo looks off, or anything seems out of place, it might be a good idea to get the email of this data subject in your database and send them an email stating: "We received a request via email from <email> by <name & surname> on the <date> at <time> that you want us to provide you with your data. Please confirm or deny this request". Only once you get a reply stating confirm, you should export their data and send it to their email as a reply. Alternatively, reply with instructions on how they can log in to your site to view all their data if your system allows it.', 'type' => 7 ),
            array( 'title' => 'Request | Data Delete', 'description' => 'You should follow the same instructions as above to verify that the requestor is indeed the DATA SUBJECT - Many people may try to claim other people\'s data for whatever reason, so be sure you confirm their identity. Even when you are sure about the identity, it would probably be in your best interest if you first again do a lookup in your database for the registered email and then send them a mail stating: "We received a request via email from <email> by <name & surname> on the <date> at <time> that you want us to delete all your data. Please note that all data shall be lost and that you will have to create a new account in future should you want to make use of our services. Please let us know that you understand that all data will be lost and that you want us to delete your data. If you did not send the request, please also let us know so we can take action accordingly."', 'type' => 7 ),
            array( 'title' => 'Request | Data Download & Delete', 'description' => 'Again ensure that the requestor is indeed the DATA SUBJECT - Then again do a lookup in your database for the registered email and then send them a mail stating: "We received a request via email from <email> by <name & surname> on the <date> at <time> that you want us to supply your data and then to delete all your data. Please note that all data shall be lost and that you will have to create a new account in future should you want to make use of our services again. Please let us know that you understand that all data will be lost and that you want us to send you your data and then delete your data from our servers. If you did not send the request, please also let us know so we can take action accordingly."', 'type' => 7 ),
            array( 'title' => 'For every confirmation of a request for the following:', 'description' => '', 'type' => 0 ),
            array( 'title' => 'Confirmation | Data Download', 'description' => 'Now that you successfully authenticated the user that requested his/her/it\'s data, you need to export all data of said party that is stored in your database, combine it all together in a table and supply this to said DATA SUBJECT. - How you do it is up to you, you can generate a random link and post it there for the person to view, or you can send it off to them in an email. It is up to you to secure the information, so be sure to password protect it. - Ensure you keep a record of this request."', 'type' => 7 ),
            array( 'title' => 'Confirmation | Data Delete', 'description' => 'Now that you successfully authenticated the DATA SUBJECT that requested deletion of their data, you need to delete or de-identify all data of said party that is in your posession, delete the data from the database, and destroy any hardcoppies you may have, also ensure you remove their data from your phone. - Ensure you keep a record of this request; you need to also notify the person that all their data has been queued for deletion. Afterwards delete all said data."', 'type' => 7 ),
            array( 'title' => 'Confirmation | Data Download & Delete', 'description' => 'Now that you successfully authenticated the DATA SUBJECT that requested download & deletion of their data, you need to export all data of said party that is stored in your database, combine it all together in a table and supply this to said DATA SUBJECT with a notice that their data has been queued for deletion. - Since you should also delete the data, it is probably best if you send it off to them in an email. It is up to you to secure the information, so be sure to password protect it. Afterwards you need to delete or de-identify all data of said party that is in your posession, delete the data from the database, and destroy any hardcoppies you may have, also ensure you remove their data from your phone. - Ensure you keep a record of this request; you need to also notify the person that all their data has been queued for deletion. Afterwards delete all said data."', 'type' => 7 ),
            array( 'title' => 'What else to remember:', 'description' => '', 'type' => 0 ),
            array( 'title' => 'With each new registration:', 'description' => '<span class="pro"><span class="withpro">With Pro: </span>We will send out this mail in a digital format that is quick and easy to sign without any printing or returning.</span><br><br>Attach the consent form to an email explaining to your client that all data will be deleted within 72 hours should they not fill in and return the CONSENT FORM, if you are able you can alter your welcome message to include these.', 'type' => 7 ),
            array( 'title' => 'What to do with returned/completed consent forms:', 'description' => '<span class="pro"><span class="withpro">With Pro: </span>We mark this in your database which users has consent and also automatically delete the data of users that used your services where CONSENT has not been given 72 hours after the delivery.</span><br><br>These forms need to be stored in such manner that you can quickly and easily recall them from your filing system and link them to the applicable user so you  know which users has not provided consent and has to be deleted.', 'type' => 7 ),
            array( 'title' => 'After completing a transaction/order:', 'description' => '<span class="pro"><span class="withpro">With Pro: </span>We take care of all of this for you!</span><br><br><ul><li>- If no consent form was received from the data subject, you have to remind the data subject that if they do not provide a signed consent form within 72 hours, that by law you will have to delete their data. And that this in return will require them to setup a new account in future, if they want to order from you again.</li><li>- If you have received consent and already stored it and linked it to the user, no further action is nessesary</li></ul>', 'type' => 7 ),
            array( 'title' => 'What to do when data gets breached?', 'description' => '<span class="pro"><span class="withpro">With Pro: </span>We allow you to answer a few questions and we will do the rest.</span><br><br>In the event that your website has been compromised or it becomes evident that any of your employees collected or distributed any of your clients data, or any other event happened where data got lost you should send out a notice to all affected parties. If you are not sure who was affected you should send a notice to your entire database notifying them that their data might have been compromised.', 'type' => 7 ),
        );

        foreach($all_items as $item){
            $wpdb->insert( 
                $table_name, 
                $item
            );
        }
    }
}



function bpc_insert_p_data() {
    global $wpdb;

    $table_name = $wpdb->prefix . 'be_popia_compliant_admin';

    $result = $wpdb->get_results("SELECT ID from $table_name");

    if(count($result) > 0){

    }else{

        $all_items = array(
            array( 'title' => 'API Key'),
            array( 'title' => 'Company Key'),
            array( 'title' => 'Suspended'),
            // array( 'title' => 'flag_IR_Problem', 'value' => '(They have been experiancing technical issues with the Portal, which results in not being accessible)<br> - If this is still the case, they provided a <a href=https://www.justice.gov.za/inforeg/docs/forms/InfoRegSA-eForm-InformationOfficersRegistration-2021.pdf target="_blank">PDF Registration</a> as an alternative, that can be filled out in the browser. You\'d still have to print it out in order to sign the document. Thereafter you can send it via email to: <a href=mailto:registration.IR@justice.gov.za>registration.IR@justice.gov.za</a>'),
        );

        foreach($all_items as $item){
            $wpdb->insert( 
                $table_name, 
                $item

            );
        }
    }
}


register_activation_hook( __FILE__, 'bpc_create' );
register_activation_hook( __FILE__, 'bpc_insert_data' );

register_activation_hook( __FILE__, 'bpc_insert_p_data' );


function bpc_dashboard_go_pro(){
    $output = '
        <div class="bpc_wrap">
            <h2>POPIA GO PRO</h2>
            <p>CONTENT NEEDED</p>
            
        </div>
    ';

    echo $output;
}


function bpc_dashboard(){
    $url = "https://py.bepopiacompliant.co.za/api/domain/" . $_SERVER['SERVER_NAME'];
        
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
            echo " Error in pinging API" . $response_code;
        }
    
        // if ( 200 === $response_code ) {
        //     echo "BODY: " . $body;
        // }

     
     
    echo '
        <div class="bpc_wrap_dashboard">
            <div class="bpc_dashboard_one">';
                    
                global $wpdb;

                $table_name = $wpdb->prefix . 'be_popia_compliant_admin';
                $result_api = $wpdb->get_row("SELECT value FROM $table_name WHERE id = 1");
                $result_company = $wpdb->get_row("SELECT value FROM $table_name WHERE id = 2");
                $result_suspended = $wpdb->get_row("SELECT value FROM $table_name WHERE id = 3");
                $result_complete = '';

                if((isset($result_api->value) && $result_api->value !='') && (isset($result_company->value) && $result_company->value != '') && $result_suspended->value != 1 && $result_complete == 1){
                    echo'
                    <div class="bpc_p_version">
                        You are using a pro version of BPC
                    </div>
                    <div class="bpc_dashboard_main_content">
                        <div class="bpc_dashboard_logo">
                            <img width="200" src="data:image/png;base64,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">
                        </div>
                        <div class="bpc_dashboard_status" style="line-height:88px">
                                You are 100% compliant
                        </div>
                    </div>
                    ';
                } elseif((isset($result_api->value) && $result_api->value !='') && (isset($result_company->value) && $result_company->value != '') && $result_suspended->value != 1 && $result_complete != 1){
                    echo'
                    <div class="bpc_p_version">
                        You are connected to Pro, but action on your account is required and the free version is still in effect. <a href="https://bepopiacompliant.co.za" style="color:#B7191A"; target="_blank"><span style="line-height: 45px; margin: 30px important;"> Fix it now!</span></a>
                    </div>
                    <div class="bpc_dashboard_main_content">
                        <div class="bpc_dashboard_logo">
                            <img width="200" src="data:image/png;base64,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">
                        </div>';

                        echo '<div class="bpc_dashboard_status">
                        You are <span id="bpc_completeness" style="line-height:88px"></span>% compliant, using the Free version of BPC.<br>
                        <!-- If you choose not to use the Pro version, please complete your free <a href="./admin.php?page=bpc_checklist"> checklist</a>.  -->
                        </div>';

                    echo '</div>
                    ';
                } else {
                    echo'
                    <div class="bpc_version">
                        You are using a free version of BPC
                    </div>
                    <div class="bpc_dashboard_main_content">
                        <div class="bpc_dashboard_logo">
                            <img width="200" src="data:image/png;base64,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">
                        </div>
                        <div class="bpc_dashboard_status">
                                You are <span id="bpc_completeness"></span>% compliant
                        </div>
                    </div>
                    ';
                }


                echo'
                <div class="bpc_dashboard_main_about">
                    Be Popia Compliant (BPC) - avoid fines and imprisonment by becoming POPIA Compliant.
                    <p>BPC has researched on your behalf, no need to read 80 pages of POPIA and countless more references into other laws such as the Electronic Communications Act 36 of 2005 and The Promotion of Access to Information Act 2 of 2000.
                    In the Pro version, we practically do all the work for you.</p>
                    
                    <p>Don\'t want to go pro? Don\'t worry, u can use our POPIA checklist to be sure to cover all requirements.</p>
                    <p>We even include cookie notice.</p>
                </div>
            </div>
            <div class="bpc_dashboard_two">';
                
                if((isset($body)) && (!empty($body)) && ($body != '') && ($body != "[]")){
                    global $wpdb;

                    $table_name = $wpdb->prefix . 'be_popia_compliant_admin';
                    $result = $wpdb->get_row("SELECT value FROM $table_name WHERE id = 1");

                    echo'<h2 class="bpc_dashboard_upgrade_heading" id="bpc_dashboard_upgrade_heading">BPC Pro</h2>';

                    $result_suspended = $wpdb->get_row("SELECT value FROM $table_name WHERE id = 3");

                    if($result_suspended->value == 1){
                        echo '
                            <h2 style="text-align:center;color:#B61F21;">Your account has been suspended due to non payment</h2>
                            <h4 style="text-align:center;color:#B61F21;">Please visit bepopiacompliant.co.za to reactivate your account</h4>
                        ';
                    }

                    echo'
                    <label class="bpc_p_label" for="bpc_api_key_input">API Key:</label>
                    <input class="bpc_api_key_input widefat" type="text" id="bpc_api_key_input" name="bpc_api_key_input" value="'. $result->value .'">';
                    
                    $table_name = $wpdb->prefix . 'be_popia_compliant_admin';
                    $result = $wpdb->get_row("SELECT value FROM $table_name WHERE id = 2");

                    echo'
                    <label class="bpc_p_label" for="bpc_company_key_input">Domain Key:</label>
                    <input class="bpc_company_key_input widefat" type="text" id="bpc_company_key_input" name="bpc_company_key_input" value="'. $result->value .'">
                    <button id="url_button" onclick="save_keys(); location.reload();">Save</button>                       
                                                    
                    <script>                                                    
                        function save_keys(){      
                            var api_key = document.getElementById("bpc_api_key_input").value;
                            var company_key = document.getElementById("bpc_company_key_input").value;
                            jQuery.ajax({
                                type: "post",
                                cache: false,
                                dataType: "json",
                                url: ajaxurl,
                                data: {
                                    "action":"bpc_p_key_save",
                                    "api_key" : api_key,
                                    "company_key" : company_key,
                                    "suspended" : 0,
                                },
                                success:function(data) {
                                    window.location.reload();
                                },  
                                error: function(errorThrown){
                                }
                            });
                        }
                    </script>
                    ';
                } else {
                    echo '
                    <!--  <h2 class="bpc_dashboard_upgrade_heading"> ' . 'BlackFriday |<br><br> GET 75% discount!' . '</h2> -->
                    <h2 class="bpc_dashboard_upgrade_heading">Upgrade to BPC Pro</h2>
                    <ul>
                        <li>Proof to visitors that you are compliant</li>
                        <li>Shorter Setup</li> 
                        <li>We also keep a log register for all actions
                        <li>Litigation? We have your back</li>
                        <li>No need to struggle to get consent</li>
                        <li>No need to manually follow up with clients</li>
                        <li>Your website will know who has consent</li>
                        <li>No need to authenticate requesters</li>
                        <li>No need to waste time on processing requests</li>
                        <li>We delete, update and send data on your behalf</li>
                        <li>Cookie notice included free of charge</li>
                        
                    </ul>
                    <a href="https://bepopiacompliant.co.za" class="bpc_dashboard_go_pro" target="_blank">Go Pro</a>';
                }
                
                echo '
            </div>
            <div class="bpc_dashboard_three">
                <h2>Consequences of non-compliance</h2>
                
                <p>Criminal: POPIA imposes various criminal offences for non-compliance. Non-compliance with POPIA can result in imprisonment not exceeding ten (10) years and/or a fine not exceeding R10 million.</p>
                <p>Civil: In terms of section 99 of POPIA, a data subject or, at the request of the data subject, the Regulator, may institute a civil action for damages in a court having jurisdiction against a responsible party for breach of POPIA.</p>
            </div>
            <div class="bpc_dashboard_four">
                <p>To find out more about Be POPIA Compliant, please <a href="https://bepopiacompliant.co.za" class="bpc_dashboard_button">visit our webpage</a>. </p>
            </div>
        </div>
        <script>
            window.onload = check_db;
            function checkStatus(){
                setInterval(function(){
                    check_db() 
                }, 3000);
            }
            function check_db(){
                
                jQuery.ajax({
                    type: "GET",
                    url: ajaxurl,
                    data: {
                        "action":"bpc_checklist_update_compliance",
                    },
                    success:function(data) {
                        document.getElementById("bpc_completeness").innerHTML = Math.floor(data);
                    },  
                    error: function(errorThrown){
                    }
                });
            }
            checkStatus();
        </script>
    ';
    
    }

    
 
function bpc_notice() {
    global $pagenow;
    $admin_pages = [ 'index.php', 'edit.php', 'plugins.php' ];


    $url = "https://py.bepopiacompliant.co.za/api/getmessage/";
        
    $args = array(
        'headers' => array(
            'Content-Type' => 'application/json',
        ),
        'body'    => array(),
    );

    $response = wp_remote_get( $url, $args );

    $response_code = wp_remote_retrieve_response_code( $response );
    $data = wp_remote_retrieve_body( $response );

    if ( 401 === $response_code ) {
        echo "Unauthorized access";
    }

    if ( 200 !== $response_code ) {
        echo " Error in pinging API" . $response_code;
    }

    if ( 200 === $response_code ) {
        $data = json_decode( $data );

        foreach ( $data as $datapoint ) {
            $server_message = $datapoint->value;
        }
        // echo "server_message:

        // " . $server_message;
    }    

    if(isset($server_message) && ($server_message != 'null')) {
        
    
            if ( in_array( $pagenow, $admin_pages ) ) {
                if(isset($server_message)) {
                        ?>
                        <div class="notice notice-warning is-dismissible"> <p>
                            <?php
                            echo $server_message;
                            ?>
                        </p></div>
                        <?

                    }
                }
            }

}

add_action( 'admin_notices', 'bpc_notice' );

function bpc_p_key_save() {
    if ( isset($_REQUEST) ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'be_popia_compliant_admin';

        $api_key = $_REQUEST["api_key"];
        $company_key = $_REQUEST["company_key"];
        $suspended = $_REQUEST["suspended"];

        $url = "https://py.bepopiacompliant.co.za/api/domainapicheck/" . $company_key;
        
        $args = array(
            'headers' => array(
                'Content-Type' => 'application/json',
                'Authorization' => 'Token ' . $api_key,
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
            if((isset($body)) && (!empty($body)) && ($body != '') && ($body != "[]")){
                if($api_key != ''){
                    $wpdb->update( $table_name, array( 'value' => $api_key ),array('id'=>1)); 
                } 
                if($company_key != '') {
                    $wpdb->update( $table_name, array( 'value' => $company_key ),array('id'=>2)); 
                }
                if($suspended != '') {
                    $wpdb->update( $table_name, array( 'value' => $suspended ),array('id'=>3)); 
                }
            } else {
                echo " Error in pinging API";
            }
        }
    }

   die();
}

add_action( 'wp_ajax_bpc_p_key_save', 'bpc_p_key_save' ); 


function bpc_dashboard_checklist(){
    
	global $wpdb;
	
	$table_name = $wpdb->prefix . 'be_popia_compliant_checklist';

    $results = $wpdb->get_results("SELECT * from $table_name");

    echo '
        <div class="bpc_wrap">
        
            <h1 style="text-align-last: center;font-size:50px;">POPIA CHECKLIST</h1>
            <center><h3>Please note that this only take effect for FREE version or when membership to Pro version has lapsed.<h/3></center>
            
            <div class="Progress">
                    <div id="completed_consent" style="display:none;">
                        <br>
                        <h1 style="text-align:center;color:green">You are 100% compliant!</h1>
                    </div>
                    <div id="not_completed_consent" style="display:none;">
                        <progress max="100" id="progress" value="" class="Progress-main" aria-labelledby="Progress-id">
                            <div class="Progress-bar" role="presentation">
                                <span class="Progress-value" style="width: 75%;">&nbsp;</span>
                            </div>
                        </progress>
                        <h2 class="Progress-label" id="Progress-id">You are <strong><span id="progress-percent"></span>%</strong> compliant.</h2>
                        <p class="Progress-paragraph">Complete the below instructions one by one, and if applicable tick them off, as you complete a task or take notice of the instruction.</p>
                    </div>
            </div>
            <script>
                window.onload = check_db;
                function checkStatus(){
                    setInterval(function(){
                        check_db() 
                    }, 3000);
                }
                function check_db(){
                    
                    jQuery.ajax({
                        type: "GET",
                        url: ajaxurl,
                        data: {
                            "action":"bpc_checklist_update_compliance",
                        },
                        success:function(data) {
                            if(data == 100){
                                document.getElementById("completed_consent").style.display = "block";
                                document.getElementById("not_completed_consent").style.display = "none";
                            } else {
                                document.getElementById("completed_consent").style.display = "none";
                                document.getElementById("not_completed_consent").style.display = "block";
                            }
                            document.getElementById("progress-percent").innerHTML = Math.floor(data);
                            document.getElementById("progress").value = Math.floor(data);
                        },  
                        error: function(errorThrown){
                        }
                    });
                    
                }
                
                checkStatus();
            </script>
            <br><br>
            <div class="bpc_row">
                <div class="bpc_col">
                    <div class="bpc_tabs">';
                        foreach($results as $result){

                            if($result->id == 3) {
                                if($result->does_comply == 1) {
                                    $marketing = 39;
                                } else {
                                    $marketing = 38;
                                } 
                            }

                            if($result->type == -1){
                                echo '
                                </div>
                                <br><br>
                                <div class="bpc_tabs">
                                <div class="bpc_tab">
                                    <span class="bpc_tab-main-section-label_completed" for="rd' . $result->id . '">' . $result->title;
                                if($result->description != '') { echo '<a href="#" title="' . $result->description . '" class="tooltip">?</a></span>';}
                                echo '</div>';
                            } elseif($result->type == 0){
                                echo '
                                <div class="bpc_tab">
                                    <input class="bpc_input"  type="radio" id="rd' . $result->id . '" name="rd">
                                    <label class="bpc_tab-section-label_completed" for="rd' . $result->id . '">' . $result->title . '</label>
                                    ';
                                    if($result->description != ""){

                                    echo'
                                        <div class="bpc_tab-section-content">
                                            ' . $result->description . '   
                                        </div>';
                                    }
                                echo'
                                </div>';
                            }  elseif($result->type == 3){
                                global $wpdb;
                                
                                $table_name = $wpdb->prefix . 'be_popia_compliant_checklist';
                            
                                
                                $checks = $wpdb->get_results("SELECT does_comply FROM $table_name WHERE id = 2");
                                foreach($checks as $check){
                                    if($check->does_comply == 1){
                                        echo '
                                        <div class="bpc_tab">
                                            <input class="bpc_input"  type="radio" id="rd' . $result->id . '" name="rd">
                                            <label '; if($result->does_comply == 0){echo 'class="bpc_tab-label"';} else {echo'class="bpc_tab-label_completed"';} echo ' for="rd' . $result->id . '">' . $result->title . '</label>
                                            ';
                                            if($result->description != ""){

                                                echo'
                                                <div class="bpc_tab-content">
                                                    ' . $result->description . '
                                                    <input type="hidden" id="bpc_id" name="bpc_id" value="' . $result->id . '">
                                                    <input type="checkbox" '; if($result->does_comply == 1){echo 'checked';} echo ' class="bpc_checkbox" id="bpc_checkbox" name="bpc_checkbox" onclick="validate(bpc_checkbox,' . $result->id . ')">';
                                            }
                                            echo'
                                            </div>
                                        </div>';
                                    }
                                }
                            } elseif($result->type == 4){
                                global $wpdb;
                                
                                $table_name = $wpdb->prefix . 'be_popia_compliant_checklist';
                            
                                $checks = $wpdb->get_results("SELECT * FROM $table_name WHERE id = 3");
                                foreach($checks as $check){
                                    if($check->does_comply == 1){
                                        
                                        
                                        global $wpdb;
        
                                        $table_name = $wpdb->prefix . 'be_popia_compliant_checklist';

                                        $needComms = $wpdb->get_var( $wpdb->prepare(
                                            " SELECT does_comply FROM $table_name WHERE id = 2")
                                        );
                                        
                                        $needMarketing = $wpdb->get_var( $wpdb->prepare(
                                            " SELECT does_comply FROM $table_name WHERE id = 3")
                                        );

                                        if($needComms == 0 && $needMarketing == 1) {
                                            echo '
                                            <div class="bpc_tab">
                                                <input class="bpc_input"  type="radio" id="rd' . $result->id . '" name="rd">
                                                <label '; if($result->does_comply == 0){echo 'class="bpc_tab-label"';} else {echo'class="bpc_tab-label_completed"';} echo ' for="rd' . $result->id . '">14. MARKETING COMMUNICATION:</label>
                                                ';
                                                if($result->description != ""){

                                                    echo'
                                                    <div class="bpc_tab-content">
                                                        ' . $result->description . '
                                                        <input type="hidden" id="bpc_id" name="bpc_id" value="' . $result->id . '">
                                                        <input type="checkbox" '; if($result->does_comply == 1){echo 'checked';} echo ' class="bpc_checkbox" id="bpc_checkbox" name="bpc_checkbox" onclick="validate(bpc_checkbox,' . $result->id . ')">';
                                                }
                                                echo'
                                                </div>
                                            </div>';
                                    } else {
                                            echo '
                                            <div class="bpc_tab">
                                                <input class="bpc_input"  type="radio" id="rd' . $result->id . '" name="rd">
                                                <label '; if($result->does_comply == 0){echo 'class="bpc_tab-label"';} else {echo'class="bpc_tab-label_completed"';} echo ' for="rd' . $result->id . '">' . $result->title . '</label>
                                                ';
                                                if($result->description != ""){

                                                    echo'
                                                    <div class="bpc_tab-content">
                                                        ' . $result->description . '
                                                        <input type="hidden" id="bpc_id" name="bpc_id" value="' . $result->id . '">
                                                        <input type="checkbox" '; if($result->does_comply == 1){echo 'checked';} echo ' class="bpc_checkbox" id="bpc_checkbox" name="bpc_checkbox" onclick="validate(bpc_checkbox,' . $result->id . ')">';
                                                }
                                                echo'
                                                </div>
                                            </div>';
                                        }
                                    }
                                }
                            } else {

                               
                                    if($marketing == 38) {
                                        if($result->type == 5) {
                                            echo '
                                            <div class="bpc_tab">
                                                <input class="bpc_input"  type="radio" id="rd' . $result->id . '" name="rd">
                                                <label '; if($result->does_comply == 0){echo 'class="bpc_tab-label"';} else {echo'class="bpc_tab-label_completed"';} echo ' for="rd' . $result->id . '" id="bpc_tab-label' . $result->id . '">' . $result->title . '</label>
                                                <div class="bpc_tab-content"><br>
                                                    
                                                    ' . $result->description . '
                                                        ';
                                                        if($result->type == 1 || $result->type == 5 || $result->type == 6 || $result->type == 7){
                                                            
                                                                echo '
                                                                    <input type="hidden" id="bpc_id" name="bpc_id" value="' . $result->id . '">
                                                                    <input type="checkbox" '; if($result->does_comply == 1){echo 'checked';} echo ' class="bpc_checkbox" id="bpc_checkbox" name="bpc_checkbox" onclick="validate(bpc_checkbox,' . $result->id . ')">';
                                                            
                                                        } elseif($result->type == 2){
                                                            echo '
                                                                <input type="hidden" id="bpc_id_url" name="bpc_id_url" value="' . $result->id . '">
                                                                <input type="text" id="input_field' . $result->id . '" name="input_field' . $result->id . '" placeholder="eg. https://' . $_SERVER['SERVER_NAME'] . '/?page_id=3" class="widefat"'; if($result->content != ''){ echo 'value="' . $result->content . '"';} echo'>
                                                                <button id="url_button" onclick="save_field(' . $result->id . ')">Save</button>
                                                                
                                                                
                                                                <script>                                                    
                                                                    function save_field(check_id){
                                                                        var result_id = "input_field" + check_id;
                                                                        var input = document.getElementById(result_id).value;
                                                                        var input_id = document.getElementById("bpc_id_url").value;
                                                                        
                                                                        jQuery.ajax({
                                                                            type: "post",
                                                                            cache: false,
                                                                            dataType: "json",
                                                                            url: ajaxurl,
                                                                            data: {
                                                                                "action":"bpc_checklist_update_url",
                                                                                "check_id" : check_id,
                                                                                "input" : input
                                                                            },
                                                                            success:function(data) {
                                                                            },  
                                                                            error: function(errorThrown){
                                                                            }
                                                                        });
                                                                        
                                                                    }
                                                                </script>
                                                            ';
                                                        }
                                                        echo'
                                                </div>
                                            </div>
                                            <script>
                                                            
                                                function validate(el, check_id) {
                                                    var label_id = "bpc_tab-label" + check_id;
                                                    if (el.checked) {
                                                        jQuery.ajax({
                                                            url: ajaxurl,
                                                            data: {
                                                                "action":"bpc_checklist_update",
                                                                "check_id" : check_id
                                                            },
                                                            success:function(data) {
                                                                document.getElementById(label_id).style.background = "#B7191A";
                                                            },  
                                                            error: function(errorThrown){
                                                                window.alert(errorThrown);
                                                            }
                                                        });
                                                    } else {
                                                        jQuery.ajax({
                                                            url: ajaxurl,
                                                            data: {
                                                                "action":"bpc_checklist_update",
                                                                "check_id" : check_id
                                                            },
                                                            success:function(data) {
                                                                document.getElementById(label_id).style.background = "#1D2327";
                                                            },  
                                                            error: function(errorThrown){
                                                                window.alert(errorThrown);
                                                            }
                                                        });
                                                    }
                                                }
                                            </script>';         
                                        }                           
                                    } elseif ($marketing == 39){
                                        if($result->type == 6) {
                                            echo '
                                <div class="bpc_tab">
                                    <input class="bpc_input"  type="radio" id="rd' . $result->id . '" name="rd">
                                    <label '; if($result->does_comply == 0){echo 'class="bpc_tab-label"';} else {echo'class="bpc_tab-label_completed"';} echo ' for="rd' . $result->id . '" id="bpc_tab-label' . $result->id . '">' . $result->title . '</label>
                                    <div class="bpc_tab-content"><br>
                                        
                                        ' . $result->description . '
                                            ';
                                            if($result->type == 1 || $result->type == 5 || $result->type == 6 || $result->type == 7){
                                            
                                                echo '
                                                <input type="hidden" id="bpc_id" name="bpc_id" value="' . $result->id . '">
                                                <input type="checkbox" '; if($result->does_comply == 1){echo 'checked';} echo ' class="bpc_checkbox" id="bpc_checkbox" name="bpc_checkbox" onclick="validate(bpc_checkbox,' . $result->id . ')">';
                                                    
                                            } elseif($result->type == 2){
                                                echo '
                                                    <input type="hidden" id="bpc_id_url" name="bpc_id_url" value="' . $result->id . '">
                                                    <input type="text" id="input_field' . $result->id . '" name="input_field' . $result->id . '" placeholder="eg. https://' . $_SERVER['SERVER_NAME'] . '/?page_id=3" class="widefat"'; if($result->content != ''){ echo 'value="' . $result->content . '"';} echo'>
                                                    <button id="url_button" onclick="save_field(' . $result->id . ')">Save</button>
                                                    
                                                    
                                                    <script>                                                    
                                                        function save_field(check_id){
                                                            var result_id = "input_field" + check_id;
                                                            var input = document.getElementById(result_id).value;
                                                            var input_id = document.getElementById("bpc_id_url").value;
                                                            
                                                            jQuery.ajax({
                                                                type: "post",
                                                                cache: false,
                                                                dataType: "json",
                                                                url: ajaxurl,
                                                                data: {
                                                                    "action":"bpc_checklist_update_url",
                                                                    "check_id" : check_id,
                                                                    "input" : input
                                                                },
                                                                success:function(data) {
                                                                },  
                                                                error: function(errorThrown){
                                                                }
                                                            });
                                                            
                                                        }
                                                    </script>
                                                ';
                                            }
                                            echo'
                                    </div>
                                </div>
                                <script>
                                                
                                    function validate(el, check_id) {
                                        var label_id = "bpc_tab-label" + check_id;
                                        if (el.checked) {
                                            jQuery.ajax({
                                                url: ajaxurl,
                                                data: {
                                                    "action":"bpc_checklist_update",
                                                    "check_id" : check_id
                                                },
                                                success:function(data) {
                                                    document.getElementById(label_id).style.background = "#B7191A";
                                                },  
                                                error: function(errorThrown){
                                                    window.alert(errorThrown);
                                                }
                                            });
                                        } else {
                                            jQuery.ajax({
                                                url: ajaxurl,
                                                data: {
                                                    "action":"bpc_checklist_update",
                                                    "check_id" : check_id
                                                },
                                                success:function(data) {
                                                    document.getElementById(label_id).style.background = "#1D2327";
                                                },  
                                                error: function(errorThrown){
                                                    window.alert(errorThrown);
                                                }
                                            });
                                        }
                                    }
                                </script>';      
                                            }    
                                    }


                                if($result->type != 5 && $result->type != 6){
                                echo '
                                <div class="bpc_tab">
                                    <input class="bpc_input"  type="radio" id="rd' . $result->id . '" name="rd">
                                    <label '; if($result->does_comply == 0){echo 'class="bpc_tab-label"';} else {echo'class="bpc_tab-label_completed"';} echo ' for="rd' . $result->id . '" id="bpc_tab-label' . $result->id . '">' . $result->title . '</label>
                                    <div class="bpc_tab-content"><br>
                                        
                                        ' . $result->description . '
                                            ';
                                            if($result->type == 1 || $result->type == 5 || $result->type == 6 || $result->type == 7){
                                                
                                                    echo '
                                                        <input type="hidden" id="bpc_id" name="bpc_id" value="' . $result->id . '">
                                                        <input type="checkbox" '; if($result->does_comply == 1){echo 'checked';} echo ' class="bpc_checkbox" id="bpc_checkbox" name="bpc_checkbox" onclick="validate(bpc_checkbox,' . $result->id . ')">';
                                                
                                            } elseif($result->type == 2){
                                                echo '
                                                    <input type="hidden" id="bpc_id_url" name="bpc_id_url" value="' . $result->id . '">
                                                    <input type="text" id="input_field' . $result->id . '" name="input_field' . $result->id . '" placeholder="eg. https://' . $_SERVER['SERVER_NAME'] . '/?page_id=3" class="widefat"'; if($result->content != ''){ echo 'value="' . $result->content . '"';} echo'>
                                                    <button id="url_button" onclick="save_field(' . $result->id . ')">Save</button>
                                                    
                                                    
                                                    <script>                                                    
                                                        function save_field(check_id){
                                                            var result_id = "input_field" + check_id;
                                                            var input = document.getElementById(result_id).value;
                                                            var input_id = document.getElementById("bpc_id_url").value;
                                                            
                                                            jQuery.ajax({
                                                                type: "post",
                                                                cache: false,
                                                                dataType: "json",
                                                                url: ajaxurl,
                                                                data: {
                                                                    "action":"bpc_checklist_update_url",
                                                                    "check_id" : check_id,
                                                                    "input" : input
                                                                },
                                                                success:function(data) {
                                                                },  
                                                                error: function(errorThrown){
                                                                }
                                                            });
                                                            
                                                        }
                                                    </script>
                                                ';
                                            }
                                            echo'
                                    </div>
                                </div>
                                <script>
                                                
                                    function validate(el, check_id) {
                                        var label_id = "bpc_tab-label" + check_id;
                                        if (el.checked) {
                                            jQuery.ajax({
                                                url: ajaxurl,
                                                data: {
                                                    "action":"bpc_checklist_update",
                                                    "check_id" : check_id
                                                },
                                                success:function(data) {
                                                    document.getElementById(label_id).style.background = "#B7191A";
                                                },  
                                                error: function(errorThrown){
                                                    window.alert(errorThrown);
                                                }
                                            });
                                        } else {
                                            jQuery.ajax({
                                                url: ajaxurl,
                                                data: {
                                                    "action":"bpc_checklist_update",
                                                    "check_id" : check_id
                                                },
                                                success:function(data) {
                                                    document.getElementById(label_id).style.background = "#1D2327";
                                                },  
                                                error: function(errorThrown){
                                                    window.alert(errorThrown);
                                                }
                                            });
                                        }
                                    }
                                </script>';
                                    }
                                }
                            
                            }








                            echo'
                        </div>
                    </div>
                </div>
        </div>
    ';
}

function bpc_checklist_update() {

    if ( isset($_REQUEST) ) {

        global $wpdb;
        $table_name = $wpdb->prefix . 'be_popia_compliant_checklist';

        $check_id = $_REQUEST["check_id"];
        
        $results = $wpdb->get_results("SELECT * FROM $table_name WHERE id = $check_id");
        foreach($results as $result){
            if($result->does_comply == 0){
                $wpdb->update( $table_name, array( 'does_comply' => 1),array('id'=>$check_id)); 
            } else{
                $wpdb->update( $table_name, array( 'does_comply' => 0),array('id'=>$check_id));  
            }
        }
    }

   die();
}

add_action( 'wp_ajax_bpc_checklist_update', 'bpc_checklist_update' ); 

function bpc_checklist_update_url() {

    if ( isset($_REQUEST) ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'be_popia_compliant_checklist';

        $check_id = $_REQUEST["check_id"];
        $input = $_REQUEST["input"];
        
        if($input != ''){
            $wpdb->update( $table_name, array( 'content' => $input, 'does_comply' => 1),array('id'=>$check_id)); 
        } else {
            $wpdb->update( $table_name, array( 'content' => $input, 'does_comply' => 0),array('id'=>$check_id)); 
        }
    }

   die();
}

add_action( 'wp_ajax_bpc_checklist_update_url', 'bpc_checklist_update_url' ); 

function bpc_checklist_update_compliance() {

    if ( isset($_REQUEST) ) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'be_popia_compliant_checklist';

        $needComms = $wpdb->get_var( $wpdb->prepare(
            " SELECT does_comply FROM $table_name WHERE id = 2")
        );
        
        $needMarketing = $wpdb->get_var( $wpdb->prepare(
            " SELECT does_comply FROM $table_name WHERE id = 3")
        );

        
        
        if($needComms == 1 && $needMarketing == 0) {

            $wpdb->get_results("SELECT * FROM $table_name WHERE (type < 8 AND type > 0) AND does_comply = 1 AND (id != 3) AND (id != 59) AND is_active = 1");
            $rowcount = $wpdb->num_rows;

            $wpdb->get_results("SELECT * FROM $table_name WHERE (type < 8 AND type > 0) AND (id != 3) AND (id != 59) AND is_active = 1");
            $rowcount2 = $wpdb->num_rows;

        } elseif($needComms == 0 && $needMarketing == 1) {

            $wpdb->get_results("SELECT * FROM $table_name WHERE (type < 8 AND type > 0) AND does_comply = 1 AND (id != 2) AND (id != 58) AND is_active = 1");
            $rowcount = $wpdb->num_rows;

            $wpdb->get_results("SELECT * FROM $table_name WHERE (type < 8 AND type > 0) AND (id != 2) AND (id != 58) AND is_active = 1");
            $rowcount2 = $wpdb->num_rows;

        } elseif($needComms == 1 && $needMarketing == 1) {

            $wpdb->get_results("SELECT * FROM $table_name WHERE (type < 8 AND type > 0) AND does_comply = 1 AND is_active = 1");
            $rowcount = $wpdb->num_rows;

            $wpdb->get_results("SELECT * FROM $table_name WHERE (type < 8 AND type > 0) AND is_active = 1");
            $rowcount2 = $wpdb->num_rows;

        } elseif($needMarketing == 0 && $needComms == 0) {
            $wpdb->get_results("SELECT * FROM $table_name WHERE (type < 8 AND type > 0) AND does_comply = 1 AND (id != 2) AND (id != 3) AND (id != 58) AND (id != 59) AND is_active = 1");
            $rowcount = $wpdb->num_rows;

            $wpdb->get_results("SELECT * FROM $table_name WHERE (type < 8 AND type > 0) AND (id != 2) AND (id != 3) AND (id != 58) AND (id != 59) AND is_active = 1");
            $rowcount2 = $wpdb->num_rows;
        }

        $rowcount = ($rowcount / $rowcount2) * 100;
        
        echo $rowcount;
    }

   die();
}

add_action( 'wp_ajax_bpc_checklist_update_compliance', 'bpc_checklist_update_compliance' ); 




// adding styles and scripts
function be_popia_compliant_cookie_enqueue_scripts() {
    // load styles and script for plugin only if cookies are not accepted
    if ( !isset( $_COOKIE['cookie-accepted'] ) ) {
        wp_enqueue_style( 'styles', plugins_url( 'styles.css', __FILE__ ) );
        wp_enqueue_script( 'be_popia_compliant_cookie_script', plugins_url( 'public/js/be_popia_compliant_cookie_script.js', __FILE__ ), array( 'jquery' ), 1.0, true );
        wp_localize_script( 'be_popia_compliant_cookie_script', 'be_popia_compliant_cookie_script_ajax_object',
            array( 
                'ajax_url' => admin_url( 'admin-ajax.php' ),
            )
        );
    }
}
add_action( 'wp_enqueue_scripts', 'be_popia_compliant_cookie_enqueue_scripts' );


// setting cookie - this function must be called before html code is displayed
function be_popia_compliant_cookie_set_cookie() {
    // make action when cookie accept button was clicked
    if ( isset( $_POST['cookie-accept-button'] ) ) {
        $domain = explode( 'https://', site_url() );
        if ( ! is_ssl() ) {
            $domain = explode( 'http://', site_url() );
        }
        $domain = explode('/', $domain[1], 2);
        $path = empty($domain[1]) ? "/" : "/".$domain[1]."/";
        setcookie( sanitize_key( 'cookie-accepted' ), 1, time()+3600*24*14, $path );
        $current_url = is_ssl() ? esc_url('https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']) : esc_url('http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
        wp_safe_redirect( $current_url );
        exit;
    }

    // make action when privacy policy button was clicked
    if ( isset( $_POST['cookie-privacy-policy'] ) ) {
        $privacy_policy = get_privacy_policy_url();
        if ( empty($privacy_policy) ) {
            $privacy_policy = get_home_url().'/privacy-policy';
        }
        wp_safe_redirect( $privacy_policy );
        exit;
    }
}
add_action('init', 'be_popia_compliant_cookie_set_cookie');

// setting cookie - ajax version
function be_popia_compliant_cookie_set_cookie_ajax() {
    // make action when cookie accept button was clicked - without page reloading
    $domain = explode( 'https://', site_url() );
    if ( ! is_ssl() ) {
        $domain = explode( 'http://', site_url() );
    }
    $domain = explode('/', $domain[1], 2);
    $path = empty($domain[1]) ? "/" : "/".$domain[1]."/";
    // setcookie( sanitize_key( 'cookie-accepted' ), 1, time()+3600*24*14, $path, $domain[0] );     // when add $domain[0], then cookies are setted to domain and all subdomains (in console domain is visible with dot - .domain)
    setcookie( sanitize_key( 'cookie-accepted' ), 1, time()+3600*24*14, $path );
    echo json_encode("cookies-added");
    die();
}
add_action( 'wp_ajax_set_cookie_ajax', 'be_popia_compliant_cookie_set_cookie_ajax' );
add_action( 'wp_ajax_nopriv_set_cookie_ajax', 'be_popia_compliant_cookie_set_cookie_ajax' );


// display cookie notice if cookie info is not set
function be_popia_compliant_cookie_display_cookie_notice() {    
    if ( !isset( $_COOKIE['cookie-accepted'] ) ) {
        add_action('wp_footer', 'be_popia_compliant_cookie_display_cookie_info');
    }
}
add_action( 'init', 'be_popia_compliant_cookie_display_cookie_notice');


// allowed html code in plugin message
function be_popia_compliant_cookie_allowed_html() {
    return array(
        'a' => array(
            'href' => array(),
            'title' => array(),
            'class' => array()
        ),
        'br' => array(),
        'em' => array(),
        'strong' => array(),
        'span' => array(
            'class' => array()
        ),
    );
}

// displaying cookie info on page
function be_popia_compliant_cookie_display_cookie_info() {
    $cookie_message = get_option( "be_popia_compliant_cookie-field1-cookie-message", 'We use cookies to improve your experience on our website. By browsing this website, you agree to our use of cookies' );
    $cookie_info_button = get_option( "be_popia_compliant_cookie-field3-cookie-button-text", 'Accept Cookies' );
    $show_policy_privacy = get_option( "be_popia_compliant_cookie-field2-checkbox-privacy-policy", false );
    $background_color = get_option( "be_popia_compliant_cookie-field5-background-color", '#B61F20' );
    $text_color = get_option( "be_popia_compliant_cookie-field6-text-color", '#f5f5f5' );
    $button_background_color = get_option( "be_popia_compliant_cookie-field7-button-background-color", '#f5f5f5' );
    $button_text_color = get_option( "be_popia_compliant_cookie-field8-button-text-color", '#000000' );
    $cookie_info_placemet = get_option( "be_popia_compliant_cookie-field4-cookie-plugin-placement", 'bottom' );
    $allowed_html = be_popia_compliant_cookie_allowed_html();
?>
    <div class="bpc-cookie-info-container" style="<?php echo 'background-color: '.esc_attr( $background_color ).'; '.esc_attr( $cookie_info_placemet ).': 0' ?>" id="bpc-cookie-info-container">
       <!-- remove action method!!! -->
        <form method="post" id="cookie-form"> 
            <p class="bpc-cookie-info" style="<?php echo 'color: '.esc_attr( $text_color ) ?>"><?php echo wp_kses( $cookie_message, $allowed_html ); ?></p>
            <div class="bpc-buttons">
            <button type="submit" name="cookie-accept-button" class="bpc-cookie-accept-button" id="cookie-accept-button" style="<?php echo 'background-color: '.esc_attr( $button_background_color ) ?>" ><span class="button-text" style="<?php echo 'color: '.esc_attr( $button_text_color ) ?>"><?php echo esc_html( $cookie_info_button ); ?></span></button>
            <?php if ( $show_policy_privacy ) { ?>
            <button type="submit" name="cookie-privacy-policy" class="bpc-cookie-privacy-policy" id="cookie-privacy-policy" style="<?php echo 'background-color: '.esc_attr( $button_background_color ) ?>"><span class="button-text" style="<?php echo 'color: '.esc_attr( $button_text_color ) ?>"><?php esc_html_e( 'Privacy Policy', 'be_popia_compliant_cookie' ) ?></span></button>
            <?php } ?>
            </div>
        </form>
    </div>
<?php
}


function bpc_admin_menus() {
    global $wpdb;

    $table_name = $wpdb->prefix . 'be_popia_compliant_admin';
    $result_api = $wpdb->get_row("SELECT value FROM $table_name WHERE id = 1");
    $result_company = $wpdb->get_row("SELECT value FROM $table_name WHERE id = 2");
    $result_suspended = $wpdb->get_row("SELECT value FROM $table_name WHERE id = 3");

    $top_menu_item = 'be_popia_compliant';

    add_menu_page( '', 'POPIA Compliance', 'manage_options', 'be_popia_compliant', 'bpc_dashboard', 'dashicons-yes' );

    // if((isset($result_api->value) && $result_api->value !='') && (isset($result_company->value) && $result_company->value != '') && $result_suspended->value != 1){

    // } else {
        add_submenu_page ( $top_menu_item, '', 'POPIA Checklist', 'manage_options', 'bpc_checklist', 'bpc_dashboard_checklist');
    // }

    add_submenu_page ( $top_menu_item, '', 'Cookie Settings', 'manage_options', 'privacy-policy', 'be_popia_compliant_cookie_page_html_content');

    add_submenu_page ( $top_menu_item, '', '<a href="https://bepopiacompliant.co.za" style="font-weight: normal;margin: -13px 0px 0px 0px;" target="_blank">Go Pro</a>', 'manage_options', 'go-pro', 'bpc_dashboard_go_pro');
}
add_action('admin_menu', 'bpc_admin_menus');

// add_action( 'admin_menu', 'be_popia_compliant_cookie_add_new_page' );
// function be_popia_compliant_cookie_add_new_page() {
//     add_submenu_page(
//         'tools.php',                                             // $parent_slug
//         'Cookie Settings',                                       // $page_title
//         'Cookie Settings',                                       // $menu_title
//         'manage_options',                                       // $capability
//         'privacy-policy',                                       // $menu_slug
//         'be_popia_compliant_cookie_page_html_content'           // $function
//     );
// }


// adding settings and sections to page in admin menu
function be_popia_compliant_cookie_add_new_settings() {
    // register settings
    $configuration_settins_field1_arg = array(
        'type' => 'string',
        'sanitize_callback' => 'be_popia_compliant_cookie_sanitize_textarea_field',
        'default' => 'We use cookies to improve your experience on our website. By browsing this website, you agree to our use of cookies'
    );
    $configuration_settins_field2_arg = array(
        'type' => 'boolean',
        'sanitize_callback' => 'be_popia_compliant_cookie_sanitize_checkbox',
        'default' => false
    );
    $configuration_settins_field3_arg = array(
        'type' => 'string',
        'sanitize_callback' => 'be_popia_compliant_cookie_sanitize_input_field',
        'default' => 'Accept Cookies'
    );
    $configuration_settins_field4_arg = array(
        'type' => 'string',
        'sanitize_callback' => 'be_popia_compliant_cookie_sanitize_input_field',
        'default' => 'bottom'
    );
    $layout_settins_field1_arg = array(
        'type' => 'string',
        'sanitize_callback' => 'be_popia_compliant_cookie_sanitize_color_input',
        'default' => '#444546'
    );
    $layout_settins_field2_arg = array(
        'type' => 'string',
        'sanitize_callback' => 'be_popia_compliant_cookie_sanitize_color_input',
        'default' => '#ffffff'
    );
    $layout_settins_field3_arg = array(
        'type' => 'string',
        'sanitize_callback' => 'be_popia_compliant_cookie_sanitize_color_input',
        'default' => '#dcf1ff'
    );
    $layout_settins_field4_arg = array(
        'type' => 'string',
        'sanitize_callback' => 'be_popia_compliant_cookie_sanitize_color_input',
        'default' => '#000000'
    );
    register_setting( 'jl_options', 'be_popia_compliant_cookie-field1-cookie-message', $configuration_settins_field1_arg);     // option group, option name, args
    register_setting( 'jl_options', 'be_popia_compliant_cookie-field2-checkbox-privacy-policy', $configuration_settins_field2_arg);
    register_setting( 'jl_options', 'be_popia_compliant_cookie-field3-cookie-button-text', $configuration_settins_field3_arg);
    register_setting( 'jl_options', 'be_popia_compliant_cookie-field4-cookie-plugin-placement', $configuration_settins_field4_arg);
    register_setting( 'jl_options', 'be_popia_compliant_cookie-field5-background-color', $layout_settins_field1_arg);
    register_setting( 'jl_options', 'be_popia_compliant_cookie-field6-text-color', $layout_settins_field2_arg);
    register_setting( 'jl_options', 'be_popia_compliant_cookie-field7-button-background-color', $layout_settins_field3_arg);
    register_setting( 'jl_options', 'be_popia_compliant_cookie-field8-button-text-color', $layout_settins_field4_arg);

    // adding sections
    add_settings_section( 'be_popia_compliant_cookie_section_1_configuration', 'Configuration', null, 'jl-slug' );  
    // id (Slug-name to identify the section), title, callback, page slug
    add_settings_section( 'be_popia_compliant_cookie_section_2_layout', 'Layout', null, 'jl-slug-2' );

    // adding fields for section
    add_settings_field( 'field-1-cookie-message', 'Cookie Message', 'be_popia_compliant_cookie_field_1_callback', 'jl-slug', 'be_popia_compliant_cookie_section_1_configuration' );       
    // id (Slug-name to identify the field), title, callback, slug-name of the settings page on which to show the section, section, args (attr for field)
    add_settings_field( 'field-2-privacy-policy-button', 'Display Privacy Policy Button', 'be_popia_compliant_cookie_field_2_callback', 'jl-slug', 'be_popia_compliant_cookie_section_1_configuration' );
    add_settings_field( 'field-3-cookie-button-text', 'Cookie Button Text', 'be_popia_compliant_cookie_field_3_callback', 'jl-slug', 'be_popia_compliant_cookie_section_1_configuration' );
    add_settings_field( 'field-4-cookie-plugin-placement', 'Cookie info placement', 'be_popia_compliant_cookie_field_4_callback', 'jl-slug', 'be_popia_compliant_cookie_section_1_configuration' );
    add_settings_field( 'field-5-cookie-background-color', 'Background color', 'be_popia_compliant_cookie_field_5_callback', 'jl-slug-2', 'be_popia_compliant_cookie_section_2_layout' );
    add_settings_field( 'field-6-cookie-text-color', 'Text color', 'be_popia_compliant_cookie_field_6_callback', 'jl-slug-2', 'be_popia_compliant_cookie_section_2_layout' );
    add_settings_field( 'field-7-cookie-button-background-color', 'Button background color', 'be_popia_compliant_cookie_field_7_callback', 'jl-slug-2', 'be_popia_compliant_cookie_section_2_layout' );
    add_settings_field( 'field-8-cookie-button-text-color', 'Button text color', 'be_popia_compliant_cookie_field_8_callback', 'jl-slug-2', 'be_popia_compliant_cookie_section_2_layout' );
}
add_action( 'admin_init', 'be_popia_compliant_cookie_add_new_settings' );


// field 1 - cookie message
function be_popia_compliant_cookie_field_1_callback() {
    echo '<textarea type="text" cols="50" rows="4" name="be_popia_compliant_cookie-field1-cookie-message" >'.esc_textarea( get_option( "be_popia_compliant_cookie-field1-cookie-message", 'We use cookies to improve your experience on our website. By browsing this website, you agree to our use of cookies' ) ).'</textarea>';
}

// field 2 - show privacy policy button
function be_popia_compliant_cookie_field_2_callback() {
    if ( get_option( "be_popia_compliant_cookie-field2-checkbox-privacy-policy", false ) ) {
        echo '<input type="checkbox" name="be_popia_compliant_cookie-field2-checkbox-privacy-policy" checked />';
        echo ' <a href="'.esc_url(admin_url()."options-privacy.php").'" style="margin-left: 20px">Set Privacy Policy Page</a>';
    } else {
        echo '<input type="checkbox" name="be_popia_compliant_cookie-field2-checkbox-privacy-policy" />';
    }
}

// field 3 - cookie button text
function be_popia_compliant_cookie_field_3_callback() {
    echo '<input type="text" name="be_popia_compliant_cookie-field3-cookie-button-text" value="'.esc_html( get_option( "be_popia_compliant_cookie-field3-cookie-button-text", 'Accept Cookies' ) ).'" />';
}

// field 4 - cookie info placement
function be_popia_compliant_cookie_field_4_callback() {
    $isChecked = get_option( "be_popia_compliant_cookie-field4-cookie-plugin-placement", 'bottom' );
    ?>
    <input type="radio" name="be_popia_compliant_cookie-field4-cookie-plugin-placement" value="top" <?php echo esc_html( $isChecked ) === 'top' ? "checked" : null ?> /> Top <br><br>
    <input type="radio" name="be_popia_compliant_cookie-field4-cookie-plugin-placement" value="bottom" <?php echo esc_html( $isChecked ) === 'bottom' ? "checked" : null ?> /> Bottom
    <?php
}

// field 5 - background color
function be_popia_compliant_cookie_field_5_callback() {
    echo '<input type="color" name="be_popia_compliant_cookie-field5-background-color" value="'.esc_html( get_option( "be_popia_compliant_cookie-field5-background-color", '#b61f20' ) ).'" />';
}

// field 6 - text color
function be_popia_compliant_cookie_field_6_callback() {
    echo '<input type="color" name="be_popia_compliant_cookie-field6-text-color" value="'.esc_html( get_option( "be_popia_compliant_cookie-field6-text-color", '#f5f5f5' ) ).'" />';
}

// field 7 - button background color
function be_popia_compliant_cookie_field_7_callback() {
    echo '<input type="color" name="be_popia_compliant_cookie-field7-button-background-color" value="'.esc_html( get_option( "be_popia_compliant_cookie-field7-button-background-color", '#f5f5f5' ) ).'" />';
}

// field 8 - button text color
function be_popia_compliant_cookie_field_8_callback() {
    echo '<input type="color" name="be_popia_compliant_cookie-field8-button-text-color" value="'.esc_html( get_option( "be_popia_compliant_cookie-field8-button-text-color", '#000000' ) ).'" />';
}

// sanitize textarea
function be_popia_compliant_cookie_sanitize_textarea_field( $input ) {
    if ( isset( $input ) ) {
        $allowed_html = be_popia_compliant_cookie_allowed_html();
        $input = wp_kses( $input, $allowed_html );
    }
    return $input;
}

// sanitize input
function be_popia_compliant_cookie_sanitize_input_field( $input ) {
    if ( isset( $input ) ) {
        $input = sanitize_text_field( $input );
    }
    return $input;
}

// sanitize checkbox
function be_popia_compliant_cookie_sanitize_checkbox( $checked ) {
    return ( ( isset( $checked ) && true == $checked ) ? true : false );
}

// sanitize color input
function be_popia_compliant_cookie_sanitize_color_input( $input ) {
    if ( isset( $input ) ) {
        $input = sanitize_hex_color( $input );
    }
    return $input;
}


// adding content to menu page
function be_popia_compliant_cookie_page_html_content() {
    if ( ! current_user_can( 'manage_options' ) ) {
        ?>
        <div style="font-size: 20px; margin-top: 20px"> <?php echo esc_html_e( "You don't have permission to manage this page", "be_popia_compliant_cookie" ); ?> </div>
        <?php
        return;
    }

    ?>
    <div class="wrap">
        <h2><?php echo esc_html( 'Be POPIA Compliant Cookie Settings') ?></h2>
        <form action="options.php" method="post">
            <?php
            // outpus settings fields (without this there is error after clicking save settings button)
            settings_fields( 'jl_options' );                        // A settings group name. This should match the group name used in register_setting()
            // output setting sections and their fields
            do_settings_sections( 'jl-slug' );                      // The slug name of settings sections you want to output.
            echo "<hr>";
            do_settings_sections( 'jl-slug-2' );                      // The slug name of settings sections you want to output.
            // output save settings button
            submit_button( 'Save Settings', 'primary', 'submit', true );     // Button text, button type, button id, wrap, any other attribute
            ?>
        </form>
    </div>
    <?php
}



add_action('wp_footer', 'Be_POPIA_Compliant_echo_footer');
    		
function Be_POPIA_Compliant_echo_footer() {
    global $wpdb;
        
        $table_name = $wpdb->prefix . 'be_popia_compliant_checklist';

        $needComms = $wpdb->get_var( $wpdb->prepare(
            " SELECT does_comply FROM $table_name WHERE id = 2")
        );
        
        $needMarketing = $wpdb->get_var( $wpdb->prepare(
            " SELECT does_comply FROM $table_name WHERE id = 3")
        );

        
        
        if($needComms == 1 && $needMarketing == 0) {

            $wpdb->get_results("SELECT * FROM $table_name WHERE (type < 8 AND type > 0) AND does_comply = 1 AND (id != 3) AND (id != 59) AND is_active = 1");
            $rowcount = $wpdb->num_rows;

            $wpdb->get_results("SELECT * FROM $table_name WHERE (type < 8 AND type > 0) AND (id != 3) AND (id != 59) AND is_active = 1");
            $rowcount2 = $wpdb->num_rows;

        } elseif($needComms == 0 && $needMarketing == 1) {

            $wpdb->get_results("SELECT * FROM $table_name WHERE (type < 8 AND type > 0) AND does_comply = 1 AND (id != 2) AND (id != 58) AND is_active = 1");
            $rowcount = $wpdb->num_rows;

            $wpdb->get_results("SELECT * FROM $table_name WHERE (type < 8 AND type > 0) AND (id != 2) AND (id != 58) AND is_active = 1");
            $rowcount2 = $wpdb->num_rows;

        } elseif($needComms == 1 && $needMarketing == 1) {

            $wpdb->get_results("SELECT * FROM $table_name WHERE (type < 8 AND type > 0) AND does_comply = 1 AND is_active = 1");
            $rowcount = $wpdb->num_rows;

            $wpdb->get_results("SELECT * FROM $table_name WHERE (type < 8 AND type > 0) AND is_active = 1");
            $rowcount2 = $wpdb->num_rows;

        } elseif($needMarketing == 0 && $needComms == 0) {
            $wpdb->get_results("SELECT * FROM $table_name WHERE (type < 8 AND type > 0) AND does_comply = 1 AND (id != 2) AND (id != 3) AND (id != 58) AND (id != 59) AND is_active = 1");
            $rowcount = $wpdb->num_rows;

            $wpdb->get_results("SELECT * FROM $table_name WHERE (type < 8 AND type > 0) AND (id != 2) AND (id != 3) AND (id != 58) AND (id != 59) AND is_active = 1");
            $rowcount2 = $wpdb->num_rows;
        }
        
        $_SESSION['rowcount'] = $rowcount2;
        $_SESSION['rowcount2'] = $rowcount2;
        $rowcount = ($rowcount / $rowcount2) * 100;

    $table_name = $wpdb->prefix . 'be_popia_compliant_admin';
    $result_api = $wpdb->get_row("SELECT value FROM $table_name WHERE id = 1");
    $result_company = $wpdb->get_row("SELECT value FROM $table_name WHERE id = 2");
    $result_suspended = $wpdb->get_row("SELECT value FROM $table_name WHERE id = 3");
    if (isset( $_COOKIE['cookie-accepted'])){
        if(isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https'){
        
            if(((isset($result_api->value) && $result_api->value != '') && ((isset($result_company->value)) && $result_company->value != ''))){
                include_once(plugin_dir_path(__FILE__).'/includes/be-popia-compliant-completed.php');

            } elseif($rowcount == 100) {

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
                        padding: 1%;
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
                            <a href="' . $privacy .'" target="_blank"><span style="white-space:nowrap">PRIVACY POLICY</span></a> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <a href="' . $data .'"target="_blank"><span style="white-space:nowrap">DATA REQUESTS</span></a> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <a href="' . $parties .'" target="_blank"><span style="white-space:nowrap">RESPONSIBLE PARTIES</span></a> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <a href="https://bepopiacompliant.co.za/information_regulator" target="_blank"><span style="white-space:nowrap">INFORMATION REGULATOR</span></a> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        </div>
                    </div>
                </div>';
            }
        }
    }

}
