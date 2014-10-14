<?php

include( "config.php" );
include( "sendkit_example_content.php" );
include( "sendkit_client.php" );

/*
 * This script uses the Sendkit API to setup a mail template, a corresponding
 * mail stream and then uses the mail stream to send a few mails.
 *
 * In order to do so it performs the following steps:
 *
 *  1. Check for Recipient Fields, see createMissingRecipientFields()
 *  2. Check for Senders, see createMissingSenders()
 *  3. Create the mail template via an HTTP POST request, see createMailTemplate()
 *  4. Create the mail stream via an HTTP PUT request, see createMailStream()
 *  5. Send a recipient list via an HTTP POST on the mail stream resource.
 *
 * All the other features of the Sendkit API are not part of this example code.
 */

// initialize the script's configuration
$config = new Config();

if ( $config->username == "" || $config->apiKey == "" ) {
    echo "Please set your username and api key in the config.php".PHP_EOL;
    exit(1);
}

// Create a unique name for mail template and mail stream
// for safe re-running of the example on the same account
date_default_timezone_set('UTC');
$timestamp =  date("Ymd_Hms");
$mail_template_id = "sendkit_example_template_".$timestamp;
$mail_stream_id   = "sendkit_example_stream_".$timestamp;

// Prepare the input data
$template = new MailTemplateData();
$template->id = $mail_template_id;
$template->fields = array('EMAIL' => 'text', 'CUSTOMER_TYPE' => 'numeric', 'LANGUAGE' => 'text');
$template->sender = $config->sender;
$template->linkDomain = $config->linkDomain;
$template->subject = SendkitExampleContent::SUBJECT();
$template->htmlContent = SendkitExampleContent::HTML();
$template->textContent = SendkitExampleContent::TEXT();
$template->conditionalIncludes = SendkitExampleContent::CONDITIONAL_INCLUDES();

$stream = new MailStreamData();
$stream->id = $mail_stream_id;
$stream->templateID = $template->id;
$stream->active = "true";
$stream->description = "Test Stream";

// Create the client instance with the given config
$sendkitClient= new SendkitClient( $config );

// Create the mail template
$sendkitClient->createMailTemplate( $template );

// Create the mail stream
$sendkitClient->createMailStream( $stream );

// Send the mail stream with to a single recipient
$sendkitClient->send( $stream,
"EMAIL,CUSTOMER_TYPE,LANGUAGE\
john.doe@example.com,0,it" );

// Send to another bunch of recipients
$sendkitClient->send( $stream,
"EMAIL,CUSTOMER_TYPE,LANGUAGE\
john.doe@example.com,2,en\
jane.roe@example.com,4,de" );

?>
