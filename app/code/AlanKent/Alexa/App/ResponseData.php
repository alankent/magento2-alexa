<?php

namespace AlanKent\Alexa\App;


/**
 * Used to collect response for the Alexa. This includes what to vocalize as plain text
 * or in "Speech Synthesis Markup Language" (SSML), what to display in the 'card' in the
 * Alexa app (can include an image), and reprompting text if Alexa is waiting for a reply.
 * See also https://developer.amazon.com/public/solutions/alexa/alexa-skills-kit/docs/alexa-skills-kit-interface-reference
 */
class ResponseData
{
    /** @var array JSON response encoded as associative array. */
    private $json;

    /**
     * ResponseData constructor.
     */
    public function __construct()
    {
        $this->json = ['shouldEndSession'=>true];
    }

    /**
     * Set simple (plain text) response text to return.
     * @param string $text The plain text for Alexa to read out.
     */
    public function setResponseText($text)
    {
        $this->json['outputSpeech'] = ['type'=>'PlainText', 'text'=>$text];
    }

    /**
     * Set response text in "Speech Synthesis Markup Language" (SSML) to return.
     * This allows richer control over pronunciation of the returned text.
     * https://developer.amazon.com/public/solutions/alexa/alexa-skills-kit/docs/speech-synthesis-markup-language-ssml-reference
     * @param string $ssml The marked up text.
     */
    public function setResponseSsml($ssml)
    {
        $this->json['outputSpeech'] = ['type'=>'PlainText', 'text'=>$ssml];
    }

    /**
     * Generate a simple card to display in the Alex app. Users can then both listen to
     * the spoken response and view greater detail inside the app for a response.
     * Newlines can be used to force line breaks.
     * https://developer.amazon.com/public/solutions/alexa/alexa-skills-kit/docs/providing-home-cards-for-the-amazon-alexa-app
     * @param string $title Title at top of card.
     * @param string $content Content of card.
     */
    public function setCardSimple($title, $content)
    {
        $this->json['card'] = ['type'=>'Simple', 'title'=>$title, 'content'=>$content];
    }

    /**
     * Generate a more sophisticated card to display in the Alexa app, including images.
     * See https://developer.amazon.com/public/solutions/alexa/alexa-skills-kit/docs/providing-home-cards-for-the-amazon-alexa-app
     * for information on supported image formats, dimensions, etc.
     * @param string $title The title to display at the top of the card.
     * @param string $text The text to display - use newline (\n) for line breaks.
     * @param string|null $smallImageUrl Optional URL for a small image (recommended size is 720w x 480h).
     * @param string|null $largeImageUrl Optional URL for a large image (recommended size is 1200w x 800h).
     */
    public function setCardStandard($title, $text, $smallImageUrl = null, $largeImageUrl = null)
    {
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

    /**
     * Set simple reprompt text. If the session is not ended and the user does not reply,
     * this text is spoken to remind the user that Alexa is waiting for a response.
     * @param string $text The text to speak if no response is heard, such as "I am sorry,
     * I did not hear you. Could you please repeat your choice?"
     */
    public function setRepromptText($text)
    {
        $this->json['reprompt'] = ['outputSpeech'=>['type'=>'PlainText', 'text'=>$text]];
    }

    /**
     * Set reprompt text in Speech Synthesis Markup Language (SSML).
     * @param string $ssml The marked up text.
     */
    public function setRepromptSsml($ssml)
    {
        $this->json['reprompt'] = ['outputSpeech'=>['type'=>'PlainText', 'text'=>$ssml]];
    }

    /**
     * By default, sessions will be ended after the current message unless this
     * setting is set to 'false'.
     * @param bool $endSession
     */
    public function setShouldEndSession($endSession) 
    {
        $this->json['shouldEndSession'] = $endSession;
    }

    /**
     * Return JSON markup of 'request' portion of Alexa response.
     * (Intended for internal usage only.)
     * @return array JSON markup encoded as associative array.
     */
    public function toJson()
    {
        return $this->json;
    }
}
