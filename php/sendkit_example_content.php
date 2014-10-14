<?php

/*
 * SendkitExampleContent contains the example content
 */
class SendkitExampleContent {

  function SUBJECT() {
      return "This is your Sendkit API example mail";
  }

  function HTML() {
    return file_get_contents( "./content.html" );
  }

  function TEXT() {
    return file_get_contents( "./content.txt" );
  }

  public static $cond1 = "
    <conditional_include id=\"GREETING\">
      <cases>
        <case>
          <when><![CDATA[(LANGUAGE = \"it\")]]></when>
          <html>Ciao!</html>
          <text>Ciao!</text>
        </case>
        <case>
          <when><![CDATA[(LANGUAGE = \"de\")]]></when>
          <html>Hallo!</html>
          <text>Hallo!</text>
        </case>
        <case>
          <when><![CDATA[(LANGUAGE = \"sv\")]]></when>
          <html>Hej!</html>
          <text>Hej!</text>
        </case>
      </cases>
      <otherwise>
        <html>Hi!</html>
        <text>Hi!</text>
      </otherwise>
    </conditional_include>
  ";

  function CONDITIONAL_INCLUDES() {
    return array( self::$cond1 );
  }
}

?>
