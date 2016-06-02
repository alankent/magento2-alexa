<?php

namespace AlanKent\Alexa\App;


class ResponseData
{
    // TODO https://developer.amazon.com/public/solutions/alexa/alexa-skills-kit/docs/alexa-skills-kit-interface-reference
    // Output Speech (PlainText or SSML markup); CARD (complex); reprompt(output speech)

    private $json;

    public function __construct() {
        $this->json = array();
        $this->json['shouldEndSession'] = true;
    }

    public function setResponseText($text){
        $this->json['outputSpeech'] = ['type'=>'PlainText', 'text'=>$text];
    }
    
    public function setResponseSsml($ssml) {
        $this->json['outputSpeech'] = ['type'=>'PlainText', 'text'=>$ssml];
    }

    public function setCardSimple($title, $content) {
        $this->json['card'] = ['type'=>'Simple', 'title'=>$title, 'content'=>$content];
    }

    public function setCardStandard($title, $text, $smallImageUrl = null, $largeImageUrl = null) {
        $this->json['card'] = ['type'=>'Standard', 'title'=>$title, 'text'=>$text];
        if ($smallImageUrl !== null || $largeImageUrl !== null) {
            $this->json['image'] = array();
        }
        if ($smallImageUrl !== null) {
            $this->json['image']['smallImageUrl'] = $smallImageUrl;
        }
        if ($largeImageUrl !== null) {
            $this->json['image']['largeImageUrl'] = $largeImageUrl;
        }
    }

    public function setRepromptText($text) {
        $this->json['reprompt'] = ['outputSpeech'=>['type'=>'PlainText', 'text'=>$text]];
    }

    public function setRepromptSsml($ssml) {
        $this->json['reprompt'] = ['outputSpeech'=>['type'=>'PlainText', 'text'=>$ssml]];
    }

    public function setShouldEndSession($endSession) {
        $this->json['shouldEndSession'] = $endSession;
    }
    
    public function toJson() {
        return $this->json;
    }
}