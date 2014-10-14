<?php

include( "rest_client.php" );

/*
 * Simple model for a mail stream.
 */
class MailStreamData {
  public $id = /*String*/ null;
  public $templateID = /*String*/ null;
  public $active = /*Boolean*/ null;
  public $description = "";

  public function toXML() {
    $xml = "
      <mail_stream
      id=\"$this->id\"
      mail_template=\"$this->templateID\"
      active=\"$this->active\"
      description=\"$this->description\"
      />
    ";
    return $xml;
  }
}

/*
 * Simple model for a mail template.
 */
class MailTemplateData {

  public $id = /*String*/ null;
  public $fields = /*Array*/ null;
  public $sender = /*Array*/ null;
  public $linkDomain = /*String*/ null;
  public $subject = /*String*/ null;
  public $htmlContent = /*String*/ null;
  public $textContent = /*String*/ null;
  public $conditionalIncludes = /*Array*/ null;

  private function fieldsXML() {
    $xml = "";
    foreach ($this->fields as $field => $type ) {
      $xml .= "<field name=\"$field\" type=\"$type\" />\n";
    }
    return $xml;
  }

  private function conditionalIncludesXML() {
    $xml = "";
    foreach ($this->conditionalIncludes as $ci ) {
      $xml .= $ci;
    }
    return $xml;
  }

  public function toXML() {
    $senderID = $this->sender['id'];

    $xml = "
      <mail_template>
        <properties>
          <from>$senderID</from>
          <encoding>UTF-8</encoding>
          <domain>$this->linkDomain</domain>
        </properties>
        <recipient_fields>
        {$this->fieldsXML()}
        </recipient_fields>
        <subject><![CDATA[".$this->subject."]]></subject>
        <html><![CDATA[".$this->htmlContent."]]></html>
        <text><![CDATA[".$this->textContent."]]></text>
        <conditional_includes>
        {$this->conditionalIncludesXML()}
        </conditional_includes>
      </mail_template>
";
      return $xml;
  }

}

/*
 * SendkitClient implements a very basic client for the Sendkit API.
 */
class  SendkitClient {

  private $_config = "";
  private $_base_url = "";

  private $_restclient = "";
  private $_restclientCSV = "";

  /*
   * Create a new SendkitClient Instance
   *
   * Parameters:
   *    mail_template_id - the unique name of the mail template
   *    mail_stream_id - the unique name of the mail stream
   *    config - an instance of Config, see config.php
   */
  function __construct( $config ) {

    $this->_config = $config;
    $this->_base_url = $this->_config->baseURL;

    $this->_restclient = new RESTClient(
      $this->_config->username, $this->_config->apiKey );
    $this->_restclientCSV = RESTClient::withContentType(
      $this->_config->username, $this->_config->apiKey, "text/csv" );
  }


  /*
   * Creates a new mail template.
   */
  function createMailTemplate( $templateData ) {

    $this->createSenderIfMissing( $templateData->sender );
    $this->createMissingRecipientFields( $templateData->fields );

    $xml = $templateData->toXML();
    $this->_restclient->doPost(
      $this->_base_url.'/mail_templates/'.$templateData->id,
      $xml
    );
    echo "Created mail template '".$templateData->id."'".PHP_EOL;
  }

  /*
   * Creates a new mail stream .
   */
  function createMailStream( $streamData ) {

    $xml = $streamData->toXML();
    $this->_restclient->doPut(
      $this->_base_url.'/mail_streams/'.$streamData->id,
      $xml
    );
    echo "Created mail stream'".$mail_stream_id."'".PHP_EOL;
  }

  /*
   * Send mails by pushing given recipient data to the stream.
   */
  function send( $streamData, $recipients ) {
    echo "Send mail stream '".$mail_stream_id."'".PHP_EOL;

    $sendResult = $this->_restclientCSV->doPost(
      $this->_base_url.'/mail_streams/'.$streamData->id,
      $recipients
    );

    echo "Result:".PHP_EOL;
    echo $sendResult.PHP_EOL;
  }

  /*
   * Verifies if all fields exist, if not, creates them
   */
  private function createMissingRecipientFields( $fields ) {
    $availableFields = $this->loadAvailableFields();

    //loop through the fields we want to use and check if they already exit, if not, add them
    foreach ( $fields as $name => $type ) {

      // When the field type wasn't specified in config.php,
      // the field name will be assigned to $type in the array.
      // We need to set the correct $name and $type.
      if ( is_numeric( $name ) && $type != 'numeric' ) {
        $name = $type;
        $type = 'text';
      }

      if ( !in_array( $name, array_keys( $availableFields ) ) )
        $this->addField( $name, $type );
    }
  }

  /*
   * Gets all the fields currently available for the account
   */
  private function loadAvailableFields() {

    $xml = $this->_restclient->doGet( $this->_base_url.'/recipient_fields' );

    //convert the output to a SimpleXML
    $xml = new SimpleXMLElement( $xml );

    foreach( $xml->children() as $child ) {
      $attrs = $child->attributes();
      $type = (string) $attrs['type'];
      $name = (string) $attrs['name'];
      $fields[$name] = $type;
    }

    return $fields;
  }

  /*
   * Add a new field to the account.
   */
  private function addField( $name, $type = 'text' ) {
    $xml = "
    <recipient_fields>
        <field name=\"$name\" type=\"$type\" />
    </recipient_fields>
    ";
    $this->_restclient->DoPost( $this->_base_url.'/recipient_fields', $xml );
    echo "Created Field '".$name."'".PHP_EOL;
  }

  /*
   * Verifies if the Sender already exists, if not, creates it
   */
  private function createSenderIfMissing( $sender ) {
    $availableSenders = $this->loadAvailableSenders();

    if( !in_array( $sender['id'], $availableSenders ) )
      $this->addSender( $sender );
  }

  /*
   * Loads all the senders currently available for the account
   */
  private function loadAvailableSenders() {
    $xml = $this->_restclient->doGet( $this->_base_url.'/senders' );

    //convert the output to a SimpleXMl
    $xml = new SimpleXMLElement( $xml);

    foreach( $xml->children() as $child) {
      $attrs = $child->attributes();
      $id = (string) $attrs['id'];

      $senders[] = $id;
    }

    return $senders;
  }


  /*
   * Adds a sender to the available senders for the account
   */
  private function addSender( $sender ) {
    $xml = "
        <sender>
            <name>{$sender['name']}</name>
            <address>{$sender['address']}</address>
        </sender>
    ";
    $this->_restclient->doPut( $this->_base_url.'/senders/'.$sender['id'], $xml );
    echo "Created sender '".$sender['id']."'".PHP_EOL;
  }
}

?>