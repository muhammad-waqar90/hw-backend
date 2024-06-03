<?php

namespace App\Traits;

trait SanitizeRequestTrait
{
    /**
     * Rules
     * Allowed Characters
     *  - AlphaNumeric
     *  - Allowed punctuation , ; : . - _ / ?
     */
    private $sanitizeTextRegex = "/[^a-zA-Z0-9_,:?; .\-\/]/s";

    /**
     * Rules
     * Allowed Characters
     *  - AlphaNumeric arabic characters
     *  - Allowed punctuation , ; : . - _ / ? \n \
     *  - < > & ! ` ~ ' " @ # $ % ^ & * +
     *  ( ) { } [ ]| =
     */
    private $sanitizeRichTextRegex = "/[^\x{0600}-\x{06ff}\x{0750}-\x{077f}\x{fb50}-\x{fbc1}\x{fbd3}-\x{fd3f}\x{fd50}-\x{fd8f}\x{fd50}-\x{fd8f}\x{fe70}-\x{fefc}\x{FDF0}-\x{FDFD}\sA-Za-z0-9_,:; .\n?\/\\!'\"`~@#$%^&*+(){}[\]|><=-]/u";

    /**
     * Non sanitize request params list
     *
     * --------------------------------
     * Hashed and stored to Database
     * --------------------------------
     * password
     * password_confirmation
     * current_password
     *
     * ----------------------------------
     * Laravel default email validation
     * ----------------------------------
     * email
     * parentEmailAddress
     *
     * ----------------------------------
     * Laravel default URL validation
     * ----------------------------------
     * url
     * [*]Url
     */
    private $whiteListKeys = [
        'password',
        'password_confirmation',
        'current_password',
        'email',
        'parentEmailAddress',
        'url',
        'facebookUrl',
        'snapchatUrl',
        'twitterUrl',
        'instagramUrl',
        'youtubeUrl',
        'pinterestUrl',
        'linkedinUrl',
        'transactionReceipt',
    ];

    /**
     * Text Area request params list
     */
    //TODO:: We added non textArea field name here as well. We should rename the variable;
    private $textAreaInputKeys = ['message', 'text', 'question', 'name'];

    /**
     * Rich text input allowed tags
     */
    private $richTextInputKeys = ['description', 'desc', 'answer'];

    private $whiteListTags = '
        <h2>,
        <h3>,
        <h4>,
        <p>,
        <strong>,
        <i>,
        <a>,
        <ul>,
        <li>,
        <ol>,
        <blockquote>,
        <table>,
        <tbody>,
        <tr>,
        <td>
    ';

    public function sanitizeTextInput($value)
    {
        $value = strip_tags($value);

        return preg_replace($this->sanitizeTextRegex, '', $value);
    }

    public function sanitizeRichTextInput($value)
    {
        $value = strip_tags($value, $this->whiteListTags);

        return preg_replace($this->sanitizeRichTextRegex, '', $value);
    }

    public function sanitizeTextAreaInput($value)
    {
        $value = strip_tags($value);

        return preg_replace($this->sanitizeRichTextRegex, '', $value);
    }

    public function sanitize($request)
    {
        array_walk_recursive($request, function (&$value, $key) {

            if ($value && ! in_array($key, $this->whiteListKeys)) {

                $value = in_array($key, $this->richTextInputKeys)
                    ? $this->sanitizeRichTextInput($value)
                    : (in_array($key, $this->textAreaInputKeys)
                        ? $this->sanitizeTextAreaInput($value)
                        : $this->sanitizeTextInput($value)
                    );

                $value = trim($value);
            }

            return $value;
        });

        return $request;
    }
}
