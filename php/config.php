<?php
    class Config {

        // API access
        public $username =  "";
        public $apiKey =  "";
        public $baseURL =   "https://api.sendkit.com/v3";

        // account settings
        public $linkDomain = "response.sendkit.com";
        public $sender =      array(
          'id' => 'default', 'name' => 'Default Sender', 'address' => 'mail@response.sendkit.com'
        );
    }
?>
